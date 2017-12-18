@extends('svms.layouts.admin')

{{-- Web site Title --}}
@section('title')
	{{{ $title }}} :: @parent
@stop

@section('extraScripts')
	{{--Use Jquery Ajax Form--}}
	{{ HTML::script('dependencies/form-master/jquery.form.js') }}
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
		.nonMflf {
			display: none;
		}
	</style>
@stop
{{-- Personnels Management Created At 28/3/16 --}}
{{-- Content --}}
@section('content')
	<div class="page-header">
		<h2>
			จัดการข้อมูลบุคลากร/วิทยากร

			<div class="pull-right">
				<a href="javascript:openCreate();" class="btn btn-small btn-primary"><span class="fa fa-plus"></span> สร้างใหม่</a>
			</div>
		</h2>
	</div>

	<table id="editor_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th class="col-md-1"></th>
				<th class="col-md-3">ชื่อ - สกุล</th>
				<th class="col-md-3">หน่วยงาน</th>
				<th class="col-md-2">ตำแหน่ง</th>
				<th class="col-md-1">วิทยากร</th>
				<th class="col-md-2">Actions</th>
			</tr>
		</thead>
	</table>

	<form id="form" role="form" method="post" enctype="multipart/form-data">
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

						<div role="tabpanel">

							<!-- Nav tabs -->
							<ul class="nav nav-tabs" role="tablist">
								<li role="presentation" class="active"><a href="#general" aria-controls="general" role="tab" data-toggle="tab">ทั่วไป</a></li>
								<li role="presentation"><a href="#work" aria-controls="work" role="tab" data-toggle="tab">การงาน</a></li>
								<li role="presentation"><a href="#expert" aria-controls="expert" role="tab" data-toggle="tab">วิทยากร</a></li>
							</ul>

							<!-- Tab panes -->
							<div class="tab-content">
								{{-- ข้อมูลทั่วไป--}}
								<div style="margin: 15px;" role="tabpanel" class="tab-pane active" id="general">

									<div class="row">
										<label class="col-sm-2">ภาพบุคลากร</label>
										<div class="col-sm-5">
											<div id="personnelImage">{{--Insert Personnel Image--}}</div>
											<input type="file" name="personnelImage" id="filePersonnelImage">
											<p class="help-block">ขนาดที่เหมาะสม 100X100 พิกเซล</p>
										</div>
									</div>

									<div class="clearfix" style="height: 10px;"></div>
								
									<div class="row">
										<label class="col-sm-2">รหัสพนักงาน</label>
										<div class="col-sm-5">
											<input type="text" class="form-control" name="code" id="inputCode" placeholder="รหัสพนักงาน 6 หลัก อาทิ 101111" maxlength="6">
										</div>
									</div>
									
									<div class="clearfix" style="height: 10px;"></div>

									<div class="row">
										<label class="col-sm-2">ชื่อ - สกุล *</label>
										<div class="col-sm-10">
											<div class="form-inline">
												<div class="form-group">
													<select class="form-control" name="prefix" id="comboPrefix" required>
														<option value="">เลือก</option>
														<option value="นางสาว">นางสาว</option>
														<option value="นาง">นาง</option>
														<option value="นาย">นาย</option>
														<option value="ม.ล.">ม.ล.</option>
														<option value="ม.ร.ว.">ม.ร.ว.</option>
														<option value="คุณหญิง">คุณหญิง</option>
														<option value="ดร.">ดร.</option>
													</select>
													<input type="text" class="form-control" name="first_name" id="inputFirstName" placeholder="ชื่อ" value="" required/>
													<input type="text" class="form-control" name="last_name" id="inputLastName" placeholder="สกุล" value="" required/>
												</div>
											</div>
										</div>
									</div>

									<div class="clearfix" style="height: 10px;"></div>

									<div class="row">
										<label class="col-sm-2">Your Name</label>
										<div class="col-sm-10">
											<div class="form-inline">
												<div class="form-group">
													<select class="form-control" name="prefix_en" id="comboPrefixEn">
														<option value="">--Select--</option>
														<option value="Miss">Miss</option>
														<option value="Mrs.">Mrs.</option>
														<option value="Mr.">Mr.</option>
														<option value="M.R.">M.R.</option>
														<option value="M.L.">M.L.</option>
														<option value="Lady">Lady</option>
														<option value="Dr.">Dr.</option>
													</select>
													<input type="text" class="form-control" name="first_name_en" id="inputFirstNameEn" placeholder="First Name" value=""/>
													<input type="text" class="form-control" name="last_name_en" id="inputLastNameEn" placeholder="Last Name" value=""/>
												</div>
											</div>
										</div>
									</div>
									
									<div class="clearfix" style="height: 10px;"></div>
									
									<div class="row">
										<label class="col-sm-2">ชื่อเล่น</label>
										<div class="col-sm-5">
											<input type="text" class="form-control" name="nick_name" id="inputNickName" placeholder="ชื่อเล่นที่ทุกคนเรียกกัน" maxlength="255">
										</div>
									</div>
									
									<div class="clearfix" style="height: 10px;"></div>

									<div class="row">
										<label class="col-sm-2" for="inputEmail">E-mail *</label>
										<div class="col-sm-7">
											<input type="email" class="form-control" name="email" id="inputEmail" placeholder="ตัวอย่าง. yourname@doitung.org" required>
										</div>
									</div>

									<div class="clearfix" style="height: 10px;"></div>

									<div class="row">
										<label class="col-sm-2" for="inputMobile">Mobile</label>
										<div class="col-sm-7">
											<input type="text" class="form-control" name="mobile" id="inputMobile" placeholder="ตัวอย่าง 0899999999">
										</div>
									</div>

									<div class="clearfix" style="height: 10px;"></div>

									<div class="row">
										<label class="col-sm-2" for="comboCountry">สัญชาติ</label>
										<div class="col-sm-7">
											<select class="form-control" name="nationality" id="comboCountry" style="width: 100%">
												<option value="th" selected="selected">Thailand</option>
											</select>
										</div>
									</div>

									<div class="clearfix" style="height: 10px;"></div>

									<div class="row">
										<label class="col-sm-2">วันเกิด</label>
										<div class="col-sm-10">
											<div class="form-inline">
												<div class="form-group">
													<select class="form-control" name="birth_date" id="comboBirthDate">
														<option value=""></option>
														@for($bd=1;$bd<=31;$bd++)
															<option value="{{ $bd }}">{{ $bd }}</option>
														@endfor
													</select>
													/
													<select class="form-control" name="birth_month" id="comboBirthMonth">
														<option value=""></option>
														<?php
															$arrayBirthMonths = array('มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม');
														?>
														@foreach($arrayBirthMonths as $birthMonth)
															<option value="{{ $birthMonth }}">{{ $birthMonth }}</option>
														@endforeach
													</select>
													/
													<select class="form-control" name="birth_year" id="comboBirthYear">
														<option value=""></option>
														@for($by=2490;$by<=2560;$by++)
															<option value="{{ $by }}">{{ $by }}</option>
														@endfor
													</select>
												</div>
											</div>
										</div>
									</div>

								</div>
								{{-- ข้อมูลงาน --}}
								<div style="margin: 15px;" role="tabpanel" class="tab-pane" id="work">

									<div class="row">
										<label class="col-sm-2">เป็นบุคลากร MFLF *</label>
										<div class="col-sm-10">
											<label class="radio-inline">
												<input type="radio" name="radioIsMflf" id="radioIsMflf1" value="1" checked> ใช่
											</label>
											<label class="radio-inline">
												<input type="radio" name="radioIsMflf" id="radioIsMflf2" value="0"> ไม่ใช่(บุคคลภายนอก)
											</label>
										</div>
									</div>

									<div class="row IsMflf">
										<label class="col-sm-2" for="comboDepartment">แผนก</label>
										<div class="col-sm-10">
											<select name="department_id" id="comboDepartment" class="form-control">
												@foreach($departments as $department)
													<option value="{{ $department->id }}">{{ $department->name }}</option>
												@endforeach
											</select>
										</div>
									</div>

									<div class="clearfix IsMflf" style="height: 10px;"></div>

									<div class="row IsMflf">
										<label class="col-sm-2" for="inputMflOffice">ฝ่าย</label>
										<div class="col-sm-7">
											<input type="text" class="form-control" name="mfl_office" id="inputMflOffice" placeholder="เช่น มหาวิทยาลัยที่มีชีวิต">
										</div>
									</div>

									<div class="clearfix IsMflf" style="height: 10px;"></div>

									<div class="row IsMflf">
										<label class="col-sm-2"></label>
										<div class="col-sm-10">
											<div class="checkbox">
												<label>
													<input name="is_administrator" id="checkboxIsAdministrator" type="checkbox"> เป็นผู้บริหารระดับผู้อำนวยการขึ้นไป (ระบุเพื่อส่งข้อมูลคณะได้)
												</label>
											</div>
										</div>
									</div>

									<div class="row IsMflf">
										<label class="col-sm-2"></label>
										<div class="col-sm-10">
											<div class="checkbox">
												<label>
													<input name="can_view_fullcalendar" id="checkboxCanViewFull" type="checkbox"> สามารถดูปฎิทินคณะแบบเต็มได้ (ระบุเพื่อให้ดูข้อมูลทั้งหมด)
												</label>
											</div>
										</div>
									</div>

									<div class="clearfix nonMflf" style="height: 10px;"></div>

									<div class="row nonMflf">
										<label class="col-sm-2" for="inputOtherOffice">องค์กร/บริษัท</label>
										<div class="col-sm-10">
											<input type="text" class="form-control" name="other_office" id="inputOtherOffice" placeholder="">
										</div>
									</div>

									<div class="clearfix" style="height: 10px;"></div>

									<div class="row">
										<label class="col-sm-2" for="inputPosition">ตำแหน่ง</label>
										<div class="col-sm-7">
											<input type="text" class="form-control" name="position" id="inputPosition" placeholder="ระบุตำแหน่งในแผนกของท่าน">
										</div>
									</div>
									
									<div class="clearfix IsMflf" style="height: 10px;"></div>

									<div class="row IsMflf">
										<label class="col-sm-2">Code Name</label>
										<div class="col-sm-7">
											<input type="text" class="form-control" name="codename" id="inputCodename" placeholder="เช่น 888, 777, 11" maxlength="100">
										</div>
									</div>

									<div class="clearfix" style="height: 10px;"></div>

									<div class="row">
										<label class="col-sm-2" for="comboPriority">ลำดับความสำคัญ</label>
										<div class="col-sm-2">
											<select class="form-control" name="priority" id="comboPriority">
												@for($p=1;$p<=100;$p++)
												<option value="{{ $p }}">{{ $p }}</option>
												@endfor
											</select>
										</div>
									</div>

									<div class="clearfix" style="height: 10px;"></div>

									<div class="row">
										<label class="col-sm-2" for="comboStatus">สถานะ</label>
										<div class="col-sm-2">
											<select class="form-control" name="priority" id="comboPriority">
												@for($p=1;$p<=100;$p++)
													<option value="{{ $p }}">{{ $p }}</option>
												@endfor
											</select>
										</div>
									</div>
									
								</div>
								{{-- ข้อมูลวิทยากร --}}
								<div style="margin: 15px;" role="tabpanel" class="tab-pane" id="expert">

									<div class="row">
										<label class="col-sm-2">เป็นวิทยากร ?</label>
										<div class="col-sm-10">
											<label class="radio-inline">
												<input type="radio" name="radioExpert" id="radioExpert1" value="1"> ใช่
											</label>
											<label class="radio-inline">
												<input type="radio" name="radioExpert" id="radioExpert2" value="0" checked> ไม่ใช่
											</label>
											<span id="helpBlock" class="help-block">ระบุค่า ในกรณีที่บุคลากรนี้เป็นวิทยากร</span>
										</div>
									</div>
									
									<div class="clearfix" style="height: 10px;"></div>

									<div id="is_expert_type" class="row" style="display: none;">
										<label class="col-sm-2">ประเภทวิทยากร</label>
										<div class="col-sm-10">
											<select class="form-control" name="expert_type" id="expert_type">
												@foreach($personnel_types as $personnel_type)
													<option value="{{ $personnel_type->id }}">{{ $personnel_type->name }}</option>
												@endforeach
											</select>
										</div>
									</div>
									
								</div>
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

			var countries = {{ json_encode($countries) }};
			/*Control Input Country*/
			$("#comboCountry").select2({
				data: countries,
				templateResult: formatCountry,
				templateSelection: formatCountry
			});

			$('#comboDepartment').on('change', function(){
				if ($(this).val()==1)
				{
					$('#inputCode').prop('readOnly', true).val('');
				}
				else
				{
					$('#inputCode').prop('readOnly', false);
				}
			});

			$('input[name=radioIsMflf]').on('change', function(){
				if ($('#radioIsMflf1').is(':checked'))
				{
					$('.IsMflf').show();
					$('.nonMflf').hide();
				}
				else
				{
					$('.nonMflf').show();
					$('.IsMflf').hide();
				}
			});

			$('input[name=radioExpert]').on('change', function(){
				if ($('#radioExpert1').is(':checked'))
				{
					$('#is_expert_type').show();
				}
				else
				{
					$('#is_expert_type').hide();
				}
			});

			/*Submit Form*/
			var data;
			$('form#form').submit(function(e){

				var create_new = $('input[name=new]').val();

				//loading btn
				$('#btnSubmit').button('loading');
				$('#btnClose').hide();

				var options = {
					type : 'POST',
					url: (create_new=="true") ? "{{ URL::action('AdminPersonnelController@postCreate') }}" : "{{ URL::action('AdminPersonnelController@postEdit') }}",
					dataType:  'json',
					data: {
						'id' : $('#old_id').val()
					},
					beforeSubmit:  function(){
						$('#btnSubmit').prop('disabled', true);
						return true;
					},  // pre-submit callback
					success: function(data){
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
					}  // post-submit callback
				};

				$('form#form').ajaxSubmit(options);

				e.preventDefault(); //STOP default action
			});

			var oTable;
			oTable = $('#editor_table').dataTable({
				"sDom": "<'row'<'col-md-6'l><'col-md-6'f>r>t<'row'<'col-md-6'i><'col-md-6'p>>",
				"language": {
					"url": "{{ URL::asset('assets/js/Thai.json') }}"
				},
				"bSort": true,
				"bProcessing": true,
				"bServerSide": true,
				"sAjaxSource": "{{{ URL::action('AdminPersonnelController@getData') }}}",
				"fnDrawCallback": function ( oSettings ) {

				}
			});
		});

		/*template select*/
		function formatCountry (countries) {
			var public_path = 'http://lu.maefahluang.org:8080/svms/public';
			if (!countries.id) { return countries.text; }
			var countryFormat = '<span><img src="'+public_path+'/assets/img/flags/' + countries.id + '.png" class="img-flag" /> ' + countries.text + '</span>';

			return countryFormat;
		}

		function openCreate()
		{
			//set title
			$('#formModalLabel').html('สร้างบุคลากรหรือวิทยากร');
			//reset form
			$('form')[0].reset();
			//set new value
			$('input[name=new]').val(true);

			//set default image
			var default_image = '<img src="{{ asset('assets/img/people.png') }}" border="0" maxwidth="100" maxheight="100" />';
			$('#personnelImage').html(default_image);

			//open modal
			$('#formModal').modal({ keyboard : false });
		}

		function openEdit(id)
		{
			//set title
			$('#formModalLabel').html('แก้ไขบุคลากรหรือบุคล');
			//set new value
			$('input[name=new]').val(false);

			//get Data From Ajax
			$.ajax({
				url: "{{ URL::action('AdminPersonnelController@getById') }}",
				data :
				{
					'_token' : $('input[name=_token]').val(),
					'id' : id
				}
			}).done(function(data) {
				var data = data.data;
				//set picture
				$('#personnelImage').html(data.image);
				//set old value
				$('#old_id').val(data.id);

				$('#inputCode').val(data.code);
				$('#comboPrefix').val(data.prefix);
				$('#inputFirstName').val(data.first_name);
				$('#inputLastName').val(data.last_name);

				$('#comboPrefixEn').val(data.prefix_en);
				$('#inputFirstNameEn').val(data.first_name_en);
				$('#inputLastNameEn').val(data.last_name_en);

				$('#inputNickName').val(data.nick_name);

				$('#inputEmail').val(data.email);
				$('#inputMobile').val(data.mobile);
				$('#comboCountry').val(data.nationality);
				$('#inputPosition').val(data.position);

				$('#comboBirthDate').val(data.birth_date);
				$('#comboBirthMonth').val(data.birth_month);
				$('#comboBirthYear').val(data.birth_year);

				$('#comboPriority').val(data.priority);

				//check if department
				if (data.department_id!=1)
				{
					//case is mflf
					$('input:radio[id=radioIsMflf1]').prop('checked', true);
					$('input:radio[name=radioIsMflf]').change();
					$('#comboDepartment').val(data.department_id).change();
					$('#inputMflOffice').val(data.mfl_office);
					$('input:radio[id=checkboxIsAdministrator]').prop('checked', (data.is_administrator==1) ? true : false);
					$('input:radio[id=checkboxCanViewFull]').prop('checked', (data.can_view_fullcalendar==1) ? true : false);
					$('#inputCodename').val(data.codename);
				}
				else
				{
					//case is not mflf or other
					$('input:radio[id=radioIsMflf2]').prop('checked', true);
					$('input:radio[name=radioIsMflf]').change();
					$('#comboDepartment').val(1).change();
					$('#inputOtherOffice').val(data.other_office);
					$('input:radio[id=checkboxIsAdministrator]').prop('checked', false);
					$('input:radio[id=checkboxCanViewFull]').prop('checked', false);
					$('#inputCodename').val('');
				}

				//check if this is expert
				if (data.is_expert==1)
				{
					$('input:radio[id=radioExpert1]').prop('checked', true);
					$('input:radio[name=radioExpert]').change();
					$('#expert_type').val(data.personnel_type_id).change();
				}
				else
				{
					$('input:radio[id=radioExpert2]').prop('checked', true);
					$('input:radio[name=radioExpert]').change();
				}
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
					url: "{{ URL::action('AdminPersonnelController@postDelete') }}",
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

	</script>
@stop
