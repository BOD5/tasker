<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'description',
    ];
    protected $casts = [
        'deleted_at' => 'datetime',
    ];
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
