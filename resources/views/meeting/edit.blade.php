@extends('layouts/app')

@php
$participants = join(',', $meeting->participants->pluck('username')->toArray());

$scheduledTimes = $meeting->participants
    ->whereNotNull('scheduled_time')
    ->pluck('scheduled_time')
    ->all();
@endphp

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="m-auto">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                    </ul>
                </div>
            @endif

            <div class="card py-2 px-sm-2 px-md-5">
                <div class="card-body">
                    <h1 class="mb-4">Edit Meeting</h1>
                    <form action="{{ route('meeting.update', $meeting) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ $meeting->title }}" />
                        </div>
                        <div class="form-group mb-3">
                            <label for="subject" class="form-label">Subject (Optional)</label>
                            <input type="text" class="form-control" id="subject" name="subject" value="{{ $meeting->subject }}" />
                        </div>
                        <div class="form-group mb-3">
                            <label for="teacher" class="form-label">Teacher</label>
                            <input type="text" class="form-control" id="teacher" value="{{ $meeting->moderatorUser->name }}" disabled readonly />
                        </div>
                        <div class="form-group mb-3">
                            <label for="duration" class="form-label">Duration of each meeting (minutes)</label>
                            <input type="text" class="form-control" id="duration" value="{{ $meeting->duration }}" disabled readonly />
                            <input type="hidden" class="form-control" name="duration" value="{{ $meeting->duration }}" />
                        </div>
                        <div class="form-group mb-3">
                            <label for="deadline" class="form-label">Deadline</label>
                            <input type="datetime-local" class="form-control" id="deadline" name="deadline" value="{{ $meeting->deadline }}" />
                        </div>
                        <div class="form-group">
                            <label for="time_slots" class="form-label">Time Slots</label>
                            <div class="card" id="meeting-time-slots">
                                <div class="card-body">
                                    @foreach ($meeting->timeslots as $date => $times)
                                            <div class="my-2">
                                                <label for="date{{ __($date) }}" class="form-label">{{ __('Date') }}: {{ __($date) }}</label><br>
                                                @foreach ($times as $time)
                                                    <div class="form-check form-check-inline">
                                                        @if (in_array($date.' '.$time, $scheduledTimes))
                                                            <button type="button" class="btn btn-secondary btn-sm" style="width: 80px" name="{{ __($date) }}-{{ __($time) }}" disabled>Scheduled</button>
                                                        @else
                                                            <input type="button" class="btn btn-danger btn-sm" style="width: 80px" value="Delete" name="{{ __($date) }}-{{ __($time) }}" />
                                                        @endif
                                                        <input type="hidden" name="timeslots[{{ __($date) }}][]" value="{{ __($time) }}" />
                                                        <label class="form-check-label" for="{{ __($date) }}-{{ __($time) }}">{{ __($time) }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                    @endforeach

                                    <div class="mt-3">
                                        <button type="button" class="btn btn-primary btn-sm" id="adday">Add Date</button>
                                    </div>

                                    <div class="container" id="daybox">
                                        <input type="hidden" id="daycount" name="daycount" value="0" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="days">Number of available days for selection in time slots:</label>
                                <input type="number" name="num_available_days" class="form-control" min="1" value="{{ $meeting->num_available_days }}" required />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="times">Number of time slots that should be selected per day:</label>
                                <input type="number" name="num_slots_per_day" class="form-control" min="1" value="{{ $meeting->num_slots_per_day }}" required />
                            </div>
                        </div>

                        <input type="hidden" name="participants" value="{{ $participants }}">

                        <div class="row justify-content-center m-2">
                            <div class="col-2 text-center"><button type="submit" class="btn btn-primary">Save</button></div>
                            <div class="col-2 text-center"><a class="btn btn-danger" href="{{ route('meeting.show', $meeting) }}">Cancel</a></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var add_day_button = $("#adday");
    var daycount = 0;

    $('#daybox').on("click", ".addtimeslot", function(e) {
        e.preventDefault();
        $(this).parent().append('<div class="row"> <div class="col"> <div class="row"> <div class="col mb-2"> <input type="time" class="form-control"   name="day'+$(this).attr("day")+'starttime[]" maxlength="100" required> </div> </div> </div> <div class="col"> <div class="row"> <div class="col mb-2"> <input type="time" class="form-control"   name="day'+$(this).attr("day")+'endtime[]" maxlength="100" required> </div> </div> </div> <div class="col-auto"> <button type="button" class="btn btn-danger btn-sm m-0  delete fw-bold">Delete</button> </div> </div>');
    });

    $(add_day_button).click(function (e) {
        e.preventDefault();
        ++daycount;
        $('#daybox').append('<div class="card bg-light mx-1 my-3"> <div class="card-body"> <div class="d-flex justify-content-between"></div> <div class="col-12"> <div class="mb-2 d-flex"><label for="day'+daycount+'date" class="form-label my-auto">Date</label><a class="btn btn-danger btn-sm ms-auto fw-bold delete" id="day'+daycount+'delete" day="'+daycount+'" role="button">Delete</a></div> <div class="row"> <div class="col mb-2"> <input type="date" class="form-control"  id="day'+daycount+'date" name="day'+daycount+'date"  required> </div> </div> </div> <div class="col-12"> <label  class="form-label fw-bold my-auto">Time Periods</label> <button type="button" class="btn btn-danger btn-sm fw-bold my-auto ms-3 addtimeslot" day="'+daycount+'" >Add</button> <div class="row"> <div class="col"> <label class="form-label ">Start time: </label> <div class="row"> <div class="col mb-2"> <input type="time" class="form-control"   name="day'+daycount+'starttime[]" maxlength="100" required> </div> </div> </div> <div class="col"> <label   class="form-label ">End time : </label> <div class="row"> <div class="col mb-2"> <input type="time" class="form-control"   name="day'+daycount+'endtime[]" maxlength="100" required> </div> </div> </div> <div class="col-auto"> <button type="button" class="btn btn-sm m-0 delete fw-bold invisible">Delete</button> </div> </div> </div> </div> </div>');
        if (daycount > 1) {
            $('#day'+(daycount-1)+'delete').addClass("disabled");
        }
        $('#daycount').val(daycount);
    });

    $('#daybox').on("click", ".delete", function(e) {
        e.preventDefault();
        if ($(this).attr("day") != null) {
            $('#day'+(daycount-2)+'delete').removeClass("disabled");
            $('#daycount').val(daycount-2);
            daycount--;
            $(this).parent('div').parent('div').parent('div').parent('div').remove();
        } else {
            $(this).parent('div').parent('div').remove();
        }
    });

    // Get all the delete buttons
    const deleteButtons = document.querySelectorAll('input[type="button"][value="Delete"]');

    // Add a click event listener to each delete button
    deleteButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Get the date and time values from the button's name attribute
            const timeSlot = button.getAttribute('name');
            const idx = timeSlot.lastIndexOf('-', timeSlot.lastIndexOf('-') - 1);
            const date = timeSlot.substring(0, idx);
            const time = timeSlot.substring(idx + 1);

            // Find the date and time elements in the form
            const dateElement = document.querySelector(`label[for="date${date}"]`);
            const timeElements = [...document.querySelectorAll(`label[for^="${date}-"]`)].filter(element => element.innerText === time);

            // Remove the time elements from the form
            timeElements.forEach(element => element.parentNode.remove());

            // If there are no more time slots for the given date, remove the date element as well
            if (![...document.querySelectorAll(`label[for^="${date}-"]`)].length) {
                dateElement.parentNode.remove();
            }
        });
    });
});
</script>
@endsection