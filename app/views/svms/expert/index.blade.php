@extends('svms.layouts.default')

{{-- Web site Title --}}
@section('title')
    Living University Management System
@stop

@section('extraScripts')
    {{--Use Jquery Ajax Form--}}
    {{ HTML::script('dependencies/form-master/jquery.form.js') }}
    {{--Use Data Tables--}}
    {{ HTML::style('dependencies/DataTables/media/css/jquery.dataTables.min.css') }}
    {{ HTML::script('dependencies/DataTables/media/js/jquery.dataTables.min.js') }}
    {{ HTML::script('https://cdn.datatables.net/plug-ins/1.10.12/api/fnReloadAjax.js') }}
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

@section('header')
    <i class="fa fa-graduation-cap" aria-hidden="true"></i>
    การจัดการข้อมูลวิทยากร

    <div class="pull-right">
        <a href="javascript:openCreate();" class="btn btn-primary"><span class="fa fa-plus"></span> เพิ่มวิทยากร</a>
    </div>
@stop

{{-- Content --}}
@section('content')

    <table id="editor_table" class="table table-condensed table-bordered" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th class="col-md-1"></th>
            <th class="col-md-3">ชื่อ - สกุล</th>
            <th class="col-md-2">ตำแหน่ง</th>
            <th class="col-md-2">ประเภท</th>
            <th class="col-md-2">สถานะ</th>
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
        <div class="modal fade" id="formModal" role="dialog" aria-labelledby="formModalLabel" aria-hidden="true">
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
                                <li role="presentation" class="active"><a href="#general" aria-controls="general" role="tab" data-toggle="tab">ข้อมูลทั่วไป</a></li>
                                <li role="presentation"><a href="#work" aria-controls="work" role="tab" data-toggle="tab">ข้อมูลการเป็นวิทยากร</a></li>
                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content">
                                {{-- ข้อมูลทั่วไป--}}
                                <div style="margin: 15px;" role="tabpanel" class="tab-pane active" id="general">

                                    <div class="row">
                                        <label class="col-sm-2">ภาพวิทยากร</label>
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
                                                        <option value="">เลือก</option>
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
                                        <label class="col-sm-2">เพศ</label>
                                        <div class="col-sm-5">
                                            <select class="form-control" name="sex" id="selectSex">
                                                <option value="">ไม่ระบุ</option>
                                                <option value="M">ชาย</option>
                                                <option value="F">หญิง</option>
                                            </select>
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

                                    <div class="clearfix" style="height: 10px;"></div>

                                    <div class="row">
                                        <label class="col-sm-2">อายุ</label>
                                        <div class="col-sm-3">
                                            <select class="form-control" name="age" id="selectAge">
                                                <option value="">ไม่ระบุ</option>
                                                @for($age=18;$age<=100;$age++)
                                                    <option value="{{ $age }}">{{ $age }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>

                                    <div class="clearfix" style="height: 10px;"></div>

                                    <div class="row">
                                        <label class="col-sm-2">ชนเผ่าท้องถิ่น</label>
                                        <div class="col-sm-4">
                                            <label class="radio-inline">
                                                <input type="radio" name="radioIsEthnic" id="radioIsEthnic1" value="yes"> ใช่
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="radioIsEthnic" id="radioIsEthnic2" value="no" checked> ไม่ใช่
                                            </label>
                                            <div class="clearfix"></div>
                                            <select style="display: none;" class="form-control" id="comboEthnic" name="ethnic">
                                                    <option value=""></option>
                                                @foreach($ethnics as $ethnic)
                                                    <option value="{{ $ethnic->id }}">{{ $ethnic->name }}</option>
                                                @endforeach
                                            </select>
                                            <span id="helpBlock" class="help-block">กรุณาระบุค่า ในกรณีที่เป็นวิทยากรท้องถิ่น</span>
                                        </div>
                                    </div>

                                    <div class="clearfix editing" style="height: 10px; display: none;"></div>

                                    <div class="row editing" style="display: none;">
                                        <label class="col-sm-2">สถานะงาน</label>
                                        <div class="col-sm-4">
                                            <select class="form-control" name="status" id="selectStatus">
                                                <option value="active" selected>ปฎิบัติงานได้</option>
                                                <option value="leave">งดเว้นการปฎิบัติงานชั่วคราว</option>
                                                <option value="quit">ออกจากการเป็นวิทยากร</option>
                                            </select>
                                        </div>
                                        <div id="divStatusNote" class="col-sm-4">
                                            <input type="text" class="form-control" name="status_note" id="inputStatusNote" placeholder="เนื่องจาก">
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

                                    <div class="clearfix IsMflf" style="height: 10px;"></div>

                                    <div class="row">
                                        <label class="col-sm-2">ประเภทวิทยากร</label>
                                        <div class="col-sm-7">
                                            <select style="width: 100%;" name="personnel_expert_types[]" id="comboExpertType" multiple="multiple" class="form-control">
                                                @foreach($expert_types as $expert_type)
                                                    <option value="{{ $expert_type->id }}">{{ $expert_type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="clearfix IsMflf" style="height: 10px;"></div>

                                    <div class="row">
                                        <label class="col-sm-2">ระดับวิทยากร</label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control" id="inputWorkLevel" name="work_level" placeholder="A,B,C">
                                        </div>
                                    </div>

                                    <div class="clearfix" style="height: 10px;"></div>

                                    <div id="location_bases" class="row" >
                                        <label class="col-sm-2">พื้นที่ทำงานหลัก</label>
                                        <div class="col-sm-7">
                                            <select class="form-control" name="work_base" id="comboWorkBase">
                                                <option value="">ไม่ทราบ</option>
                                                @foreach($areas as $area)
                                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="clearfix IsMflf" style="height: 10px;"></div>

                                    <div class="row IsMflf">
                                        <label class="col-sm-2" for="comboDepartment">แผนก</label>
                                        <div class="col-sm-7">
                                            <select name="department_id" id="comboDepartment" class="form-control" style="width: 100%;">
                                                @foreach($departments as $department)
                                                    <option value="{{ $department->id }}">{{ $department->financial_code }} {{ $department->name }}</option>
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

                                    <div class="clearfix" style="height: 10px;"></div>

                                    <div class="row">
                                        <label class="col-sm-2" for="inputLocalRole">บทบาทในชุมชน</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" name="local_role" id="inputLocalRole" placeholder="ระบุบทบาทในชุมชน เช่น อบต.">
                                        </div>
                                    </div>

                                    {{--<div class="clearfix IsMflf" style="height: 10px;"></div>

                                    <div class="row IsMflf">
                                        <label class="col-sm-2">Code Name</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" name="codename" id="inputCodename" placeholder="เช่น 888, 777, 11" maxlength="100">
                                        </div>
                                    </div>--}}

                                    <div class="clearfix" style="height: 10px;"></div>

                                    <div id="is_expert_type" class="row" >
                                        <label class="col-sm-2">การจ่ายเงินตามตำแหน่ง</label>
                                        <div class="col-sm-7">
                                            <select class="form-control" name="expert_type" id="expert_type">
                                                <option value="">บุคคลภายนอก</option>
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

            /*Initail*/
            $('#selectStatus').change();

            var countries = {{ json_encode($countries) }};
            /*Control Input Country*/
            $("#comboCountry").select2({
                data: countries,
                templateResult: formatCountry,
                templateSelection: formatCountry
            });

            $("#comboExpertType").select2({
                placeholder : "คลิกเลือกได้มากกว่า 1 ค่า"
            });

            $('#comboDepartment').select2();

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

            $('#selectStatus').on('change', function(){
                if ($(this).val()!='active')
                {
                    $('#divStatusNote').show();
                }
                else
                {
                    $('#divStatusNote').hide();
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

            $('input[name=radioIsEthnic]').on('change', function(){
                if ($(this).val()=='yes')
                {
                    $('#comboEthnic').show();
                }
                else
                {
                    $('#comboEthnic').hide().val('');
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
                    url: "{{ URL::action('ExpertController@postCreateOrUpdate') }}",
                    dataType:  'json',
                    data: {
                        'id' : (create_new=="true") ? 0 : $('#old_id').val()
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
                            //oTable.fnDraw();
                            //oTable.fnReloadAjax();
                            location.reload();
                            successAlert('ทำรายการสำเร็จ !', data.msg);
                        }
                        else
                        {
                            //else not success reset old value
                            errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                        }
                    }  // post-submit callback
                };

                $('form#form').ajaxSubmit(options);

                e.preventDefault(); //STOP default action
            });

            var oTable;
            oTable = $('#editor_table').dataTable({
                "language": {
                    "url": "{{ URL::asset('assets/js/Thai.json') }}"
                },
                "order": [[1,'desc']],
                //"info": false,
                //"searching": false,
                "processing": true,
                "serverSide": true,
                "ajax": "{{ URL::action('ExpertController@getData') }}",
                "stateSave": true,
                "columns":
                        [
                            { "data" : "image" },
                            { "data" : "full_name", "title" : "ชื่อ - สกุล", "orderable": true, "searchable": true },
                            { "data" : "position", "title" : "ตำแหน่ง", "orderable": true, "searchable": true },
                            { "data" : "expert_type", "title" : "ประเภท", "orderable": true, "searchable": true},
                            { "data" : "status_thai", "title" : "สถานะ", "orderable": true, "searchable": true},
                            { "data" : "actions" }
                        ],
                "fnDrawCallback": function ( oSettings ) {
                },
                "createdRow": function( row, data, dataIndex ) {

                    if ( data['status'] == 'quit' )
                    {
                        $(row).addClass( 'danger' );
                    }
                    if ( data['status'] == 'leave' )
                    {
                        $(row).addClass( 'warning' );
                    }

                }
            });
        });

        /*template select*/
        function formatCountry (countries) {
            if (!countries.id) { return countries.text; }
           
            var countryFormat = '<span>' + countries.text + '</span>';

            return countryFormat;
        }

        function openCreate()
        {
            //set title
            $('#formModalLabel').html('เพิ่มวิทยากร');
            //reset form
            $('form')[0].reset();
            //set new value
            $('input[name=new]').val(true);
            $('.editing').hide();

            //set default image
            var default_image = '<img src="{{ asset('assets/img/people.png') }}" border="0" maxwidth="100" maxheight="100" />';
            $('#personnelImage').html(default_image);

            //open modal
            $('#formModal').modal({
                keyboard : false,
                backdrop : false
            });
        }

        function openEdit(id)
        {
            //set title
            $('#formModalLabel').html('แก้ไขวิทยากร');
            //set new value
            $('input[name=new]').val(false);
            $('.editing').show();

            //get Data From Ajax
            $.ajax({
                url: "{{ URL::action('ExpertController@getById') }}",
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

                $('#selectAge').val(data.age);
                $('#selectSex').val(data.sex);
                $('#comboWorkBase').val(data.work_base);
                $('#inputWorkLevel').val(data.work_level);

                $('#comboExpertType').select2().val(data.expert_types).change();
                $('#inputLocalRole').val(data.local_role);

                $('#selectStatus').val(data.status).change();
                $('#inputStatusNote').val(data.status_note);

                if(data.is_ethnic)
                {
                    $('#radioIsEthnic1').prop('checked', true).change();
                    $('#comboEthnic').val(data.ethnic).change();
                }
                else
                {
                    $('#radioIsEthnic2').prop('checked', true).change();
                    $('#comboEthnic').val('').change();
                }

                //check if department
                if (data.department_id!=1)
                {
                    //case is mflf
                    $('input:radio[id=radioIsMflf1]').prop('checked', true);
                    $('input:radio[name=radioIsMflf]').change();
                    $('#comboDepartment').val(data.department_id).change();
                    $('#inputMflOffice').val(data.mfl_office);
                }
                else
                {
                    //case is not mflf or other
                    $('input:radio[id=radioIsMflf2]').prop('checked', true);
                    $('input:radio[name=radioIsMflf]').change();
                    $('#comboDepartment').val(1).change();
                    $('#inputOtherOffice').val(data.other_office);
                }

                $('#expert_type').val(data.personnel_type_id).change();

            });

            //open modal
            $('#formModal').modal({
                keyboard : false,
                backdrop : false
            });
        }

        function openDelete(id)
        {
            if(confirm('ต้องการยืนยันการลบหรือไม่ ?')==true)
            {
                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('ExpertController@postDelete') }}",
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
