<div class="navbar-default sidebar" role="navigation">
    <div class="sidebar-nav navbar-collapse">
        <ul class="nav" id="side-menu">
            @if(Auth::check())
                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('reviewer') || Auth::user()->hasRole('manager') || Auth::user()->hasRole('vip'))
                    <li class="sidebar-search">
                        <div class="custom-search-form">
                            <input id="search-quick" type="text" class="form-control" placeholder="พิมพ์คำค้นหาคณะ...">
                        </div>
                        <!-- /input-group -->
                    </li>
                @else
                    @if (Auth::user()->canViewFullCalendar())
                        {{--case if have permission view full can search--}}
                        <li class="sidebar-search">
                            <div class="custom-search-form">
                                <input id="search-quick" type="text" class="form-control" placeholder="พิมพ์คำค้นหาคณะ...">
                            </div>
                            <!-- /input-group -->
                        </li>
                    @else
                        {{--case no permission--}}
                        <li style="height: 40px;"></li>
                    @endif
                @endif
            @endif
            {{--Show a Dashboard--}}
            <li>
                <a {{ (Request::is('/') ? 'class="active"' : '') }} href="{{ URL::to('/') }}"><i class="logo-label fa fa-dashboard fa-fw"></i> Dashboard</a>
            </li>
            @if(Auth::check())
				{{--VIP--}}
                @if(Auth::user()->hasRole('vip'))
                    <li>
                        <a {{ (Request::is('vip*') ? 'class="active"' : '') }} href="{{ URL::to('vip/send-to-administrator') }}"><i class="logo-label fa fa-share-square-o"></i> แจ้งเรื่องให้ผู้บริหารทราบ</a>
                    </li>
                @endif
                {{--Reviewer--}}
                @if(Auth::user()->hasRole('reviewer'))
                    <li>
                        <a {{ (Request::is('reviewer*') ? 'class="active"' : '') }} href="{{ URL::to('reviewer') }}"><i class="logo-label fa fa-check-square-o"></i> ตรวจสอบคำร้อง <span id="reviewer-task-number" class="badge pull-right">{{ ReviewerController::countTask() }}</span></a>
                    </li>
                @endif
                {{--Manager--}}
                @if(Auth::user()->hasRole('manager'))
                    <li>
                        <a {{ (Request::is('manager*') ? 'class="active"' : '') }} href="{{ URL::to('manager') }}"><i class="logo-label fa fa-pencil-square-o"></i> มอบหมายงาน <span id="manager-task-number" class="badge pull-right">{{ ManagerController::countTask() }}</span></a>
                    </li>
                @endif

                {{--Contributor--}}
                @if(Auth::user()->hasRole('contributor'))
                    {{--<li>
                        <a{{ (Request::is('party/history') ? ' class="active"' : '') }} href="{{{ URL::to('party/history') }}}"><i class="logo-label fa fa-male"></i> ข้อมูลคณะย้อนหลัง</a>
                    </li>--}}
                @endif

                {{--Project Coordinator--}}
                @if(Auth::user()->hasRole('project coordinator'))
                    <li>
                        <a href='#'><i class="logo-label fa fa-users fa-fw"></i> ข้อมูลคณะ<span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li>
                                <a {{ (Request::is('party*') ? ' class="active"' : '') }} href="{{ URL::action('PartyController@getIndex') }}"><i class="logo-label fa fa-th-list fa-fw"></i> คณะที่ดำเนินการรับ</a>
                            </li>
                            <li>
                                <a {{ (Request::is('schedule*') ? ' class="active"' : '') }} href="{{ URL::action('ScheduleController@getIndex') }}"><i class="logo-label fa fa-calendar-o fa-fw"></i> จัดการกำหนดการ</a>
                            </li>
                            <li>
                                <a {{ (Request::is('budget*') ? ' class="active"' : '') }} href="{{ URL::action('BudgetController@getIndex') }}"><i class="logo-label fa fa-money fa-fw"></i> จัดการงบประมาณ</a>
                            </li>
                        </ul>
                        <!-- /.nav-second-level -->
                    </li>
                @endif

                {{--Expert Panel :: Management Team can access--}}
                @if(Auth::user()->hasRole('manager') || Auth::user()->hasRole('project coordinator'))
                    <li>
                        <a {{ (Request::is('expert*') ? 'class="active"' : '') }} href="{{ URL::to('expert') }}"><i class="fa fa-graduation-cap" aria-hidden="true"></i> ข้อมูลวิทยากร</a>
                    </li>
                @endif

            @endif

            {{--Report Service--}}
            @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('vip') || Auth::user()->hasRole('reviewer') || Auth::user()->hasRole('manager'))
                    <li>
                        <a href="{{ URL::to('report') }}"><i class="logo-label fa fa-bar-chart"></i> รายงานระบบ</a>
                    </li>
            @endif

            {{--Only Admin--}}
            @if(Auth::check() && Auth::user()->hasRole('admin'))
                <li>
                    <a href="{{ URL::to('admin') }}"><i class="logo-label fa fa-cogs fa-fw"></i> ข้อมูลตั้งต้นระบบ</a>
                </li>
                <li>
                    <a href="{{ URL::to('stats') }}" target="_blank"><i class="fa fa-stack-exchange" aria-hidden="true"></i>
                        Tracker User</a>
                </li>
            @endif

        </ul>
    </div>
    <!-- /.sidebar-collapse -->
</div>