{{--หน้าสรุปจำนวนคนเข้าดูงาน--}}
@extends('svms.layouts.reporting')

@section('title')
    รายงานสรุปรายรับ
@stop

@section('extraScripts')
	{{--Use Jquery Number--}}
    {{ HTML::script('dependencies/jquery-number-master/jquery.number.min.js') }}
    {{--Use Bootstrap Datepicker--}}
    {{ HTML::script('assets/js/moment.js') }}
    {{ HTML::script('assets/js/th.js') }}
    {{ HTML::script('dependencies/bootstrap-datetimepicker-master/build/js/bootstrap-datetimepicker.min.js') }}
    {{ HTML::style('dependencies/bootstrap-datetimepicker-master/build/css/bootstrap-datetimepicker.min.css') }}
	{{--Use Jquery Highchart--}}
    {{ HTML::script('dependencies/highchart/js/highcharts.js') }}
	{{ HTML::script('dependencies/highchart/js/modules/exporting.js') }}
	{{--Use Jquery Export To Excel--}}
    {{ HTML::script('dependencies/jquery-table2excel/src/jquery.table2excel.js') }}
@stop

@section('extraStyles')
    <style type="text/css">
		.strong{
			font-weight : bold;
		}
		.export{
			margin : 5px auto;
		}
    </style>
@stop

@section('content')

    <div class="page-header">
        <h3>
            <i class="fa fa-pie-chart"></i> รายงานสรุปจำนวนคนเข้าดูงาน
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
				<form id="report_form" name="form" class="form-horizontal" role="form" method="POST">
					<!-- CSRF Token -->
					<input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
					<!-- ./ csrf token -->

					<div class="form-group">
						<label class="col-sm-3 control-label">รายงานสรุปตามช่วง *</label>
						<div class="col-sm-9">
							<select class="form-control" name="report_type" id="comboReportType" style="width: 100%" required>
								<option value="monthly">รอบเดือน</option>
								<option value="quarter">รอบไตรมาส</option>
								<option value="yearly">รอบปีปกติ</option>
								<option value="budget">รอบปีงบประมาณ</option>
							</select>
						</div>
					</div>
					
					<div id="criteria_monthly" style="display: none;">
						<div class="form-group">
							<label class="col-sm-3 control-label">เดือนเริ่มต้น *</label>
							<div class="col-sm-3">
								<select class="form-control" name="report_month_monthly" id="comboReportMonthMonthly" style="width: 100%" required>
									@foreach(array_keys($months) as $key)
										<option {{{ (date('m')==$key) ? 'selected' : '' }}} value="{{ $key }}">{{ $months[$key] }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">ปีเริ่มต้น *</label>
							<div class="col-sm-3">
								<select class="form-control" name="report_year_monthly" id="comboReportYearMonthly" style="width: 100%" required>
									@for($y=2550;$y<=(date('Y')+543);$y++)
										<option {{{ ((date('Y')+543)==$y) ? 'selected' : '' }}} value="{{ $y }}">{{ $y }}</option>
									@endfor
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">เดือนสิ้นสุด *</label>
							<div class="col-sm-3">
								<select class="form-control" name="report_month_monthly_end" id="comboReportMonthMonthlyEnd" style="width: 100%" required>
									@foreach(array_keys($months) as $key)
										<option {{{ (date('m')==$key) ? 'selected' : '' }}} value="{{ $key }}">{{ $months[$key] }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">ปีสิ้นสุด *</label>
							<div class="col-sm-3">
								<select class="form-control" name="report_year_monthly_end" id="comboReportYearMonthlyEnd" style="width: 100%" required>
									@for($y=2550;$y<=(date('Y')+543);$y++)
										<option {{{ ((date('Y')+543)==$y) ? 'selected' : '' }}} value="{{ $y }}">{{ $y }}</option>
									@endfor
								</select>
							</div>
						</div>
					</div>
					
					<div id="criteria_quarter" style="display: none;">
						<div class="form-group">
							<label class="col-sm-3 control-label">เดือนเริ่มต้น *</label>
							<div class="col-sm-3">
								<select class="form-control" name="report_month_quarter" id="comboReportMonthQuarter" style="width: 100%" required>
									@foreach(array_keys($months) as $key)
										@if($key=='01' || $key=='04' || $key=='07' || $key=='10')
											<option {{{ (date('m')==$key) ? 'selected' : '' }}} value="{{ $key }}">{{ $months[$key] }}</option>
										@endif
									@endforeach
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">ปีเริ่มต้น *</label>
							<div class="col-sm-3">
								<select class="form-control" name="report_year_quarter" id="comboReportYearQuarter" style="width: 100%" required>
									@for($y=2550;$y<=(date('Y')+543);$y++)
										<option {{{ ((date('Y')+543)==$y) ? 'selected' : '' }}} value="{{ $y }}">{{ $y }}</option>
									@endfor
								</select>
							</div>
						</div>
					</div>
					
					<div id="criteria_yearly" style="display: none;">
						<div class="form-group">
							<label class="col-sm-3 control-label">ปีพุทธศักราช  *</label>
							<div class="col-sm-3">
								<select class="form-control" name="report_year_yearly" id="comboReportYearYearly" style="width: 100%" required>
									@for($y=2550;$y<=(date('Y')+543);$y++)
										<option {{{ ((date('Y')+543)==$y) ? 'selected' : '' }}} value="{{ $y }}">{{ $y }}</option>
									@endfor
								</select>
							</div>
						</div>
					</div>
					
					<div id="criteria_budget" style="display: none;">
						<div class="form-group">
							<label class="col-sm-3 control-label">ปีงบประมาณ  *</label>
							<div class="col-sm-3">
								<select class="form-control" name="report_year_budget" id="comboReportYearBudget" style="width: 100%" required>
									@for($y=2550;$y<=(date('Y')+543);$y++)
										<option {{{ ((date('Y')+543)==$y) ? 'selected' : '' }}} value="{{ $y }}">{{ $y }}</option>
									@endfor
								</select>
							</div>
						</div>
					</div>
					
					<div class="form-group">
						<label for="comboType" class="col-sm-3 control-label">ประเภทคณะ</label>
						<div class="col-sm-9">
							<select class="form-control" name="party_type" id="comboType" style="width: 100%">
								<option value="">ทั้งหมด</option>
								@foreach($types as $type)
									<option value="{{ $type->id }}">{{ $type->name }}</option>
								@endforeach
							</select>
						</div>
					</div>
					
					<div class="form-group">
						<label for="comboCountry" class="col-sm-3 control-label">คณะมาจาก</label>
						<div class="col-sm-9">
							<select class="form-control" name="country" id="comboCountry" style="width: 100%">
								<option value="">ทั้งหมด</option>
								<option value="th">ในประเทศ</option>
								<option value="inter">ต่างประเทศ</option>
							</select>
						</div>
					</div>
					
					<div class="form-group">
						<label for="serviceBy" class="col-sm-3 control-label">รับคณะโดยแผนก</label>
						<div class="col-sm-9">
							<select class="form-control" name="service" id="serviceBy" style="width: 100%">
								<option value="">ทั้งหมด</option>
								@foreach($service_departments as $service_department)
									<option value="{{ $service_department->code }}">{{ $service_department->code.' '.$service_department->name }}</option>
								@endforeach
							</select>
						</div>
					</div>

					<div class="pull-right">
						<button id="process_button" type="button" class="btn btn-lg btn-primary"><i class="fa fa-search"></i> ประมวลผลรายงาน</button>
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
				<div id="show_result" style="display:none;">
				
					<!-- Nav tabs -->
					<ul class="nav nav-tabs" role="tablist">
						<li role="presentation" class="active"><a href="#table" aria-controls="table" role="tab" data-toggle="tab">ตาราง</a></li>
						<li style="display:none;" role="presentation"><a href="#graph" aria-controls="graph" role="tab" data-toggle="tab">กราฟ</a></li>
					</ul>

					  <!-- Tab panes -->
					<div class="tab-content">
						<div role="tabpanel" class="tab-pane active" id="table">
							<div class="export">
								<button id="btnExport" class="btn btn-success" type="button">ส่งออก Excel</button>
							</div>
							<div id="result_table"></div>
						</div>
						<div style="display:none;" role="tabpanel" class="tab-pane" id="graph">
							<div id="result_graph"></div>
						</div>
					</div>	
					
				</div>
				<div id="show_loading" style="display:none;">
					 <div class="alert alert-warning" role="alert"><i class="fa fa-spinner fa-spin fa-2x"></i> กำลังประมวลผลข้อมูลกรุณารอสักครู่</div>
				</div>
			</div>
		</div>
	  </div>
	</div>

    <script type="text/javascript">

        $(function () {
			
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
			
			/*Control criteria*/
			$('#comboReportType').on('change', function(){
				var c = $(this).val();
				//hide other
				$("[id^='criteria'][id!='criteria_"+c+"']").hide();
				//open criteria
				$('#criteria_'+c).show();
			});
			
			/*case if have select monthly only*/
			$('#comboReportMonthMonthly').on('change', function(){
				$('#comboReportMonthMonthlyEnd').val($(this).val()).change();
			});
			
			$('#comboReportYearMonthly').on('change', function(){
				$('#comboReportYearMonthlyEnd').val($(this).val()).change();
			});
			
			/*call ajax when submit*/
			$('#process_button').on('click', function(){
				//show collapse
				$('#search').collapse('hide');
				$('#result').collapse('show');
				//show loading and hide result before finished
				$('#show_loading').show();
				$('#show_result').hide();
				
				$.ajax({
                    type: "POST",
                    url: "{{ URL::action('SummaryReportController@postPartyByParticipant') }}",
                    data: $('#report_form').serialize(),
                    success: function (data) 
					{
						//when loading finished
						$('#show_loading').hide();
						$('#show_result').show();
						
						//create table to show summary
						var all_total_income = 0;
						var all_total_party_qty = 0;
						var all_total_people_qty = 0;
						var html = '';
						
						html += '<table id="table_total" class="table table-condensed" cellspacing="0">';

						html += '<tr class="strong">';
						html += '<td>เดือน</td>';
						html += '<td align="right">จำนวนคน</td>';
						html += '</tr>';
					
						$.each(data, function(index,item){
							var monthYear = monthThai(item.month) + ' ' + String(parseInt(item.year)+543);
							
							html += '<tr>';
							html += '<td>' + monthYear + '</td>';
							html += '<td align="right">' + addThousandsSeparator(parseInt(item.total_people_qty)) + '</td>';
							html += '</tr>';
							
							all_total_income += parseFloat(item.total_income);
							all_total_party_qty += parseFloat(item.total_party_qty);
							all_total_people_qty += parseFloat(item.total_people_qty);
						});

						html += '<tr class="strong active">';
						html += '<td>รวมทั้งสิ้น</td>';
						html += '<td align="right">' + addThousandsSeparator(parseInt(all_total_people_qty)) + '</td>';
						html += '</tr>';

						html += '</table>';
												
						$('#result_table').empty().append(html);
						
						//create graph from data
						//not yet waiting for user request
                    },
                    dataType: 'json'
                });
			});
			
			/*Button to open table with excel*/
			$("#btnExport").click(function (e) {
				  $("#table_total").table2excel({
					// exclude CSS class
					//exclude: ".noExl",
					name: "report-by-party-participant"
				  }); 
				e.preventDefault();
			});
			
        });
		
		$(document).ready(
			function()
			{
				$('#comboReportType').trigger('change');
			}
		);
		
    </script>

@stop