@extends('layouts/app')


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
                  <h1 class="mb-4">Create Meeting</h1>
                  <form action="{{ route('meeting.store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                      <label for="title" class="form-label">{{ __('Meeting title') }}</label>
                      <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" maxlength="255" required />
                    </div>
                    <div class="mb-3">
                      <label for="subject" class="form-label">{{ __('Subject title (Optional)') }}</label>
                      <input type="text" class="form-control" id="subject" name="subject" value="{{ old('subject') }}" />
                    </div>
                    <div class="mb-3">
                      <label for="teacher" class="form-label">{{ __('Teacher') }}</label>
                      <input type="text" class="form-control" id="teacher" value="{{ Auth::user()->name }}" disabled readonly />
                    </div>
                    <div class="mb-3">
                      <label for="duration" class="form-label">{{ __('Duration of each meeting (minutes)') }}</label>
                      <input type="number" class="form-control" id="duration" name="duration" min="0" max="65535" value="{{ old('duration', 20) }}" required />
                    </div>
                    <div class="mb-3">
                      <label for="deadline" class="form-label">{{ __('Deadline') }}</label>
                      <input type="datetime-local" class="form-control" id="deadline" name="deadline" value="{{ old('deadline') }}" required />
                    </div>
                    <div class="mb-3 " id="daybox">
                      <div class="row mb-2">
                        <label for="deadline" class="form-label my-auto">
                          {{ __('Meeting day') }}
                          <button type="button" id="adday" class="btn btn-danger btn-sm fw-bold my-auto ms-3">Add</button>
                        </label>
                      </div>
                      <div class="card bg-light mx-2 mb-3">
                        <div class="card-body">
                          <div class="col-12">
                            <div class="mb-2 d-flex">
                              <label for="day1date" class="form-label ">{{ __('Date') }}</label>
                            </div>
                            <div class="row">
                              <div class="col mb-2">
                                <input type="date" class="form-control" id="day1date" name="day1date" required />
                              </div>
                            </div>
                          </div>
                          <div class="col-12">
                            <label class="form-label fw-bold my-auto">{{ __('Time Periods') }}</label>
                            <button type="button" class="btn btn-danger btn-sm fw-bold my-auto ms-3 addtimeslot" day="1">{{ __('Add') }}</button>
                            <div class="row">
                              <div class="col">
                                <label class="form-label ">{{ __('Start time:') }}</label>
                                <div class="row">
                                  <div class="col mb-2">
                                    <input type="time" class="form-control" name="day1starttime[]" maxlength="100" required />
                                  </div>
                                </div>
                              </div>
                              <div class="col">
                                <label class="form-label ">{{ __('End time :') }}</label>
                                <div class="row">
                                  <div class="col mb-2">
                                    <input type="time" class="form-control" name="day1endtime[]" maxlength="100" required />
                                  </div>
                                </div>
                              </div>
                              <div class="col-auto">
                                <button type="button" class="btn btn-sm m-0 delete fw-bold invisible">Delete</button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label for="days">Number of available days for selection in time slots:</label>
                        <input type="number" name="num_available_days" class="form-control" min="1" required />
                      </div>
                      <div class="col-md-6 mb-3">
                        <label for="times">Number of time slots that should be selected per day:</label>
                        <input type="number" name="num_slots_per_day" class="form-control" min="1" required />
                      </div>
                    </div>

                    <div class="mb-3">
                      <label for="studentid" class="form-label">{{ __('Student ID') }}</label>
                      <br>
                      <label for="studentid" class="form-label">
                        <small>{{ __('(use commas to separate more than one id, example: 20032741d, 20032743d, 20032752d)') }}</small>
                      </label>
                      <textarea class="form-control" id="studentid" name="participants" rows="3" required></textarea>
                    </div>
                    <div class="d-grid">
                      <button type="submit" class="border btn btn-primary fw-bold">Submit</button>
                    </div>
                    <input type="hidden" id="daycount" name="daycount" value="1" />
                  </form>
                </div>
              </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var add_day_button = $("#adday");
    var daycount = 2;

    $('#daybox').on("click", ".addtimeslot", function(e) {
    e.preventDefault();
    $(this).parent().append('<div class="row"> <div class="col"> <div class="row"> <div class="col mb-2"> <input type="time" class="form-control"   name="day'+$(this).attr("day")+'starttime[]" maxlength="100" required> </div> </div> </div> <div class="col"> <div class="row"> <div class="col mb-2"> <input type="time" class="form-control"   name="day'+$(this).attr("day")+'endtime[]" maxlength="100" required> </div> </div> </div> <div class="col-auto"> <button type="button" class="btn btn-danger btn-sm m-0  delete fw-bold">Delete</button> </div> </div>');
    });

    $(add_day_button).click(function (e) {
    e.preventDefault();
    $(this).parent().parent().parent().append('<div class="card bg-light mx-1 my-3"> <div class="card-body"> <div class="d-flex justify-content-between"></div> <div class="col-12"> <div class="mb-2 d-flex"><label for="day'+daycount+'date" class="form-label my-auto">Date</label><a class="btn btn-danger btn-sm ms-auto fw-bold delete" id="day'+daycount+'delete" day="'+daycount+'" role="button">Delete</a></div> <div class="row"> <div class="col mb-2"> <input type="date" class="form-control"  id="day'+daycount+'date" name="day'+daycount+'date"  required> </div> </div> </div> <div class="col-12"> <label  class="form-label fw-bold my-auto">Time Periods</label> <button type="button" class="btn btn-danger btn-sm fw-bold my-auto ms-3 addtimeslot" day="'+daycount+'" >Add</button> <div class="row"> <div class="col"> <label class="form-label ">Start time: </label> <div class="row"> <div class="col mb-2"> <input type="time" class="form-control"   name="day'+daycount+'starttime[]" maxlength="100" required> </div> </div> </div> <div class="col"> <label   class="form-label ">End time : </label> <div class="row"> <div class="col mb-2"> <input type="time" class="form-control"   name="day'+daycount+'endtime[]" maxlength="100" required> </div> </div> </div> <div class="col-auto"> <button type="button" class="btn btn-sm m-0 delete fw-bold invisible">Delete</button> </div> </div> </div> </div> </div>');
    if (daycount>2){
        $('#day'+(daycount-1)+'delete').addClass("disabled");
    }
    $('#daycount').val(daycount);
    daycount++;
    });

    $('#daybox').on("click", ".delete", function(e) {
    e.preventDefault();
    if ( $(this).attr("day") != null){
        $('#day'+(daycount-2)+'delete').removeClass("disabled");
        $('#daycount').val(daycount-2);
        daycount--;
        $(this).parent('div').parent('div').parent('div').parent('div').remove();
    }else{
        $(this).parent('div').parent('div').remove();
    }
    });
});
</script>
@endsection
