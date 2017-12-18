{{--หน้า Senior Manager--}}
@extends('svms.layouts.default')

@section('title')
    Living University Management System
@stop

@section('extraScripts')
    {{--Use DataTables--}}
    {{ HTML::script('dependencies/DataTables/media/js/jquery.dataTables.min.js') }}
    {{ HTML::style('dependencies/DataTables/media/css/jquery.dataTables.min.css') }}
    {{--Use Bootstrap Select2--}}
    {{ HTML::script('dependencies/select2-4.0.0-beta.3/dist/js/select2.min.js') }}
    {{ HTML::style('dependencies/select2-4.0.0-beta.3/dist/css/select2.min.css') }}
	{{--Use Bootstrap Select2--}}
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
    </style>
@stop

@section('header')
    <span class="fa fa-pencil-square-o"></span>
    มอบหมายงาน (Manager)
@stop

@section('content')

    <div class="col-xs-12 col-md-12">
        <div role="tabpanel">

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist" id="manager_tab">
                <li role="presentation" class="active"><a href="#backlog" aria-controls="backlog" role="tab" data-toggle="tab"><i class="fa fa-tasks"></i> <span class="text-lg">ภาระงานค้าง</span></a></li>
                <li role="presentation"><a id="show_history" href="#history" aria-controls="history" role="tab" data-toggle="tab"><i class="fa fa-history"></i> <span class="text-lg">ภาระงานที่ผ่านมาของท่าน</span></a></li>
                <li role="presentation"><a id="show_all_history" href="#allhistory" aria-controls="allhistory" role="tab" data-toggle="tab"><i class="fa fa-globe"></i> <span class="text-lg">ภาระงานทั้งหมด</span></a></li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">

                <div role="tabpanel" class="tab-pane active" id="backlog">
                    <div class="panel" style="margin-top: 0px;">
                        <div class="panel-body">
                            <table id="grid_view_manager" class="table table-condensed table-hover">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th class="col-md-2">รหัสคำร้อง</th>
                                    <th class="col-md-4">ชื่อคณะ/บุคคล</th>
                                    <th class="col-md-3">ช่วงวันที่</th>
                                    <th class="col-md-3">Actions</th>
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
                            <table id="grid_history_manager" class="table table-condensed table-hover">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th class="col-md-2">รหัสคำร้อง</th>
                                    <th class="col-md-4">ชื่อคณะ/บุคคล</th>
                                    <th class="col-md-3">ช่วงวันที่</th>
                                    <th class="col-md-3">Actions</th>
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
                            <table id="grid_allhistory_manager" class="table table-condensed table-hover">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th class="col-md-2">รหัสคำร้อง</th>
                                    <th class="col-md-4">ชื่อคณะ/บุคคล</th>
                                    <th class="col-md-3">ช่วงวันที่</th>
                                    <th class="col-md-3">Actioned</th>
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

    {{-- !Modal Send work to project co --}}
    <form id="accept_form" role="form" method="POST">
        <div class="modal fade" id="accept" tabindex="-1" role="dialog" aria-labelledby="acceptLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="acceptLabel">มอบหมายงานแก่ Project Coordinator</h4>
                    </div>
                    <div class="modal-body">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                        <!-- ./ csrf token -->
                        <input type="hidden" id="manager_accept_id" value="" />
						<input type="hidden" id="manager_accept_method" value="" />
                        <div class="form-group">
                            <label for="manager_accept_party">ชื่อคณะ</label>
                            <input type="text" class="form-control" id="manager_accept_party" value="" disabled>
                        </div>
                        <div class="form-group">
                            <label for="manager_accept_department">รหัส/แผนก</label>
                            <select id="manager_accept_department" class="form-control">
                                @foreach($departments as $department)
                                    <option {{{ ($department->code==='B') ? 'selected' : '' }}} value="{{ $department->id }}">{{ $department->code.' '.$department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="manager_accept_define_work">หน่วยงานที่ดำเนินการรับคณะ</label>
                            <div class="clearfix"></div>
                            <label class="radio-inline">
                                <input type="radio" name="manager_accept_define_work" id="manager_accept_define_work_yes" value="yes">
                                หน่วยงานเจ้าของงานดำเนินการเอง
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="manager_accept_define_work" id="manager_accept_define_work_no" value="no" checked>
                                ดำเนินการร่วมกับ LU
                            </label>
                        </div>
                        <div id="projectCoordinators" class="form-group">
                            <label for="manager_accept_assign">ผู้ประสานงานรับคณะหลัก</label>
                            <span></span>
                            <select id="manager_accept_assign" class="form-control" style="width: 100%; display: none;">
                            </select>
                            {{--show work load--}}
                            <div id="project_coordinator_works">

                            </div>
                        </div>
                        <!--CC. Recipient-->
                        <div id="personnelRecipients" class="form-group">
                            <label for="manager_accept_recipients">ผู้รับทราบเพิ่มเติม :</label>
                            <select id="manager_accept_recipients" class="form-control" multiple="multiple" style="width: 100%;">
                                @foreach($personnels as $personnel)
                                    <option value="{{ $personnel->email }}">{{ $personnel->fullName() }}</option>
                                @endforeach
                            </select>
                        </div>
						<!--Command-->
						<div class="form-group">
                            <label for="manager_accept_note">คำสั่งเพิ่มเติม :</label>
                            <textarea id="manager_accept_note" class="form-control" placeholder=""></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
                        <button type="submit" data-loading-text="กำลังทำการส่งงานทางเมล..." id="btnSubmitAccept" class="btn btn-success"><i class="fa fa-check"></i> ส่งต่องาน</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- !Modal Reject --}}
    <form id="cancel_form" role="form" method="POST">
        <div class="modal fade" id="cancel" tabindex="-1" role="dialog" aria-labelledby="cancelLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="cancelLabel">ปฎิเสธการรับคณะ</h4>
                    </div>
                    <div class="modal-body">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                        <!-- ./ csrf token -->
                        <input type="hidden" id="manager_cancel_id" value="" />
                        <div class="form-group">
                            <label for="manager_cancel_receive">ส่งถึง(ผู้กรอก) :</label>
                            <input type="text" class="form-control" id="manager_cancel_receive" value="" disabled>
                        </div>
                        <div class="form-group">
                            <label for="manager_cancel_party">ชื่อคณะ :</label>
                            <input type="text" class="form-control" id="manager_cancel_party" value="" disabled>
                        </div>
                        <div class="form-group">
                            <label for="manager_cancel_reason">เหตุผลที่ปฎิเสธ :</label>
                            <textarea id="manager_cancel_reason" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
                        <button type="submit" data-loading-text="กำลังทำการส่งผลทางเมล..." id="btnSubmitCancel" class="btn btn-danger"><i class="fa fa-envelope-o"></i> ส่งเมล</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script type="text/javascript">
        (function($){
            /*Initial Config*/
            var departments = {{ json_encode($departments) }};

            $('#projectCoordinators').hide();
			/*Enable TinyMce Rich Text*/
			tinymce.init({ 
				selector:'textarea',
				language: 'th_TH'
			});
            /*Enable Recipient to know mail insert*/
            $("#manager_accept_recipients").select2({
                tags: true
            });
            //$("#manager_accept_assign").select2();
            /*bootstrap popover*/
            $('[data-toggle="popover"]').popover();
            /*DataTables*/
            var table = $('#grid_view_manager').DataTable({
                "language": {
                    "url": "{{ URL::asset('assets/js/Thai.json') }}"
                },
                "order": [[1,'desc']],
                "processing": true,
                "serverSide": true,
                "ajax": "{{ URL::action('ManagerController@getData') }}",
                "columns":
                        [
                            //title will auto-generate th columns
                            {
                                "className": 'details-control', "orderable": false, "data": null, "defaultContent": ''
                            },
                            { "data" : "request_code", "title" : "รหัสคำร้อง", "className": 'col-md-2', "orderable": true, "searchable": true },
                            { "data" : "name", "title" : "ชื่อคณะ", "className": 'col-md-4', "orderable": true, "searchable": true },
                            { "data" : "start_date", "title" : "ช่วงวันที่", "className": 'col-md-3', "orderable": true, "searchable": true },
                            { "data" : "actions", "title" : "Actions", "className": 'col-md-3', "orderable": false, "searchable": false }
                        ]
            });

            //เพิ่มเติมเข้ามาเพื่อกรณีคณะนี้ lu ไม่ได้รับเอง
            $('input[name=manager_accept_define_work]').on('click', function(e){

                var answer = $('input[name=manager_accept_define_work]:checked').val();

                $('#projectCoordinators > span').empty();
                $('#project_coordinator_works').empty();

                if (answer=='yes')
                {
                    //case lu not 100%
                    $('#manager_accept_assign').hide();
                    $('#projectCoordinators > span').append('<div class="alert alert-success" role="alert"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i> หน่วยงานเจ้าของงานรับ</div>');
                }
                else
                {
                    //case is lu
                    $('#manager_accept_assign').show();
                }
            });

            // Add event listener for opening and closing details
            $('#grid_view_manager tbody').on('click', 'td.details-control', function () {
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

            var approved_table = $('#grid_history_manager').DataTable({
                "language": {
                    "url": "{{ URL::asset('assets/js/Thai.json') }}"
                },
                "order": [[1,'desc']],
                "deferLoading": 0, // default not loading
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{{ URL::action('ManagerController@getData') }}}",
                    "data": {
                        "is_history": 1
                    }
                },
                "columns":
                        [
                            //title will auto-generate th columns
                            {
                                "className": 'details-control', "orderable": false, "data": null, "defaultContent": ''
                            },
                            { "data" : "request_code", "className": 'col-md-2', "title" : "รหัสคำร้อง", "orderable": true, "searchable": true },
                            { "data" : "name", "className": 'col-md-4', "title" : "ชื่อคณะ", "orderable": true, "searchable": true },
                            { "data" : "start_date", "className": 'col-md-3', "title" : "ช่วงวันที่", "orderable": true, "searchable": true },
                            { "data" : "actions", "className": 'col-md-3', "title" : "Actions", "orderable": false, "searchable": false }
                        ]
            });

            // Add event listener for opening and closing details
            $('#grid_history_manager tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = approved_table.row(tr);

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

            var all_approved_table = $('#grid_allhistory_manager').DataTable({
                "language": {
                    "url": "{{ URL::asset('assets/js/Thai.json') }}"
                },
                "order": [[1,'desc']],
                "deferLoading": 0, // default not loading
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{{ URL::action('ManagerController@getData') }}}",
                    "data": {
                        "is_history": 1,
                        "is_all": 1
                    }
                },
                "columns":
                        [
                            //title will auto-generate th columns
                            {
                                "className": 'details-control', "orderable": false, "data": null, "defaultContent": ''
                            },
                            { "data" : "request_code", "className": 'col-md-2', "title" : "รหัสคำร้อง", "orderable": true, "searchable": true },
                            { "data" : "name", "className": 'col-md-4', "title" : "ชื่อคณะ", "orderable": true, "searchable": true },
                            { "data" : "start_date", "className": 'col-md-3', "title" : "ช่วงวันที่", "orderable": true, "searchable": true },
                            { "data" : "actioned", "className": 'col-md-3', "title" : "Actioned", "orderable": false, "searchable": false }
                        ]
            });

            // Add event listener for opening and closing details
            $('#grid_allhistory_manager tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = all_approved_table.row(tr);

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

            /*check active tab when click history tab load data*/
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                if ($(e.target).attr('id')=='show_history') {
                    //reload manager data when click
                    approved_table.ajax.reload();
                    approved_table.draw();
                    $('#accept_create_new, #other_create_new, #cancel_create_new').val(0);
                }else if ($(e.target).attr('id')=='show_all_history') {
                    //reload manager data when click
                    all_approved_table.ajax.reload();
                    all_approved_table.draw();
                    $('#accept_create_new, #other_create_new, #cancel_create_new').val(0);
                }else{
                    table.ajax.reload();
                    table.draw();
                    $('#accept_create_new, #other_create_new, #cancel_create_new').val(1);
                }
            });

            /*Hook Department Select*/
            $('#manager_accept_department').on('change', function(){
                //load ajax coordinator by department
                $.ajax({
                    type: "GET",
                    url: "{{ URL::action('ManagerController@getCoordinators') }}",
                    data: { '_token' : $('input[name=_token]').val(), 'department_id' : $(this).val() },
                    success: function (data) {
                        //if reload set submit disabled again
                        //console.log(data);
                        //$('#btnSubmitAccept').prop('disabled', true);
                        //show project coor
                        $('#projectCoordinators').show();
                        $('#projectCoordinators > span').empty();
                        $('#project_coordinator_works').empty();

                        if ($('#manager_accept_assign').empty())
                        {
                            $('#manager_accept_assign').show();
                            if (data.data.length > 0)
                            {
                                $('#manager_accept_assign').append($("<option selected disabled>").val('').text('กรุณาเลือก'));
                                $(data.data).each(function(index, item) {
                                    $('#manager_accept_assign').append($("<option>").val(item.id).text(item.text));
                                });
                            }
                            else
                            {
                                $('#manager_accept_assign').hide();
                                $('#projectCoordinators > span').append('<div class="alert alert-warning" role="alert">ไม่มีข้อมูลผู้ประสานงานให้เลือก</div>');
                            }
                        }
                    },
                    dataType: 'json'
                });
                //assign mail teams of department
                var team = getObjects(departments, 'id', $(this).val());
                var recipients = [];

                if (team[0].personnels.length>0)
                {
                    $.each( team[0].personnels, function( key, personnel ) {
                        recipients.push(personnel.email);
                    });
                }
                //force add email
                $('#manager_accept_recipients').select2().val(recipients).change();
            });

            /*Hook Personnel Select*/
            $('#manager_accept_assign').on('change', function(){
                //load work task of coordinator
                $.ajax({
                    type: "GET",
                    url: "{{ URL::action('ManagerController@getCoordinatorTasks') }}",
                    data: 
					{ 
							'_token' : $('input[name=_token]').val(),
							'coordinator_id' : $(this).val()	
					},
                    success: function (data) {
                        //clear old work
                        $('#project_coordinator_works').empty();
                        //loadworks
                        showWorks(data.data);
                    },
                    dataType: 'json'
                });
            });

            //cancel submit form
            $("form#cancel_form").submit(function(e){

                var data = {
                    '_token' : $("input[name=_token]").val(),
                    'party_id' : $('#manager_cancel_id').val(),
                    'note' : tinyMCE.get('manager_cancel_reason').getContent()
                };

                $('#btnSubmitCancel').button('loading');

                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('ManagerController@postManagerCancel') }}",
                    data: data,
                    success: function (data) {
                        if (data.status=='success')
                        {
                            $('#cancel').modal('hide');
                            table.ajax.reload();
                            approved_table.ajax.reload();
                            all_approved_table.ajax.reload();
                            successAlert('ทำรายการสำเร็จ !', data.msg);
                            $('#btnSubmitCancel').button('reset');
                            //update tasks number
                            $('#manager-task-number').empty().html(data.tasks.manager);
                            //also update other work task
                            @if(Auth::user()->hasRole('reviewer'))
                                $('#reviewer-task-number').empty().html(data.tasks.reviewer);
                            @endif
                        }
                        else
                        {
                            errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                            $('#btnSubmitCancel').button('reset');
                        }
                    },
                    dataType: 'json'
                });

                e.preventDefault(); //STOP default action
            });

            //accept submit form
            $('form#accept_form').submit(function(e){

                //if use lu power check value of project co selected
                if ($('input[name=manager_accept_define_work]:checked').val()=='no')
                {
                    if ($('#manager_accept_assign').val()==null || $('#manager_accept_assign').val()=="")
                    {
                        warningAlert('ทำรายการไม่ได้ !', 'หากท่านเลือกใช้บุคลากร LU กรุณาเลือกบุคลากรก่อนบันทึก');
                        return false;
                    }
                }

                var data = {
                    '_token' : $("input[name=_token]").val(),
                    'party_id' : $('#manager_accept_id').val(),
                    'department_id' : $('#manager_accept_department').val(),
                    'coordinator_assigned' : $('#manager_accept_assign').val(),
                    'not_use_coordinator' : $('input[name=manager_accept_define_work]:checked').val(),
                    'more_recipients' : $('#manager_accept_recipients').val(),
					'method' : $('#manager_accept_method').val(),
					'note' : tinyMCE.get('manager_accept_note').getContent()
                };

                $('#btnSubmitAccept').button('loading');

                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('ManagerController@postManagerAccept') }}",
                    data: data,
                    success: function (data) {
                        if (data.status=='success')
                        {
                            $('#accept').modal('hide');
                            table.ajax.reload();
                            approved_table.ajax.reload();
                            all_approved_table.ajax.reload();
                            successAlert('ทำรายการสำเร็จ !', data.msg);
                            $('#btnSubmitAccept').button('reset');
                            //update tasks number
                            $('#manager-task-number').empty().html(data.tasks.manager);
                            //also update other work task
                            @if(Auth::user()->hasRole('reviewer'))
                                $('#reviewer-task-number').empty().html(data.tasks.reviewer);
                            @endif
                        }
                        else
                        {
                            errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                            $('#btnSubmitAccept').button('reset');
                        }
                    },
                    dataType: 'json'
                });

                e.preventDefault(); //STOP default action
            });

        }(jQuery))

        /*show work load*/
        function showWorks(data)
        {
            //always can enable submit
            //$('#btnSubmitAccept').prop('disabled', false);
            if (data.length > 0)
            {
                //add work load view in project_coordinator_works
                var html = '<br/>';
                html += '<table id="coor_works" class="table table-bordered" width="95%" data-page-length="5">';
                html += '<thead>';
                html += '<tr>';
                html += '<th width="65%">คณะ</th>';
                html += '<th width="35%">ช่วงวันที่</th>';
                html += '</tr>';
                html += '</thead>';
                html += '<tbody>';
                $(data).each(function(index,item){
                    html += '<tr>';
                    html += '<td width="65%">' + item.name + '</td>';
                    html += '<td width="35%">' + item.party_range + '</td>';
                    html += '</tr>';
                });
                html += '</tbody>';
                html += '</table>';

                $('#project_coordinator_works').empty().append(html);

                setTimeout(function(){
                    $('#coor_works').dataTable( {
                        "info": false,
                        "ordering": false,
                        "scrollY": "280px",
                        "scrollX": false,
                        "scrollCollapse": true,
                        "paging": false,
                        "searching": false
                    } );
                }, 500);
            }
            else
            {
                //set warning
                $('#project_coordinator_works').empty().append('<div style="margin-top: 10px;" class="alert alert-warning" role="alert">ไม่มีภาระการประสานงานค้าง</div>');
            }
        }

        /*manager approval step*/
        function managerApproval(party_id, party_desc, party_request_mail)
        {    
		    var act = $('#party_'+party_id).val();
			if (act == 'accept' || act == 'changePeople')
			{
				//***accept and send work to project coordinator ***
				//open modal
				$('#accept').modal({ backdrop:false });
				//$('#btnSubmitAccept').prop('disabled', true);
				//set hidden id
				$('#manager_accept_id').val(party_id);
				$('#manager_accept_party').val(party_desc);
				$('#manager_accept_department').change();
				//***send to senior manager or manager***
				//set Method
				$('#manager_accept_method').val(act);
			}
			else
			{
				//***send mail back request person with reason ***
				//open modal
				$('#cancel').modal();
				//set hidden id
				$('#manager_cancel_id').val(party_id);
				$('#manager_cancel_receive').val(party_request_mail);
				$('#manager_cancel_party').val(party_desc);
			}
        }

        /* Formatting function for row details - modify as you need */
        function format ( d )
        {
            return getPartyExpansion(d);
        }

        /*popover request person*/
        function popRequestPerson(id, name, tel, email)
        {
            var options = {
                content : 'ชื่อผู้รับเรื่อง : '+ name + '<br/> เบอร์ติดต่อ : ' + tel + '<br/> อีเมล : ' + email,
                title : 'ชื่อผู้รับเรื่อง',
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
    </script>
@stop