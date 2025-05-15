@extends('layout.tenant')

@section('title', __('Attendance'))

@section('contents')
    <div id="app">
        <attendance
            :user-id="{{ auth()->id() }}"
            name="{{ auth()->user()->name }}"
            role="{{ auth()->user()->roles->first()->name ?? 'Employee' }}"
            profile-picture="{{ auth()->user()->profilePicture->url ?? '/images/avatar.png' }}"
        ></attendance>
    </div>
@endsection