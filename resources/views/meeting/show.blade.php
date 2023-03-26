@extends('layouts.app')

@php
$participant = $meeting->participants->where('username', auth()->user()->name)->first();
$scheduledTimes = $meeting->participants
    ->whereNotNull('scheduled_time')
    ->pluck('scheduled_time', 'username')
    ->all();
@endphp

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @elseif (session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="card mb-2">
                <div class="card-header d-flex">
                    <div class="my-auto">{{ __('Meeting') }} ({{ __($meeting->id)}})</div>
                    <div class="ms-auto">
                        <div class="d-flex flex-row">
                            @if ($meeting_role == 'moderator')
                                <a href="{{ route('meeting.edit', $meeting) }}" class="btn btn-primary btn-sm mx-1">{{ __('Edit') }}</a>
                                <form action="{{ route('meeting.destroy', $meeting) }}" method="post">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you certain that you want to delete this meeting?')">{{ __('Delete') }}</button>
                                </form>
                            @elseif ($meeting->status == 'Pending' && $participant && $participant->scheduled_time === null)
                                <a class="btn btn-primary btn-sm" href="{{ route('meeting.choose', $meeting) }}">{{ __('Choose Time') }}</a>
                            @else
                                <button class="btn btn-danger btn-sm invisible">{{ __('Button') }}</button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <h5>Title: <span class="text-muted">{{ __($meeting->title) }}</span></h5>
                    <h5>Subject: <span class="text-muted">{{ $meeting->subject ?: __('(empty)') }}</span></h5>
                    <h5>Teacher: <span class="text-muted">{{ __($meeting->moderatorUser->name) }}</span></h5>
                    <h5>Duration: <span class="text-muted">{{ __($meeting->duration . ' minutes') }}</span></h5>
                    <h5>Deadline: <span class="text-muted">{{ __($meeting->deadline) }}</span></h5>
                    @if ($meeting_role == 'moderator')
                        <h5>Students:</h5>
                        <div class="row mb-2">
                            @foreach ($meeting->participants as $p)
                                <div class="col-md-4 mb-2">
                                    <div class="card{{ $p->scheduled_time ? ' text-bg-success' : ( $p->preferred_time ? ' text-bg-primary' : '') }}">
                                        <div class="card-body">
                                            <span class="card-text">{{ $p->username }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <h5>Time Slots:</h5>
                    <div class="row">
                        @foreach ($meeting->timeslots as $date => $times)
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $date }}</h5>
                                        <ul class="list-unstyled">
                                            @foreach ($times as $time)
                                                @if ($meeting_role == 'moderator' && $username = array_search($date . ' ' . $time, $scheduledTimes))
                                                    <li>{{ $time }} ({{ __($username) }})</li>
                                                @else
                                                    <li>{{ $time }}</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($participant && $scheduledTime = $participant->scheduled_time)
                        <h5>Scheduled Time:</h5>
                        <ul class="list-group">
                            <li class="list-group-item bg-green list-group-item-success">{{ $scheduledTime }}</li>
                        </ul>
                    @elseif ($participant && $preferredTimes = $participant->preferred_time ?? '')
                        <h5>Your Preferred Times:</h5>
                        <ul class="list-group">
                            @foreach ($preferredTimes as $time)
                                <li class="list-group-item">{{ $time }}</li>
                            @endforeach
                        </ul>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
