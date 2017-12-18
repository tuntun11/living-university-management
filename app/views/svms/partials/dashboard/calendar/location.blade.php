{{--Show calendar for parties location base--}}
<div id="party_transaction_review" class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-map-marker" aria-hidden="true"></i> ปฎิทินแสดงสถานที่ดูงาน</h3>
    </div>
    <div class="panel-body">
        {{--Filter Party Event Data--}}
        <form role="form">
            <div class="control-group" style="margin-bottom: 15px;">
                <span class="button-checkbox">
                    <button type="button" class="btn btn-xs" data-color="doitung" title="ดอยตุง+ปางมะหัน+ปูนะ">โครงการพัฒนาดอยตุงฯ</button>
                    <input type="checkbox" class="hidden" id="location_dt" value="1" checked />
                </span>
                <span class="button-checkbox">
                    <button type="button" class="btn btn-xs" data-color="nan" title="น่าน">โครงการปลูกป่าน่าน</button>
                    <input type="checkbox" class="hidden" id="location_nan" value="1" checked />
                </span>
                <span class="button-checkbox">
                    <button type="button" class="btn btn-xs" data-color="dtandnan" title="ดอยตุง+ปางมะหัน+ปูนะ+น่าน">ดอยตุงและน่าน</button>
                    <input type="checkbox" class="hidden" id="location_all" value="1" checked />
                </span>
                <span class="button-checkbox">
                    <button type="button" class="btn btn-xs" data-color="bkk" title="สำนักงานพระราม 4 กรุงเทพฯ">สำนักงานกรุงเทพฯ</button>
                    <input type="checkbox" class="hidden" id="location_bkk" value="1" checked />
                </span>
                <span class="button-checkbox">
                    <button type="button" class="btn btn-xs" data-color="otherplace">อื่นๆ</button>
                    <input type="checkbox" class="hidden" id="location_other" value="1" checked />
                </span>
            </div>
            <div class="control-group" style="margin-bottom: 15px;">
                <select id="comboCoordinator" name="coordinators" class="form-control" multiple="multiple" style="width: 100%;">
                    @foreach($personnels as $personnel)
                        <option value="{{ $personnel->id }}">{{ $personnel->shortName() }}</option>
                    @endforeach
                </select>
            </div>
        </form>
        {{--Tab Party Event Data--}}
        <p id="location-base-calendar">
            <div id='transaction-calendar'></div>
        </p>
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

    $( "document:not(input#search-quick)" ).ajaxStart(function() {
        dialog.open();
    });
    $( "document:not(input#search-quick)" ).ajaxStop(function() {
        dialog.close();
    });

    //declare default value
    $(function () {

        /*tooltip*/
        $('[data-toggle="tooltip"]').tooltip();

        /* Full Calendar Party Schedule Feed */
        $('#transaction-calendar').fullCalendar({
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
            defaultView: 'month',
            editable: false,
            eventLimit: true, // allow "more" link when too many events
            viewRender: function( view, element ) {
                loadCalendar();
            },
            eventRender: function(event, element) {
                //element.attr('title', 'คลิกที่ป้ายเพื่อแสดงข้อมูลเพิ่มเติม');
                if (event.project_coordinator) {
                    element.find('span.fc-title')
                            .append("<span class='project_coordinator'> - " + event.project_coordinator) + "</span>";
                }
                element.addClass(event.class)
            },
            eventClick: function(calEvent, jsEvent, view) {
                var data = calEvent;
                window.open('party/' + data.id + '/view', '_self');
            },
            eventMouseover: function(calEvent, jsEvent) {

                var project_co = "";
                if (calEvent.project_coordinator)
                {
                    project_co = '<br/> ผู้ประสานงานหลัก : ' + calEvent.project_coordinator;
                }

                var tooltip = '<div class="tooltipevent" style="padding: 5px;width:auto;height:auto;background:#EEEEEE;position:absolute;z-index:10001;">' + calEvent.title + project_co + '</div>';
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

        /*coordinator select*/
        $("#comboCoordinator")
                .select2({
                    placeholder: "ผู้ประสานงานหลัก"
                })
                .on('change', function(e){
                    //load calendar
                    loadCalendar();
                });

        /*Hook button checkbox checked*/
        $('#location_dt, #location_nan, #location_all, #location_bkk, #location_other').on('change', function(e)
        {
            //load calendar
            loadCalendar();
        });
    });

    function loadCalendar()
    {
        var start = $('#transaction-calendar').fullCalendar('getView').start._d;
        var end = $('#transaction-calendar').fullCalendar('getView').end._d;
        $.ajax({
            url: "{{ URL::action('PartyController@getAllLocations') }}",
            data:
            {
                'dt' : $("#location_dt").is(':checked'),
                'nan' : $("#location_nan").is(':checked'),
                'all' : $("#location_all").is(':checked'),
                'bkk' : $("#location_bkk").is(':checked'),
                'other' : $("#location_other").is(':checked'),
                'start' : convertDate(start),
                'end' : convertDate(end),
                'personnels' : $("#comboCoordinator").val()
            },
            dataType: 'json',
            success : function(data){
                $('#transaction-calendar').fullCalendar('removeEvents');
                $('#transaction-calendar').fullCalendar('addEventSource', data);
            }
        });
    }

    function convertDate(inputFormat) {
        function pad(s) { return (s < 10) ? '0' + s : s; }
        var d = new Date(inputFormat);
        return [d.getFullYear(), pad(d.getMonth()+1), pad(d.getDate())].join('-');
    }
</script>