{{--หน้า Reviewer Index--}}
@extends('svms.layouts.default')

@section('title')
    Living University Management System :: Review and Approval
@stop

@section('extraScripts')
    {{--Use DataTables--}}
    {{ HTML::script('dependencies/DataTables/media/js/jquery.dataTables.min.js') }}
    {{ HTML::style('dependencies/DataTables/media/css/jquery.dataTables.min.css') }}
    {{--Use DataTables fnReload--}}
    {{ HTML::script('assets/js/fnReloadAjax.js') }}
    {{--Use Bootstrap Select2--}}
    {{ HTML::script('dependencies/select2-4.0.0-beta.3/dist/js/select2.min.js') }}
    {{ HTML::style('dependencies/select2-4.0.0-beta.3/dist/css/select2.min.css') }}
    {{--Use Bootstrap TinyMCE--}}
    {{ HTML::script('dependencies/tinymce/js/tinymce/tinymce.min.js') }}
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
        .padding-beautiful{
            margin: 10px 5px;
            width: 98%;
        }
    </style>
@stop

@section('header')
    <span class="fa fa-check-square-o"></span>
    ตรวจสอบและอนุมัติคำร้อง
@stop

@section('content')

    <form role="form" id="formReview">
        <!-- CSRF Token -->
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <!-- ./ csrf token -->
        <div class="col-xs-12 col-md-12">
            <div role="tabpanel">

                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist" id="reviewer_tab">
                    <li role="presentation" class="active"><a href="#backlog" aria-controls="backlog" role="tab" data-toggle="tab"><i class="fa fa-tasks"></i> <span class="text-lg">ภาระงานค้าง</span></a></li>
                    <li role="presentation"><a id="show_history" href="#history" aria-controls="history" role="tab" data-toggle="tab"><i class="fa fa-history"></i> <span class="text-lg">ภาระงานที่ผ่านมาของท่าน</span></a></li>
                    <li role="presentation"><a id="show_all_history" href="#allhistory" aria-controls="allhistory" role="tab" data-toggle="tab"><i class="fa fa-globe"></i> <span class="text-lg">งานตรวจสอบทั้งหมด</span></a></li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">

                    <div role="tabpanel" class="tab-pane active" id="backlog">
                        <div class="panel" style="margin-top: 0px;">
                            <div class="panel-body">
                                <table id="reviewer" class="table table-condensed table-hover">
                                    <thead>
                                    <tr>
                                        <th class="col-md-1 col-sm-1"></th>
                                        <th class="col-md-7 col-sm-7">ชื่อคณะ</th>
                                        <th class="col-md-4 col-sm-4">สถานะงาน</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div role="tabpanel" class="tab-pane" id="history">
                        <div class="panel" style="margin-top: 0px;">
                            <div class="panel-body">
                                <table id="reviewed" class="table table-condensed table-hover">
                                    <thead>
                                    <tr>
                                        <th class="col-md-1 col-sm-1"></th>
                                        <th class="col-md-7 col-sm-7">ชื่อคณะ</th>
                                        <th class="col-md-4 col-sm-4">สถานะงาน</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div role="tabpanel" class="tab-pane" id="allhistory">
                        <div class="panel" style="margin-top: 0px;">
                            <div class="panel-body">
                                <table id="all_reviewed" class="table table-condensed table-hover">
                                    <thead>
                                    <tr>
                                        <th class="col-md-1 col-sm-1"></th>
                                        <th class="col-md-7 col-sm-7">ชื่อคณะ</th>
                                        <th class="col-md-4 col-sm-4">สถานะงาน</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>

    <script type="text/javascript">
        $(document).ready(function() {

            /*Enable TinyMce Rich Text*/
            tinymce.init({
                selector: 'textarea',
                language: 'th_TH'
            });
            /*bootstrap popover*/
            $('[data-toggle="popover"]').popover();
            /*Select2 */
            $('#reviewer_other_receive').select2({
                tags: true
            });
            /*DataTables*/
            var table = $('#reviewer').DataTable({
                "language": {
                    "url": "{{ URL::asset('assets/js/Thai.json') }}"
                },
                "order": [[1, 'desc']],
                "processing": true,
                "serverSide": true,
                "ajax": "{{ URL::action('ReviewerController@getData') }}",
                "columns": [
                    //title will auto-generate th columns
                    {
                        "className": 'details-control col-md-1 col-sm-1',
                        "orderable": false,
                        "data": null,
                        "defaultContent": ''
                    },
                    {
                        "data": "name",
                        "className": 'col-md-7 col-sm-7',
                        "title": "ชื่อคณะ",
                        "orderable": true,
                        "searchable": true
                    },
                    {
                        "data": "status",
                        "className": 'col-md-4 col-sm-4',
                        "title": "สถานะงาน",
                        "orderable": false,
                        "searchable": false
                    }
                ],
                "fnDrawCallback": function (oSettings) {
                }
            });

            // Add event listener for opening and closing details
            $('#reviewer tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = table.row(tr);

                if (row.child.isShown()) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    // Open this row
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                }
            });

            /*reviewed*/
            var reviewed = $('#reviewed').DataTable({
                "language": {
                    "url": "{{ URL::asset('assets/js/Thai.json') }}"
                },
                "order": [[1, 'desc']],
                "deferLoading": 0, // default not loading
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ URL::action('ReviewerController@getData') }}",
                    "data": {
                        "is_history": 1
                    }
                },
                "columns": [
                    //title will auto-generate th columns
                    {
                        "className": 'details-control col-md-1 col-sm-1',
                        "orderable": false,
                        "data": null,
                        "defaultContent": ''
                    },
                    {
                        "data": "name",
                        "className": 'col-md-7 col-sm-7',
                        "title": "ชื่อคณะ",
                        "orderable": true,
                        "searchable": true
                    },
                    {
                        "data": "status",
                        "className": 'col-md-4 col-sm-4',
                        "title": "สถานะงาน",
                        "orderable": false,
                        "searchable": false
                    }
                ],
                "fnDrawCallback": function (oSettings) {
                }
            });

            $('#reviewed tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = reviewed.row(tr);

                if (row.child.isShown()) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    // Open this row
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                }
            });

            /*all reviewed*/
            var all_reviewed = $('#all_reviewed').DataTable({
                "language": {
                    "url": "{{ URL::asset('assets/js/Thai.json') }}"
                },
                "order": [[1, 'desc']],
                "deferLoading": 0, // default not loading
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ URL::action('ReviewerController@getData') }}",
                    "data": {
                        "is_history": 1,
                        "is_all": 1
                    }
                },
                "columns": [
                    //title will auto-generate th columns
                    {
                        "className": 'details-control col-md-1 col-sm-1',
                        "orderable": false,
                        "data": null,
                        "defaultContent": ''
                    },
                    {
                        "data": "name",
                        "className": 'col-md-7 col-sm-7',
                        "title": "ชื่อคณะ",
                        "orderable": true,
                        "searchable": true
                    },
                    {
                        "data": "status",
                        "className": 'col-md-4 col-sm-4',
                        "title": "สถานะงาน",
                        "orderable": false,
                        "searchable": false
                    }
                ],
                "fnDrawCallback": function (oSettings) {
                }
            });

            $('#all_reviewed tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = all_reviewed.row(tr);

                if (row.child.isShown()) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    // Open this row
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                }
            });

            /*check active tab when click history tab load data*/
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                if ($(e.target).attr('id') == 'show_history') {
                    //reload reviewed data when click
                    reviewed.ajax.reload();
                    reviewed.draw();
                    $('#accept_create_new, #other_create_new, #cancel_create_new').val(0);
                } else if ($(e.target).attr('id') == 'show_all_history') {
                    //reload all reviewed data when click
                    all_reviewed.ajax.reload();
                    all_reviewed.draw();
                    $('#accept_create_new, #other_create_new, #cancel_create_new').val(0);
                } else {
                    table.ajax.reload();
                    table.draw();
                    $('#accept_create_new, #other_create_new, #cancel_create_new').val(1);
                }
            });

        });

        /* Formatting function for row details - modify as you need */
        function format (d)
        {
            return getPartyExpansion(d, 1);
        }

        /*for delete cache or incomplete send*/
        function deleteCache(party_id)
        {
            $('#btnDelRequestNotComplete'+party_id).button('loading');

            $.ajax({
                type: "POST",
                url: "{{ URL::action('ReviewerController@postReviewerDelete') }}",
                data: {
                    '_token' : $("input[name=_token]").val(),
                    'party_id' : party_id,
                    'create_new' : 1,
                    'note' : '',
                    'is_cache' : 1
                },
                success: function (data) {
                    if (data.status=='success')
                    {
                        successAlert('ทำรายการสำเร็จ !', data.msg);
                        $('#btnDelRequestNotComplete'+party_id).button('reset');
                        location.reload();
                    }
                    else
                    {
                        errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                        $('#btnDelRequestNotComplete'+party_id).button('reset');
                    }
                },
                dataType: 'json'
            });
        }

        /*for force send request when sender mistake*/
        function forceSend(encrypt,party_id)
        {
            //disabling button
            $('#btnForceRequest'+party_id).button('loading');
            //post ajax confirm send to reviewer
            $.ajax({
                type: "POST",
                url: "{{ URL::action('PartyController@postRequestConfirm') }}",
                data: {
                    '_token' : $("input[name=_token]").val(),
                    'encrypt' : encrypt,
                    'state' : 'firstRequest'
                },
                success: function (data) {
                    $(this).button('reset');

                    //if success alert with button to close page
                    if (data.status=='success')
                    {
                        $('#btnForceRequest'+party_id).button('reset');
                        successButton('ทำรายการสำเร็จ !', data.msg, buttons);
                        location.reload();
                    }
                    else
                    {
                        $('#btnForceRequest'+party_id).button('reset');
                        errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                    }
                },
                dataType: 'json'
            });
        }

        /*popover request person*/
        function popRequestPerson(id, name, tel, email)
        {
            var options = {
                content : 'ชื่อผู้รับเรื่อง : '+ name + '<br/> เบอร์ติดต่อ : ' + tel + '<br/> อีเมล : ' + email,
                title : 'ข้อมูลผู้รับเรื่อง',
                placement : 'top',
                container : 'body',
                html : true,
                trigger : 'hover'
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
                trigger : 'hover'
            };
            $('#coordinator'+id).popover(options);
            $('#coordinator'+id).popover('toggle')
        }

        /*Add Slash Escape String*/
        function addslashes( str ) {
            return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
        }

    </script>
@stop