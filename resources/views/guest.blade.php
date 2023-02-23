@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="alert alert-primary mb-2">
                {{ __('System: Please log in first.') }}
            </div>

            <div class="card mb-2">
                <div class="card-header">{{ __('About') }}</div>
                <div class="card-body">
                    {{ __('This meeting reservation system is aimed to assist both professors and students in Department of Computing handling the problems and inconvenience caused by allocating time slots manually.') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
