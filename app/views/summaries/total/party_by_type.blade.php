{{--หน้าสรุปจำนวนคณะแยกประเภท--}}
@extends('svms.layouts.reporting')

@section('title')
    รายงานสรุปจำนวนคณะแยกประเภท
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
	{{ HTML::script('assets/js/export-th.js') }}
	{{--Use Jquery Export To Excel--}}
    {{ HTML::script('dependencies/jquery-table2excel/src/jquery.table2excel.js') }}
@stop

@section('extraStyles')
    <style type="text/css">
		.strong{
			font-weight : bold;
		}
		.byTypeHeader{
			min-width : 220px;
		}
		.byMonthHeader{
			min-width : 150px;
		}
		#table_total > th {
			text-align : center;
		}
		.export{
			margin : 5px auto;
		}
    </style>
@stop

@section('content')

    <div class="page-header">
        <h3>
            <i class="fa fa-pie-chart"></i> รายงานสรุปจำนวนคณะแยกประเภท
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
						<li role="presentation"><a href="#graph" aria-controls="graph" role="tab" data-toggle="tab">กราฟภาพรวม (Pie)</a></li>
						<li role="presentation"><a href="#chart" aria-controls="chart" role="tab" data-toggle="tab">กราฟแท่ง (Chart)</a></li>
					</ul>

					  <!-- Tab panes -->
					<div class="tab-content">
						<div role="tabpanel" class="tab-pane active" id="table">
							<div class="export">
								<button id="btnExport" class="btn btn-success" type="button">ส่งออก Excel</button>
							</div>
							<div id="result_table"></div>
						</div>
						<div role="tabpanel" class="tab-pane" id="graph">
							<div id="result_graph"></div>
						</div>
						<div role="tabpanel" class="tab-pane" id="chart">
							<div id="result_chart"></div>
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
                    url: "{{ URL::action('SummaryReportController@postPartyByType') }}",
                    data: $('#report_form').serialize(),
                    success: function (data) 
					{
						//when loading finished
						$('#show_loading').hide();
						$('#show_result').show();
						
						//***create table to show summary***
						var html = '';
						
						html += '<div id="div_table_total" class="table-responsive">';
						
						html += '<table id="table_total" class="table table-bordered" cellspacing="0" width="100%">';

						html += '<tr id="first_row" class="strong">';
						html += '<th class="byMonthHeader" rowspan="2">เดือน</th>';
										
						//loop for set first row header by type name
						$.each(data.types, function(index, type)
						{
							if ($('#comboCountry').val()=="")
							{
								html += '<th class="byTypeHeader" colspan="2">' + type + '</th>';	
							}
							else
							{
								html += '<th class="byTypeHeader">' + type + '</th>';	
							}
						});
						
						html += '<th rowspan="2">รวม</th>';
						
						html += '</tr>';
						
						//loop for set second row header by select countries
						html += '<tr id="second_row" class="strong">';
						
						$.each(data.types, function(index, type)
						{
							if ($('#comboCountry').val()=="")
							{
								html += '<th>ในประเทศ</th>';	
								html += '<th>ต่างประเทศ</th>';	
							}
							else
							{
								//else case select one 
								if ($('#comboCountry').val()=="th")
								{
									html += '<td>ในประเทศ</td>';
								}
								else
								{
									html += '<td>ต่างประเทศ</td>';	
								}
							}
						});
						
						html += '</tr>';
						
						//at second loop fill month/year in first column
						var total_all = 0;
						$.each(data.summaries, function(index, round)
						{
							//fill month/year in first column every row
							html += '<tr>';
							
							var monthYear = monthThai(round.month) + ' ' + String(parseInt(round.year)+543);
							
							html += '<td>' + monthYear + '</td>';
								
							//loop fill data total in column
							$.each(round.totals, function(i, total)
							{
								if ($('#comboCountry').val()=="")
								{
									//if no select add 2 countries
									html += '<td align="right">' + total.th + '</td>';
									html += '<td align="right">' + total.inter + '</td>';
								}
								else
								{
									//else case select one 
									if ($('#comboCountry').val()=="th")
									{
										html += '<td align="right">' + total.th + '</td>';
									}
									else
									{
										html += '<td align="right">' + total.inter + '</td>';
									}
								}
								
							});
							
							//row sum total
							total_all += parseInt(round.round_total);
							
							html += '<td class="active" align="right">' + round.round_total + '</td>';
							
							html += '</tr>';
						});
								
						//summary in footer
						var total_summary = 0;
						var chart_series = [];//use in pie
						var valuable_types = [];//use in chart
						var bar_total_data = [];//use in chart for total value
						var bar_thai_data = [];//use in chart for thai value
						var bar_inter_data = [];//use in chart for inter value
						
						html += '<tr class="active">';
						html += '<td>รวม</td>';
						
						$.each(data.type_totals, function(type_name, type_total)
						{
							if ($('#comboCountry').val()=="")
							{
								html += '<td align="right">' + type_total.th + '</td>';	
								html += '<td align="right">' + type_total.inter + '</td>';
								//set total by type
								var type_summary = parseInt(type_total.th)+parseInt(type_total.inter);
								//summary fill in table
								total_summary += type_summary;
								//set series in graph 
								var total_by_type = (type_summary/total_all)*100;
								var serie = {
									name: type_name,
									y: total_by_type
								};
								//set bar series input value if total value > 0
								if (type_summary>0)
								{
									//declare type is not null
									valuable_types.push(type_name);
									//then set value fill in series
									bar_total_data.push(type_summary);
									bar_thai_data.push(parseInt(type_total.th));
									bar_inter_data.push(parseInt(type_total.inter));
								}
							}
							else
							{
								//else case select one 
								if ($('#comboCountry').val()=="th")
								{
									//set total by type
									var type_summary = parseInt(type_total.th);

									html += '<td align="right">' + type_total.th + '</td>';		
									//summary fill in table
									total_summary += type_summary;
									//set series in graph 
									var total_by_type = (type_summary/total_all)*100;
									var serie = {
										name: type_name,
										y: total_by_type
									};
									//set bar series input value if total value > 0
									if (type_summary>0)
									{
										//declare type is not null
										valuable_types.push(type_name);
										//then set value fill in series
										bar_thai_data.push(type_summary);
									}
								}
								else
								{
									//set total by type
									var type_summary = parseInt(type_total.inter);

									html += '<td align="right">' + type_total.inter + '</td>';
									//summary fill in table
									total_summary += type_summary;
									//set series in graph 
									var total_by_type = (type_summary/total_all)*100;
									var serie = {
										name: type_name,
										y: total_by_type
									};
									//set bar series input value if total value > 0
									if (type_summary>0)
									{
										//declare type is not null
										valuable_types.push(type_name);
										//then set value fill in series
										bar_inter_data.push(type_summary);
									}
								}
							}
							
							chart_series.push(serie);
						});
						
						//summary all query
						html += '<td align="right">' + addThousandsSeparator(total_summary) + '</td>';
						
						html += '</tr>';
						
						html += '</table>';
						
						html += '</div>';
						
						$('#result_table').empty().append(html);
						
						//***create graph from data***
						
						//set pastel color
						Highcharts.setOptions({
						  colors: [
						  '#DEA5A4', '#B19CD9', '#FF6961', 
						  '#77DD77', '#966FD6', '#AEC6CF',
						  '#F49AC2', '#779ECB', '#FDFD96',
						  '#FFB347', '#C23B22', '#836953', 
						  '#800020', 'teal'
						  ]
						});
						
						 // Radialize the colors
						Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
							return {
								radialGradient: {
									cx: 0.5,
									cy: 0.3,
									r: 0.7
								},
								stops: [
									[0, color],
									[1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
								]
							};
						});
						//pie graph
						$('#result_graph').highcharts({
							chart: {
								plotBackgroundColor: null,
								plotBorderWidth: null,
								plotShadow: false,
								//width: 800,
								type: 'pie'
							},
							credits: {
								enabled: false
							},
							title: {
								text: 'รายงานสรุปคณะแยกประเภท (แสดงภาพรวม)'
							},
							tooltip: {
								pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
							},
							plotOptions: {
								pie: {
									allowPointSelect: true,
									cursor: 'pointer',
									dataLabels: {
										enabled: true,
										formatter: function(){
											if (this.y==0)
											{
												return null;
											}
											else
											{
												return this.point.name + ' ' + $.number(this.percentage, 1) + ' %';
											}
										},
										style: {
											color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
										}
									}
								}
							},
							 exporting: {
								buttons: {
									contextButton: {
										text: 'ส่งออก'
									}
								}
							},
							series:  [{
								name: "ประเภทคณะ",
								colorByPoint: true,
								data: chart_series
							}],
							credits: {
								text: 'จากจำนวนคณะทั้งสิ้น ' + total_all + 'คณะ',
								position: {
									align: 'center',
									y: -5 // position of credits
								},
								style: {
									fontSize: '12pt' // you can style it!
								}
							}
						});
						//create chart graph
						//before set graph schema data by condition
						var bar_series;

						if ($('#comboCountry').val()=="")
						{
							bar_series = [
								{
									showInLegend: false,
									name : 'รวมทั้งสิ้น',
									data : bar_total_data
								}
							];
						}
						else
						{
							if ($('#comboCountry').val()=="th")
							{
								bar_series = [
									{
										showInLegend: false,
										name : 'รวมทั้งสิ้น',
										data : bar_thai_data
									}
								];
							}
							else
							{
								bar_series = [
									{
										showInLegend: false,
										name : 'รวมทั้งสิ้น',
										data : bar_inter_data
									}
								];
							}
						}

						//after run generate graph
						$('#result_chart').highcharts({
							chart: {
								type: 'bar',
								events: {
									load: function () {
										/*Redraw by desc*/
										/*this.series[0].data.sort(function(a, b) {
											return b.y - a.y;
										});

										var newData = {};

										for (var i = 0; i < this.series[0].data.length; i++) {
											newData.x = i;
											newData.y = this.series[0].data[i].y;
											newData.color = Highcharts.getOptions().colors[i];

											this.series[0].data[i].update(newData, false);

											// Workaround:
											this.legend.colorizeItem(this.series[0].data[i], this.series[0].data[i].visible);
										}

										this.redraw({ duration: 1000 });*/
									}
								}
							},
							title: {
								text: 'รายงานสรุปคณะแยกประเภท (แสดงจำนวน)'
							},
							xAxis: {
								categories: valuable_types,
								title: {
									text: null
								}
							},
							yAxis: {
								min: 0,
								//max: 30,
								tickInterval: 5,
								title: {
									text: 'จำนวนคณะศึกษาดูงาน',
									align: 'high'
								},
								labels: {
									overflow: 'justify'
								}
							},
							tooltip: {
								valueSuffix: ' คณะ'
							},
							plotOptions: {
								bar: {
									colorByPoint: true,
									dataLabels: {
										enabled: true,
										formatter: function(){
											var p = (this.y/total_all)*100;

											return this.y + ' คณะ (ร้อยละ ' + p.toFixed(1) + ' %)';
										}
									}
								}
							},
							/*legend: {
								layout: 'vertical',
								align: 'right',
								verticalAlign: 'top',
								x: -40,
								y: -80,
								floating: true,
								borderWidth: 1,
								backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
								shadow: true
							},*/
							credits: {
								text: 'จากจำนวนคณะทั้งสิ้น ' + total_all + 'คณะ',
								position: {
									align: 'center',
									y: -5 // position of credits
								},
								style: {
									fontSize: '12pt' // you can style it!
								}
							},
							series: bar_series
						});
                    },
                    dataType: 'json'
                });
			});
				
			/*Button to open table with excel*/
			$("#btnExport").click(function (e) {
				  $("#table_total").table2excel({
					// exclude CSS class
					//exclude: ".noExl",
					name: "report-by-party-type"
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