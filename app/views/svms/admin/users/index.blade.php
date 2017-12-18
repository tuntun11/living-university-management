@extends('svms.layouts.admin')

{{-- Web site Title --}}
@section('title')
	{{{ $title }}} :: @parent
@stop

@section('extraScripts')
	{{--Use Data Tables--}}
	{{ HTML::style('dependencies/DataTables/media/css/jquery.dataTables.min.css') }}
	{{ HTML::script('dependencies/DataTables/media/js/jquery.dataTables.min.js') }}
	{{--Use Bootstrap Select2--}}
	{{ HTML::script('dependencies/select2-4.0.0-beta.3/dist/js/select2.min.js') }}
	{{ HTML::style('dependencies/select2-4.0.0-beta.3/dist/css/select2.min.css') }}
	{{--Use Bootstrap Datepicker--}}
	{{ HTML::script('assets/js/moment.js') }}
	{{ HTML::script('assets/js/th.js') }}
	{{ HTML::script('dependencies/bootstrap-datetimepicker-master/build/js/bootstrap-datetimepicker.min.js') }}
	{{ HTML::style('dependencies/bootstrap-datetimepicker-master/build/css/bootstrap-datetimepicker.min.css') }}
@stop

@section('extraStyles')
	<style type="text/css">
		@media screen and (min-width: 768px) {
			#formModal .modal-dialog  {width:900px;}
		}
	</style>
@stop

{{-- Content --}}
@section('content')
	<div class="page-header">
		<h2>
			จัดการผู้ใช้งานระบบ

			<div class="pull-right">
				<a href="javascript:openCreate();" class="btn btn-small btn-primary"><span class="fa fa-plus"></span> สร้างใหม่</a>
			</div>
		</h2>
	</div>

	<table id="editor_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th class="col-md-2">Username</th>
				<th class="col-md-3">ชื่อ - สกุล</th>
				<th class="col-md-3">หน่วยงาน</th>
				<th class="col-md-2">E-mail</th>
				<th class="col-md-2">Actions</th>
			</tr>
		</thead>
	</table>

	<form id="form" role="form" method="post">
		{{--Id--}}
		<input type="hidden" id="old_id" name="old_id">
		{{--Create New True--}}
		<input type="hidden" id="new" name="new" value="true">
		<!-- CSRF Token -->
		<input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
		<!-- ./ csrf token -->

		{{--Create Edit Form--}}
		<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="formModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="formModalLabel"></h4>
					</div>
					<div class="modal-body">

						<div class="alert alert-info alert-block">
							<button type="button" class="close" data-dismiss="alert">&times;</button>
								เมื่อทำการสร้างUserใหม่ <b>Username</b> และ <b>Password</b> จะถูกส่งไปยัง Email ของบุคลากรที่เลือก
						</div>

						<div class="row">
							<label class="col-sm-2" for="comboPersonnel">บุคลากรที่ใช้ *</label>
							<div class="col-sm-7">
								{{--Use for new user--}}
								<select class="form-control newUser" id="comboPersonnel">
									@foreach($personnels as $personnel)
										<option value="{{ $personnel->id }}">{{ $personnel->code." ".$personnel->fullName() }}</option>
									@endforeach
								</select>
								{{--Use for old user--}}
								<input type="hidden" id="oldPersonnelId" name="oldPersonnelId" value="">
								<input type="text" class="form-control oldUser" id="showOldUser" value="" readonly>
							</div>
						</div>

						<div class="clearfix" style="height: 10px;"></div>

						<div class="row">
							<label class="col-sm-2" for="inputUsername">Username *</label>
							<div class="col-sm-7">
								<input type="text" class="form-control" id="inputUsername" placeholder="" required>
							</div>
						</div>

						<div class="clearfix" style="height: 10px;"></div>

						<div class="row">
							<label class="col-sm-2">Password</label>
							<div class="col-sm-7">
								<div class="newUser">
									Auto-Generate <span id="descriptionGenerate"></span>
								</div>
								<div class="oldUser">
									Password is only edit by owner
								</div>
							</div>
						</div>

						<div class="clearfix" style="height: 10px;"></div>

						<!-- Activation Status -->
						<div class="row">
							<label class="col-md-2" for="confirm">เปิดให้ใช้ระบบ ?</label>
							<div class="col-md-6">
								<select class="form-control" name="confirm" id="confirm">
									<option value="1" selected>{{{ Lang::get('general.yes') }}}</option>
									<option value="0">{{{ Lang::get('general.no') }}}</option>
								</select>
							</div>
						</div>
						<!-- ./ activation status -->

						<div class="clearfix" style="height: 10px;"></div>

						<!-- Groups -->
						<div class="row">
							<label class="col-md-2" for="roles">บทบาทการใช้งาน</label>
							<div class="col-md-8">
								<select class="form-control" name="roles[]" id="roles" multiple="multiple" style="width: 100%;">
									@foreach ($roles as $role)
										@if($role->name!='user' && $role->name!='admin')
											<option value="{{{ $role->id }}}">{{{ $role->name }}}</option>
										@endif
									@endforeach
								</select>
								<p class="help-block">สามารถเลือกได้มากกว่า 1 บทบาทการใช้งาน</p>
							</div>
						</div>

					</div>
					<div class="modal-footer">
						<button id="btnClose" type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-times"></span> ปิด</button>
						<button id="btnSubmit" type="submit" class="btn btn-success" data-loading-text="กำลังบันทึก..." autocomplete="off"><span class="fa fa-floppy-o"></span> ยืนยัน</button>
					</div>
				</div>
			</div>
		</div>

	</form>

	{{-- Scripts --}}
	<script type="text/javascript">
		$(document).ready(function() {

			$('#roles').select2({
				'placeholder' : 'คลิกเลือก'
			});

			/*Submit Form*/
			var data;
			$('form#form').submit(function(e){

				var create_new = $('input[name=new]').val();

				data = {
					'_token' : $("input[name=_token]").val(),
					'id' : $('#old_id').val(),
					'personnel_id' : (create_new=="true") ? $('#comboPersonnel').val() : $('#oldPersonnelId').val(),
					'username' : $('#inputUsername').val(),
					'confirm' : $('#confirm').val(),
					'roles' : ($('#roles').size() > 0) ? $('#roles').val() : ['5']
				};

				//loading btn
				$('#btnSubmit').button('loading');
				$('#btnClose').hide();

				$.ajax({
					type: "POST",
					url: (create_new=="true") ? "{{ URL::action('AdminPersonnelUsersController@postCreate') }}" : "{{ URL::action('AdminPersonnelUsersController@postEdit') }}",
					data: data,
					success: function (data) {
						//loading btn
						$('#btnSubmit').button('reset');
						$('#btnClose').show();
						if (data.status=='success')
						{
							$('#formModal').modal('hide');
							oTable.fnDraw();
						}
						else
						{
							alert(data.msg);
						}
					},
					dataType: 'json'
				});

				e.preventDefault(); //STOP default action
			});

			var oTable;
			oTable = $('#editor_table').dataTable({
				"sDom": "<'row'<'col-md-6'l><'col-md-6'f>r>t<'row'<'col-md-6'i><'col-md-6'p>>",
				"language": {
					"url": "{{ URL::asset('assets/js/Thai.json') }}"
				},
				"order": [[0,'desc']],
				"bProcessing": true,
				"bServerSide": true,
				"sAjaxSource": "{{{ URL::action('AdminPersonnelUsersController@getData') }}}",
				"fnDrawCallback": function ( oSettings ) {

				}
			});
		});

		function openCreate()
		{
			//set title
			$('#formModalLabel').html('สร้างผู้ใช้ระบบใหม่');
			//reset form
			$('form')[0].reset();
			$('#roles').select2().val('').change();
			//set new value
			$('input[name=new]').val(true);
			$('.newUser').show();
			$('.oldUser').hide();
			//open modal
			$('#formModal').modal({ keyboard : false });
		}

		function openEdit(id)
		{
			//set title
			$('#formModalLabel').html('แก้ไขผู้ใช้ระบบ');
			//set new value
			$('input[name=new]').val(false);
			$('.newUser').hide();
			$('.oldUser').show();
			//get Data From Ajax
			$.ajax({
				url: "{{ URL::action('AdminPersonnelUsersController@getById') }}",
				data :
				{
					'_token' : $('input[name=_token]').val(),
					'id' : id
				}
			}).done(function(data) {
				var data = data.data;
				//set old value
				$('#old_id').val(data.user_id);

				$('#oldPersonnelId').val(data.personnel_id);

				if (data.code==null || data.code=="")
				{
					$('#descriptionGenerate').html('from randomize');
					$('#showOldUser').val(data.personnel_name);
				}
				else
				{
					$('#descriptionGenerate').html('from employee code');
					$('#showOldUser').val(data.code+' '+data.personnel_name);
				}

				if (typeof data.username == 'undefined')
				{
					$('#inputUsername').val(makeUsername());
				}
				else
				{
					$('#inputUsername').val(data.username);
				}

				if (typeof data.confirmed == 'undefined')
				{
					$('#confirm').val(0);
				}
				else
				{
					$('#confirm').val(data.confirmed);
				}

				$('#confirm').trigger('change');
				//unselect all before re add
				//re add role
				var id_roles = [];
				$.each(data.roles, function(i,e){
					id_roles.push(e.id);
				});
				//assign data
				$('#roles').select2().val(id_roles).change();
			});

			//open modal
			$('#formModal').modal({ keyboard : false });
		}

		function openDelete(id)
		{
			if(confirm('ต้องการยืนยันการลบหรือไม่ ?')==true)
			{
				$.ajax({
					type: "POST",
					url: "{{ URL::action('AdminPersonnelUsersController@postDelete') }}",
					data :
					{
						'_token' : $('input[name=_token]').val(),
						'id' : id
					}
				}).done(function(data) {
					location.reload();
				});
			}
			return false;
		}

		function makeUsername()
		{
			var text = "";
			var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

			for( var i=0; i < 5; i++ )
				text += possible.charAt(Math.floor(Math.random() * possible.length));

			return text;
		}
	</script>
@stop
