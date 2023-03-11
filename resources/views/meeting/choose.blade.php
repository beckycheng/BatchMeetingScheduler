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
                </div>

                <div class="card-body">
                    <h2 class="text-poly mb-2">
                        {{ __('Choose Time') }}
                        <br>
                        <span class="h5 text-muted">{{ __('Please select time slots by your preferences') }}</span>
                    </h2>

                    <div class="card">
                        <div class="card-body">
                            <h5>Title: <span class="text-muted">{{ __($meeting->title) }}</span></h5>
                            <h5>Subject: <span class="text-muted">{{ $meeting->subject ?: __('(empty)') }}</span></h5>
                            <h5>Teacher: <span class="text-muted">{{ __($meeting->moderatorUser->name) }}</span></h5>
                            <h5>Duration: <span class="text-muted">{{ __($meeting->duration) }}</span></h5>
                            <h5>Deadline: <span class="text-muted">{{ __($meeting->deadline) }}</span></h5>
                            <h5>Students: <span class="text-muted">{{ __(join(', ', array_column($meeting->participants->toArray(), 'username'))) }}</span></h5>
                        </div>
                    </div>

                    <div class="row my-3">
                        <div class="col-md-6 mb-3">
                            <label for="days">Number of available days for selection in time slots:</label>
                            <input type="number" name="num_available_days" class="form-control" value="{{$meeting->num_available_days}}" disabled readonly />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="times">Number of time slots that can be selected per day:</label>
                            <input type="number" name="num_available_days" class="form-control" value="{{$meeting->num_slots_per_day}}" disabled readonly />
                        </div>
                    </div>

                    <form id="meeting-time-slots" action="">
                        @csrf
                        @foreach ($meeting->timeslots as $date => $times)
                            <div class="my-3">
                                <label for="date{{ __($date) }}" class="form-label">{{ __('Date') }}: {{ __($date) }}</label><br>
                                @foreach ($times as $time)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="{{ __($date) }}" value="{{ __($time) }}">
                                        <label class="form-check-label" for="{{ __($date) }}-{{ __($time) }}">{{ __($time) }}</label>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        <div class="row justify-content-center m-2">
                            <div class="col-2 text-center"><button type="reset" class="btn btn-danger">Reset</button></div>
                            <div class="col-2 text-center"><button type="submit" class="btn btn-primary">Next</button></div>
                        </div>
                    </form>

                    <form style="display: none;" id="meeting-time-order" action="{{ route('meeting.store-choose', $meeting) }}" method="post">
                        @csrf
                        <div class="my-3">
                            <div class="h5 m-2">Sort selected timeslots by priority (from high to low)</div>
                            <ul id="sortable-timeslots" class="list-group">
                            </ul>
                            <div class="row justify-content-center m-2">
                                <div class="col-2 text-center"><button type="reset" class="btn btn-danger">Back</button></div>
                                <div class="col-2 text-center"><button type="submit" class="btn btn-primary">Submit</button></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(() => {
        $('#meeting-time-slots').submit((e) => {
            const selectedTime = $('#meeting-time-slots').serializeArray()
                .filter(v => !isNaN(Date.parse(v.name)))
                .map(v => [v.name, v.value]);

            if ($("input[type='checkbox']:checked").length > 0) {
                $('#meeting-time-slots').hide();
                selectedTime.forEach((timeSlot) => {
                    const val = timeSlot[0] + ' ' + timeSlot[1];
                    const newTimeSlot = $('<li>').addClass('list-group-item').text(val);
                    const i = $('<input>').attr({
                        type: 'hidden',
                        name: 'time-slot',
                        value: val
                    });
                    newTimeSlot.append(
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'preferred_time[]',
                            value: val,
                        })
                    );
                    $('#sortable-timeslots').append(newTimeSlot);
                });
                jQuery('#sortable-timeslots').sortable();
                $('#meeting-time-order').show();
            } else {
                // ...
            }
            e.preventDefault();
        });

        $('#meeting-time-order button[type="reset"]').click(() => {
            $('#meeting-time-order').hide();
            $('#sortable-timeslots').empty();
            $('#meeting-time-slots').show();
        });
    });
</script>
@endsection
