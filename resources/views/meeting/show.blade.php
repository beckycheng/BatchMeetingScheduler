@extends('layouts.app')

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
                        @if ($meeting_role == 'moderator')
                            <form action="{{ route('meeting.destroy', $meeting) }}" method="post">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you certain that you want to delete this meeting?')">{{ __('Delete') }}</button>
                            </form>
                        @elseif ($meeting_role == 'participant')
                            <a class="btn btn-primary btn-sm" href="{{ route('meeting.choose', $meeting) }}">{{ __('Choose Time') }}</a>
                        @else
                            <button class="btn btn-danger btn-sm invisible">{{ __('Button') }}</button>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <h5>Title: <span class="text-muted">{{ __($meeting->title) }}</span></h5>
                    <h5>Subject: <span class="text-muted">{{ $meeting->subject ?: __('(empty)') }}</span></h5>
                    <h5>Teacher: <span class="text-muted">{{ __($meeting->moderatorUser->name) }}</span></h5>
                    <h5>Duration: <span class="text-muted">{{ __($meeting->duration . ' minutes') }}</span></h5>
                    <h5>Deadline: <span class="text-muted">{{ __($meeting->deadline) }}</span></h5>
                    <h5>Students:</h5>
                    <div class="row mb-2">
                        @foreach ($meeting->participants as $participant)
                            <div class="col-md-4 mb-2">
                                <div class="card">
                                    <div class="card-body">
                                        <span class="card-text">{{ $participant->username }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <h5>Time Slots:</h5>
                    <div class="row">
                        @foreach ($meeting->timeslots as $date => $times)
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $date }}</h5>
                                        <ul class="list-unstyled">
                                            @foreach ($times as $time)
                                                <li>{{ $time }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
