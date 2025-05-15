@extends('common.master')

@section('master')
    <div class="container-scroller">
        @section('top-bar')
            <app-top-navigation-bar logo-icon-src="{{ $logo_icon }}"
                                    :profile-data="{{ json_encode($top_bar_menu) }}">
            </app-top-navigation-bar>
        @show

        @section('side-bar')
            @php
                $sidebarMenu = json_decode(json_encode($permissions), true);

                $dtrSubMenu = [
                    ['name' => 'Time Clock', 'url' => route('dtr.time_clock'), 'permission' => true],
                    ['name' => 'Attendance', 'url' => route('dtr.attendance'), 'permission' => true],
                ];

                if (Auth::user()->roles->first()->pivot->role_id == 1) {
                    $dtrSubMenu[] = ['name' => 'Employee Id', 'url' => route('dtr.employee_id'), 'permission' => true];
                    $dtrSubMenu[] = ['name' => 'Schedule', 'url' => route('dtr.configuration'), 'permission' => true];
                }

                $payrollSubMenu = [
                    ['name' => 'Payslip', 'url' => route('payroll.payslip'), 'permission' => true],
                ];

                if (Auth::user()->roles->first()->pivot->role_id == 1) {
                    $payrollSubMenu[] = ['name' => 'Reports', 'url' => route('payroll.reports'), 'permission' => true];
                    $payrollSubMenu[] = ['name' => 'Computation', 'url' => route('payroll.computation'), 'permission' => true];
                }

                $sidebarMenu[] = [
                    'name' => 'Daily Time Record',
                    'id' => 'dtr_system',
                    'icon' => 'clock',
                    'subMenu' => $dtrSubMenu,
                    'permission' => true
                ];

                $sidebarMenu[] = [
                    'name' => 'Payroll',
                    'id' => 'payroll_system',
                    'icon' => 'dollar-sign',
                    'subMenu' => $payrollSubMenu,
                    'permission' => true
                ];
            @endphp

            <sidebar :data="{{ json_encode($sidebarMenu)  }}"
                     logo-src="{{ $logo }}"
                     logo-icon-src="{{ $logo_icon }}"
                     logo-url="{{ request()->root()  }}">
            </sidebar>
        @show
        <div class="container-fluid page-body-wrapper">
            <div class="main-panel">
                @yield('contents')
            </div>
        </div>
    </div>
@endsection
    
@push('before-scripts')
    <script>
        window.tenant = <?php echo json_encode(tenant()) ?>
    </script>
    <script>
        window.settings = {!! isset($settings) ? json_encode($settings): '{}' !!}
    </script>
    <script>
        window.user = <?php echo auth()->user()->load('profilePicture', 'roles:id,name', 'branches:id,name') ?>
    </script>
@endpush
