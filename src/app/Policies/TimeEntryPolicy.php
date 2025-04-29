<?php

namespace App\Policies;

use App\Models\TimeEntry;
use App\Models\User;

class TimeEntryPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function delete(User $user, TimeEntry $timeEntry): bool
    {
        return $user->id === $timeEntry->user_id;
    }
}
