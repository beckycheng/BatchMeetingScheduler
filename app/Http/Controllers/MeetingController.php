<?php

namespace App\Http\Controllers;

use App\Mail\CreateMeetingMail;
use App\Models\Meeting;
use App\Models\Participant;
use Illuminate\Http\Request;
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
        $meetings = Meeting::with('participants')
            ->whereHas('participants', fn($query) => $query->where('username', $user->name))
            ->orWhere('moderator', $user->id)
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
        $participant->update(['preferred_time' => $preferredTimeSlots->toJson()]);
        return redirect()->route('meeting.show', $meeting)
            ->with('success', 'Your preferred times have been saved.');
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
        //
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
        //
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
}
