{{--หน้า Send To Administrator For VIP--}}
@extends('svms.layouts.default')

@section('title')
    Living University Management System
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
        .padding-beautiful{
            margin: 10px 5px;
            width: 98%;
        }
    </style>
@stop

@section('header')
    <span class="fa fa-share-square-o"></span>
    ส่งเรื่องให้ผู้บริหารรับทราบ
@stop

@section('content')

    <div class="col-xs-12 col-md-12">
        <div role="tabpanel">

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist" id="manager_tab">
                <li role="presentation" class="active"><a href="#backlog" aria-controls="backlog" role="tab" data-toggle="tab"><i class="fa fa-share"></i> คณะที่ยังไม่ได้ส่งให้ผู้บริหาร</a></li>
                <li role="presentation"><a id="show_history" href="#history" aria-controls="history" role="tab" data-toggle="tab"><i class="fa fa-history"></i> คณะที่ท่านส่งให้ผู้บริหารทราบ</a></li>
                <li role="presentation"><a id="show_all_history" href="#allhistory" aria-controls="allhistory" role="tab" data-toggle="tab"><i class="fa fa-globe"></i> คณะที่ส่งไปให้ผู้บริหารทราบทั้งหมด</a></li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">

                <div role="tabpanel" class="tab-pane active" id="backlog">
                    <div class="panel" style="margin-top: 0px;">
                        <div class="panel-body">
                            <table id="not-send" class="table table-striped table-bordered table-hover">
							{{-- Fetch From Ajax --}}
                            </table>
                        </div>
                    </div>
                </div>

                <div role="tabpanel" class="tab-pane" id="history">
                    <div class="panel" style="margin-top: 0px;">
                        <div class="panel-body">
                            <table id="sended" class="table table-striped table-bordered table-hover">
                                {{-- Fetch From Ajax --}}
                            </table>
                        </div>
                    </div>
                </div>

                <div role="tabpanel" class="tab-pane" id="allhistory">
                    <div class="panel" style="margin-top: 0px;">
                        <div class="panel-body">
                            <table id="all_sended" class="table table-striped table-bordered table-hover">
                                {{-- Fetch From Ajax --}}
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- !Modal Send To Accept --}}
    <form id="send_to_admin_form" role="form" method="POST">
        <div class="modal fade" id="send_to_admin" tabindex="-1" role="dialog" aria-labelledby="sendToAdminLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="sendToAdminLabel"><i class="fa fa-envelope"></i> ส่งข้อมูลคณะให้แก่ผู้บริหาร</h4>
                    </div>
                    <div class="modal-body">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                        <!-- ./ csrf token -->
                        <input type="hidden" id="send_to_admin_id" value="" />
                        <input type="hidden" id="send_create_new" value="1" />
						
						<div class="form-group">
                            <label for="send_admin_recipient">เรียน  *</label>
                            <select class="form-control" multiple="multiple" id="send_admin_recipient" style="width: 100%;" required>  
								@foreach($administrators as $administrator)
									<option value="{{ $administrator->id }}">{{ $administrator->fullNameWithCodeName() }}</option>
								@endforeach
                            </select>
                        </div>
						
						<div class="form-group">
                            <label for="send_admin_body">ข้อความเกริ่นนำ  *</label>
                            <textarea id="send_admin_body" class="form-control" placeholder="" required>
							</textarea>
                        </div>
						
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
                        <button id="btnAccept" data-loading-text="กำลังทำการส่งข้อมูลไปยังเมล..." type="submit" class="btn btn-success"><i class="fa fa-paper-plane"></i> ส่งข้อมูลคณะ</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script type="text/javascript">
        $(document).ready(function(){
			
			/*Enable TinyMce Rich Text*/
			tinymce.init({ 
				selector:'textarea',
				language: 'th_TH'
			});
            /*bootstrap popover*/
            $('[data-toggle="popover"]').popover();
            /*Select2 */
            $('#send_admin_recipient').select2({
                placeholder: "ชื่อจริงหรือCode"
            });
            /*DataTables*/
            var table = $('#not-send').DataTable({
                "language": {
                    "url": "{{ URL::asset('assets/js/Thai.json') }}"
                },
                "order": [[1,'desc']],
                "processing": true,
                "serverSide": true,
                "ajax": "{{{ URL::action('VipController@getSendAdministratorData') }}}",
                "columnDefs":
                [
                    {
                        "targets": 4,
                        "data": function ( row, type, val, meta ) {

                            var html = '';
                            html += '<button id="person' + row.id + '" type="button" class="btn btn-link" onclick="popRequestPerson(' + row.id + ',&quot;' + row.request_person_name + '&quot;,&quot;' + row.request_person_tel + '&quot;,&quot;' + row.request_person_email +'&quot;);">';
                            html += row.request_person_name;
                            html += '</button>';

                            return html;

                        }
                    }
                ],
                "columns":
                [
                    //title will auto-generate th columns
                    {
                        "className": 'details-control', "orderable": false, "data": null, "defaultContent": ''
                    },
                    { "data" : "request_code", "className": 'col-md-2', "title" : "รหัสคำร้อง", "orderable": true, "searchable": true },
                    { "data" : "name", "className": 'col-md-4', "title" : "ชื่อคณะ", "orderable": true, "searchable": true },
                    { "data" : "start_date", "className": 'col-md-2', "title" : "วันที่มา", "orderable": true, "searchable": true },
                    { "title" : "ส่งคำร้องโดย", "className": 'col-md-2', "orderable": true, "searchable": true },
                    { "data" : "actions", "className": 'col-md-2', "title" : "Actions", "orderable": false, "searchable": false }
                ],
                "fnDrawCallback": function ( oSettings ) {
                }
            });

            // Add event listener for opening and closing details
            $('#not-send tbody').on('click', 'td.details-control', function () {
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

            /*for sended*/
            var sended = $('#sended').DataTable({
                "language": {
                    "url": "{{ URL::asset('assets/js/Thai.json') }}"
                },
                "order": [[1,'desc']],
                "deferLoading": 0, // default not loading
                "processing": true,
                "serverSide": true,
                "ajax":
                {
                    "url": "{{{ URL::action('VipController@getSendAdministratorData') }}}",
                    "data": {
                        "is_history": 1
                    }
                },
                "columnDefs":
                        [
                            {
                                "targets": 4,
                                "data": function ( row, type, val, meta ) {

                                    var html = '';
                                    html += '<button id="person' + row.id + '" type="button" class="btn btn-link" onclick="popRequestPerson(' + row.id + ',&quot;' + row.request_person_name + '&quot;,&quot;' + row.request_person_tel + '&quot;,&quot;' + row.request_person_email +'&quot;);">';
                                    html += row.request_person_name;
                                    html += '</button>';

                                    return html;

                                }
                            }
                        ],
                "columns":
                        [
                            //title will auto-generate th columns
                            {
                                "className": 'details-control', "orderable": false, "data": null, "defaultContent": ''
                            },
                            { "data" : "request_code", "className": 'col-md-2', "title" : "รหัสคำร้อง", "orderable": true, "searchable": true },
                            { "data" : "name", "className": 'col-md-3', "title" : "ชื่อคณะ", "orderable": true, "searchable": true },
                            { "data" : "start_date", "className": 'col-md-2', "title" : "วันที่มา", "orderable": true, "searchable": true },
                            { "title" : "ส่งคำร้องโดย", "className": 'col-md-2', "orderable": true, "searchable": true },
                            { "data" : "actions", "className": 'col-md-3', "title" : "Actions", "orderable": false, "searchable": false }
                        ],
                "fnDrawCallback": function ( oSettings ) {
                }
            });

            $('#sended tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = sended.row(tr);

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

            /*for all sended*/
            var all_sended = $('#all_sended').DataTable({
                "language": {
                    "url": "{{ URL::asset('assets/js/Thai.json') }}"
                },
                "order": [[1,'desc']],
                "deferLoading": 0, // default not loading
                "processing": true,
                "serverSide": true,
                "ajax":
                {
                    "url": "{{{ URL::action('VipController@getSendAdministratorData') }}}",
                    "data": {
                        "is_history": 1,
                        "is_all": 1
                    }
                },
                "columnDefs":
                        [
                            {
                                "targets": 4,
                                "data": function ( row, type, val, meta ) {

                                    var html = '';
                                    html += '<button id="person' + row.id + '" type="button" class="btn btn-link" onclick="popRequestPerson(' + row.id + ',&quot;' + row.request_person_name + '&quot;,&quot;' + row.request_person_tel + '&quot;,&quot;' + row.request_person_email +'&quot;);">';
                                    html += row.request_person_name;
                                    html += '</button>';

                                    return html;

                                }
                            }
                        ],
                "columns":
                        [
                            //title will auto-generate th columns
                            {
                                "className": 'details-control', "orderable": false, "data": null, "defaultContent": ''
                            },
                            { "data" : "request_code", "className": 'col-md-2', "title" : "รหัสคำร้อง", "orderable": true, "searchable": true },
                            { "data" : "name", "className": 'col-md-3', "title" : "ชื่อคณะ", "orderable": true, "searchable": true },
                            { "data" : "start_date", "className": 'col-md-2', "title" : "วันที่มา", "orderable": true, "searchable": true },
                            { "title" : "ส่งคำร้องโดย", "className": 'col-md-2', "orderable": true, "searchable": true },
                            { "data" : "actioned", "className": 'col-md-3', "title" : "Actioned", "orderable": false, "searchable": false }
                        ],
                "fnDrawCallback": function ( oSettings ) {
                }
            });

            $('#all_sended tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = all_sended.row(tr);

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
                   //reload sended data when click
                   sended.ajax.reload();
                   sended.draw();
                   $('#send_create_new').val(0);
               }else if ($(e.target).attr('id')=='show_all_history') {
                    //reload all sended data when click
					all_sended.ajax.reload();
					all_sended.draw();
                    $('#send_create_new').val(0);
               }else{
                   table.ajax.reload();
                   table.draw();
                   $('#send_create_new').val(1);
               }
            });

            //accept submit form
            $('form#send_to_admin_form').submit(function(e){
                var data = {
                    '_token' : $("input[name=_token]").val(),
                    'create_new' : $('#send_create_new').val(),
                    'party_id' : $('#send_to_admin_id').val(),
					'persons' : $('#send_admin_recipient').val(),
					'send_body' : tinyMCE.get('send_admin_body').getContent()
                };

                $('#btnAccept').button('loading');

                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('VipController@postSendToAdministrator') }}",
                    data: data,
                    success: function (data) {
                        if (data.status=='success')
                        {
                            $('#send_to_admin').modal('hide');
                            table.ajax.reload();
                            sended.ajax.reload();
                            all_sended.ajax.reload();
                            successAlert('ส่งข้อมูลไปให้ผู้บริหารสำเร็จ  !', data.msg);
                            $('#btnAccept').button('reset');
                        }
                        else
                        {
                            errorAlert('ส่งข้อมูลไปไม่สำเร็จ กรุณาแจ้ง Admin !', data.msg);
                            $('#btnAccept').button('reset');
                        }
                    },
                    dataType: 'json'
                });

                e.preventDefault(); //STOP default action
            });

        });
		
		/*open to send method*/
		function openSendAdmin( party_id, is_new )
		{
			//1 open dialog box
			$('#send_to_admin').modal();
			//2 set now party
			$('#send_to_admin_id').val(party_id);
			//3 set init text
			if (is_new==1)
			{
				tinymce.get('send_admin_body').setContent('<p>ขออนุญาตส่งข้อมูลข้อคณะดูงาน เพื่อแจ้งให้ทราบโดยทั่วกันโดยข้อมูลมีดังต่อไปนี้</p>');
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

    </script>
@stop