<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;

class ClassroomPolicy
{
    /**
     * Determine whether the user can manage grades/attendance for the classroom.
     */
    public function manage(User $user, Classroom $classroom): bool
    {
        // Admins may manage any classroom
        if ($user->role === 'admin') {
            return $classroom->status === 'active';
        }

        // Faculty may manage only their assigned classrooms and only if active
        if ($user->role === 'faculty') {
            return (int) $classroom->faculty_user_id === (int) $user->id && $classroom->status === 'active';
        }

        return false;
    }
}
