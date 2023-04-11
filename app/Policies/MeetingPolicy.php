<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MeetingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->role->name === 'Administrator';
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Meeting  $meeting
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Meeting $meeting)
    {
        return $meeting->moderator === $user->id || $meeting->participants->contains('username', $user->name);
    }

    public function choose(User $user, Meeting $meeting)
    {
        $participant = $meeting->participants->where('username', '=', $user->name)->first();
        return $meeting->status == 'Pending' && $participant && $participant->scheduled_time === null;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->role->name === 'Teacher';
    }

    /**
     * Determine whether the user can edit models.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Meeting  $meeting
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function edit(User $user, Meeting $meeting)
    {
        return $user->id === $meeting->moderator;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Meeting  $meeting
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Meeting $meeting)
    {
        return $user->id === $meeting->moderator || $user->role->name === 'Administrator';
    }
}
