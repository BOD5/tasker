<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomFieldDefinition extends Model
{
    /** @use HasFactory<\Database\Factories\CustomFieldDefinitionFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id', // Nullable для глобальних полів
        'name',
        'code',
        'type',
        'options',
        'is_required',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(TimeEntryCustomFieldValue::class);
    }
}
