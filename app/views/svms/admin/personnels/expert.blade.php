{{--***Already not use***--}}
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
{{-- Expert Data Management Created At 25/4/16 --}}
{{-- Content --}}
@section('content')
    <div class="page-header">
        <h2>
            จัดการข้อมูลประวัติวิทยากร

            <div class="pull-right">
                <a href="javascript:openCreate();" class="btn btn-small btn-primary"><span class="fa fa-plus"></span> สร้างใหม่</a>
            </div>
        </h2>
    </div>

    <table id="editor_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th class="col-md-1"></th>
            <th class="col-md-3">ชื่อ - สกุล (ชื่อเล่น)</th>
            <th class="col-md-2">Office</th>
            <th class="col-md-2">ประเภทวิทยากร</th>
            <th class="col-md-2">บุคลากรมูลนิธิฯ</th>
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

                        <!--General Data-->
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-2">
                                        <img src="{{ asset('assets/img/people.png') }}" class="img-thumbnail" maxwidth="100" maxheight="100" />
                                    </div>
                                    <div class="col-md-10">
                                        <div class="row">
                                            <div class="col-md-3"><label>ชื่อ-สกุล</label></div>
                                            <div class="col-md-3"><span id="show_expert_fullname"></span></div>
                                            <div class="col-md-3"><label>ชื่อเล่น</label></div>
                                            <div class="col-md-3"><span id="show_expert_nickname"></span></div>
                                        </div>
                                        <div class="row isMflf">
                                            <div class="col-md-3"><label>แผนก</label></div>
                                            <div class="col-md-3"><span id="show_expert_mfl_department"></span></div>
                                            <div class="col-md-3"><label>ส่วนงาน</label></div>
                                            <div class="col-md-3"><span id="show_expert_mfl_office"></span></div>
                                        </div>
                                        <div class="row nonMflf">
                                            <div class="col-md-3"><label>หน่วยงาน/องค์กร</label></div>
                                            <div class="col-md-9"><span id="show_expert_other_office"></span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--Expansion Data Etc. Training, Skill-->
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Home</a></li>
                            <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">Profile</a></li>
                        </ul>

                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="home">...</div>
                            <div role="tabpanel" class="tab-pane" id="profile">...</div>
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

            $('#BD').datetimepicker({
                pickTime: false,
                language: 'th'
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

            $('#confirm').on('change', function(){
                if ($(this).val()=='1')
                {
                    $('#roles').prop('disabled', false);
                }else{
                    $('#roles').prop('disabled', true);
                    $("#roles option:selected").removeAttr("selected");
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
                    url: (create_new=="true") ? "{{ URL::action('AdminPersonnelExpertController@postCreate') }}" : "{{ URL::action('AdminPersonnelExpertController@postEdit') }}",
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
                "bSort": false,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": "{{{ URL::action('AdminPersonnelExpertController@getData') }}}",
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
            $('#formModalLabel').html('เพิ่มประวัติวิทยากร');
            //reset form
            $('form')[0].reset();
            //set new value
            $('input[name=new]').val(true);

            //open modal
            $('#formModal').modal({ keyboard : false });
        }

        function openEdit(id)
        {
            //set title
            $('#formModalLabel').html('แก้ไขประวัติวิทยากร');
            //set new value
            $('input[name=new]').val(false);

            //get Data From Ajax
            $.ajax({
                url: "{{ URL::action('AdminPersonnelExpertController@getById') }}",
                data :
                {
                    '_token' : $('input[name=_token]').val(),
                    'id' : id
                }
            }).done(function(data) {
                var data = data.data;
                //console.log(data);
                //set old value
                $('#old_id').val(data.personnel_id);

                $('#inputCode').val(data.code);
                $('#comboPrefix').val(data.prefix);
                $('#inputFirstName').val(data.first_name);
                $('#inputLastName').val(data.last_name);

                $('#comboPrefixEn').val(data.prefix_en);
                $('#inputFirstNameEn').val(data.first_name_en);
                $('#inputLastNameEn').val(data.last_name_en);

                $('#inputEmail').val(data.email);
                $('#inputMobile').val(data.mobile);
                $('#comboCountry').val(data.nationality);
                $('#inputPosition').val(data.position);

                $('#comboDepartment').val(data.department_id);

                if (typeof data.confirmed == 'undefined')
                {
                    $('#confirm').val(0);
                }
                else
                {
                    $('#confirm').val(data.confirmed);
                }

                $('#comboDepartment').trigger('change');

                $('#confirm').trigger('change');

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
                    url: "{{ URL::action('AdminPersonnelExpertController@postDelete') }}",
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
