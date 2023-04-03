<?php

namespace App\Services;

use App\Models\Meeting;
use Illuminate\Support\Facades\DB;

class MeetingService
{
    /**
     * Schedule a meeting by finding available time slots that work for all participants.
     *
     * @param Meeting $meeting The meeting to schedule.
     * @return void
     */
    public static function schedule(Meeting $meeting): void
    {
        // Begin a database transaction to ensure atomicity.
        DB::transaction(function () use ($meeting) {
            // Retrieve a list of all eligible participants,
            // sorted by their most recently updated preferred time.
            $participants = $meeting->participants
                ->whereNull('scheduled_time')
                ->whereNotNull('preferred_time')
                ->sortBy('preferred_time_updated_at')
                ->all();

            // Keep track of scheduled time slots.
            $scheduledSlots = [];

            // Loop through each participant and attempt to schedule them for the first available time slot.
            foreach ($participants as $participant) {
                $preferredTimes = $participant->preferred_time;
                $scheduledTime = null;

                // Loop through each of the participant's preferred times.
                foreach ($preferredTimes as $time) {
                    // If the preferred time is not already scheduled, schedule it and break out of the loop.
                    if (!in_array($time, $scheduledSlots)) {
                        $scheduledTime = $time;
                        $scheduledSlots[] = $scheduledTime;
                        break;
                    }
                }
    
                // If a participant cannot be scheduled for any of their preferred times,
                // reset their `preferred_time` and `preferred_time_updated_at` to null.
                if ($scheduledTime === null) {
                    $participant->preferred_time = null;
                    $participant->preferred_time_updated_at = null;
                } else {
                    // Otherwise, set the participant's scheduled time.
                    $participant->scheduled_time = $scheduledTime;
                }
            
                $participant->save();
            }

            //  If all participants have been scheduled meeting times, set the meeting status to 'Completed'.
            if ($meeting->participants->whereNull('scheduled_time')->count() === 0) {
                $meeting->status = 'Completed';
            } else {
                // Otherwise, set the status to 'Confirmed'.
                $meeting->status = 'Confirmed';
            }
            $meeting->save();
        });
    }

    public static function randomSchedule(Meeting $meeting)
    {
        MeetingService::schedule($meeting);
        if ($meeting->status == 'Completed') {
            return;
        }

        DB::transaction(function () use ($meeting) {
            $participants = $meeting->participants
                ->whereNull('scheduled_time')
                ->shuffle();

            $scheduledSlots = $meeting->participants
                ->pluck('scheduled_time')
                ->filter();

            $availableTimeSlots = $meeting->flattenTimeslots()
                ->filter(fn($time, $idx) => !$scheduledSlots->contains($time))
                ->shuffle();

            foreach ($participants as $participant) {
                $scheduledTime = $availableTimeSlots->pop();
                if ($scheduledTime !== null) {
                    $scheduledSlots[] = $scheduledTime;
                    $participant->scheduled_time = $scheduledTime;
                    $participant->save();
                }
            }

            if ($meeting->participants->whereNull('scheduled_time')->count() === 0) {
                $meeting->status = 'Completed';
            } else {
                $meeting->status = 'Confirmed';
            }
            $meeting->save();
        });
    }
}