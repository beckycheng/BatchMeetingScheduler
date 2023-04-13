<?php

namespace App\Http\Controllers;

use App\Mail\CreateMeetingMail;
use App\Models\Meeting;
use App\Models\Participant;
use App\Services\MeetingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class MeetingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        $meetings = Meeting::where('moderator', $user->id)
            ->orWhereHas('participants', fn($query) => $query->where('username', $user->name))
            ->get();
        return view('meeting.index', compact('meetings'));
    }

    public function choose(Meeting $meeting)
    {
        $this->authorize('choose', $meeting);
        return view('meeting.choose', compact('meeting'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', Meeting::class);
        return view('meeting.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', Meeting::class);

        $timeslots = collect($this->getTimeslots($request))
            ->map(fn($intervals) => $this->mergeIntervals($intervals))
            ->map(fn($intervals) => $this->splitIntervals($intervals, $request->duration))
            ->toArray();

        $meetingData = $this->validatedData($request, $timeslots);
        $meeting = auth()->user()->meetings()->create($meetingData);
        collect(explode(',', $request->participants))
            ->map(fn($participant) => $this->createParticipant($meeting, $participant))
            ->each(function ($participant) use ($meeting) {
                $email = "{$participant->username}@connect.polyu.hk";
                Mail::to($email)->later(now()->addSeconds(5), new CreateMeetingMail([
                    'meeting' => $meeting,
                    'student_id' => $participant->username,
                ]));
            });
        return redirect()->route('meeting.show', $meeting)
            ->with('success', 'The meeting has been created successfully.');
    }

    public function storeChoose(Request $request, Meeting $meeting)
    {
        $this->authorize('choose', $meeting);
        $participant = $meeting->participants->where('username', auth()->user()->name)->firstOrFail();
        $timeslots = $meeting->flattenTimeslots();
        $preferredTimeSlots = collect($request->input('preferred_time'));
        if (!$preferredTimeSlots->every(fn($time) => $timeslots->contains($time)))
        {
            abort(400, 'Invalid preferred times.');
        }
        $participant->update([
            'preferred_time' => $preferredTimeSlots->toArray(),
            'preferred_time_updated_at' => Carbon::now(),
        ]);
        return redirect()->route('meeting.show', $meeting)
            ->with('success', 'Your preferred times have been saved.');
    }

    public function random(Request $request, Meeting $meeting) {
        $this->authorize('edit', $meeting);
        MeetingService::randomSchedule($meeting);
        return redirect()->route('meeting.show', $meeting);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Meeting  $meeting
     * @return \Illuminate\Http\Response
     */
    public function show(Meeting $meeting)
    {
        $this->authorize('view', $meeting);
        $user = auth()->user();
        $meeting_role = $meeting->moderator === $user->id ? 'moderator' :
            ($meeting->participants->contains('username', $user->name) ? 'participant' : null);
        return view('meeting.show', compact('meeting', 'meeting_role'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Meeting  $meeting
     * @return \Illuminate\Http\Response
     */
    public function edit(Meeting $meeting)
    {
        $this->authorize('edit', $meeting);
        return view('meeting.edit', compact('meeting'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Meeting  $meeting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Meeting $meeting)
    {
        $this->authorize('edit', $meeting);


        $timeSlots = collect($request->input('timeslots'));
        $newSlots = collect($this->subtractTimes(
            collect($this->getTimeslots($request))
                ->map(fn($intervals) => $this->mergeIntervals($intervals)),
            $timeSlots->map(fn($times, $date) => $this->transformTimeSlots($times))
        ))->map(fn($intervals) => $this->splitIntervals($intervals, $meeting->duration));

        $mergedTimeSlots = $this->mergeTimeSlots(
            $timeSlots->toArray(),
            $newSlots->toArray()
        );

        $sendEmails = $request->has('send_emails');
        $meetingData = $this->validatedData($request, $mergedTimeSlots);
        $meetingData['status'] = 'Pending';
        $meeting->update($meetingData);

        $meeting->participants
            ->whereNull('scheduled_time')
            ->pluck('username')
            ->each(function ($username) use ($meeting, $sendEmails) {
                if ($sendEmails) {
                    $email = "{$username}@connect.polyu.hk";
                    Mail::to($email)->later(now()->addSeconds(5), new CreateMeetingMail([
                        'meeting' => $meeting,
                        'student_id' => $username,
                    ]));
                }
            });

        return redirect()->route('meeting.show', $meeting)
            ->with('success', 'The meeting has been updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Meeting  $meeting
     * @return \Illuminate\Http\Response
     */
    public function destroy(Meeting $meeting)
    {
        $this->authorize('delete', $meeting);
        $meeting->delete();
        return redirect()->route('meeting.index')
            ->with('success', 'Meeting deleted successfully.');
    }

    private function validatedData(Request $request, $timeslots): array
    {
        return $request->validate([
            'title' => 'required|max:100',
            'subject' => 'nullable',
            'deadline' => 'required|date',
            'duration' => 'required|max:65535',
            'participants' => 'required',
            'num_available_days' => 'required|min:1',
            'num_slots_per_day' => 'required|min:1',
        ]) + [
            'timeslots' => $timeslots,
            'moderator' => auth()->id(),
        ];
    }

    private function createParticipant(Meeting $meeting, string $username)
    {
        return Participant::create([
            'meeting_id' => $meeting->id,
            'username' => trim($username),
        ]);
    }

    private function getTimeslots(Request $request): array
    {
        $timeslots = [];
        $dayCount = $request->input('daycount');
        for ($i = 1; $i <= $dayCount; $i++) {
            $date = $request->input("day{$i}date");
            $startTimes = $request->input("day{$i}starttime");
            $endTimes = $request->input("day{$i}endtime");
            foreach ($startTimes as $index => $start) {
                $end = $endTimes[$index];
                $timeslots[$date][] = [
                    'start' => strtotime($start),
                    'end' => strtotime($end),
                ];
            }
        }
        ksort($timeslots);
        return $timeslots;
    }

    private function mergeIntervals(array $intervals): array
    {
        if (count($intervals) <= 1) {
            return $intervals;
        }
        usort($intervals, fn($a, $b) => $a['start'] <=> $b['start']);

        $merged = [];
        $lastMerged = $intervals[0];
        foreach ($intervals as $interval) {
            if ($interval['start'] <= $lastMerged['end']) {
                $lastMerged['end'] = max($lastMerged['end'], $interval['end']);
            } else {
                $merged[] = $lastMerged;
                $lastMerged = $interval;
            }
        }
        $merged[] = $lastMerged;
        return $merged;
    }

    private function splitIntervals(array $intervals, int $duration): array
    {
        if (empty($intervals) || $duration <= 0) {
            return [];
        }
        $subIntervals = [];
        foreach ($intervals as $interval) {
            $start = $interval['start'];
            $end = $interval['end'];
            while ($start + 60 * $duration <= $end) {
                $startTime = date('H:i', $start);
                $endTime = date('H:i', $start + 60 * $duration);
                $subIntervals[] = "${startTime}-${endTime}";
                $start += 60 * $duration;
            }
        }
        return $subIntervals;
    }

    private function subtractTimes($freeTimes, $busyTimes)
    {
        $result = [];
        foreach ($freeTimes as $date => $freeSlots) {
            if (!isset($busyTimes[$date])) {
                $result[$date] = $freeSlots;
                continue;
            }
            $busySlots = $busyTimes[$date];
            $slotsAfterSubtraction = [];
            foreach ($freeSlots as $freeSlot) {
                $freeStart = $freeSlot['start'];
                $freeEnd = $freeSlot['end'];
                $subtract = false;
                foreach ($busySlots as $busySlot) {
                    $busyStart = $busySlot['start'];
                    $busyEnd = $busySlot['end'];
                    if ($freeStart < $busyEnd && $freeEnd > $busyStart) {
                        $subtract = true;
                        if ($freeStart < $busyStart) {
                            $slotsAfterSubtraction[] = [
                                'start' => $freeStart,
                                'end' => $busyStart,
                            ];
                        }
                        $freeStart = $busyEnd;
                    }
                }
                if (!$subtract || $freeStart < $freeEnd) {
                    $slotsAfterSubtraction[] = [
                        'start' => $freeStart,
                        'end' => $freeEnd,
                    ];
                }
            }
            $result[$date] = $slotsAfterSubtraction;
        }
        return $result;
    }

    private function transformTimeSlots($times)
    {
        $res = [];
        foreach ($times as $time) {
            [$start, $end] = explode('-', $time);
            $res[] = ['start' => strtotime($start), 'end' => strtotime($end)];
        }
        return $res;
    }

    private function mergeTimeSlots(array $existing, array $new): array
    {
        $merged = array_merge_recursive($existing, $new);
        foreach ($merged as $date => $slots) {
            sort($slots);
            $merged[$date] = $slots;
        }
        return $merged;
    }
}
