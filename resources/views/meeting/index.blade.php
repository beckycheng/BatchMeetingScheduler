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

            <div class="card">
                <div class="card-header d-flex">
                    <span class="my-auto">{{ __('Meetings') }}</span>
                    @if (auth()->user()->role->name == 'Teacher')
                        <a class="btn btn-primary btn-sm ms-auto my-auto" href="{{ route('meeting.create') }}">{{ __('Create') }}</a>
                    @else
                        <a class="invisible btn btn-primary btn-sm ms-auto my-auto">{{ __('Button') }}</a>
                    @endif
                </div>

                <div class="card-body">
                    @forelse ($meetings as $meeting)
                        <div class="card mb-2">
                            <div class="card-header d-flex">
                                <a class="my-auto" href="{{ route('meeting.show', $meeting->id) }}">{{ __('Meeting') }} ({{ __($meeting->id)}})</a>
                            </div>
                            <div class="card-body">
                                <h5>Title: <span class="text-muted">{{ __($meeting->title) }}</span></h5>
                                <h5>Subject: <span class="text-muted">{{ $meeting->subject ?? __('(empty)') }}</span></h5>
                                <h5>Teacher: <span class="text-muted">{{ __($meeting->moderatorUser->name) }}</span></h5>
                                <h5>Duration: <span class="text-muted">{{ __($meeting->duration . ' minutes') }}</span></h5>
                                <h5>Deadline: <span class="text-muted">{{ __($meeting->deadline) }}</span></h5>
                            </div>
                        </div>
                    @empty
                        {{ __('No meetings') }}
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
