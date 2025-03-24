@extends('layout.tenant')

@section('title', __('Payslip'))

@section('contents')
<div id="app">
    <payroll-payslip
        name="{{ auth()->user()->name }}"
        role="{{ auth()->user()->roles->first()->name ?? 'Employee' }}"
        profile-picture="{{ auth()->user()->profilePicture->url ?? '/images/avatar.png' }}"
    ></payroll-payslip>
</div>
@endsection