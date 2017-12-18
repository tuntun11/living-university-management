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
    {{--Use Data Tables--}}
    {{ HTML::style('dependencies/DataTables/media/css/jquery.dataTables.min.css') }}
    {{ HTML::script('dependencies/DataTables/media/js/jquery.dataTables.min.js') }}
    {{--Use Data Tables Extension Responsive--}}
    {{-- HTML::style('//cdn.datatables.net/responsive/1.0.3/css/dataTables.responsive.css') --}}
    {{-- HTML::script('//cdn.datatables.net/responsive/1.0.3/js/dataTables.responsive.js') --}}
    {{--Use Data Tables Extension Bootstrap--}}
    {{-- HTML::style('//cdn.datatables.net/plug-ins/3cfcc339e89/integration/bootstrap/3/dataTables.bootstrap.css') --}}
    {{-- HTML::script('//cdn.datatables.net/plug-ins/3cfcc339e89/integration/bootstrap/3/dataTables.bootstrap.js') --}}
@stop

@section('extraStyles')
    <style type="text/css">
        td.details-control {
            background: url("{{ asset('assets/img/details_open.png') }}") no-repeat center center;
            cursor: pointer;
        }
        tr.shown td.details-control {
            background: url("{{ asset('assets/img/details_close.png') }}") no-repeat center center;
        }
    </style>
@stop

@section('header')
    <i class="fa fa-th-list" aria-hidden="true"></i>
    จัดการข้อมูลคณะที่ดำเนินการรับรอง
@stop

@section('content')

    <div class="col-xs-12 col-md-12">
        {{--Start Data Table Plugin--}}
        <table id="grid-parties" class="table table-condensed table-hover" cellspacing="0">
            <thead>
            <tr>
                <th class="col-sm-1 col-md-1"></th>
                <th class="col-sm-2 col-md-2">รหัสลูกค้า</th>
                <th class="col-sm-4 col-md-4">ชื่อคณะ</th>
                <th class="col-sm-2 col-md-2">ช่วงวันที่</th>
                <th class="col-sm-3 col-md-3">สถานะ</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        {{--Finish Data Table Plugin--}}
    </div>

    <script type="text/javascript">

        $(function () {
            /*bootstrap popover*/
            $('[data-toggle="popover"]').popover();
            /* DataTables */
            var table = $('#grid-parties').DataTable({
                "language": {
                    "url": "{{ URL::asset('assets/js/Thai.json') }}"
                },
                "order": [[1,'desc']],
                "processing": true,
                "serverSide": true,
                "ajax": "{{{ URL::action('PartyController@getData') }}}",
                "columns":
                [
                    //title will auto-generate th columns
                    {
                        "className": 'details-control col-sm-1 col-md-1', "orderable": false, "data": null, "defaultContent": ''
                    },
                    { "data" : "customer_code", "className": 'col-sm-2 col-md-2', "title" : "รหัสลูกค้า", "orderable": true, "searchable": true },
                    { "data" : "name", "className": 'col-sm-4 col-md-4', "title" : "ชื่อคณะ", "orderable": true, "searchable": true },
                    { "data" : "start_date", "className": 'col-sm-2 col-md-2', "title" : "ช่วงวันที่", "orderable": true, "searchable": true },
                    { "data" : "status", "className": 'col-sm-3 col-md-3', "title" : "สถานะ", "orderable": true, "searchable": false }
                ],
                "fnDrawCallback": function ( oSettings ) {
                }
            });

            // Add event listener for opening and closing details
            $('#grid-parties tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = table.row(tr);

                if ( row.child.isShown() ) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    // Open this row
                    row.child( format(row.data()) ).show();
                    tr.addClass('shown');
                }
            } );

        });

        /* Formatting function for row details - modify as you need */
        function format ( d )
        {
            return getPartyExpansion(d);
        }

        /*popover request person*/
        function popRequestPerson(id, name, tel, email)
        {
            var options = {
                content : 'ชื่อผู้กรอก : '+ name + '<br/> เบอร์ติดต่อ : ' + tel + '<br/> อีเมล : ' + email,
                title : 'ข้อมูลผู้กรอก',
                placement : 'top',
                container : 'body',
                html : true,
                trigger : 'click'
            };
            $('#person'+id).popover(options);
            $('#person'+id).popover('toggle')
        }

        /*popover contact person*/
        function popContactPerson(id, name, tel, email)
        {
            var options = {
                content : 'ชื่อผู้ติดต่อ : '+ name + '<br/> เบอร์ติดต่อ : ' + tel + '<br/> อีเมล : ' + email,
                title : 'ข้อมูลผู้ติดต่อ',
                placement : 'top',
                container : 'body',
                html : true,
                trigger : 'click'
            };
            $('#coordinator'+id).popover(options);
            $('#coordinator'+id).popover('toggle')
        }

    </script>

@stop