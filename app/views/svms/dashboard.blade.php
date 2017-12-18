@extends('svms.layouts.default')

@section('title')
    Living University Management System
@stop

@section('extraScripts')
    {{--Use Bootstrap Checkbox Button--}}
    {{ HTML::script('assets/js/bootstrap-checkbox-button.js') }}
    {{--Use MomentJs--}}
    {{ HTML::script('assets/js/moment.js') }}
    {{ HTML::script('assets/js/th.js') }}
    {{--Use Full Calendar--}}
    {{ HTML::style('dependencies/fullcalendar/fullcalendar.min.css') }}
    {{ HTML::style('dependencies/fullcalendar/fullcalendar.print.css', array('media' => 'print')) }}
    {{ HTML::script('dependencies/fullcalendar/fullcalendar.min.js') }}
    {{ HTML::script('dependencies/fullcalendar/locale/th.js') }}
    {{--Use DataTables--}}
    {{ HTML::script('dependencies/DataTables/media/js/jquery.dataTables.min.js') }}
    {{ HTML::style('dependencies/DataTables/media/css/jquery.dataTables.min.css') }}
    {{--Use Data Tables Extension Responsive--}}
    {{ HTML::style('dependencies/DataTables/extensions/responsive/css/dataTables.responsive.css') }}
    {{ HTML::script('dependencies/DataTables/extensions/responsive/js/dataTables.responsive.js') }}
    {{--Use Bootstrap Select2--}}
    {{ HTML::script('dependencies/select2-4.0.0-beta.3/dist/js/select2.min.js') }}
    {{ HTML::style('dependencies/select2-4.0.0-beta.3/dist/css/select2.min.css') }}
@stop

@section('extraStyles')
    <style type="text/css">
        #transaction-tab-calendar
        {
            margin-top: 2%;
        }
    </style>
@stop

@section('header')
    <span class="fa fa-dashboard"></span>
    Dashboard

    @if(!Auth::user()->hasRole('admin'))
        @if(Auth::user()->canViewFullCalendar())
            <div class="pull-right">
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-default {{ (Request::getQueryString()=='status' || Request::getQueryString()==null) ? 'active' : '' }}" onclick="window.location='{{ URL::to('/?status') }}';"><i class="fa fa-tasks" aria-hidden="true"></i> <span class="text-lg">สถานะ</span></button>
                    <button type="button" class="btn btn-default {{ (Request::getQueryString()=='location') ? 'active' : '' }}" onclick="window.location='{{ URL::to('/?location') }}';"><i class="fa fa-map-marker" aria-hidden="true"></i> <span class="text-lg">สถานที่ดูงาน</span></button>
                </div>
            </div>
            <div class="clearfix"></div>
        @endif
    @endif

@stop

@section('content')

    <div id="dashboard_div" class="col-xs-12 col-md-12">
        @if(Auth::user()->hasRole('admin'))
            {{--System Admin--}}
            @include('svms.partials.dashboard.admin_panel')
        @elseif(Auth::user()->canViewFullCalendar())
            {{--Executive or have permission--}}
            @include('svms.partials.dashboard.full_calendar')
        @elseif(Auth::user()->hasRole('project coordinator'))
            {{--Permision to coordinator or organize only--}}
            @include('svms.partials.dashboard.current_tasks')
        @else
            {{--Supervisor and Contributor--}}
            @include('svms.partials.dashboard.supervisor_panel')
        @endif
    </div>

@stop