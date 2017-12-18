@extends('svms.layouts.default')

@section('title')
    Living University Management System
@stop

@section('extraScripts')
    {{--Use MomentJs--}}
    {{ HTML::script('assets/js/moment.js') }}
    {{ HTML::script('assets/js/th.js') }}
    {{--Use Full Calendar--}}
    {{ HTML::style('dependencies/fullcalendar/fullcalendar.min.css') }}
    {{ HTML::style('dependencies/fullcalendar/fullcalendar.print.css', array('media' => 'print')) }}
    {{ HTML::script('dependencies/fullcalendar/fullcalendar.min.js') }}
    {{ HTML::script('dependencies/fullcalendar/locale/th.js') }}
    {{--Use Bootstrap Checkbox Button--}}
    {{ HTML::script('assets/js/bootstrap-checkbox-button.js') }}
    {{--Use Bootstrap Select2--}}
    {{ HTML::script('dependencies/select2-4.0.0-beta.3/dist/js/select2.min.js') }}
    {{ HTML::style('dependencies/select2-4.0.0-beta.3/dist/css/select2.min.css') }}
    {{--Use Jquery Timepicker--}}
    {{ HTML::script('dependencies/jquery-timepicker-master/jquery.timepicker.js') }}
    {{ HTML::style('dependencies/jquery-timepicker-master/jquery.timepicker.css') }}
    {{--Use Bootstrap Context Menu--}}
    {{ HTML::script('dependencies/bootstrap-menu-master/dist/BootstrapMenu.min.js') }}

@stop

@section('extraStyles')
    <style type="text/css">
        .EventTime {
           padding: 5px; border: 1px #b3b3b3 solid; border-radius: 5px;
        }
    </style>
@stop

@section('header')
    <span class="fa fa-calendar-o"></span>
    การจัดการกำหนดการและสถานที่พัก

    @if(isset($party))
        <div class="pull-right">
            <!-- Split button -->
            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-cog"></i> จัดการกำหนดการ
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="javascript:openCreateSchedule();"><span class="fa fa-calendar"></span> สร้างกิจกรรม</a></li>
                    <li><a href="javascript:openCreateAccommodation();"><span class="fa fa-bed"></span> สร้างกำหนดการที่พัก</a></li>
                </ul>
            </div>
        </div>
    @endif
@stop

@section('content')

    <?php
    $partyInfo = (isset($party)) ? $party->customer_code.' '.$party->name.' ('.$party->people_quantity.')' : '';
    ?>

    <div id="party_section" class="container-fluid">
        <select class="form-control" id="party_select" style="width: 100%;">
            <option value="" selected disabled>กรุณาเลือกคณะ</option>
            @foreach($parties as $p)
                <?php
                $create_programed = ($p->programingPassed()) ? '*' : '';
                ?>
                <option {{ (isset($party) && $party->id===$p->id) ? 'selected' : '' }} value="{{ URL::to('coordinator/schedule/'.$p->id.'/view') }}">{{ $p->customer_code.' '.$p->name.' ('.$p->people_quantity.') '.$create_programed }}</option>
            @endforeach
        </select>
    </div>

    <div class="clearfix" style="height: 10px;"></div>

    {{--Alert แสดงการทำงาน--}}
    @if(!isset($party))
        <div class="alert alert-warning alert-block" style="margin: 10px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <p>ท่านต้องทำการเลือกคณะเพื่อทำการลงกำหนดการ</p>
        </div>
    @else
        {{--Alert ว่าหากมีการทำงบประมาณไปแล้วกลับมาแก้งบประมาณต้องไปตรวจสอบงบประมาณ--}}
        @if($party->budgetingPassed())
            <div class="alert alert-warning alert-block" style="margin: 10px;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <p><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> คณะนี้ได้ทำตั้งงบประมาณไปแล้วหากทำการปรับปรุงกำหนดการจะมีผลกระทบกับยอดของงบประมาณ</p>
            </div>
        @endif
        {{--Agenda View--}}
        <div id="schedule_section" class="container-fluid show-schedule">
            <div class="pull-left">
                <span class="button-checkbox">
                    <button type="button" class="btn" data-color="primary">แผน A</button>
                    <input type="checkbox" class="hidden" id="show_plan_a" value="1" checked />
                </span>
                <span class="button-checkbox">
                    <button type="button" class="btn" data-color="danger">แผน B</button>
                    <input type="checkbox" class="hidden" id="show_plan_b" value="1" checked />
                </span>
                |
                <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default {{ ($main_lang=='th') ? 'active' : '' }}">
                        <input type="radio" name="show_lang" id="show_lang_th" value="th" autocomplete="off" {{ ($main_lang=='th') ? 'checked' : '' }}>
                        <i><img src="{{ asset('assets/img/flags/th.png') }}" style="margin-top: -3px;"></i> ไทย </label>
                    <label class="btn btn-default {{ ($main_lang=='en') ? 'active' : '' }}">
                        <input type="radio" name="show_lang" id="show_lang_en" value="en" {{ ($main_lang=='en') ? 'checked' : '' }}>
                        <i><img src="{{ asset('assets/img/flags/gb.png') }}" style="margin-top: -3px;"></i> ENG </label>
                </div>
            </div>

            <div class="pull-right">
                <h4><span id="party-schedule">{{-- date range text --}}</span>
                    <div class="controlAgendaEvent btn-group btn-group-sm" role="group" aria-label="...">
                        <button type="button" id="prevAgenda" class="btn btn-default">&lt;&lt;</button>
                        <button type="button" id="nextAgenda" class="btn btn-default">&gt;&gt;</button>
                    </div>
                </h4>
            </div>
        </div>

        <div class="container-fluid show-schedule">
            <form id="schedule-agenda-form">
                <!-- Language set thai is default -->
                <input type="hidden" id="schedule-lang" value="th" />
                <!-- CSRF Token -->
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <!-- ./ csrf token -->
                <div id="schedule-agendaDay"></div>
                <div class="row pull-right" style="margin: 10px 0;">
                    {{--Button create document appear when create schedule everyday--}}
                    <button id="submitCreateDocument" data-loading-text="กำลังดำเนินการ..."  lang="{{ $party->country }}" type="button" class="btn btn-lg btn-success"><i class="fa fa-file-text"></i> สร้างเอกสารร่างโปรแกรม</button>
                </div>
            </form>
        </div>

        <!-- Events Modal Start-->
        <form id="formEvent" role="form" method="post">
            {{--Schedule id--}}
            <input type="hidden" id="event_schedule_id" name="event_schedule_id">
            {{--Id--}}
            <input type="hidden" id="event_old_id" name="event_old_id">
            {{--Create New True--}}
            <input type="hidden" id="event_new" name="event_new" value="true">
            {{--Current Location--}}
            <input type="hidden" id="event_current_location" name="event_current_location">
            {{--Current Location Activity--}}
            <input type="hidden" id="event_current_active" name="event_current_active">
            {{--Current Task Location Activity--}}
            <input type="hidden" id="event_current_task_location" name="event_current_task_location">

            <!-- CSRF Token -->
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <!-- ./ csrf token -->

            <div class="modal fade" id="luEventModal" role="dialog" aria-labelledby="luEventModalLabel" aria-hidden="true">
                <div class="modal-dialog" style="overflow-y: scroll; max-height:95%;  margin-top: 5px; margin-bottom:5px;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="luEventModalLabel"><span class="fa fa-calendar"></span> <label>ลงกำหนดการ</label></h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="event_party">คณะดูงาน :</label>
                                <input type="text" class="form-control" id="event_party" value="{{ $partyInfo }}" disabled>
                            </div>
                            <div class="form-group">
                                <label for="event_date">วันที่และเวลา :</label>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <select class="form-control" id="event_date">
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <input type="text" class="time EventTime" style="width: 100px;" id='event_start'> ถึง  <input type="text" class="time EventTime" style="width: 100px;" id='event_end'>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="event_plan">แผน</label>
                                <select class="form-control" id="event_plan" name="event_plan">
                                    <option value="A">A (แผนหลัก)</option>
                                    <option value="B">B (แผนรอง)</option>
                                </select>
                            </div>

                            <input type="hidden" id="event_plan_active" value="0">

                            <div class="form-group">
                                <label for="event_location">สถานที่ :</label>
                                <select class="select_locations form-control" id="event_location" multiple="multiple" style="width: 100%">
                                    @foreach(array_keys($activity_locations) as $area)
                                        @if(count($activity_locations[$area])>0)
                                            <optgroup label="{{ $area }}">
                                                @foreach($activity_locations[$area] as $location)
                                                    <option value="{{ $location->id }}">{{ $location->text }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs" role="tablist">
                                    <li role="presentation" class="active"><a href="#event_lang_th" aria-controls="event_lang_th" role="tab" data-toggle="tab">ภาษาไทย</a></li>
                                    <li role="presentation"><a href="#event_lang_en" aria-controls="event_lang_en" role="tab" data-toggle="tab">English</a></li>
                                </ul>

                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane active" id="event_lang_th" style="margin-top:15px;">
                                        <div class="form-group">
                                            <input type="text" class="form-control activities" lang="th" autocomplete="off" id="event_title_th" placeholder="คำที่เขียนจะแสดงในกำหนดการ">
                                        </div>

                                        <div class="form-group">
                                            <textarea id="event_desc_th" class="form-control" placeholder="คำอธิบายที่จะแสดง"></textarea>
                                        </div>
                                    </div>
                                    <div role="tabpanel" class="tab-pane" id="event_lang_en" style="margin-top:15px;">
                                        <div class="form-group">
                                            <input type="text" class="form-control activities" lang="en" autocomplete="off" id="event_title_en" placeholder="คำที่เขียนจะแสดงในกำหนดการ">
                                        </div>

                                        <div class="form-group">
                                            <textarea id="event_desc_en" class="form-control" placeholder="คำอธิบายที่จะแสดง"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{--Copy param area--}}
                            <input type="hidden" id="event_copy_state" value="0">{{--This is check for copy state 1=true 0=false--}}
                            <hr class="openToCopy" style="display:none;"/>
                            <div class="form-group openToCopy" style="display:none;">
                                <label for="event_copy_date">คัดลอกไปยังวันที่และเวลา :</label>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <select class="form-control" id="event_copy_date">
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <input type="text" class="time EventTime" style="width: 100px;" id='event_copy_start'> ถึง  <input type="text" class="time EventTime" style="width: 100px;" id='event_copy_end'>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group openToCopy" style="display:none;">
                                <label for="event_copy_plan">คัดลอกไปยังแผน</label>
                                <select class="form-control" id="event_copy_plan" name="event_copy_plan">
                                    <option value="A">A (แผนหลัก)</option>
                                    <option value="B">B (แผนรอง)</option>
                                </select>
                            </div>
                            <div class="openToCopy" style="display:none;">
                                <button type="button" class="btn btn-xs btn-default" onclick="cancelCopy('A');">&lt; ยกเลิกคัดลอก</button>
                            </div>
                            <div class="clearfix"></div>

                        </div>
                        <div class="modal-footer">
                            <div class="pull-left openToDelete" style="display: none;">
                                <button type="button" class="btn btn-danger" onclick="return openDelete('');"><i class="fa fa-trash-o"></i> ลบ</button>
                            </div>
                            <button type="button" class="btn btn-default open-create" data-dismiss="modal">ปิด</button>
                            <button type="button" class="btn btn-warning open-edit" onclick="openCopy('A');"><i class="fa fa-clipboard" aria-hidden="true"></i> คัดลอก</button>
                            <button type="submit" class="btn btn-primary save-button"><i class="fa fa-save"></i> บันทึก</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!-- Events Modal End-->

        <!-- Accom Modal Start-->
        <form id="formAccom" role="form" method="post">
            {{--Schedule id--}}
            <input type="hidden" id="accom_schedule_id" name="accom_schedule_id">
            {{--Id--}}
            <input type="hidden" id="accom_old_id" name="accom_old_id">
            {{--Create New True--}}
            <input type="hidden" id="accom_new" name="accom_new" value="true">
            {{--Current Location--}}
            <input type="hidden" id="accom_current_location" name="accom_current_location">
            {{--Current Location Activity--}}
            <input type="hidden" id="accom_current_active" name="accom_current_active">
            {{--Current Task Location Activity--}}
            <input type="hidden" id="accom_current_task_location" name="accom_current_task_location">

            <!-- CSRF Token -->
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <!-- ./ csrf token -->

            <div class="modal fade" id="luAccomModal" role="dialog" aria-labelledby="luAccomModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="luAccomModalLabel"><span class="fa fa-bed"></span> <label>ลงสถานที่พัก</label></h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="accom_party">คณะดูงาน :</label>
                                <input type="text" class="form-control" id="accom_party" value="{{ $partyInfo }}" disabled>
                            </div>

                            <div class="form-group">
                                <label for="accom_date">เริ่มพักวันที่/จำนวนคืน :</label>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <select class="form-control" id="accom_date">
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <?php
                                            $maxDayCount = (isset($party)) ? $party->date_count : 1;
                                        ?>
                                        <select class="form-control" id="accom_night">
                                            @for($d=1;$d<=$maxDayCount;$d++)
                                                <option value="{{ $d }}">{{ 'พัก '.$d.' คืน' }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="accom_plan">แผน</label>
                                <select class="form-control" id="accom_plan" name="accom_plan">
                                    <option value="A">A (แผนหลัก)</option>
                                    <option value="B">B (แผนรอง)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="accom_location">สถานที่พัก/โรงแรม :</label>
                                <select class="select_locations form-control" id="accom_location" multiple="multiple" style="width: 100%">
                                    @foreach(array_keys($sleep_locations) as $area)
                                        @if(count($sleep_locations[$area])>0)
                                            <optgroup label="{{ $area }}">
                                                @foreach($sleep_locations[$area] as $location)
                                                    <option value="{{ $location->id }}">{{ $location->text }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="accom_desc">บันทึกเพิ่มเติม:</label>
                                <textarea id="accom_desc" class="form-control"></textarea>
                            </div>

                            {{--Copy param area--}}
                            <input type="hidden" id="accom_copy_state" value="0">{{--This is check for copy state 1=true 0=false--}}
                            <hr class="openToCopy" style="display:none;"/>
                            <div class="form-group openToCopy" style="display:none;">
                                <label for="accom_copy_date">คัดลอกไปยังเริ่มพักวันที่/จำนวนคืน :</label>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <select class="form-control" id="accom_copy_date">
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <?php
                                            $maxDayCount = (isset($party)) ? $party->date_count : 1;
                                        ?>
                                        <select class="form-control" id="accom_copy_night">
                                            @for($d=1;$d<=$maxDayCount;$d++)
                                                <option value="{{ $d }}">{{ 'พัก '.$d.' คืน' }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group openToCopy" style="display:none;">
                                <label for="accom_copy_plan">คัดลอกไปยังแผน</label>
                                <select class="form-control" id="accom_copy_plan" name="accom_copy_plan">
                                    <option value="A">A (แผนหลัก)</option>
                                    <option value="B">B (แผนรอง)</option>
                                </select>
                            </div>
                            <div class="openToCopy" style="display:none;">
                                <button type="button" class="btn btn-xs btn-default" onclick="cancelCopy('S');">&lt; ยกเลิกคัดลอก</button>
                            </div>
                            <div class="clearfix"></div>

                        </div>
                        <div class="modal-footer">
                            <div class="pull-left openToDelete" style="display: none;">
                                <button type="button" class="btn btn-danger" onclick="return openDelete('S');"><i class="fa fa-trash-o"></i> ลบ</button>
                            </div>
                            <button type="button" class="btn btn-default open-create" data-dismiss="modal">ปิด</button>
                            <button type="button" class="btn btn-warning open-edit" onclick="openCopy('S');"><i class="fa fa-clipboard" aria-hidden="true"></i> คัดลอก</button>
                            <button type="submit" class="btn btn-primary save-button"><i class="fa fa-save"></i> บันทึก</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!-- Accom Modal End-->

    @endif

    <script type="text/javascript">

        var party_id = {{ (isset($party)) ? $party->id : 0 }};

        $(function () {
            // go to the latest tab, if it exists:
            var lastTab = localStorage.getItem('lastTab');
            if (lastTab) {
                $('[href="' + lastTab + '"]').tab('show');
            }
            /*Select Party*/
            $('#party_select').select2({
                placeholder: "กรุณาเลือกคณะ"
            });
            /*On Change Party Select View*/
            $('#party_select').on('change', function(){
                //use laroute to redirect
                window.location = $(this).val();
            });
            /*Config location select as select2*/
            $("#accom_location").select2({
                placeholder: "กรุณาเลือกที่พัก",
                allowClear: true
            });

            $("#event_location").select2({
                placeholder: "กรุณาเลือกสถานที่",
                allowClear: true
            });

            //loop for date range
            var event_dates = [];
            @if(isset($party))
                //load events
                //loop add select day of events
                @foreach($party->date_range as $dr)
                    event_dates.push("{{ $dr }}");
                @endforeach

                //config fullcalendar
                /*JSON Ajax Feed*/
                var event_range = event_dates.length; //Query Day of Event
                var event_start = event_dates[0];
                var event_end = event_dates[event_dates.length-1];

                /*set date range text*/
                $('#party-schedule').html('{{ $party->date_range_text }}');

                //control event
                if (event_range>3) {
                    $('.controlAgendaEvent').show();
                }else{
                    $('.controlAgendaEvent').hide();
                }

                $('#schedule-agendaDay').fullCalendar({
                    header: false,
                    axisFormat : 'H.mm น.',
                    minTime : '06:00:00',
                    maxTime : '24:00:00',
                    scrollTime : '06:30:00',
                    allDayText : 'สถานที่พัก',
                    defaultDate: event_start,
                    defaultView: 'agendaParty',
                    slotEventOverlap: false,
                    slotDuration: '00:15:00',
                    columnFormat : 'dd D MMMM',
                    editable: true,
                    views: {
                        agendaParty: {
                            type: 'agenda',
                            duration: { days: (event_range<=3) ? event_range : 3 }
                        }
                    },
                    eventSources:
                    [
                        {
                            url: '{{ URL::action('ScheduleController@getTasks') }}',
                            data: {
                                '_token' : $('input[name=_token]').val(),
                                'party_id' : party_id,
                                'plan_a' : $("#show_plan_a").is(':checked'),
                                'plan_b' : $("#show_plan_b").is(':checked'),
                                'lang' : "{{ $main_lang }}"
                            }
                        }
                    ],
                    viewRender: function( view, element ) {

                    },
                    eventRender: function(event, element) {
                        var typeDesc

                        switch (event.type){
                            case 'S' :
                                typeDesc = event.locations;
                                break;
                            default  :
                                typeDesc = '<br/>@'+event.locations;
                        }

                        element.find('.fc-title').append(typeDesc);

                    },
                    dayClick: function(date, jsEvent, view) {
                        //check if have time in date return to activity else not return to accommodation
                        if (date.format().indexOf("T")===10)
                        {
                            openCreateSchedule(date.format());
                        }
                        else
                        {
                            openCreateAccommodation(date.format());
                        }
                    },
                    eventClick: function(calEvent, jsEvent, view) {
                        openEditSchedule(calEvent);
                    },
                    eventDrop: function(event, delta, revertFunc) {
                        /*send it to ajax post*/
                        updateScheduleMoment({ '_token' : $('input[name=_token]').val(), 'id' : event.id, 'start' : event.event_date+' '+event.event_time_start, 'end' : event.event_date+' '+event.event_time_end, 'event' : 'dd', 'calendar' :  delta._data});
                    },
                    eventResize: function(event, delta, revertFunc) {
                        /*send it to ajax post*/
                        updateScheduleMoment({ '_token' : $('input[name=_token]').val(), 'id' : event.id, 'end' : event.event_date+' '+event.event_time_end, 'event' : 'resize', 'calendar' :  delta._data});
                    },
                    eventMouseover: function(event, jsEvent, view){

                         var title = ($('#show_lang_th').is(':checked')) ? event.title_th : event.title_en;

                         //show เฉพาะเวลากิจกรรมน้อยกว่าเท่ากับ 30 นาที
                         $('.popover.in').remove(); //Remove the popover
                         if (event.type!='S' && parseInt(event.hour_diff)<1 && parseInt(event.minute_diff)<10)
                         {
                             $(this).popover({
                                 title: event.full_date,
                                 placement: 'top',
                                 content: title+' <br/>@'+event.locations,
                                 html: true,
                                 container: 'body'
                             });
                             $(this).popover('show');
                         }
                    }
                });

                /*Event Control Prev Next*/
                $('#prevAgenda').on('click',function(){
                    $('#schedule-agendaDay').fullCalendar('prev');
                    //reload task again
                    loadTasks();
                });

                $('#nextAgenda').on('click',function(){
                    $('#schedule-agendaDay').fullCalendar('next');
                    //reload task again
                    loadTasks();
                });

                /*Event Date Pick*/
                $(event_dates).each(function(index, item) {

                    var monthNames = [
                        "มกราคม", "กุมภาพันธ์", "มีนาคม",
                        "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฏาคม",
                        "สิงหาคม", "กันยายน", "ตุลาคม",
                        "พฤศจิกายน", "ธันวาคม"
                    ];

                    var d = new Date(item);
                    var dateString = d.getDate() + ' ' + monthNames[d.getMonth()] + ' ' + d.getFullYear();

                    $('#event_date').append($("<option>").val(item).text(dateString));
                    $('#accom_date').append($("<option>").val(item).text(dateString));
                    //also fill in copy step
                    $('#event_copy_date').append($("<option>").val(item).text(dateString));
                    $('#accom_copy_date').append($("<option>").val(item).text(dateString));
                });

                /*Event Time range*/
                $('.EventTime').timepicker({
                    'timeFormat': 'H:i',
                    'minTime': '06:00',
                    'maxTime': '23:30'
                });
                /*lang select*/
                $('input[type=radio][name=show_lang]').on('change', function(){
                    loadTasks();
                });

                /*submit to create document*/
                $('#submitCreateDocument').on('click', function(){
                    /*Bootstrap Dialog ask before submit*/
                    var bd = BootstrapDialog.show({
                        title: 'ยืนยันการบันทึกและสร้างเอกสารใหม่',
                        message: 'ท่านต้องทำการบันทึกและสร้างเอกสารใหม่หรือไม่ ?',
                        buttons: [{
                            icon: 'fa fa-floppy-o',
                            label: 'ต้องการ',
                            cssClass: 'btn-success',
                            action: function(dialog) {
                                //close before
                                bd.close();
                                //go to save version and export document
                                var btn = $('#submitCreateDocument').button('loading');
                                $.ajax({
                                    type: "POST",
                                    url: "{{ URL::action('ScheduleController@getDocument') }}",
                                    data: {
                                        '_token' : $("input[name=_token]").val(),
                                        'party_id' : party_id
                                    },
                                    success: function (data) {
                                        btn.button('reset');
                                        if (data.status=='success')
                                        {
                                            //modal with download button
                                            var buttons = [
                                                {
                                                    icon: 'fa fa-download',
                                                    label: 'ดาวน์โหลดเอกสาร',
                                                    action: function(){
                                                        window.open(data.document);
                                                    }
                                                },
                                                {
                                                    icon: 'fa fa-money',
                                                    label: 'จัดการงบประมาณต่อ',
                                                    action: function(){
                                                        window.location.href = "{{ URL::to('coordinator/budget/' . $party->id . '/view') }}";
                                                    }
                                                }
                                            ];
                                            successButton('ทำการบันทึกและสร้างเอกสารสำเร็จ', data.msg, buttons);
                                        }
                                        else
                                        {
                                            errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                                        }
                                    },
                                    dataType: 'json'
                                });
                            }
                        }, {
                            icon: 'fa fa-ban',
                            label: 'ยกเลิก',
                            cssClass: 'btn-danger',
                            action: function(dialog) {
                                dialogItself.close();
                            }
                        }]
                    });
                });

                /*control load by plan a b*/
                $('#show_plan_a, #show_plan_b').on('change', function(){
                   loadTasks();
                });

                /*submit form formEvent*/
                $('form[id=formEvent]').submit(function(e){

                    var create_new = $('input[name=event_new]').val();

                    //check state to make a api url
                    var form_submit_url;
                    //check and fill in copy data
                    var copy_data;

                    if (create_new=='true')
                    {
                        copy_data = 0;//if create new cannot copy this
                        form_submit_url = "{{ URL::action('ScheduleController@postCreate') }}";
                    }
                    else
                    {
                        //check if have a copy state fill data it.
                        if ($('#event_copy_state').val()==1)
                        {
                            copy_data = {
                                'plan' : $('#event_copy_plan').val(), 
                                'event_date' : $('#event_copy_date').val(), 
                                'event_time_start' : $('#event_copy_start').val(), 
                                'event_time_end' : $('#event_copy_end').val() 
                            };
                            form_submit_url = "{{ URL::action('ScheduleController@postCopy') }}";
                        }
                        else
                        {
                            //else not fill
                            copy_data = 0;
                            form_submit_url = "{{ URL::action('ScheduleController@postEdit') }}";
                        }
                    }

                    var data =
                    {
                        '_token' : $("input[name=_token]").val(),
                        'lu_schedule_id' : $('#event_schedule_id').val(),
                        'id' : $('#event_old_id').val(),
                        'type' : 'A',
                        'plan' : $('#event_plan').val(),
                        'event_date' : $('#event_date').val(),
                        'title_th' : $('#event_title_th').val(),
                        'note_th' : $('#event_desc_th').val(),
                        'title_en' : $('#event_title_en').val(),
                        'note_en' : $('#event_desc_en').val(),
                        'locations' : $('#event_location').val(),
                        'old_locations' : $('#event_current_location').val(),
                        'active_locations' : $('#event_current_active').val(),
                        'task_locations' : $('#event_current_task_location').val(),
                        'event_time_start' : $('#event_start').val(),
                        'event_time_end' : $('#event_end').val(),
                        'location_activity_id' : $('#event_plan_active').val(),
                        'copy' : copy_data,
                        'party_id' : party_id
                    };

                    $.ajax({
                        type: "POST",
                        url: form_submit_url,
                        data: data,
                        success: function (data) {
                            if (data.status=='success')
                            {
                                $('#luEventModal').modal('hide');

                                loadTasks();
                            }
                            else
                            {
                                errorAlert('บันทึกไม่สำเร็จ !', data.msg);
                            }
                        },
                        dataType: 'json'
                    });

                    e.preventDefault(); //STOP default action
                });

                 /*submit form formAccom*/
                 $('form[id=formAccom]').submit(function(e){

                     var create_new = $('input[name=accom_new]').val();
                        console.log('555');
                     //check state to make a api url
                     var form_submit_url;
                     //check and fill in copy data
                     var copy_data;

                     if (create_new=='true')
                     {
                        copy_data = 0;//if create new cannot copy this
                        form_submit_url = "{{ URL::action('ScheduleController@postCreate') }}";
                     }
                     else
                     {
                        //check if have a copy state fill data it.
                        if ($('#accom_copy_state').val()==1)
                        {
                            copy_data = {
                                'plan' : $('#accom_copy_plan').val(), 
                                'accom_date' : $('#accom_copy_date').val(), 
                                'accom_night' : $('#accom_copy_night').val()
                            };
                            form_submit_url = "{{ URL::action('ScheduleController@postCopy') }}";
                        }
                        else
                        {
                            //else not fill
                            copy_data = 0;
                            form_submit_url = "{{ URL::action('ScheduleController@postEdit') }}";
                        }
                     }

                     var data = {
                         '_token' : $("input[name=_token]").val(),
                         'lu_schedule_id' : $('#accom_schedule_id').val(),
                         'id' : $('#accom_old_id').val(),
                         'type' : 'S',
                         'plan' : $('#accom_plan').val(),
                         'accom_date' : $('#accom_date').val(),
                         'accom_night' : $('#accom_night').val(),
                         'remark' : $('#accom_desc').val(),
                         'locations' : $('#accom_location').val(),
                         'old_locations' : $('#accom_current_location').val(),
                         'active_locations' : $('#accom_current_active').val(),
                         'task_locations' : $('#accom_current_task_location').val(),
                         'copy' : copy_data,
                         'party_id' : party_id
                     };

                     $.ajax({
                         type: "POST",
                         url: form_submit_url,
                         data: data,
                         success: function (data) {
                             if (data.status=='success')
                             {
                                 $('#luAccomModal').modal('hide');

                                 loadTasks();
                             }
                             else
                             {
                                 errorAlert('บันทึกไม่สำเร็จ !', data.msg);
                             }
                         },
                         dataType: 'json'
                     });

                     e.preventDefault(); //STOP default action
                });
				 
				//query activities template 
				$('.activities').on('keydown', function(){
                    //fill data from key
					var lang = $(this).attr('lang');
					
					$('#event_title_'+lang).typeahead({
						items : 10,
						ajax: {
							url: "{{ URL::to('coordinator/schedule/search-activities') }}",
							method: "post",
							preDispatch: function (query) {
								
								var locations = $('#event_location').val();
								
								return {
									_token: $("input[name=_token]").val(),
									search: query,
									lang: lang,
									locations: locations
								}
							},
							loadingClass: "loading-circle"
						},
						highlighter: function(item){
							return "<div style='white-space:normal !important; word-wrap:break-word;'>" + item + "</div>";
						},
						onSelect: function(item) {
							var id = item.value;
							
							//call ajax to set activities
							$.ajax({
								url: "{{ URL::action('ScheduleController@getActivity') }}",
								data: {
									id : id
								},
								success: function (data) {
									//set plan template
									$('#event_active').val(data.id);
									//set other field too
									if (lang=='th')
									{
                                        //for thai language
										$('#event_desc_th').val(data.note_th);
										$('#event_title_en').val(data.title_en);
										$('#event_desc_en').val(data.note_en);
									}
									else
									{
                                        //for english language
										$('#event_desc_en').val(data.note_en);
										$('#event_title_th').val(data.title_th);
										$('#event_desc_th').val(data.note_th);
									}
									
								},
								dataType: 'json'
							});
						}
					});
				});
				
            @endif

        });

        /*javascript function use when create update delete schedule*/
        @if(isset($party))
            function openCreateSchedule(datetime)
            {
                //show open edit
                $('.open-create').show();
                $('.open-edit').hide();
                //open insert location
                $('.select_locations').prop('disabled', false);
                //set title
                $('#luEventModalLabel > label').html('ลงกำหนดการ');
                //reset form
                $('form[id=formEvent]')[0].reset();
                //set new value
                $('input[name=event_new]').val(true);
                //Also cancel copy
                cancelCopy('A');

                //enable is can copy control
                $('#event_date').prop('disabled', false);
                $('#event_plan').prop('disabled', false);
                $('#event_start').prop('disabled', false);
                $('#event_end').prop('disabled', false);

                //clear old value
                $('#event_location').select2().val('');
               
                //hide open to delete
                $('.openToDelete').hide();
                //open modal
                $('#luEventModal').modal({ keyboard : false });
                //set event on click
                if (typeof datetime != "undefined" || datetime != null)
                {
                    //set datetime by param
                    var d = datetime.split("T");
                    var useDate = d[0];
                    var useTime = d[1];
                    setTimeout(function(){
                        $('#event_date').val(useDate);
                        $('#event_start').val(useTime.substr(0,5));
                    }, 100);
                }
            }

            function openEditSchedule(calEvent)
            {
                //get Data From Fullcalendar
                var data = calEvent;

                //show open edit
                $('.open-edit').show();
                $('.open-create').hide();
                //show open to delete
                $('.openToDelete').show();
                //remove popover before
                $('.popover.in').remove();

                if (data.type=='S')
                {
                    /*For Accommodation Modal*/
                    //Also cancel copy
                    cancelCopy('S');
                    //set title
                    $('#luAccomModalLabel > label').html('แสดงที่พัก');
                    //set new value
                    $('input[name=accom_new]').val(false);
                    //enable is can copy control
                    $('#accom_date').prop('disabled', false);
                    $('#accom_plan').prop('disabled', false);
                    $('#accom_night').prop('disabled', false);

                    $('#accom_schedule_id').val(data.lu_schedule_id);//this is schedule id
                    $('#accom_old_id').val(data.id);
                    $('#accom_date').val(data.event_date);
                    $('#accom_night').val(data.count_days);
                    $('#accom_plan').val(data.plan);
                    $('#accom_current_location').val(JSON.stringify(data.id_locations));
                    $('#accom_current_active').val(JSON.stringify(data.activity_locations));
                    $('#accom_current_task_location').val(JSON.stringify(data.id_task_locations));//use for update or delete task
                    
                    $('#accom_location').select2().val(data.id_locations).change();
                    $('#accom_desc').val(data.remark);

                    //open modal
                    $('#luAccomModal').modal({ keyboard : false });
                }
                else
                {
                    /*For Event Modal*/
                    //Also cancel copy
                    cancelCopy('A');
                    //set title
                    $('#luEventModalLabel > label').html('แสดงกำหนดการ');
                    //set new value
                    $('input[name=event_new]').val(false);
                    //enable is can copy control
                    $('#event_date').prop('disabled', false);
                    $('#event_plan').prop('disabled', false);
                    $('#event_start').prop('disabled', false);
                    $('#event_end').prop('disabled', false);

                    $('#event_schedule_id').val(data.lu_schedule_id);//this is schedule id
                    $('#event_old_id').val(data.id);
                    $('#event_date').val(data.event_date);
                    $('#event_plan').val(data.plan);
                    $('#event_current_location').val(JSON.stringify(data.id_locations));
                    $('#event_current_active').val(JSON.stringify(data.activity_locations));
                    $('#event_current_task_location').val(JSON.stringify(data.id_task_locations));//use for update or delete task

                    $('#event_location').select2().val(data.id_locations).change();

                    $('#event_title_th').val(data.title_th);
                    $('#event_title_en').val(data.title_en);
                    $('#event_start').val(data.event_time_start);
                    $('#event_end').val(data.event_time_end);
                    $('#event_desc_th').val(data.note_th);
                    $('#event_desc_en').val(data.note_en);

                    //open modal
                    $('#luEventModal').modal({ keyboard : false });
                }
            }

            function openCreateAccommodation(date)
            {
                //Also cancel copy
                cancelCopy('S');
                //open insert location
                $('.select_locations').prop('disabled', false);
                //set title
                $('#luAccomModalLabel > label').html('ลงสถานที่พัก');
                //reset form
                $('form[id=formAccom]')[0].reset();
                //set new value
                $('input[name=accom_new]').val(true);

                //enable is can copy control
                $('#accom_date').prop('disabled', false);
                $('#accom_plan').prop('disabled', false);
                $('#accom_night').prop('disabled', false);
               
                //hide open to delete
                $('.openToDelete').hide();
                //clear old value
                $('#accom_location').select2().val('');

                //open modal
                $('#luAccomModal').modal({ keyboard : false });
                //set event on click
                if (typeof date != "undefined" || date != null)
                {
                    //set datetime by param
                    setTimeout(function(){
                        $('#accom_date').val(date);
                    }, 100);
                }
            }

            //open delete
            function openDelete(type)
            {
                cancelCopy(type);//Also cancel copy
                if(confirm('ต้องการยืนยันการลบหรือไม่ ?')==true)
                {
                    $.ajax({
                        type: "POST",
                        url: "{{ URL::action('ScheduleController@postDelete') }}",
                        data :
                        {
                            '_token' : $('input[name=_token]').val(),
                            'id' : (type=='S') ? $('#accom_old_id').val() : $('#event_old_id').val()
                        }
                    }).done(function(data) {
                        if (data.status=='success')
                        {
                            //alert success message
                            $((type=='S') ? '#luAccomModal' : '#luEventModal').modal('hide');

                            loadTasks();

                            successAlert('ทำการลบสำเร็จ !', data.msg);
                        }
                        else
                        {
                            errorAlert('ลบไม่สำเร็จ !', data.msg);
                        }
                    });
                }
                return false;
            }

            /*open to copy a cell of schedule*/
            function openCopy(type)
            {
                //keep control
                var copy_type = (type=='S') ? 'accom' : 'event'; 
                //set pre-copy data
                $('#'+copy_type+'_copy_date').val($('#'+copy_type+'_date').val()).change();
                $('#'+copy_type+'_copy_plan').val($('#'+copy_type+'_plan').val()).change();

                if (copy_type=='accom')
                {
                    $('#'+copy_type+'_copy_night').val($('#'+copy_type+'_night').val()).change();
                }
                else
                {
                    $('#'+copy_type+'_copy_start').val($('#'+copy_type+'_start').val());
                    $('#'+copy_type+'_copy_end').val($('#'+copy_type+'_end').val());
                }
                //disabled is can copy control
                $('#'+copy_type+'_date').prop('disabled', true);
                $('#'+copy_type+'_plan').prop('disabled', true);

                if (copy_type=='accom')
                {
                    $('#'+copy_type+'_night').prop('disabled', true);
                }
                else
                {
                    $('#'+copy_type+'_start').prop('disabled', true);
                    $('#'+copy_type+'_end').prop('disabled', true);
                }
                //show copy panel
                $('.openToCopy').show();
                //set copy state is true
                $('#'+copy_type+'_copy_state').val(1);
                //hide copy button
                $('.open-edit').hide();
            }
            /*cancel to copy a cell*/
            function cancelCopy(type)
            {
                //keep control
                var copy_type = (type=='S') ? 'accom' : 'event'; 
                //enabled is can copy control
                $('#'+copy_type+'_date').prop('disabled', false);
                $('#'+copy_type+'_plan').prop('disabled', false);

                if (copy_type=='accom')
                {
                    $('#'+copy_type+'_night').prop('disabled', false);
                }
                else
                {
                    $('#'+copy_type+'_start').prop('disabled', false);
                    $('#'+copy_type+'_end').prop('disabled', false);
                }
                //hide copy panel
                $('.openToCopy').hide();
                //set copy state is false
                $('#'+copy_type+'_copy_state').val(0);
                 //re-show copy button
                $('.open-edit').show();
            }

            /*Update DateTime by drag and resize*/
            function updateScheduleMoment(data)
            {
                if (typeof data == "undefined" || data == null)
                {
                    return false;
                }

                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('ScheduleController@postCalendarTime') }}",
                    data: data,
                    success: function (data) {
                        if (data.status=='success')
                        {
                            //alert success message
                            //$('#schedule-agendaDay').fullCalendar( 'updateEvent', data.event );
                            loadTasks();
                        }
                        else
                        {
                            errorAlert('บันทึกข้อมูลไม่สำเร็จ !', data.msg);
                            loadTasks();
                        }
                    },
                    dataType: 'json'
                });
            }

            function loadTasks()
            {
                $.ajax({
                    url: "{{ URL::action('ScheduleController@getTasks') }}",
                    data:
                    {
                        '_token' : $('input[name=_token]').val(),
                        'party_id' : party_id,
                        'plan_a' : $("#show_plan_a").is(':checked'),
                        'plan_b' : $("#show_plan_b").is(':checked'),
                        'lang' : $('input[type=radio][name=show_lang]:checked').val(),
                        'start' : '{{ (isset($party)) ? $party->start_date : 0 }}',
                        'end' : '{{ (isset($party)) ? date('Y-m-d', strtotime($party->end_date . ' + 1 day')) : 0 }}'
                    },
                    dataType: 'json',
                    success : function(data){
                        $('#schedule-agendaDay').fullCalendar('removeEvents');
                        $('#schedule-agendaDay').fullCalendar('addEventSource', data);
                    }
                });
            }

        @endif
    </script>

@stop