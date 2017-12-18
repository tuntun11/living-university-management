{{--Navigation Part--}}
<ul class="nav navbar-top-links navbar-right">

    {{-- Check if homepage --}}
    @if (Auth::check())
        @if(!Request::is('/'))
            <li class="dropdown">
                <a href="{{ URL::to('/') }}">
                    <i class="fa fa-home fa-lg"></i> Dashboard
                </a>
            </li>
        @endif
    @endif

    {{--start calendar customer only--}}
    @if (!Auth::check())
        <li class="dropdown {{ (Request::is('calendar') ? 'active' : '') }}">
            <a href="{{ URL::to('calendar') }}">
                <i class="fa fa-calendar"></i> ปฎิทินคณะดูงาน
            </a>
        </li>
    @endif

    {{--start request--}}
    <li class="dropdown {{ (Request::is('request') ? 'active' : '') }}">
        <a href="{{ URL::to('request') }}">
            <i class="fa fa-envelope"></i> ยื่นคำร้องดูงาน
        </a>
    </li>

    @if (Auth::check())
        <!-- /.dropdown -->
        <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                @if(Auth::user()->hasRole('admin'))
                    <i class="fa fa-user-secret"></i>
                @elseif(Auth::user()->hasRole('vip'))
                    <i class="fa fa-star"></i>
                @else
                    <i class="fa fa-user fa-fw"></i>
                @endif
                 {{{ (Auth::user()->hasRole('admin')) ? Auth::user()->username : Auth::user()->getShortName() }}} <i class="fa fa-caret-down"></i>
            </a>
            <ul class="dropdown-menu dropdown-user">
                @if(!Auth::user()->hasRole('admin'))
                    <li><a href="{{{ URL::to('user/setting') }}}" target="_blank"><i class="fa fa-user fa-fw"></i> ข้อมูลผู้ใช้งาน</a></li>
                @endif
                {{--<li><a href="#"><i class="fa fa-gear fa-fw"></i> ตั้งค่า</a></li>--}}
                <li><a href="#"><i class="fa fa-download fa-fw"></i> คู่มือการใช้งาน v.1.0</a>
                <li class="divider"></li>
                <li><a href="{{{ URL::to('user/logout') }}}"><i class="fa fa-sign-out fa-fw"></i> ออกจากระบบ</a>
                </li>
            </ul>
            <!-- /.dropdown-user -->
        </li>
        <!-- /.dropdown -->
    @endif

</ul>