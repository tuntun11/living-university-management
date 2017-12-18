{{--Show calendar for parties status--}}
<div id="party_transaction_review" class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-tasks" aria-hidden="true"></i> ปฎิทินแสดงสถานะคณะดูงาน</h3>
    </div>
    <div class="panel-body">
        {{--Filter Party Event Data--}}
        <form role="form">
            <div class="control-group" style="margin-bottom: 15px;">
                <span class="button-checkbox">
                    <button type="button" class="btn btn-xs" data-color="info">กำลังติดต่อ</button>
                    <input type="checkbox" class="hidden" id="is_dealing" value="1" checked />
                </span>
                <span class="button-checkbox">
                    <button type="button" class="btn btn-xs" data-color="primary">กำลังเตรียมรับคณะ</button>
                    <input type="checkbox" class="hidden" id="is_dealt" value="1" checked />
                </span>
                <span class="button-checkbox">
                    <button type="button" class="btn btn-xs" data-color="success">คณะที่กำลังรับอยู่หรือรับเสร็จแล้ว</button>
                    <input type="checkbox" class="hidden" id="is_finish" value="1" checked />
                </span>
                <span class="button-checkbox">
                    <button type="button" class="btn btn-xs" data-color="warning">คณะที่เลื่อนกำหนดการ</button>
                    <input type="checkbox" class="hidden" id="is_postpone" value="1" />
                </span>
                <span class="button-checkbox">
                    <button type="button" class="btn btn-xs" data-color="danger">คณะที่ยกเลิก</button>
                    <input type="checkbox" class="hidden" id="is_cancelled" value="1" />
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
        <div role="tabpanel">

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist" id="party_transaction_review_tab">
                <li role="presentation" class="active"><a href="#transaction-tab-calendar" aria-controls="transaction-tab-calendar" role="tab" data-toggle="tab"> <span class="fa fa-calendar" aria-hidden="true"></span> ปฎิทิน</a></li>
                <li role="presentation"><a href="#transaction-tab-grid" aria-controls="transaction-tab-grid" role="tab" data-toggle="tab"> <span class="fa fa-table" aria-hidden="true"></span> ตาราง</a></li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="transaction-tab-calendar">
                    <div id='transaction-calendar'></div>
                </div>
                <div role="tabpanel" class="tab-pane" id="transaction-tab-grid">
                    <div class="panel">
                        <div class="panel-body">
                            {{--Start Data Table Plugin--}}
                            <div class="table-responsive">
                                <table id="transaction-grid" class="table table-condensed responsive dt-responsive">
                                    <thead>
                                    <tr>
                                        <th>รหัส</th>
                                        <th>ชื่อคณะ/บุคคล</th>
                                        <th>ช่วงวันที่</th>
                                        <th>ประสานงานหลัก</th>
                                        <th>คณะทำงาน</th>
                                        <th>View</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            {{--Finish Data Table Plugin--}}
                        </div>
                    </div>
                </div>
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

    $( "document:not(input#search-quick)" ).ajaxStart(function() {
        dialog.open();
    });
    $( "document:not(input#search-quick)" ).ajaxStop(function() {
        dialog.close();
    });
    //declare default value
    var activeTab = '#transaction-tab-calendar';
    $(function () {

        /*tooltip*/
        $('[data-toggle="tooltip"]').tooltip();

        /*data tables event*/
        var table = $('#transaction-grid').DataTable({
            "responsive": true,
            "language": {
                "url": "{{ URL::asset('assets/js/Thai.json') }}"
            },
            "processing": true,
            "serverSide": true,
            "deferLoading": 0, // default not loading
            "ajax": {
                "url" : "{{ URL::action('PartyController@getAllData') }}",
                "data" : function(d)
                {
                    d.is_dealing = $("#is_dealing").is(':checked');
                    d.is_dealt = $("#is_dealt").is(':checked');
                    d.is_finish = $("#is_finish").is(':checked');
                    d.is_postpone = $("#is_postpone").is(':checked');
                    d.is_cancelled = $("#is_cancelled").is(':checked');
                    d.personnels = $("#comboCoordinator").val();
                }
            },
            "columns":
                    [
                        { "data" : "customer_code", "className": 'col-md-1', "title" : "รหัส", "orderable": true, "searchable": true },
                        { "data" : "party_name", "className": 'col-md-3', "title" : "คณะ/บุคคล", "orderable": true, "searchable": true },
                        { "data" : "start_date", "className": 'col-md-2', "title" : "ช่วงวันที่", "orderable": true, "searchable": true },
                        { "data" : "coordinator_name", "className": 'col-md-2', "title" : "ประสานงานหลัก", "orderable": true, "searchable": true },
                        { "data" : "staffs", "className": 'col-md-3', "title" : "คณะทำงาน", "orderable": true, "searchable": true },
                        { "data" : "more_detail", "className": 'col-md-1', "title" : "View", "orderable": false, "searchable": false }
                    ],
            "fnDrawCallback": function ( oSettings ) {
            },
            "createdRow": function( row, data, dataIndex ) {

                if ( data['status'] == "reviewing" || data['status'] == "reviewed" || data['status'] == "approved" ) {
                    //กำลังติดต่อผ่าน flow
                    $(row).addClass( 'info' );
                } else if ( data['status'] == "terminated" || data['status'] == "cancelled1" || data['status'] == "cancelled2" ) {
                    //ยกเลิกไปแล้ว
                    $(row).addClass( 'danger' );
                } else if ( data['status'] == "preparing" || data['status'] == "ongoing" ) {
                    //กำลังติดต่อในการรับคณะ
                    $(row).addClass( 'row-primary' );
                } else if ( data['status'] == "finishing" || data['status'] == "finished" ) {
                    //รับคณะไปแล้ว
                    $(row).addClass( 'success' );
                } else {
                    //เลื่อนรับ
                    $(row).addClass( 'warning' );
                }

            }
        });

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

                var base = "";
                if (calEvent.base)
                {
                    base = '<br/> พื้นที่ดูงาน : ' + calEvent.base;
                }

                var tooltip = '<div class="tooltipevent" style="padding: 5px;width:auto;height:auto;background:#EEEEEE;position:absolute;z-index:10001;">' + calEvent.title + project_co + base + '</div>';
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

        /*Hook Event Tab*/
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");
            activeTab = $(e.target).attr("href");
            if ((target == '#transaction-tab-calendar')) {
                $('#transaction-calendar').fullCalendar('render');
            }
            else if ((target == '#transaction-tab-grid')) {
                $('#transaction-grid').DataTable().draw();
            }
            else
            {
                //do nothing
            }
        });

        /*coordinator select*/
        $("#comboCoordinator")
                .select2({
                    placeholder: "ผู้ประสานงานหลัก"
                })
                .on('change', function(e){
                    if (activeTab=='#transaction-tab-calendar')
                    {
                        //load calendar
                        loadCalendar();
                    }else{
                        //load table
                        $('#transaction-grid').DataTable().draw();
                    }
                    console.log(activeTab);
                });

        /*Hook button checkbox checked*/
        $('#is_dealing, #is_dealt, #is_finish, #is_postpone, #is_cancelled').on('change', function(e){
            if (activeTab=='#transaction-tab-calendar')
            {
                //load calendar
                loadCalendar();
            }else{
                //load table
                $('#transaction-grid').DataTable().draw();
            }
        });
    });

    function loadCalendar()
    {
        var start = $('#transaction-calendar').fullCalendar('getView').start._d;
        var end = $('#transaction-calendar').fullCalendar('getView').end._d;
        $.ajax({
            url: "{{ URL::action('PartyController@getAllEvents') }}",
            data:
            {
                'is_dealing' : $("#is_dealing").is(':checked'),
                'is_dealt' : $("#is_dealt").is(':checked'),
                'is_finish' : $("#is_finish").is(':checked'),
                'is_postpone' : $("#is_postpone").is(':checked'),
                'is_cancelled' : $("#is_cancelled").is(':checked'),
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