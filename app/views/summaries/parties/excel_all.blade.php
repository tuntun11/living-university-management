{{--หน้า รายงานคณะออกเป็น Excel ได้--}}
@extends('svms.layouts.reporting')

@section('title')
    รายงานรายชื่อคณะ
@stop

@section('extraScripts')
	{{--Use DataTables--}}
    {{ HTML::script('dependencies/DataTables/media/js/jquery.dataTables.min.js') }}
    {{ HTML::style('dependencies/DataTables/media/css/jquery.dataTables.min.css') }}
	{{--Use DataTables Responsive--}}
    {{ HTML::script('https://cdn.datatables.net/responsive/2.0.0/js/dataTables.responsive.min.js') }}
    {{ HTML::style('https://cdn.datatables.net/responsive/2.0.0/css/responsive.dataTables.min.css') }}
    {{--Use Bootstrap Datepicker--}}
    {{ HTML::script('assets/js/moment.js') }}
    {{ HTML::script('assets/js/th.js') }}
    {{ HTML::script('dependencies/bootstrap-datetimepicker-master/build/js/bootstrap-datetimepicker.min.js') }}
    {{ HTML::style('dependencies/bootstrap-datetimepicker-master/build/css/bootstrap-datetimepicker.min.css') }}
    {{--Use Bootstrap Select2--}}
    {{ HTML::script('dependencies/select2-4.0.0-beta.3/dist/js/select2.min.js') }}
    {{ HTML::style('dependencies/select2-4.0.0-beta.3/dist/css/select2.min.css') }}
@stop

@section('extraStyles')
    <style type="text/css">
		#show_result { max-width: 1200px }
		#table_result > th{
			min-width:150px;
			width:auto;
			white-space: nowrap;
		}
    </style>
@stop

@section('content')

    <div class="page-header">
        <h3>
            <i class="fa fa-file-excel-o"></i> รายชื่อคณะ
        </h3>
    </div>
	
	<div class="panel-group" id="accordion">
	  <div class="panel panel-default">
		<div class="panel-heading">
		  <h4 class="panel-title">
			<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#search">
				<i class="fa fa-search"></i> เงื่อนไขการออกรายงาน
			</a>
		  </h4>
		</div>
		<div id="search" class="panel-collapse collapse in">
			<div class="panel-body">
				<form id="search_form" name="form" class="form-horizontal" role="form" method="POST" action="{{ URL::action('PartiesReportController@postExcelParties') }}">
					<!-- CSRF Token -->
					<input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
					<!-- ./ csrf token -->

					<div class="form-group">
						<label for="inputName" class="col-sm-3 control-label">ชื่อคณะ</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" name="name" id="inputName" placeholder="ชื่อคณะมีคำว่า ?">
						</div>
					</div>

					<div class="form-group">
						<label for="comboCountry" class="col-sm-3 control-label">คณะจาก</label>
						<div class="col-sm-9">
							<select class="form-control" name="countries[]" id="comboCountry" multiple="multiple" style="width: 100%">
							</select>
						</div>
					</div>

					<div class="form-group">
						<label for="comboType" class="col-sm-3 control-label">ประเภทคณะ</label>
						<div class="col-sm-9">
							<select class="form-control" name="party_types[]" id="comboType" multiple="multiple" style="width: 100%">
								@foreach($types as $type)
									<option value="{{ $type->id }}">{{ $type->name }}</option>
								@endforeach
							</select>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">ช่วงจำนวนผู้ดูงาน</label>
						<div class="col-sm-3">
							<input type="number" id="people_start" name="people_start" class="form-control" placeholder="เริ่มที่จำนวน">
						</div>
						<div class="col-sm-3">
							<input type="number" id="people_end" name="people_end" class="form-control" placeholder="สิ้นสุดที่จำนวน">
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">ช่วงวันที่เริ่มดูงาน</label>
						<div class="col-sm-3">
							<div class='input-group date' id='dateStart'>
								<input type='text' class="form-control" data-date-format="YYYY-MM-DD" name="start_date" id="inputDateStart" placeholder="เริ่ม"/>
										<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
										</span>
							</div>
						</div>
						<div class="col-sm-3">
							<div class='input-group date' id='dateEnd'>
								<input type='text' class="form-control" data-date-format="YYYY-MM-DD" name="end_date" id="inputDateEnd" placeholder="ถึง"/>
										<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
										</span>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="comboObjective" class="col-sm-3 control-label">วัตถุประสงค์การมา</label>
						<div class="col-sm-9">
							<select class="form-control" name="objectives[]" id="comboObjective" multiple="multiple" style="width: 100%">
								@foreach($objectives as $objective)
									<option value="{{ $objective->id }}">{{ $objective->name }}</option>
								@endforeach
							</select>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">แหล่งที่มา</label>
						<div class="col-sm-9">
							<label class="radio-inline">
								<input type="radio" name="radioArea" id="radioArea1" value="base" checked> พื้นที่โครงการ
							</label>
							<label class="radio-inline">
								<input type="radio" name="radioArea" id="radioArea2" value="location"> สถานที่
							</label>
						</div>
					</div>

					<div class="form-group" id="findBase">
						<label for="comboLocationBase" class="col-sm-3 control-label">พื้นที่โครงการ</label>
						<div class="col-sm-9">
							<select class="form-control" name="location_bases[]" id="comboLocationBase" multiple="multiple" style="width: 100%">
								@foreach($mflfAreas as $mflfArea)
									<option value="{{ $mflfArea->id }}">{{ $mflfArea->name }}</option>
								@endforeach
							</select>
						</div>
					</div>

					<div class="form-group" id="findLocation" style="display: none;">
						<label for="comboLocations" class="col-sm-3 control-label">สถานที่ศึกษาดูงาน</label>
						<div class="col-sm-9">
							<select class="form-control" name="locations[]" id="comboLocations" multiple="multiple" style="width: 100%">
								@foreach(array_keys($locations) as $area)
									@if(count($locations[$area])>0)
										<optgroup label="{{ $area }}">
											@foreach($locations[$area] as $location)
												<option value="{{ $location->id }}">{{ $location->text }}</option>
											@endforeach
										</optgroup>
									@endif
								@endforeach
							</select>
						</div>
					</div>

					<div class="form-group">
						<label for="serviceBy" class="col-sm-3 control-label">รับคณะโดยแผนก</label>
						<div class="col-sm-9">
							<select class="form-control" name="services[]" id="serviceBy" multiple="multiple" style="width: 100%">
								@foreach($service_departments as $service_department)
									<option value="{{ $service_department->code }}">{{ $service_department->code.' '.$service_department->name }}</option>
								@endforeach
							</select>
						</div>
					</div>

					<div class="form-group">
						<label for="incomeBy" class="col-sm-3 control-label">รายได้เข้าตามแผนก</label>
						<div class="col-sm-9">
							<select class="form-control" name="incomes[]" id="incomeBy" multiple="multiple" style="width: 100%">
								@foreach($financial_departments as $financial_department)
									<option value="{{ $financial_department->financial_code }}">{{ $financial_department->financial_code.' '.$financial_department->name }}</option>
								@endforeach
							</select>
						</div>
					</div>

					<div class="form-group">
						<label for="coordinatorBy" class="col-sm-3 control-label">ผู้ประสานงานหลัก</label>
						<div class="col-sm-9">
							<select class="form-control" name="coordinators[]" id="coordinatorBy" multiple="multiple" style="width: 100%">
								@foreach($personnels as $personnel)
									<option value="{{ $personnel->id }}">{{ $personnel->fullName() }}</option>
								@endforeach
							</select>
						</div>
					</div>

					<div class="form-group">
						<label for="tag" class="col-sm-3 control-label">Tag</label>
						<div class="col-sm-9">
							<select class="form-control" name="tags[]" id="tag" multiple="multiple" style="width: 100%">
								@foreach($tags as $tag)
									<option value="{{ $tag->tag }}">{{ $tag->tag }}</option>
								@endforeach
							</select>
						</div>
					</div>

					<div class="form-group">
						<label for="status" class="col-sm-3 control-label">สถานะ</label>
						<div class="col-sm-9">
							<select class="form-control" name="statuses[]" id="status" multiple="multiple" style="width: 100%">
							</select>
						</div>
					</div>

					<div class="pull-right">
						<button id="export_button" type="submit" class="btn btn-lg btn-success"><i class="fa fa-file-excel-o"></i> ออกรายงาน Excel</button>
						<button id="search_button" type="button" class="btn btn-lg btn-primary"><i class="fa fa-search"></i> ประมวลผลรายงาน</button>
					</div>

				</form>
			</div>
		</div>
	  </div>
	  <div class="panel panel-default">
		<div class="panel-heading">
		  <h4 class="panel-title">
			<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#result">
				<i class="fa fa-table"></i> การประมวลผล
			</a>
		  </h4>
		</div>
		<div id="result" class="panel-collapse collapse">
			<div class="panel-body">
				<div id="show_result" class="table-responsive" style="display:none;">
				<div id="show_number_result">
				</div>
				<div class="clearfix"></div>
					<table id="table_result" class="table table-bordered" cellpadding="0" width="100%">
						<thead>
							<tr>
								<th>รหัสเงิน</th>
								<th>รหัสคำร้อง</th>
								<th>รหัสลูกค้า</th>
								<th>ชื่อคณะ</th>
								<th>มาจาก</th>	
								<th>ประเภท</th>
								<th>วัตถุประสงค์</th>
								<th>จำนวนคน</th>
								<th>เริ่มวันที่</th>	
								<th>สิ้นสุดวันที่</th>
								<th>ผู้ประสานงานดูแลคณะ</th>	
								<th>Staff</th>	
								<th>สถานะล่าสุดของคณะ</th>
								<th>รายได้สุทธิ</th>
								<th>การปรับปรุงรายได้</th>
								<th>แผนงานที่ใช้</th>
								<th>ผู้ประสานงานคณะที่มา</th>
								<th>สร้างเมื่อ</th>
								<th>แก้ไขเมื่อ</th>								
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
				<div id="show_loading" style="display:none;">
					 <div class="alert alert-warning" role="alert"><i class="fa fa-spinner fa-spin fa-2x"></i> กำลังประมวลผลข้อมูลกรุณารอสักครู่</div>
				</div>
			</div>
		</div>
	  </div>
	</div>
	
    <script type="text/javascript">

        var countries = {{ json_encode($countries) }};
		var all_statuses = {{ json_encode($statuses) }};
		var statuses = [];
		
		all_statuses.forEach(function(all_status) {
			var status = {
				'id' : all_status,
				'text' : statusThai(all_status)
			};
			
			statuses.push(status);
		});
		
		$(function () {

			$('input[name=radioArea]').on('change', function(e){
				var selected = $('input[name=radioArea]:checked').val();

				if (selected=='base')
				{
					$('#findBase').show();
					$('#findLocation').hide();
				}
				else
				{
					$('#findBase').hide();
					$('#findLocation').show();
				}
			});
		
            /*Control Date Range*/
            $('#dateStart').datetimepicker({
                pickTime: false,
                language: 'th'
            });
            $('#dateEnd').datetimepicker({
                pickTime: false,
                language: 'th'
            });
            $("#dateStart").on("dp.change",function (e) {
                $('#dateEnd').data("DateTimePicker").setMinDate(e.date);
                $('#dateEnd').data("DateTimePicker").setValue(e.date);
            });
            $("#dateEnd").on("dp.change",function (e) {
                $('#dateStart').data("DateTimePicker").setMaxDate(e.date);
            });

            $("#comboCountry").select2({
                'placeholder' : 'สามารถเลือกได้มากกว่า 1 ประเทศ',
                'data' : countries,
                'templateResult' : formatCountry,
                'templateSelection' : formatCountry
            });

            $("#comboType").select2({
                'placeholder' : 'สามารถเลือกได้มากกว่า 1 ประเภทคณะ'
            });

            $("#comboObjective").select2({
                'placeholder' : 'สามารถเลือกได้มากกว่า 1 วัตถุประสงค์'
            });

			$("#comboLocationBase").select2({
				'placeholder' : 'สามารถเลือกได้มากกว่า 1 พื้นที่'
			});

			$("#comboLocations").select2({
				'placeholder' : 'สามารถเลือกได้มากกว่า 1 สถานที่'
			});

            $("#serviceBy").select2({
                'placeholder' : 'สามารถเลือกได้มากกว่า 1 แผนก'
            });

            $("#incomeBy").select2({
                'placeholder' : 'สามารถเลือกได้มากกว่า 1 รหัสและสามารถพิมพ์ได้',
				'tags' : true
            });

            $("#coordinatorBy").select2({
                'placeholder' : 'สามารถเลือกได้มากกว่า 1 ท่าน'
            });

            $("#tag").select2({
                'placeholder' : 'สามารถเลือกได้มากกว่า 1 เงื่อนไข'
            });

            $("#status").select2({
				'data' : statuses,
                'placeholder' : 'สามารถเลือกได้มากกว่า 1 สถานะ'
            });
			
			/*click for show result*/
			$('#search_button').on('click', function(){
				//show collapse
				$('#search').collapse('hide');
				$('#result').collapse('show');
				//show loading and hide result before finished
				$('#show_loading').show();
				$('#show_result').hide();
				
				$.ajax({
                    type: "GET",
                    url: "{{ URL::action('PartiesReportController@getFilterParties') }}",
                    data: {
						'name' : $('#inputName').val(),
						'countries' : $('#comboCountry').val(),
						'party_types' : $('#comboType').val(),
						'people_start' : $('#people_start').val(),
						'people_end' : $('#people_end').val(),
						'start_date' : $('#inputDateStart').val(),
						'end_date' : $('#inputDateEnd').val(),
						'objectives' : $('#comboObjective').val(),
						'area' : $('input[name=radioArea]:checked').val(),
						'bases' : $('#comboLocationBase').val(),
						'locations' : $('#comboLocations').val(),
						'services' : $('#serviceBy').val(),
						'incomes' : $('#incomeBy').val(),
						'coordinators' : $('#coordinatorBy').val(),
						'tags' : $('#tag').val(),
						'statuses' : $('#status').val()
					},
                    success: function (data) 
					{
						//when loading finished
						$('#show_loading').hide();
						$('#show_result').show();
						
						var html = ''; 
						$.each(data, function(index, item){
							html += '<tr>';
							html += '<td>' + item.budget_code + '</td>';
							html += '<td>' + item.request_code + '</td>';
							html += '<td>' + item.customer_code + '</td>';
							html += '<td>' + item.name + '</td>';
							html += '<td>' + item.country + '</td>';
							html += '<td>' + item.party_type_id + '</td>';
							html += '<td>' + item.objectives + '</td>';
							html += '<td>' + item.people_quantity + '</td>';
							html += '<td>' + item.start_date + '</td>';
							html += '<td>' + item.end_date + '</td>';
							html += '<td>' + item.project_co + '</td>';
							html += '<td>' + item.staff_works + '</td>';
							html += '<td>' + item.status + '</td>';
							html += '<td>' + item.summary_income + '</td>';
							html += '<td>' + item.income_edited_by + '</td>';
							html += '<td>' + item.final_plan + '</td>';
							html += '<td>' + item.coordinators + '</td>';
							//html += '<td>' + item.objective_detail + '</td>';
							html += '<td>' + item.created_at + '</td>';
							html += '<td>' + item.updated_at + '</td>';
							html += '</tr>';
						});
						
						$('#table_result > tbody').empty().append(html);
						
						$('#show_number_result').empty().append('<strong>จำนวน ' + data.length + ' รายการ</strong>');
						
                    },
                    dataType: 'json'
                });
			});
			
        });

		$(document).ready(function(){
			$('input[name=radioArea]').change();
		});

        /*template select*/
        function formatCountry (countries) {
			var public_path = 'http://lu.maefahluang.org:8080/svms/public';
            if (!countries.id) { return countries.text; }
            var countryFormat = '<span><img src="'+public_path+'/assets/img/flags/' + countries.id + '.png" class="img-flag" /> ' + countries.text + '</span>';

            return countryFormat;
        }
		
		function statusThai(latest_status)
		{
			var status = '';
			switch(latest_status)
			{
				case 'pending' :
					status = 'กำลังกรอก';
					break;
				case 'editing' :
					status = 'กำลังแก้ไขตามที่ผู้อนุมัติขอ';
					break;
				case 'reviewing' :
					status = 'ส่งยื่นคำร้อง';
					break;
				case 'reviewed' :
					status = 'ผ่านการ Approve';
					break;
				case 'approved' :
					status = 'ผ่านการเลือก Project Co จาก Manager';
					break;
				case 'preparing' :
					status = 'เตรียมการรับคณะ';
					break;
				case 'ongoing' :
					status = 'ระหว่างการรับคณะ';
					break;
				case 'finished' :
					status = 'ดำเนินการสำเร็จ(ชำระเงินแล้ว)';
					break;
				case 'finishing' :
					status = 'ดำเนินการสำเร็จ(ยังไม่ได้รับเงิน)';
					break;
				case 'postpone' :
					status = 'เลื่อนกำหนดการ';
					break;
				case 'cancelled1' :
					status = 'ยกเลิกจาก Reviewer';
					break;
				case 'cancelled2' :
					status = 'ยกเลิกจาก Manager';
					break;
				case 'terminated' :
					status = 'ยกเลิกการรับคณะ';
					break;
				case 'other' :
					status = 'ส่งให้หน่วยงานอื่นรับ';
					break;
			}
			
			return status;
		}

    </script>

@stop