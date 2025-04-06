<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntryCustomFieldValue extends Model
{
    /** @use HasFactory<\Database\Factories\TimeEntryCustomFieldValueFactory> */
    use HasFactory;
    protected $fillable = [
        'time_entry_id',
        'custom_field_definition_id',
        'value',
    ];

    public function timeEntry(): BelongsTo
    {
        return $this->belongsTo(TimeEntry::class);
    }
    public function definition(): BelongsTo
    {
        return $this->belongsTo(CustomFieldDefinition::class, 'custom_field_definition_id');
    }

}
