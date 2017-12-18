@extends('svms.layouts.default')

@section('title')
    Living University Management System
@stop

@section('extraScripts')
    {{--Use Bootstrap Datepicker--}}
    {{ HTML::script('assets/js/moment.js') }}
    {{ HTML::script('assets/js/th.js') }}
    {{ HTML::script('dependencies/bootstrap-datetimepicker-master/build/js/bootstrap-datetimepicker.min.js') }}
    {{ HTML::style('dependencies/bootstrap-datetimepicker-master/build/css/bootstrap-datetimepicker.min.css') }}
    {{--Use Bootstrap Select2--}}
    {{ HTML::script('dependencies/select2-4.0.0-beta.3/dist/js/select2.min.js') }}
    {{ HTML::style('dependencies/select2-4.0.0-beta.3/dist/css/select2.min.css') }}
    {{--Use Data Tables--}}
    {{ HTML::style('dependencies/DataTables/media/css/jquery.dataTables.min.css') }}
    {{ HTML::script('dependencies/DataTables/media/js/jquery.dataTables.min.js') }}
    {{--Use DataTables fnReload--}}
    {{ HTML::script('assets/js/fnReloadAjax.js') }}
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
    <span class="fa fa-male"></span>
    ข้อมูลคณะย้อนหลัง
    <div class="pull-right">
        <a href="javascript:openCreate();" class="btn btn-small btn-primary"><span class="fa fa-plus"></span> เพิ่มข้อมูล</a>
    </div>
@stop

@section('content')

    <div class="col-xs-12 col-md-12">
        {{--Start Data Table Plugin--}}
        <table id="grid-history-parties" class="table table-striped table-bordered table-hover">
            <thead>
            <tr>
                <th class="col-md-1">รหัส</th>
                <th class="col-md-3">ชื่อคณะ</th>
                <th class="col-md-2">ประเภท</th>
                <th class="col-md-1">จำนวน</th>
                <th class="col-md-2">ช่วงวันที่มา</th>
                <th class="col-md-3">Actions</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        {{--Finish Data Table Plugin--}}
    </div>

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
                        <div class="form-group">
                            <label class="control-label">รหัสลูกค้า</label>
                            <input id="input_customer_code" name="customer_code" type="text" class="form-control" value="">
                        </div>
                        <div class="form-group">
                            <label class="control-label">ชื่อคณะ/บุคคล *</label>
                            <input id="input_name" name="name" type="text" class="form-control" value="" required>
                        </div>
                        <div class="form-group">
                            <label class="control-label">มาจาก *</label>
                            <label class="radio-inline">
                                <input type="radio" name="radioFromCountry" id="radioFromCountry1" value="th" checked> <i><img src="{{ asset('assets/img/flags/th.png') }}" class="img-flag" /></i> ในประเทศไทย
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="radioFromCountry" id="radioFromCountry2" value="other"> ประเทศอื่นๆ
                            </label>
                            <div id="divCountrySelect" style="margin-top: 5px; display: none;">
                                <select class="form-control" name="countries[]" id="comboCountry" multiple="multiple" style="width: 100%">

                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comboType" class="control-label">ประเภทคณะ *</label>
                            <select class="form-control" name="party_type_id" id="comboType" required>
                                <option value="">เลือก</option>
                                @foreach($partyTypes as $partyType)
                                    <option value="{{ $partyType->ID }}">{{ $partyType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="numberQuantity" class="control-label">จำนวนผู้เข้าร่วม *</label>
                            <div class="input-group" style="width: 50%">
                                <input type="number" class="form-control" name="people_quantity" id="numberQuantity" max="9999" value="1" min="1" aria-describedby="numberQuantity-addon" required>
                                <span class="input-group-addon" id="numberQuantity-addon">คน</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputDateStart" class="control-label">วันที่เริ่ม *</label>
                                <div class='input-group date' id='dateStart' style="width: 50%;">
                                    <input type='text' class="form-control" data-date-format="YYYY-MM-DD" name="start_date" id="inputDateStart" placeholder="เริ่มวันที่" value="" required />
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                        </div>
                        <div class="form-group">
                            <label for="inputDateEnd" class="control-label">วันที่สิ้นสุด *</label>
                                <div class='input-group date' id='dateEnd' style="width: 50%;">
                                    <input type='text' class="form-control" data-date-format="YYYY-MM-DD" name="end_date" id="inputDateEnd" placeholder="ถึงวันที่" value="" required />
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                        </div>
                        <div class="form-group">
                            <label for="comboObjective" class="control-label">วัตถุประสงค์การมา *</label>
                            <select class="form-control" name="objectives[]" id="comboObjective" multiple="multiple" style="width: 100%">
                                @foreach($partyObjectives as $partyObjective)
                                    <option value="{{ $partyObjective->id }}">{{ $partyObjective->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="textareaMoreObjective" class="control-label">รายละเอียดเพิ่มเติม</label>
                            <textarea class="form-control" name="objective_detail" id="textareaMoreObjective"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="coordinatorSelected" class="control-label">ผู้ประสานงานหลัก</label>
                            <select id='coordinatorSelected' name="project_co" class='form-control'>
                                <option value=''>ยังไม่ได้ระบุ</option>
                                @foreach($coordinators as $coordinator)
                                    <option value='{{ $coordinator->personnel_id }}'>{{ $coordinator->fullName().'('.$coordinator->department->code.')' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="summaryIncome" class="control-label">รายได้สุทธิ</label>
                            <div class="input-group">
                                <input id="summaryIncome" name="summary_income" type="number" class="form-control" step="any" aria-describedby="summaryIncome-addon">
                                <span class="input-group-addon" id="summaryIncome-addon">บาท</span>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-times"></span> ปิด</button>
                        <button type="submit" class="btn btn-success"><span class="fa fa-floppy-o"></span> บันทึกข้อมูล</button>
                    </div>
                </div>
            </div>
        </div>

    </form>

    <script type="text/javascript">
        $(function () {

            /*Control Input Country*/
            var countries = {{ json_encode($countries) }};

            $("#comboCountry").select2({
                data: countries,
                templateResult: formatCountry,
                templateSelection: formatCountry
            });

            /*checked country*/
            $('input[name=radioFromCountry]').on('change', function(e){
                e.preventDefault();

                if ($('input[name=radioFromCountry]:checked').val()==='th')
                {
                    $('#divCountrySelect').hide();
                    $('#comboCountry').select2("val", "");
                }
                else
                {
                    $('#divCountrySelect').show();
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

            /*Combo Objective*/
            $('#comboObjective').select2();

            /* DataTables */
            var table = $('#grid-history-parties').DataTable({
                "language": {
                    "url": "{{-- URL::asset('assets/js/Thai.json') --}}"
                },
                "order": [[0,'desc']],
                "processing": true,
                "serverSide": true,
                "ajax": "{{{ URL::to('party/histories') }}}",
                "fnDrawCallback": function ( oSettings ) {
                }
            });

            /*Submit Form*/
            var data;
            $('form').submit(function(e){
                //hide modal
                $('#formModal').modal('hide');
                //ajax submit
                $.ajax({
                    type: "POST",
                    url: "{{{ URL::to('party/create-or-update') }}}",
                    data: $( this ).serialize(),
                    success: function (data) {
                        if (data.status=='success')
                        {
                            table.ajax.reload();
                            //alert success
                            successAlert('ทำรายการสำเร็จ !', data.msg);
                        }
                        else
                        {
                            //alert error
                            errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                        }
                    },
                    dataType: 'json'
                });

                e.preventDefault(); //STOP default action
            });
        });

        function openDelete(id)
        {
            var buttons = [
                {
                    label: 'ยืนยันการลบ',
                    cssClass: 'btn-success',
                    action: function(dialogItself){
                        $.ajax({
                            type: "POST",
                            url: "{{{ URL::to('party/delete') }}}",
                            data :
                            {
                                '_token' : $('input[name=_token]').val(),
                                'id' : id
                            }
                        }).done(function(data) {
                            dialogItself.close();
                            if (data.status=='success')
                            {
                                successAlert('ทำรายการสำเร็จ !', data.msg);
                                //set time out to reload
                                setTimeout(function(){
                                    location.reload();
                                }, 2100);
                            }
                            else
                            {
                                errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                            }
                        });
                    }
                },
                {
                    label: 'ยกเลิก',
                    action: function(dialogItself){
                        dialogItself.close();
                    }
                }
            ];
            warningButton('ต้องการยืนยันการลบหรือไม่ ?', 'ระบบจะทำลบลงถังขยะ Admin สามารถกู้คืนได้ภายหลัง', buttons);
        }

        function openCreate()
        {
            //set title
            $('#formModalLabel').html('เพิ่มข้อมูลคณะย้อนหลัง');
            //reset form
            $('form')[0].reset();
            $('#radioFromCountry1').change();
            $('#comboCountry').val("").change();
            $('#comboObjective').val("").change();
            //set new value
            $('input[name=new]').val(true);
            //open modal
            $('#formModal').modal({ keyboard : false });
        }

        function openEdit(id)
        {
            //set title
            $('#formModalLabel').html('แก้ไขข้อมูลคณะย้อนหลัง');
            //set new value
            $('input[name=new]').val(false);

            //get Data From Ajax
            $.ajax({
                url: "{{{ URL::to('party/get-by-id') }}}",
                data :
                {
                    '_token' : $('input[name=_token]').val(),
                    'id' : id
                }
            }).done(function(data) {

                if (data.status=='error')
                {
                    errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                    return false;
                }

                var data = data.data;
                //console.log(data);
                //set old value
                $('#old_id').val(data.id);
                $('#input_customer_code').val(data.customer_code);
                $('#input_name').val(data.name);

                if (data.is_local==1)
                {
                    $('#radioFromCountry1').prop('checked', true).change();
                    $('#radioFromCountry2').prop('checked', false);
                }
                else
                {
                    $('#radioFromCountry1').prop('checked', false);
                    $('#radioFromCountry2').prop('checked', true).change();
                    //$('#comboCountry').select2("val", data.countries).change();
                    $('#comboCountry').val(data.countries).trigger("change");
                }

                $('#comboType').val(data.party_type_id).change();
                $('input[name=people_quantity]').val(data.people_quantity);
                $('input[name=start_date]').val(data.start_date);
                $('input[name=end_date]').val(data.end_date);
                $('textarea[name=objective_detail]').val(data.objective_detail);
                $('select[name=project_co]').val(data.project_co);
                $('input[name=summary_income]').val(data.summary_income);

                //$('#comboObjective').select2("val", data.objective_arrays).change();
               $('#comboObjective').val(data.objective_arrays).trigger("change");
            });

            //open modal
            $('#formModal').modal({ keyboard : false });
        }

        /*template select*/
        function formatCountry (countries) {
            var public_path = 'http://lu.maefahluang.org:8080/svms/public';
            if (!countries.id) { return countries.text; }
            var countryFormat = '<span><img src="'+public_path+'/assets/img/flags/' + countries.id + '.png" class="img-flag" /> ' + countries.text + '</span>';

            return countryFormat;
        }
    </script>

@stop