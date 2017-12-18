{{--หน้า รายงานบุคลากรออกเป็น Excel ได้--}}
@extends('svms.layouts.reporting')

@section('title')
    รายงานข้อมูลบุคลากร
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
            <i class="fa fa-file-excel-o"></i> รายนามบุคลากร
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
                    <form id="search_form" name="form" class="form-horizontal" role="form" method="POST" action="{{ URL::action('PersonnelsReportController@postExcelPersonnels') }}">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                        <!-- ./ csrf token -->

                        <div class="form-group">
                            <label for="comboPersonnelType" class="col-sm-3 control-label">ประเภทบุคลากร</label>
                            <div class="col-sm-6">
                                <select class="form-control" name="type" id="comboPersonnelType">
                                    <option value="">เป็นทั้งบุคลากรและวิทยากร</option>
                                    <option value="mflf">เป็นวิทยากรเท่านั้น</option>
                                    <option value="other">เป็นบุคลากรเท่านั้น</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group isExpert">
                            <label for="comboExpertType" class="col-sm-3 control-label">วิทยากรประเภท</label>
                            <div class="col-sm-6">
                                <select class="form-control select2" name="expert_types[]" id="comboExpertType" multiple="multiple" style="width: 100%;">
                                    @foreach($expert_types as $expert_type)
                                        <option value="{{ $expert_type->id }}">{{ $expert_type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group isExpert">
                            <label for="inputLevel" class="col-sm-3 control-label">ระดับของวิทยากร</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="level" id="inputLevel" placeholder="สามารถใส่ , คั่นเพื่อแสดงมากกว่า 1 ระดับ เช่น A,B,C">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="inputName" class="col-sm-3 control-label">ชื่อบุคลากร (ชื่อจริงภาษาไทย, ชื่อจริงภาษาอังกฤษ หรือชื่อเล่น)</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="name" id="inputName" placeholder="สามารถใส่ , คั่นเพื่อแสดงมากกว่า 1 ชื่อ">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="comboNationality" class="col-sm-3 control-label">สัญชาติ/ประเทศ (เลือกได้มากกว่า 1)</label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="nationalities[]" id="comboNationality" multiple="multiple" style="width: 100%">
                                    @foreach($countries as $country)
                                        <option {{ ($country->id=='th') ? 'selected' : '' }} value="{{ $country->id }}">{{ $country->text }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="comboEthnic" class="col-sm-3 control-label">ชนเผ่า (เลือกได้มากกว่า 1)</label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="ethnics[]" id="comboEthnic" multiple="multiple" style="width: 100%">
                                    @foreach($ethnics as $ethnic)
                                        <option value="{{ $ethnic->id }}">{{ $ethnic->text }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="comboOrganization" class="col-sm-3 control-label">หน่วยงาน</label>
                            <div class="col-sm-6">
                                <select class="form-control" name="org" id="comboOrganization">
                                    <option value="">หน่วยงานทั้งในและนอกมูลนิธิฯ</option>
                                    <option value="mflf">เฉพาะของมูลนิธิแม่ฟ้าหลวงฯ</option>
                                    <option value="other">เฉพาะภายนอกมูลนิธิฯ</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group isMflf">
                            <label for="inputPosition" class="col-sm-3 control-label">แผนก (เลือกได้มากกว่า 1)</label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="departments[]" id="comboDepartment" multiple="multiple" style="width: 100%">

                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="inputPosition" class="col-sm-3 control-label">ตำแหน่งงาน</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="position" id="inputPosition" placeholder="สามารถใส่ , คั่นเพื่อแสดงมากกว่า 1 ตำแหน่ง">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="inputLocalRole" class="col-sm-3 control-label">บทบาทในชุมชน</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="local_role" id="inputLocalRole" placeholder="สามารถใส่ , คั่นเพื่อแสดงมากกว่า 1 บทบาท">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="comboLocationBase" class="col-sm-3 control-label">พื้นที่ทำงานหลัก (เลือกได้มากกว่า 1)</label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="location_bases[]" id="comboLocationBase" multiple="multiple" style="width: 100%">
                                    @foreach($mflfAreas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status" class="col-sm-3 control-label">สถานะ</label>
                            <div class="col-sm-6">
                                <select class="form-control" name="status" id="comboStatus">
                                    <option value="">ทั้งหมด</option>
                                    @foreach(array_keys($statuses) as $key)
                                        <option value="{{ $key }}">{{ $statuses[$key] }}</option>
                                    @endforeach
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

        $(document).ready(function(){
            $('#comboOrganization').change();
        });

        $(function () {

            $(".select2").select2();

            $('#comboOrganization').on('change', function (e)
            {
                if ($(this).val()=='other')
                {
                    $('.isMflf').hide();
                }
                else
                {
                    $('.isMflf').show();
                }
            });

            $('#comboPersonnelType').on('change', function (e)
            {
                if ($(this).val()=='other')
                {
                    $('.isExpert').hide();
                }
                else
                {
                    $('.isExpert').show();
                }
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
                        'bases' : $('#comboLocationBase').val(),
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

    </script>

@stop