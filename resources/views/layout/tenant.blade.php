@extends('common.master')

@section('master')
    <div class="container-scroller">
        @section('top-bar')
            <app-top-navigation-bar logo-icon-src="{{ $logo_icon }}"
                                    :profile-data="{{ json_encode($top_bar_menu) }}">
            </app-top-navigation-bar>
            <script>
                console.log("Top Bar Menu Data:", @json($top_bar_menu));
            </script>
        @show

        @section('side-bar')
            @php
                $sidebarMenu = json_decode(json_encode($permissions), true);
                $sidebarMenu[] = [
                    'name' => 'Daily Time Record',
                    'id' => 'dtr_system',
                    'icon' => 'clock',
                    'subMenu' => [
                        ['name' => 'Time Clock', 'url' => url('/dtr/clock-in-out'), 'permission' => true],
                        ['name' => 'Schedule', 'url' => url('/dtr/configuration'), 'permission' => true]
                    ],
                    'permission' => true
                ];

                $sidebarMenu[] = [
                    'name' => 'Payroll',
                    'id' => 'payroll_system',
                    'icon' => 'dollar-sign',
                    'subMenu' => [
                        ['name' => 'Payslip', 'url' => url('/payroll/payslip'), 'permission' => true],
                        ['name' => 'Reports', 'url' => url('/payroll/reports'), 'permission' => true]
                    ],
                    'permission' => true
                ];
            @endphp
            <sidebar :data="{{ json_encode($sidebarMenu)  }}"
                     logo-src="{{ $logo }}"
                     logo-icon-src="{{ $logo_icon }}"
                     logo-url="{{ request()->root()  }}">
            </sidebar>
            <script>
                console.log("Permissions Data:", @json($sidebarMenu));
            </script>
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
