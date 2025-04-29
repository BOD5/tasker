<?php

namespace App\Http\Requests;

use App\Models\CustomFieldDefinition;
use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreTimeEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Дозволяємо лише автентифікованим користувачам
        // І перевіряємо, чи користувач належить до обраної команди
        if (!Auth::check()) {
            return false;
        }
        $teamId = $this->input('team_id');
        if ($teamId) {
            // Перевіряємо активне членство користувача в обраній команді
            return Auth::user()->teams()->where('teams.id', $teamId)->exists();
        }
        // Якщо team_id не передано (хоча ми зробили його required), забороняємо
        // Або можна дозволити, якщо є логіка "особистих" записів без команди
        return false; // Робимо команду обов'язковою для створення запису
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'description' => 'required|string|max:65535',
            'team_id'     => 'required|integer|exists:teams,id',
            'task_id'     => 'nullable|integer|exists:tasks,id',
            'custom_fields' => 'nullable|array',
        ];

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
                if ($definition->is_required) {
                    $fieldRules[] = 'required';
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
                        $fieldRules[] = 'date';
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

        return $rules;
    }
}
