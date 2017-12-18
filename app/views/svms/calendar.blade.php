@extends('svms.layouts.landing')

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
@stop

@section('extraStyles')
    <style type="text/css">

    </style>
@stop

@section('header')
    <span class="fa fa-calendar"></span>
    ปฎิทินคณะดูงาน Living University
@stop

@section('content')

    <div class="panel panel-default">
        <div class="panel-body">

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a id="status" href="#tab-status" aria-controls="tab-status" role="tab" data-toggle="tab"><i class="fa fa-tasks" aria-hidden="true"></i> <span class="text-lg">สถานะ</span></a></li>
                <li role="presentation"><a id="location" href="#tab-location" aria-controls="tab-location" role="tab" data-toggle="tab"><i class="fa fa-map-marker" aria-hidden="true"></i> <span class="text-lg">สถานที่ดูงาน</span></a></li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="tab-status">
                    <div class="pull-right" style="margin-bottom: 10px;">
                        <span class="label label-info">สถานะ : อยู่ในระหว่างยื่นเรื่องเพื่อพิจารณา</span> <span class="label label-primary">สถานะ : ผ่านการพิจารณาแล้ว</span> <span class="label label-danger">สถานะ : ปฎิเสธการรับคณะ</span>
                    </div>
                    <div class="clearfix"></div>
                    <div id="calendar-status"></div>
                </div>

                <div role="tabpanel" class="tab-pane" id="tab-location">
                    <div class="pull-right" style="margin-bottom: 10px;">
                        <span class="label label-doitung">โครงการพัฒนาดอยตุงฯ</span> <span class="label label-nan">โครงการปลูกป่าน่าน</span> <span class="label label-dtandnan">ดอยตุงและน่าน</span> <span class="label label-bkk">สำนักงานกรุงเทพฯ</span> <span class="label label-otherplace">อื่นๆ</span>
                    </div>
                    <div class="clearfix"></div>
                    <div id="calendar-location"></div>
                </div>
            </div>

        </div>
    </div>

    <script type="text/javascript">

        //pre action with ajax load
        var dialog = new BootstrapDialog({
            type:  	BootstrapDialog.TYPE_DEFAULT,
            title: '<strong>กรุณารอสักครู่</strong>',
            message: '<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i> <span>กำลังโหลดข้อมูลปฎิทิน...</span>',
            closable: false
        });

        $( document ).ajaxStart(function() {
            dialog.open();
        });
        $( document ).ajaxStop(function() {
            dialog.close();
        });

        $(function () {

            /*Check load calendar*/
            var activeTab = 'status';

            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                activeTab = $(e.target).attr('id');
                //console.log(activeTab);
                if (activeTab=='status')
                {
                    $('#calendar-status').fullCalendar('render');
                    //loadCalendar('status');
                }
                else
                {
                    $('#calendar-location').fullCalendar('render');
                    //loadCalendar('location');
                }
            });

            /* Full Calendar Party Schedule Feed */
            $('#calendar-status').fullCalendar({
                buttonText: {
                    prev : '<<',
                    next : '>>',
                    prevYear : '<< ปีที่แล้ว',
                    nextYear : 'ปีหน้า >>',
                    listMonth : 'รายวัน'
                },
                header: {
                    left: 'month,basicWeek,listMonth',
                    center: 'title',
                    right: 'prev,next'
                },
                views: {
                    month: {
                        columnFormat: (screen.width<480) ? 'dd' : 'dddd'
                    },
                    basicWeek: {
                        titleFormat: (screen.width<480) ? 'D MMM YYYY' : 'D MMMM YYYY',
                        columnFormat: (screen.width<480) ? 'dd DD/M' : 'dddd DD/M'
                    },
                    listMonth: {
                        columnFormat: (screen.width<480) ? 'dd' : 'dddd',
                        displayEventTime: false,
                        displayEventEnd: false
                    }
                },
                editable: false,
                eventLimit: true, // allow "more" link when too many events
                loading: function( isLoading, view ){

                },
                viewRender: function( view, element ) {
                    loadCalendar('status');
                },
                eventMouseover: function(calEvent, jsEvent) {
                    var tooltip = '<div class="tooltipevent" style="padding: 5px;width:auto;height:auto;background:#EEEEEE;position:absolute;z-index:10001;">' + calEvent.title + '</div>';
                    $("body").append(tooltip);
                    $(this).mouseover(function(e) {
                        $(this).css('z-index', 10000);
                        $('.tooltipevent').fadeIn('500');
                        $('.tooltipevent').fadeTo('10', 1.9);
                    }).mousemove(function(e) {
                        $('.tooltipevent').css('top', e.pageY + 10);
                        $('.tooltipevent').css('left', e.pageX + 20);
                    });
                },
                eventMouseout: function(calEvent, jsEvent) {
                    $(this).css('z-index', 8);
                    $('.tooltipevent').remove();
                }
            });

            $('#calendar-location').fullCalendar({
                buttonText: {
                    prev : '<<',
                    next : '>>',
                    prevYear : '<< ปีที่แล้ว',
                    nextYear : 'ปีหน้า >>',
                    listMonth : 'รายวัน'
                },
                header: {
                    left: 'month,basicWeek,listMonth',
                    center: 'title',
                    right: 'prev,next'
                },
                views: {
                    month: {
                        columnFormat: (screen.width<480) ? 'dd' : 'dddd'
                    },
                    basicWeek: {
                        titleFormat: (screen.width<480) ? 'D MMM YYYY' : 'D MMMM YYYY',
                        columnFormat: (screen.width<480) ? 'dd DD/M' : 'dddd DD/M'
                    },
                    listMonth: {
                        columnFormat: (screen.width<480) ? 'dd' : 'dddd',
                        displayEventTime: false,
                        displayEventEnd: false
                    }
                },
                editable: false,
                eventLimit: true, // allow "more" link when too many events
                loading: function( isLoading, view ){

                },
                viewRender: function( view, element ) {
                    loadCalendar('location');
                },
                eventMouseover: function(calEvent, jsEvent) {
                    var tooltip = '<div class="tooltipevent" style="padding: 5px;width:auto;height:auto;background:#EEEEEE;position:absolute;z-index:10001;">' + calEvent.title + '</div>';
                    $("body").append(tooltip);
                    $(this).mouseover(function(e) {
                        $(this).css('z-index', 10000);
                        $('.tooltipevent').fadeIn('500');
                        $('.tooltipevent').fadeTo('10', 1.9);
                    }).mousemove(function(e) {
                        $('.tooltipevent').css('top', e.pageY + 10);
                        $('.tooltipevent').css('left', e.pageX + 20);
                    });
                },
                eventMouseout: function(calEvent, jsEvent) {
                    $(this).css('z-index', 8);
                    $('.tooltipevent').remove();
                }
            });

        });

        //load เฉพาะคณะที่รับแล้ว
        function loadCalendar(v)
        {
            if ($('#calendar-'+v).fullCalendar('getView').start==null)
            {
                var start = '2017-03-26';
                var end = '2017-05-07';
            }
            else
            {
                var start = $('#calendar-'+v).fullCalendar('getView').start._d;
                var end = $('#calendar-'+v).fullCalendar('getView').end._d;
            }

            $.ajax({
                url: "{{ URL::to('calendar-events') }}",
                data:
                {
                    'start' : convertDate(start),
                    'end' : convertDate(end),
                    'view' : v
                },
                dataType: 'json',
                success : function(data){
                    $('#calendar-'+v).fullCalendar('removeEvents');
                    $('#calendar-'+v).fullCalendar('addEventSource', data);
                }
            });
        }

        function convertDate(inputFormat) {
            function pad(s) { return (s < 10) ? '0' + s : s; }
            var d = new Date(inputFormat);
            return [d.getFullYear(), pad(d.getMonth()+1), pad(d.getDate())].join('-');
        }
    </script>

@stop