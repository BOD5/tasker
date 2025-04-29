<?php

namespace App\Http\Requests;

use App\Models\CustomFieldDefinition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateTimeEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $timeEntry = $this->route('timeEntry');
        return $timeEntry && Auth::id() === $timeEntry->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'description' => 'required|string|max:65535',
            'team_id'     => [
                'required',
                'integer',
                Rule::exists('teams', 'id')->where(function ($query) {
                    $query->whereIn('id', Auth::user()->teams()->pluck('teams.id'));
                }),
            ],
            'task_id'     => 'nullable|integer|exists:tasks,id',
            // Робимо дати необов'язковими для цього запиту
            'started_at'  => 'nullable|date', // Приймаємо UTC ISO 8601, який надсилає фронтенд
            'ended_at'    => 'nullable|date|after_or_equal:started_at',
            'custom_fields' => 'nullable|array',
        ];

        // --- Динамічна валідація кастомних полів (залишаємо як є) ---
        $teamId = $this->input('team_id');
        $customFieldsInput = $this->input('custom_fields', []);

        if ($teamId && is_array($customFieldsInput)) {
            $definitions = CustomFieldDefinition::query()
                ->where(function ($q) use ($teamId) {
                    $q->whereNull('team_id')->orWhere('team_id', $teamId);
                })
                ->get();

            foreach ($definitions as $definition) {
                $fieldRules = [];
                if (array_key_exists($definition->code, $customFieldsInput) || $definition->is_required) {
                    if ($definition->is_required) {
                        $fieldRules[] = 'present';
                        if ($definition->type !== 'boolean') {
                            $fieldRules[] = 'nullable';
                        } else {
                            $fieldRules[] = 'required';
                        }
                    } else {
                        $fieldRules[] = 'nullable';
                    }

                    switch ($definition->type) {
                        case 'text':
                            $fieldRules[] = 'string';
                            $fieldRules[] = 'max:65535';
                            break;
                        case 'number':
                            $fieldRules[] = 'numeric';
                            break;
                        case 'date':
                            $fieldRules[] = 'date_format:Y-m-d';
                            break;
                        case 'boolean':
                            $fieldRules[] = 'boolean';
                            break;
                        case 'select':
                            $fieldRules[] = 'string';
                            if (!empty($definition->options)) {
                                $fieldRules[] = Rule::in($definition->options);
                            }
                            break;
                    }
                    $rules['custom_fields.' . $definition->code] = $fieldRules;
                }
            }
        }
        return $rules;
    }
}
