@extends('svms.layouts.landing')

@section('title')
    Living University Management System
@stop

@section('extraScripts')
    {{--Use Jquery Ajax Form--}}
    {{ HTML::script('dependencies/form-master/jquery.form.js') }}
    {{--Use Jquery Validator--}}
    {{ HTML::script('dependencies/jquery-validation/dist/jquery.validate.min.js') }}
    {{ HTML::script('dependencies/jquery-validation/dist/additional-methods.min.js') }}
    {{ HTML::script('dependencies/jquery-validation/dist/localization/messages_th.min.js') }}
    {{ HTML::script('assets/js/jquery.validate.default.js') }}
    {{--Use Bootstrap Datepicker--}}
    {{ HTML::script('assets/js/moment.js') }}
    {{ HTML::script('assets/js/th.js') }}
    {{ HTML::script('dependencies/bootstrap-datetimepicker-master/build/js/bootstrap-datetimepicker.min.js') }}
    {{ HTML::style('dependencies/bootstrap-datetimepicker-master/build/css/bootstrap-datetimepicker.min.css') }}
    {{--Use Bootstrap Select2--}}
    {{ HTML::script('dependencies/select2-4.0.0-beta.3/dist/js/select2.min.js') }}
    {{ HTML::style('dependencies/select2-4.0.0-beta.3/dist/css/select2.min.css') }}
    {{--Use Jquery Treeview--}}
    {{ HTML::script('dependencies/vakata-jstree/dist/jstree.min.js') }}
    {{ HTML::style('dependencies/vakata-jstree/dist/themes/default/style.min.css') }}
    {{--Use Bootstrap TinyMCE--}}
    {{ HTML::script('dependencies/tinymce/js/tinymce/tinymce.min.js') }}

@stop

@section('extraStyles')
    <style type="text/css">
        .tab-pane{
            margin: 5px;
        }
        .expert-list-info {
            border-bottom: 1px #EEEEEE solid;
        }
        .persona-avatar{
            max-width: 200px;
            text-align: center;
        }
    </style>
@stop

@section('header')
    <div class="pull-left">
        <a class="btn btn-default btn-xs" href="{{ URL::to('/') }}" role="button"><i class="fa fa-home"></i> Dashboard</a>
    </div>

    @if($expert->sex=='M')
        <i class="fa fa-male" aria-hidden="true"></i>
    @else
        <i class="fa fa-female" aria-hidden="true"></i>
    @endif

    วิทยากร :

    @if($expert->code)
        {{ $expert->code }}
    @endif

    {{ $expert->fullName() }}

    <div class="pull-right">
        <a class="btn btn-default btn-xs" href="{{ URL::to('expert') }}" role="button"><i class="fa fa-graduation-cap"></i> หน้ารวมวิทยากร</a>
    </div>
@stop

@section('content')

    {{--Start Content--}}
    <form rel="form" id="formPersona" name="formPersona" enctype="multipart/form-data">
    <!-- CSRF Token -->
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <!-- ./ csrf token -->
        <input type="hidden" name="persona_id" value="{{ $expert->id }}" />
        <div class="container bootstrap snippet">
            <div class="row">
                <div class="col-sm-3"><!--left col-->

                    <ul class="list-group">
                        <li class="list-group-item text-muted"><strong><i class="fa fa-info" aria-hidden="true"></i> ข้อมูลทั่วไป</strong></li>
                        <li class="list-group-item">
                            <center><img title="profile image" src="{{ $expert->imagePath() }}" alt="{{ $expert->fullName() }}" class="img-round img-responsive persona-avatar"></center>
                        </li>
                        @if(trim($expert->code)!="")
                            <li class="list-group-item text-right"><span class="pull-left"><strong>รหัสพนักงาน</strong></span> {{ $expert->code }}</li>
                        @endif
                        @if(trim($expert->fullName())!="")
                            <li class="list-group-item text-right"><span class="pull-left"><strong>ชื่อ - สกุล</strong></span> {{ $expert->fullName() }}</li>
                        @endif
                        @if(trim($expert->fullName('en'))!="")
                            <li class="list-group-item text-right"><span class="pull-left"><strong>Full Name</strong></span> {{ $expert->fullName('en') }}</li>
                        @endif
                        @if(trim($expert->nick_name)!="")
                            <li class="list-group-item text-right"><span class="pull-left"><strong>ชื่อเล่น</strong></span> {{ $expert->nick_name }}</li>
                        @endif
                        @if($expert->birth_date!="" || $expert->birth_month!="" || $expert->birth_year!="")
                            <li class="list-group-item text-right"><span class="pull-left"><strong>วันเกิด</strong></span> {{ $expert->birth_date.' '.$expert->birth_month.' '.$expert->birth_year }}</li>
                        @endif
                        @if($expert->age)
                            <li class="list-group-item text-right"><span class="pull-left"><strong>อายุ</strong></span> {{ $expert->age }} ปี</li>
                        @endif
                        @if($expert->email)
                            <li class="list-group-item text-right"><span class="pull-left"><strong>E-mail</strong></span> {{ $expert->email }}</li>
                        @endif
                        @if($expert->mobile)
                            <li class="list-group-item text-right"><span class="pull-left"><strong>เบอร์โทร</strong></span> {{ $expert->mobile }}</li>
                        @endif
                        @if($expert->nationality)
                            <li class="list-group-item text-right"><span class="pull-left"><strong>สัญชาติ</strong></span> <img class="img-responsive pull-right" src="{{ asset('assets/img/flags/'.$expert->nationality.'.png') }}"> <div class="clearfix"></div></li>
                        @endif
                        @if($expert->is_ethnic)
                            @if($expert->ethnic())
                                <li class="list-group-item text-right"><span class="pull-left"><strong>ชนเผ่า</strong></span> {{ $expert->ethnic() }}</li>
                            @endif
                        @endif

                    </ul>

                    <!-- //Already not use
                    <div class="panel panel-default">
                        <div class="panel-heading">Social Media</div>
                        <div class="panel-body">
                            <i class="fa fa-facebook fa-2x"></i> <i class="fa fa-github fa-2x"></i> <i class="fa fa-twitter fa-2x"></i> <i class="fa fa-pinterest fa-2x"></i> <i class="fa fa-google-plus fa-2x"></i>
                        </div>
                    </div>
                    -->

                </div><!--/col-3-->
                <div class="col-sm-9">

                    {{--Expert Data--}}
                    <div class="panel panel-default">
                        <div class="panel-heading"><strong><i class="fa fa-info" aria-hidden="true"></i> ข้อมูลการเป็นวิทยากร</strong></div>
                        <div class="panel-body">

                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <div class="row">
                                        @if($expert->department_id!=1)
                                            <div class="col-sm-4 text-left">
                                                <strong>บุคลากร</strong>
                                            </div>
                                            <div class="col-sm-8 text-right">
                                                มูลนิธิแม่ฟ้าหลวง ในพระบรมราชูปถัมภ์
                                            </div>
                                            @if ($expert->department())
                                                <div class="col-sm-4 text-left">
                                                    <strong>แผนก</strong>
                                                </div>
                                                <div class="col-sm-8 text-right">
                                                    {{ $expert->department()->pluck('name') }}
                                                </div>
                                            @endif
                                            @if ($expert->mfl_office)
                                                <div class="col-sm-4 text-left">
                                                    <strong>ฝ่าย</strong>
                                                </div>
                                                <div class="col-sm-8 text-right">
                                                    {{ $expert->mfl_office }}
                                                </div>
                                            @endif
                                        @else
                                            <div class="col-sm-4 text-left">
                                                <strong>บุคลากร</strong>
                                            </div>
                                            <div class="col-sm-8 text-right">
                                                ภายนอก
                                            </div>
                                            @if ($expert->other_office)
                                                <div class="col-sm-4 text-left">
                                                    <strong>องค์กร</strong>
                                                </div>
                                                <div class="col-sm-8 text-right">
                                                    {{ $expert->other_office }}
                                                </div>
                                            @endif
                                        @endif
                                        @if ($expert->position)
                                            <div class="col-sm-4 text-left">
                                                <strong>ตำแหน่ง</strong>
                                            </div>
                                            <div class="col-sm-8 text-right">
                                                {{ $expert->position }}
                                            </div>
                                        @endif
                                        @if ($expert->local_role)
                                            <div class="col-sm-4 text-left">
                                                <strong>บทบาทในชุมชุน</strong>
                                            </div>
                                            <div class="col-sm-8 text-right">
                                                {{ $expert->local_role }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">

                                    @if($expert->expertType())
                                        <div class="col-sm-4 text-left">
                                            <strong>ประเภท</strong>
                                        </div>
                                        <div class="col-sm-8 text-right">
                                            {{  $expert->expertType() }}
                                        </div>
                                    @endif
                                    @if($expert->work_base)
                                        <div class="col-sm-4 text-left">
                                            <strong>พื้นที่หลัก</strong>
                                        </div>
                                        <div class="col-sm-8 text-right">
                                            {{ $expert->WorkBase() }}
                                        </div>
                                    @endif
                                    @if($expert->expertPaidType())
                                        <div class="col-sm-4 text-left">
                                            <strong>เรทค่าตัว</strong>
                                        </div>
                                        <div class="col-sm-8 text-right">
                                            {{ $expert->expertPaidType()->name }}
                                        </div>
                                    @endif

                                </div>
                            </div>

                        </div>
                    </div>

                    {{--Extend Expert Data--}}
                    <div id="panelPersonnelExtendData" class="panel panel-info">
                        <div class="panel-heading"><strong><i class="fa fa-info-circle" aria-hidden="true"></i> ข้อมูลเพิ่มเติม สำหรับวิทยากร</strong></div>
                        <div class="panel-body">
                            {{--loading indicator--}}
                            <span id="loadingPersonnelExtendData"></span>
                            {{--Div Show Extend Data--}}
                            <div id="personnelExtendData">
                                <!-- Nav tabs -->
                                <ul id="tabPersonnelExtendData" class="nav nav-tabs" role="tablist">
                                    <li role="presentation" class="active"><a href="#lecture" aria-controls="lecture" role="tab" data-toggle="tab">ประเด็นบรรยาย</a></li>
                                    <li role="presentation"><a href="#experience" aria-controls="experience" role="tab" data-toggle="tab">ประสบการณ์</a></li>
                                    <li role="presentation"><a href="#history" aria-controls="history" role="tab" data-toggle="tab">ภูมิหลัง/ประวัติ</a></li>
                                    <li role="presentation"><a href="#education" aria-controls="education" role="tab" data-toggle="tab">การศึกษา</a></li>
                                    <li role="presentation"><a href="#training" aria-controls="training" role="tab" data-toggle="tab">การฝึกอบรม</a></li>
                                    <li role="presentation"><a href="#language" aria-controls="language" role="tab" data-toggle="tab">ความสามารถด้านภาษา</a></li>
                                    <li role="presentation"><a href="#upload_file" aria-controls="upload_file" role="tab" data-toggle="tab">เอกสาร</a></li>
                                </ul>

                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane active" id="lecture">
                                        <h5>ประเด็นหลัก
                                            <div class="pull-right"><button type="button" id="addMainSubjectBullet" data-content="main_subject" class="btn btn-xs btn-primary addBulletInput"><i class="fa fa-plus-circle" aria-hidden="true"></i> เพิ่มประเด็นหลัก</button></div>
                                        </h5>
                                        <table id="table_main_subject" class="table table-striped">
                                            <tbody>
                                            {{--Attend by ajax--}}
                                            </tbody>
                                        </table>
                                        <h5>ประเด็นรอง
                                            <div class="pull-right"><button type="button" id="addSecondSubjectBullet" data-content="second_subject" class="btn btn-xs btn-primary addBulletInput"><i class="fa fa-plus-circle" aria-hidden="true"></i> เพิ่มประเด็นรอง</button></div>
                                        </h5>
                                        <table id="table_second_subject" class="table table-condensed">
                                            <tbody>
                                            {{--Attend by ajax--}}
                                            </tbody>
                                        </table>
                                    </div>

                                    <div role="tabpanel" class="tab-pane" id="experience">
                                        <div class="pull-left"><strong>คำเตือน กรุณาเรียงลำดับข้อมูลทำเป็นปัจจุบันที่สุดก่อน</strong></div>
                                        <div class="pull-right" style="margin: 5px auto;"><button type="button" id="addExperienceBullet" data-content="experience" class="btn btn-xs btn-primary addBoxInput"><i class="fa fa-plus-circle" aria-hidden="true"></i> เพิ่มข้อมูลประสบการณ์ทำงาน</button></div>
                                        <div class="clearfix"></div>
                                        {{--Start Div Box to add Experience--}}
                                        <div id="box_experience">
                                            {{--Start Add Box--}}
                                            {{--End Add Box--}}
                                        </div>
                                        {{--End Div Box--}}
                                    </div>

                                    <div role="tabpanel" class="tab-pane" id="history">
                                        <div class="clearfix" style="padding: 5px auto;"></div>
                                        <div class="form-group">
                                            <textarea rows="5" id="history_note" name="history_note" class="form-control" placeholder=""></textarea>
                                        </div>
                                    </div>

                                    <div role="tabpanel" class="tab-pane" id="education">
                                        <div class="pull-left"><strong>คำเตือน กรุณาเรียงลำดับข้อมูลทำเป็นปัจจุบันที่สุดก่อน</strong></div>
                                        <div class="pull-right" style="margin: 5px auto;"><button type="button" id="addEducationBullet" data-content="education" class="btn btn-xs btn-primary addBoxInput"><i class="fa fa-plus-circle" aria-hidden="true"></i> เพิ่มวุฒิการศึกษา</button></div>
                                        <div class="clearfix"></div>
                                        {{--Start Div Box to add Education--}}
                                        <div id="box_education">
                                            {{--Start Add Box--}}
                                            {{--End Box--}}
                                        </div>
                                        {{--Start Div Box to add Education--}}
                                    </div>

                                    <div role="tabpanel" class="tab-pane" id="training">
                                        <div class="pull-left"><strong>คำเตือน กรุณาเรียงลำดับข้อมูลทำเป็นปัจจุบันที่สุดก่อน</strong></div>
                                        <div class="pull-right" style="margin: 5px auto;"><button type="button" id="addTrainingBullet" data-content="training" class="btn btn-xs btn-primary addBoxInput"><i class="fa fa-plus-circle" aria-hidden="true"></i> เพิ่มข้อมูลการฝึกอบรม</button></div>
                                        <div class="clearfix"></div>
                                        {{--Start Div Box to add Training--}}
                                        <div id="box_training">
                                            {{--Start Add Box--}}
                                            {{--End Add Box--}}
                                        </div>
                                        {{--End Div Box to add Training--}}
                                    </div>

                                    <div role="tabpanel" class="tab-pane" id="language">
                                        <div class="pull-right" style="margin: 5px auto;"><button type="button" id="addLanguageBullet" data-content="languages" class="btn btn-xs btn-primary addBulletInput"><i class="fa fa-plus-circle" aria-hidden="true"></i> เพิ่มภาษา</button></div>
                                        <div class="clearfix"></div>
                                        {{--Start Div Box to add Language--}}
                                        <table id="table_languages" class="table table-condensed">
                                            <thead>
                                            <tr>
                                                <th class="col-md-2">ภาษา</th>
                                                <th class="col-md-2">ฟัง</th>
                                                <th class="col-md-2">พูด</th>
                                                <th class="col-md-2">อ่าน</th>
                                                <th class="col-md-2">เขียน</th>
                                                <th class="col-md-2"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr id="row_languages1">
                                                <td>
                                                    <select class="form-control" name="languages[]">
                                                        <option value="ไทย" selected>ไทย</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control" name="listen_levels[]">
                                                        <option value="ไม่ได้">ไม่ได้</option>
                                                        <option value="พอใช้" selected>พอใช้</option>
                                                        <option value="ดี">ดี</option>
                                                        <option value="เยี่ยม">เยี่ยม</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control" name="speak_levels[]">
                                                        <option value="ไม่ได้">ไม่ได้</option>
                                                        <option value="พอใช้" selected>พอใช้</option>
                                                        <option value="ดี">ดี</option>
                                                        <option value="เยี่ยม">เยี่ยม</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control" name="read_levels[]">
                                                        <option value="ไม่ได้">ไม่ได้</option>
                                                        <option value="พอใช้" selected>พอใช้</option>
                                                        <option value="ดี">ดี</option>
                                                        <option value="เยี่ยม">เยี่ยม</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control" name="write_levels[]">
                                                        <option value="ไม่ได้">ไม่ได้</option>
                                                        <option value="พอใช้" selected>พอใช้</option>
                                                        <option value="ดี">ดี</option>
                                                        <option value="เยี่ยม">เยี่ยม</option>
                                                    </select>
                                                </td>
                                                <td></td>
                                            </tr>
                                            <tr id="row_languages2">
                                                <td>
                                                    <select class="form-control" name="languages[]">
                                                        <option value="English" selected>English</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control" name="listen_levels[]">
                                                        <option value="ไม่ได้">ไม่ได้</option>
                                                        <option value="พอใช้" selected>พอใช้</option>
                                                        <option value="ดี">ดี</option>
                                                        <option value="เยี่ยม">เยี่ยม</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control" name="speak_levels[]">
                                                        <option value="ไม่ได้">ไม่ได้</option>
                                                        <option value="พอใช้" selected>พอใช้</option>
                                                        <option value="ดี">ดี</option>
                                                        <option value="เยี่ยม">เยี่ยม</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control" name="read_levels[]">
                                                        <option value="ไม่ได้">ไม่ได้</option>
                                                        <option value="พอใช้" selected>พอใช้</option>
                                                        <option value="ดี">ดี</option>
                                                        <option value="เยี่ยม">เยี่ยม</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control" name="write_levels[]">
                                                        <option value="ไม่ได้">ไม่ได้</option>
                                                        <option value="พอใช้" selected>พอใช้</option>
                                                        <option value="ดี">ดี</option>
                                                        <option value="เยี่ยม">เยี่ยม</option>
                                                    </select>
                                                </td>
                                                <td></td>
                                            </tr>
                                            {{--Attend by button--}}
                                            </tbody>
                                        </table>
                                    </div>

                                    <div role="tabpanel" class="tab-pane" id="upload_file">
                                        <h5>ไฟล์เอกสารที่เกี่ยวข้อง (CV, Resume, ใบรับรองหรือเอกสารอื่นๆที่เกี่ยวข้องกับตัววิทยากร)
                                            <div class="pull-right"><button type="button" id="addUploadFile" data-content="file" class="btn btn-xs btn-primary addBulletInput"><i class="fa fa-plus" aria-hidden="true"></i> เพิ่มไฟล์</button></div>
                                        </h5>
                                        <table id="table_file" class="table table-condensed">
                                            <thead>
                                            <tr>
                                                <th class="col-md-3">ไฟล์</th>
                                                <th class="col-md-5">คำอธิบาย</th>
                                                <th class="col-md-2">เรียกดู</th>
                                                <th class="col-md-2"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            {{--Attend by ajax--}}
                                            </tbody>
                                        </table>
                                    </div>
                                    {{--End of last content--}}

                                </div>
                            </div>
                            {{--Start Submit Zone--}}
                            <div class="pull-right">
                                <button id="submitForm" type="submit" class="btn btn-lg btn-success" data-loading-text="กำลังบันทึกกรุณารอสักครู่..." autocomplete="off">
                                    <span class="fa fa-save" aria-hidden="true"></span>
                                    บันทึก
                                </button>
                            </div>
                            {{--End Submit Zone--}}
                        </div>
                    </div>

                </div><!--/tab-content-->

            </div><!--/col-9-->
        </div><!--/row-->
    </form>
    <div class="clearfix"></div>
    {{--End Content--}}

    <script type="text/javascript">

        var expert_id = {{ $expert->id }};
        var edus = {{ json_encode($educations) }};
        var language_levels = {{ json_encode($language_levels) }};

        $(function () {

            /*Enable TinyMce Rich Text*/
            tinymce.init({
                selector:'textarea#history_note',
                language: 'th_TH'
            });

            //add bullet control under table
            $('.addBulletInput').on('click', function(e){
                //get content to add bullet
                var content = $(this).data('content');
                //get table object
                var table = $('#table_'+content+' > tbody');

                //get count row of table
                var count_row = table.children().length;
                var new_row = count_row+1;

                //if box number = 0 remove no data before
                $('#table_'+content+' > tbody > tr.haveNoData').empty();

                //create bullet html
                var bullet = '';

                //in case if work in tab lecture
                if (content=='main_subject' || content=='second_subject')
                {
                    bullet += '<tr id="row_'+content+new_row+'">';
                    bullet += '<td class="col-md-10">';
                        bullet += '<textarea class="form-control" id="'+content+new_row+'" name="'+content+'[]"></textarea>';
                    bullet += '</td>';
                    bullet += '<td class="col-md-2">';
                        //new subject id
                        bullet += '<input value="0" type="hidden" name="' + content + '_id[]">';
                        //data-content in delete button assign row to delete use button link
                        bullet += '<a name="btnDeleteSubjectBullet" class="btn btn-xs btn-danger" href="javascript:deleteBullet(\'row_'+content+new_row+'\');" role="button"><i class="fa fa-minus-circle" aria-hidden="true"></i> ลบ</a>';
                    bullet += '</td>';
                    bullet += '</tr>';
                }
                else if (content=='languages')
                {
                    bullet += '<tr id="row_'+content+new_row+'">';
                        bullet +='<td>';
                            bullet += '<input value="0" type="hidden" name="language_id[]">';
                            bullet += '<input type="text" class="form-control" id="languages'+new_row+'" name="languages[]">';
                        bullet +='</td>';
                        bullet +='<td>';
                            bullet +='<select class="form-control" name="listen_levels[]">';
                                $.each(language_levels, function(index, value){
                                    bullet +='<option value="' + value + '">' + value + '</option>';
                                });
                            bullet +='</select>';
                        bullet +='</td>';
                        bullet +='<td>';
                            bullet +='<select class="form-control" name="speak_levels[]">';
                                $.each(language_levels, function(index, value){
                                    bullet +='<option value="' + value + '">' + value + '</option>';
                                });
                            bullet +='</select>';
                        bullet +='</td>';
                        bullet +='<td>';
                            bullet +='<select class="form-control" name="read_levels[]">';
                                $.each(language_levels, function(index, value){
                                    bullet +='<option value="' + value + '">' + value + '</option>';
                                });
                            bullet +='</select>';
                        bullet +='</td>';
                        bullet +='<td>';
                            bullet +='<select class="form-control" name="write_levels[]">';
                                $.each(language_levels, function(index, value){
                                    bullet +='<option value="' + value + '">' + value + '</option>';
                                });
                            bullet +='</select>';
                        bullet +='</td>';
                        bullet += '<td>';
                            bullet += '<a name="btnDeleteLanguageBullet" class="btn btn-xs btn-danger" href="javascript:deleteBullet(\'row_'+content+new_row+'\');" role="button"><i class="fa fa-minus-circle" aria-hidden="true"></i> ลบ</a>';
                        bullet += '</td>';
                    bullet += '</tr>';
                }
                else
                {
                    //content is upload files
                    bullet += '<tr id="row_file'+new_row+'">';
                        bullet += '<td><input value="0" type="hidden" name="file_id[]"><input type="hidden" name="new_file[]" value="1"><input type="file" id="file'+new_row+'" name="file[]" placeholder="ไฟล์เอกสาร"></td>';
                        bullet += '<td><textarea id="file_desc'+new_row+'" name="file_desc[]" class="form-control" placeholder="คำอธิบายเอกสาร"></textarea></td>';
                        bullet += '<td><span class="label label-default">ไม่มีไฟล์</span></td>';
                        bullet += '<td>';
                            bullet += '<a name="btnDeleteFileBullet" class="btn btn-xs btn-danger" href="javascript:deleteBullet(\'row_file'+new_row+'\');" role="button"><i class="fa fa-minus-circle" aria-hidden="true"></i> ลบ</a>';
                        bullet += '</td>';
                    bullet += '</tr>';
                }

                table.append(bullet);
            });

            //add bullet control under box
            $('.addBoxInput').on('click', function(e){
                //get content to add bullet
                var content = $(this).data('content');
                //get box object
                var div = $('#box_'+content);

                //get count box of div
                var count_row = $('#box_'+content+' > .boxData').length;
                var new_row = count_row+1;

                //if box number = 0 empty before
                $('#box_'+content+' > div.haveNoData').remove();

                //create bullet html
                var box = '';

                //in case if work in tab experience
                if (content=='experience')
                {
                    box += '<input value="0" type="hidden" name="exp_id[]">';

                    box +='<div id="experience'+new_row+'" class="boxData panel panel-default">';
                    box +='<div class="panel-heading">ประสบการณ์ทำงาน '+new_row+' ';
                    box +='<div class="pull-right"><a name="btnDeleteExpBullet" class="btn btn-xs btn-danger" href="javascript:deleteBox(\'experience'+new_row+'\');" role="button"><i class="fa fa-minus-circle" aria-hidden="true"></i> ลบ</a></div> </div>';
                    box +='<div class="panel-body">';
                    box +='<div class="row form-group">';
                    box +='<div class="col-md-6">';
                    box +='<label>เริ่มงาน</label>';
                    box +='<div class="input-group">';
                    box +='<select class="form-control" id="exp_start_month'+new_row+'" name="exp_start_month[]" aria-describedby="work_exp_start_addon">';
                    box +='<option value=""></option>';
                        for(m=1;m<=12;m++)
                        {
                            box +='<option value="'+m+'">'+monthThai(m)+'</option>';
                        }
                    box +='</select>';
                    box +='<span class="input-group-addon" id="work_exp_start_addon">/</span>';
                    box +='<select class="form-control" id="exp_start_year'+new_row+'" name="exp_start_year[]" aria-describedby="work_exp_start_addon">';
                    box +='<option value=""></option>';
                        for(y=2470;y<=2562;y++)
                        {
                            box +='<option value="'+y+'">'+y+'</option>';
                        }
                    box +='</select>';
                    box +='</div>';
                    box +='</div>';
                    box +='<div class="col-md-6">';
                    box +='<label>จนถึง</label>';
                    box +='<div class="input-group">';
                    box +='<select class="form-control" id="exp_end_month'+new_row+'" name="exp_end_month[]" aria-describedby="work_exp_end_addon'+new_row+'">';
                    box +='<option value=""></option>';
                        for(m=1;m<=12;m++)
                        {
                            box +='<option value="'+m+'">'+monthThai(m)+'</option>';
                        }
                    box +='</select>';
                    box +='<span class="input-group-addon" id="work_exp_end_addon'+new_row+'">/</span>';
                    box +='<select class="form-control" id="exp_end_year'+new_row+'" name="exp_end_year[]" aria-describedby="work_exp_end_addon'+new_row+'">';
                    box +='<option value=""></option>';
                        for(y=2470;y<=2562;y++)
                        {
                            box +='<option value="'+y+'">'+y+'</option>';
                        }
                    box +='</select>';
                    box +='</div>';
                    if (new_row==1)
                    {
                        box +='<div class="pull-right">';
                        box +='<label class="checkbox-inline">';
                        box +='<input type="checkbox" name="chkIfPresentWork" id="chkIfPresentWork" value="yes"> จนถึงปัจจุบัน';
                        box +='</label>';
                        box +='</div>';
                    }
                    box +='</div>';
                    box +='</div>';
                    box +='<div class="row form-group">';
                    box +='<div class="col-md-6">';
                    box +='<label>บริษัท/องค์กร</label>';
                    box +='<input type="text" id="exp_org'+new_row+'" name="exp_org[]" class="form-control">';
                    box +='</div>';
                    box +='<div class="col-md-6">';
                    box +='<label>ตำแหน่ง</label>';
                    box +='<input type="text" id="exp_position'+new_row+'" name="exp_position[]" class="form-control">';
                    box +='</div>';
                    box +='</div>';
                    box +='<div class="row form-group">';
                    box +='<div class="col-md-12">';
                    box +='<label>คำอธิบายหน้าที่การงาน</label>';
                    box +='<textarea id="exp_work_desc'+new_row+'" name="exp_work_desc[]" class="form-control"></textarea>';
                    box +='</div>';
                    box +='</div>';
                    box +='</div>';
                    box +='</div>';
                }
                else if (content=='education')
                {
                    box += '<input value="0" type="hidden" name="education_id[]">';

                    box +='<div id="education'+new_row+'" class="boxData panel panel-default">';
                    box +='<div class="panel-heading">วุฒิการศึกษา '+new_row+' ';
                    box +='<div class="pull-right"><a name="btnDeleteExpBullet" class="btn btn-xs btn-danger" href="javascript:deleteBox(\'education'+new_row+'\');" role="button"><i class="fa fa-minus-circle" aria-hidden="true"></i> ลบ</a></div> </div>';
                    box +='<div class="panel-body">';
                    box +='<div class="row form-group">';
                    box +='<div class="col-md-6">';
                    box +='<label>ระดับ</label>';
                    box +='<select id="education_level'+new_row+'" name="education_level[]" class="form-control">';
                    box +='<option value=""></option>';
                        $.each(edus, function(index, value){
                            box +='<option value="'+value+'">'+value+'</option>';
                        });
                    box +='</select>';
                    box +='</div>';
                    box +='<div class="col-md-6">';
                    box +='<label>สถานศึกษา</label>';
                    box +='<input type="text" class="form-control" id="education_school'+new_row+'" name="education_school[]">';
                    box +='</div>';
                    box +='</div>';
                    box +='<div class="row form-group">';
                    box +='<div class="col-md-6">';
                    box +='<label>วุฒิการศึกษา</label>';
                    box +='<input type="text" class="form-control" id="education_graduation'+new_row+'" name="education_graduation[]" placeholder="เช่น คุรุศาสตร์บัณฑิต">';
                    box +='</div>';
                    box +='<div class="col-md-6">';
                    box +='<label>วิชาเอก</label>';
                    box +='<input type="text" class="form-control" id="education_major'+new_row+'" name="education_major[]" placeholder="เช่น ภาษาอังกฤษ">';
                    box +='</div>';
                    box +='</div>';
                    box +='<div class="row form-group">';
                    box +='<div class="col-md-3">';
                    box +='<label>GPA</label>';
                    box +='<input type="text" maxlength="4" class="form-control" id="education_gpa'+new_row+'" name="education_gpa[]" placeholder="เช่น 3.00">';
                    box +='</div>';
                    box +='<div class="col-md-3 col-md-offset-3">';
                    box +='<label>ปีที่สำเร็จการศึกษา</label>';
                    box +='<input type="text" maxlength="4" class="form-control" id="education_finish_year'+new_row+'" name="education_finish_year[]" placeholder="ปี พ.ศ.ที่จบ">';
                    box +='</div>';
                    box +='</div>';
                    box +='</div>';
                    box +='</div>';
                }
                else
                {
                    //content is training
                    box += '<input value="0" type="hidden" name="training_id[]">';

                    box +='<div id="training'+new_row+'" class="boxData panel panel-default">';
                    box +='<div class="panel-heading">การฝึกอบรม '+new_row+' ';
                    box +='<div class="pull-right"><a name="btnDeleteTrainingBullet" class="btn btn-xs btn-danger" href="javascript:deleteBox(\'training'+new_row+'\');" role="button"><i class="fa fa-minus-circle" aria-hidden="true"></i> ลบ</a></div> </div>';
                    box +='<div class="panel-body">';
                    box +='<div class="row form-group">';
                    box +='<div class="col-md-6">';
                    box +='<label>เริ่มฝึก</label>';
                        box +='<div class="input-group">';
                        box +='<select class="form-control" id="training_start_day'+new_row+'" name="training_start_day[]" aria-describedby="training_start_addon'+new_row+'">';
                        box +='<option value=""></option>';
                        for(d=1;d<=31;d++)
                        {
                            box +='<option value="'+d+'">'+d+'</option>';
                        }
                        box +='</select>';
                        box +='<span class="input-group-addon" id="training_start_addon'+new_row+'">/</span>';
                        box +='<select class="form-control" id="training_start_month'+new_row+'" name="training_start_month[]" aria-describedby="training_start_addon'+new_row+'">';
                        box +='<option value=""></option>';
                            for(m=1;m<=12;m++)
                            {
                                box +='<option value="'+m+'">'+monthThai(m)+'</option>';
                            }
                        box +='</select>';
                        box +='<span class="input-group-addon" id="training_start_addon'+new_row+'">/</span>';
                        box +='<select class="form-control" id="training_start_year'+new_row+'" name="training_start_year[]" aria-describedby="training_start_addon'+new_row+'">';
                        box +='<option value=""></option>';
                            for(y=2500;y<=2562;y++)
                            {
                                box +='<option value="'+y+'">'+y+'</option>';
                            }
                        box +='</select>';
                        box +='</div>';
                    box +='</div>';
                    box +='<div class="col-md-6">';
                    box +='<label>สิ้นสุด</label>';
                        box +='<div class="input-group">';
                        box +='<select class="form-control" id="training_end_day'+new_row+'" name="training_end_day[]" aria-describedby="training_end_addon'+new_row+'">';
                        box +='<option value=""></option>';
                            for(d=1;d<=31;d++)
                            {
                                box +='<option value="'+d+'">'+d+'</option>';
                            }
                        box +='</select>';
                        box +='<span class="input-group-addon" id="training_end_addon'+new_row+'">/</span>';
                        box +='<select class="form-control" id="training_end_month'+new_row+'" name="training_end_month[]" aria-describedby="training_end_addon'+new_row+'">';
                        box +='<option value=""></option>';
                            for(m=1;m<=12;m++)
                            {
                                box +='<option value="'+m+'">'+monthThai(m)+'</option>';
                            }
                        box +='</select>';
                        box +='<span class="input-group-addon" id="training_end_addon'+new_row+'">/</span>';
                        box +='<select class="form-control" id="training_end_year'+new_row+'" name="training_end_year[]" aria-describedby="training_end_addon'+new_row+'">';
                        box +='<option value=""></option>';
                            for(y=2500;y<=2562;y++)
                            {
                                box +='<option value="'+y+'">'+y+'</option>';
                            }
                        box +='</select>';
                        box +='</div>';
                    box +='</div>';
                    box +='</div>';
                    box +='<div class="row form-group">';
                    box +='<div class="col-md-6">';
                    box +='<label>ช่วงเวลาที่ฝึกอบรม</label>';
                    box +='<input id="training_time'+new_row+'" name="training_time[]" type="text" class="form-control" placeholder="ระบุในกรณีไม่ทราบวันที่แน่ชัด">';
                    box +='</div>';
                    box +='<div class="col-md-6">';
                    box +='<label>องค์กรหรือหน่วยงานที่ให้การฝึกอบรม</label>';
                    box +='<input id="training_org'+new_row+'" name="training_org[]" type="text" class="form-control">';
                    box +='</div>';
                    box +='</div>';
                    box +='<div class="row form-group">';
                    box +='<div class="col-md-12">';
                    box +='<label>ชื่อหลักสูตรที่อบรม</label>';
                    box +='<input id="training_course'+new_row+'" name="training_course[]" type="text" class="form-control">';
                    box +='</div>';
                    box +='</div>';
                    box +='<div class="row form-group">';
                    box +='<div class="col-md-12">';
                    box +='<label>รายละเอียดการอบรม</label>';
                    box +='<textarea id="training_desc'+new_row+'" name="training_desc[]" class="form-control"></textarea>';
                    box +='</div>';
                    box +='</div>';
                    box +='</div>';
                    box +='</div>';
                }

                div.append(box);
            });

            /*Form Submit*/
            $("#formPersona").validate({
                highlight: function(element) {
                    $(element).closest('.form-group').addClass('has-error');
                },
                unhighlight: function(element) {
                    $(element).closest('.form-group').removeClass('has-error');
                },
                errorElement: 'span',
                errorClass: 'help-block',
                errorPlacement: function(error, element) {
                    if(element.parent('.input-group').length) {
                        error.insertAfter(element.parent());
                    } else {
                        error.insertAfter(element);
                    }
                },
                submitHandler: function(form) {

                    $('#submitForm').button('loading');

                    var options = {
                        type : 'POST',
                        url: "{{ URL::action('ExpertController@postPersona') }}",
                        dataType:  'json',
                        data: {
                            'history' : tinyMCE.get('history_note').getContent()
                        },
                        beforeSubmit:  function(){
                            $('#submitForm').prop('disabled', true);
                            return true;
                        },  // pre-submit callback
                        success: function(data){
                            $('#submitForm').button('reset');

                            $('#submitForm').prop('disabled', false);
                            if (data.status==='success')
                            {
                                successAlert('ทำรายการสำเร็จ !', data.msg);
                                loadPersona(data.id);
                            }
                            else
                            {
                                errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                            }
                        }  // post-submit callback
                    };

                    $('#formPersona').ajaxSubmit(options);

                }
            });

        });

        //when dom ready
        $(document).ready(function(){
            loadPersona(expert_id);

            // store the currently selected tab in the hash value
            $("ul.nav-tabs > li > a").on("shown.bs.tab", function(e) {
                var id = $(e.target).attr("href").substr(1);
                window.location.hash = id;
            });

            // on load of the page: switch to the currently selected tab
            var hash = window.location.hash;
            $('#tabPersonnelExtendData a[href="' + hash + '"]').tab('show');
        });

        //function delete bullet
        function deleteBullet(control)
        {
            $('#'+control).empty();
        }
        //function delete box input
        function deleteBox(control)
        {
            $('#'+control).remove();

        }

        //function to ajax delete bullet in database
        function deleteInDatabase(element, control, data, id)
        {
            $.ajax({
                type: "POST",
                url: "{{ URL::action('ExpertController@postDeleteExpertBullet') }}",
                data :
                {
                    '_token' : $('input[name=_token]').val(),
                    'id' : id,
                    'data' : data,
                    'personnel_id' : expert_id
                }
            }).done(function(data) {
                if (data.status==='success')
                {
                    if (element=='bullet')
                    {
                        $('#'+control).empty();
                    }
                    else
                    {
                        $('#'+control).remove();
                    }
                    loadPersona(data.id);
                }
                else
                {
                    errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                }
            });
        }

        //function delete file also delete in path
        function deleteFile(file, id)
        {
            $('#'+file).remove();
            //also delete real file
            $.ajax({
                type: "POST",
                url: "{{ URL::action('ExpertController@postDeleteExpertFile') }}",
                data :
                {
                    '_token' : $('input[name=_token]').val(),
                    'id' : id
                }
            }).done(function(data) {
                if (data.status==='success')
                {
                    loadPersona(data.id);
                }
                else
                {
                    errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                }
            });
        }

        //function loading extend data
        function loadPersona(id)
        {
            //set busy load indicator
            $('#personnelExtendData').hide();
            $('#loadingPersonnelExtendData').empty().append('<div class="alert alert-warning" role="alert"><i class="fa fa-spinner fa-spin fa-2x"></i> กำลังประมวลผลข้อมูลกรุณารอสักครู่</div>');
            //send data by ajax to calculate
            $.ajax({
                url: "{{ URL::action('ExpertController@getPersona') }}",
                data: {
                    '_token' : $("input[name=_token]").val(),
                    'id' : id
                },
                dataType: 'json',
                success : function(data){
                    $('#loadingPersonnelExtendData').empty();
                    if (data.status=='success')
                    {
                        $('#personnelExtendData').show();

                        var data = data.data;
                        //set total summary
                        setPersona(data);
                    }
                    else
                    {
                        $('#loadingPersonnelExtendData').append('<div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-triangle fa-2x"></i> ' + data.msg + '</div>');
                    }
                }
            });
        }

        //function set person html
        function setPersona(expert)
        {
            //Add Lecture
            if (expert.history)
            {
                tinyMCE.get('history_note').setContent(expert.history);
            }

            if (expert.lectures.length>0) {

                var count_main_subject = 0;
                var count_second_subject = 0;
                $('#table_main_subject > tbody').empty();
                $('#table_second_subject > tbody').empty();

                $.each(expert.lectures, function( index, subject ) {
                    var html = '';

                    html += (subject.type=='main') ? '<tr id="row_main_subject'+count_main_subject+'">' : '<tr id="row_second_subject'+count_second_subject+'">';
                    html += '<td class="col-md-10">';

                    if (subject.type=='main')
                    {
                        html += '<textarea class="form-control" id="main_subject'+count_main_subject+'" name="main_subject[]">'+subject.subject+'</textarea>';
                        html += '<input value="'+subject.id+'" type="hidden" name="main_subject_id[]">';
                    }
                    else
                    {
                        html += '<textarea class="form-control" id="second_subject'+count_second_subject+'" name="second_subject[]">'+subject.subject+'</textarea>';
                        html += '<input value="'+subject.id+'" type="hidden" name="second_subject_id[]">';
                    }

                    html += '</td>';
                    html += '<td class="col-md-2">';
                    if (subject.type=='main')
                    {
                        html += '<a name="btnDeleteSubjectBullet" class="btn btn-xs btn-danger" href="javascript:deleteInDatabase(\'bullet\',\'row_main_subject'+count_main_subject+'\',\'subjects\','+subject.id+');" role="button"><i class="fa fa-minus-circle" aria-hidden="true"></i> ลบ</a>';
                    }
                    else
                    {
                        html += '<a name="btnDeleteSubjectBullet" class="btn btn-xs btn-danger" href="javascript:deleteInDatabase(\'bullet\',\'row_second_subject'+count_second_subject+'\',\'subjects\','+subject.id+');" role="button"><i class="fa fa-minus-circle" aria-hidden="true"></i> ลบ</a>';
                    }
                    html += '</td>';
                    html += '</tr>';

                    if (subject.type=='main')
                    {
                        $('#table_main_subject > tbody').append(html);
                        count_main_subject++;
                    }
                    else
                    {
                        $('#table_second_subject > tbody').append(html);
                        count_second_subject++;
                    }

                });
                //if not have main subject then show alert
                if (count_main_subject==0)
                {
                    $('#table_main_subject > tbody').empty().append('<tr class="haveNoData"><td colspan="2"><center>ไม่มีข้อมูล</center></td></tr>');
                }
                //if not have second subject then show alert
                if (count_second_subject==0)
                {
                    $('#table_second_subject > tbody').empty().append('<tr class="haveNoData"><td colspan="2"><center>ไม่มีข้อมูล</center></td></tr>');
                }
            }else{
                $('#table_main_subject > tbody').empty().append('<tr class="haveNoData"><td colspan="2"><center>ไม่มีข้อมูล</center></td></tr>');
                $('#table_second_subject > tbody').empty().append('<tr class="haveNoData"><td colspan="2"><center>ไม่มีข้อมูล</center></td></tr>');
            }
            //Add Languages
            if (expert.languages.length>0) {
                var html = '';
                var row = 1;

                $.each(expert.languages, function( index, language ) {
                    var lock_language = (language.lang=='ไทย' || language.lang=='English') ? '' : '';
                    html += '<tr id="row_languages'+row+'">';
                    html +='<td>';
                    html += '<input value="'+language.id+'" type="hidden" name="language_id[]">';
                    html += '<input '+lock_language+' value="'+language.lang+'" type="text" class="form-control" id="languages'+row+'" name="languages[]">';
                    html +='</td>';
                    html +='<td>';
                    html +='<select class="form-control" name="listen_levels[]">';
                        $.each(language_levels, function(index, value){
                            html += (language.listen_level==value) ? '<option value="'+value+'" selected>'+value+'</option>' : '<option value="'+value+'">'+value+'</option>';
                        });
                    html +='</select>';
                    html +='</td>';
                    html +='<td>';
                    html +='<select class="form-control" name="speak_levels[]">';
                        $.each(language_levels, function(index, value){
                            html += (language.speak_level==value) ? '<option value="'+value+'" selected>'+value+'</option>' : '<option value="'+value+'">'+value+'</option>';
                        });
                    html +='</select>';
                    html +='</td>';
                    html +='<td>';
                    html +='<select class="form-control" name="read_levels[]">';
                        $.each(language_levels, function(index, value){
                            html += (language.read_level==value) ? '<option value="'+value+'" selected>'+value+'</option>' : '<option value="'+value+'">'+value+'</option>';
                        });
                    html +='</select>';
                    html +='</td>';
                    html +='<td>';
                    html +='<select class="form-control" name="write_levels[]">';
                        $.each(language_levels, function(index, value){
                            html += (language.write_level==value) ? '<option value="'+value+'" selected>'+value+'</option>' : '<option value="'+value+'">'+value+'</option>';
                        });
                    html +='</select>';
                    html +='</td>';
                    html += '<td>';

                    html += '<a name="btnDeleteLanguageBullet" class="btn btn-xs btn-danger" href="javascript:deleteInDatabase(\'bullet\',\'row_languages'+row+'\',\'languages\','+language.id+');" role="button"><i class="fa fa-minus-circle" aria-hidden="true"></i> ลบ</a>';

                    html += '</td>';
                    html += '</tr>';

                    row++;
                });

                $('#table_languages > tbody').empty().append(html);
            }
            //Add Educations
            if (expert.educations.length>0) {
                var html = '';
                var row = 1;

                $('#box_education').empty();

                $.each(expert.educations, function( index, value ) {

                    html += '<input value="'+value.id+'" type="hidden" name="education_id[]">';

                    html +='<div id="education'+row+'" class="boxData panel panel-default">';
                    html +='<div class="panel-heading">วุฒิการศึกษา '+row+' ';
                    html +='<div class="pull-right"><a name="btnDeleteExpBullet" class="btn btn-xs btn-danger" href="javascript:deleteInDatabase(\'box\',\'educations'+row+'\',\'educations\','+value.id+');" role="button"><i class="fa fa-minus-circle" aria-hidden="true"></i> ลบ</a></div> </div>';
                    html +='<div class="panel-body">';
                    html +='<div class="row form-group">';
                    html +='<div class="col-md-6">';
                    html +='<label>ระดับ</label>';
                    html +='<select id="education_level'+row+'" name="education_level[]" class="form-control">';
                    html +='<option value="'+value.level+'" selected>'+value.level+'</option>';
                        $.each(edus, function(index, value){
                            html += '<option value="'+value+'">'+value+'</option>';
                        });
                    html +='</select>';
                    html +='</div>';
                    html +='<div class="col-md-6">';
                    html +='<label>สถานศึกษา</label>';
                    html +='<input value="'+value.school_name+'" type="text" class="form-control" id="education_school'+row+'" name="education_school[]">';
                    html +='</div>';
                    html +='</div>';
                    html +='<div class="row form-group">';
                    html +='<div class="col-md-6">';
                    html +='<label>วุฒิการศึกษา</label>';
                    html +='<input value="'+value.graduation+'" type="text" class="form-control" id="education_graduation'+row+'" name="education_graduation[]" placeholder="เช่น คุรุศาสตร์บัณฑิต">';
                    html +='</div>';
                    html +='<div class="col-md-6">';
                    html +='<label>วิชาเอก</label>';
                    html +='<input value="'+value.major+'" type="text" class="form-control" id="education_major'+row+'" name="education_major[]" placeholder="เช่น ภาษาอังกฤษ">';
                    html +='</div>';
                    html +='</div>';
                    html +='<div class="row form-group">';
                    html +='<div class="col-md-3">';
                    html +='<label>GPA</label>';
                    html +='<input value="'+value.gpa+'" type="text" maxlength="4" class="form-control" id="education_gpa'+row+'" name="education_gpa[]" placeholder="เช่น 3.00">';
                    html +='</div>';
                    html +='<div class="col-md-3 col-md-offset-3">';
                    html +='<label>ปีที่สำเร็จการศึกษา</label>';
                    html +='<input value="'+value.finish_year+'" type="text" maxlength="4" class="form-control" id="education_finish_year'+row+'" name="education_finish_year[]" placeholder="ปี พ.ศ.ที่จบ">';
                    html +='</div>';
                    html +='</div>';
                    html +='</div>';
                    html +='</div>';

                    row++;
                });

                $('#box_education').append(html);

            }else{
                $('#box_education').empty().append('<div class="haveNoData alert alert-info" role="alert">ไม่มีข้อมูล กรุณาเพิ่มข้อมูล</div>');
            }
            //Add Training
            if (expert.training_sessions.length>0) {
                var html = '';
                var row = 1;

                $('#box_training').empty();

                $.each(expert.training_sessions, function( index, value ) {

                    html += '<input value="'+value.id+'" type="hidden" name="training_id[]">';

                    html +='<div id="training'+row+'" class="boxData panel panel-default">';
                    html +='<div class="panel-heading">การฝึกอบรม '+row+' ';
                    html +='<div class="pull-right"><a name="btnDeleteTrainingBullet" class="btn btn-xs btn-danger" href="javascript:deleteInDatabase(\'box\',\'training'+row+'\',\'trainings\','+value.id+');" role="button"><i class="fa fa-minus-circle" aria-hidden="true"></i> ลบ</a></div> </div>';
                    html +='<div class="panel-body">';
                    html +='<div class="row form-group">';
                    html +='<div class="col-md-6">';
                    html +='<label>เริ่มฝึก</label>';
                    html +='<div class="input-group">';
                    html +='<select class="form-control" id="training_start_day'+row+'" name="training_start_day[]" aria-describedby="training_start_addon'+row+'">';
                    html +='<option value=""></option>';
                    for(d=1;d<=31;d++)
                    {
                        html += (value.start_day==d) ? '<option value="'+d+'" selected>'+d+'</option>' : '<option value="'+d+'">'+d+'</option>';
                    }
                    html +='</select>';
                    html +='<span class="input-group-addon" id="training_start_addon'+row+'">/</span>';
                    html +='<select class="form-control" id="training_start_month'+row+'" name="training_start_month[]" aria-describedby="training_start_addon'+row+'">';
                    html +='<option value=""></option>';
                    for(m=1;m<=12;m++)
                    {
                        html += (value.start_month==m) ? '<option value="'+m+'" selected>'+monthThai(m)+'</option>' : '<option value="'+m+'">'+monthThai(m)+'</option>';
                    }
                    html +='</select>';
                    html +='<span class="input-group-addon" id="training_start_addon'+row+'">/</span>';
                    html +='<select class="form-control" id="training_start_year'+row+'" name="training_start_year[]" aria-describedby="training_start_addon'+row+'">';
                    html +='<option value=""></option>';
                    for(y=2500;y<=2562;y++)
                    {
                        html += (value.start_year==y) ? '<option value="'+y+'" selected>'+y+'</option>' : '<option value="'+y+'">'+y+'</option>';
                    }
                    html +='</select>';
                    html +='</div>';
                    html +='</div>';
                    html +='<div class="col-md-6">';
                    html +='<label>สิ้นสุด</label>';
                    html +='<div class="input-group">';
                    html +='<select class="form-control" id="training_end_day'+row+'" name="training_end_day[]" aria-describedby="training_end_addon'+row+'">';
                    html +='<option value=""></option>';
                    for(d=1;d<=31;d++)
                    {
                        html += (value.end_day==d) ? '<option value="'+d+'" selected>'+d+'</option>' : '<option value="'+d+'">'+d+'</option>';
                    }
                    html +='</select>';
                    html +='<span class="input-group-addon" id="training_end_addon'+row+'">/</span>';
                    html +='<select class="form-control" id="training_end_month'+row+'" name="training_end_month[]" aria-describedby="training_end_addon'+row+'">';
                    html +='<option value=""></option>';
                    for(m=1;m<=12;m++)
                    {
                        html += (value.end_month==m) ? '<option value="'+m+'" selected>'+monthThai(m)+'</option>' : '<option value="'+m+'">'+monthThai(m)+'</option>';
                    }
                    html +='</select>';
                    html +='<span class="input-group-addon" id="training_end_addon'+row+'">/</span>';
                    html +='<select class="form-control" id="training_end_year'+row+'" name="training_end_year[]" aria-describedby="training_end_addon'+row+'">';
                    html +='<option value=""></option>';
                    for(y=2500;y<=2562;y++)
                    {
                        html += (value.end_year==y) ? '<option value="'+y+'" selected>'+y+'</option>' : '<option value="'+y+'">'+y+'</option>';
                    }
                    html +='</select>';
                    html +='</div>';
                    html +='</div>';
                    html +='</div>';
                    html +='<div class="row form-group">';
                    html +='<div class="col-md-6">';
                    html +='<label>ช่วงเวลาที่ฝึกอบรม</label>';
                    html +='<input value="'+value.session_period+'" id="training_time'+row+'" name="training_time[]" type="text" class="form-control" placeholder="ระบุในกรณีไม่ทราบวันที่แน่ชัด">';
                    html +='</div>';
                    html +='<div class="col-md-6">';
                    html +='<label>องค์กรหรือหน่วยงานที่ให้การฝึกอบรม</label>';
                    html +='<input value="'+value.organization+'" id="training_org'+row+'" name="training_org[]" type="text" class="form-control">';
                    html +='</div>';
                    html +='</div>';
                    html +='<div class="row form-group">';
                    html +='<div class="col-md-12">';
                    html +='<label>ชื่อหลักสูตรที่อบรม</label>';
                    html +='<input value="'+value.session_name+'" id="training_course'+row+'" name="training_course[]" type="text" class="form-control">';
                    html +='</div>';
                    html +='</div>';
                    html +='<div class="row form-group">';
                    html +='<div class="col-md-12">';
                    html +='<label>รายละเอียดการอบรม</label>';
                    html +='<textarea id="training_desc'+row+'" name="training_desc[]" class="form-control">'+value.session_detail+'</textarea>';
                    html +='</div>';
                    html +='</div>';
                    html +='</div>';
                    html +='</div>';

                    row++;
                });

                $('#box_training').append(html);

            }else{
                $('#box_training').empty().append('<div class="haveNoData alert alert-info" role="alert">ไม่มีข้อมูล กรุณาเพิ่มข้อมูล</div>');
            }
            //Add Experience
            if (expert.work_experiences.length>0) {
                var html = '';
                var row = 1;

                $('#box_experience').empty();

                $.each(expert.work_experiences, function( index, value ) {

                    html += '<input value="'+value.id+'" type="hidden" name="exp_id[]">';

                    html +='<div id="experience'+row+'" class="boxData panel panel-default">';
                    html +='<div class="panel-heading">ประสบการณ์ทำงาน '+row+' ';
                    html +='<div class="pull-right"><a name="btnDeleteExpBullet" class="btn btn-xs btn-danger" href="javascript:deleteInDatabase(\'box\',\'experience'+row+'\',\'experiences\','+value.id+');" role="button"><i class="fa fa-minus-circle" aria-hidden="true"></i> ลบ</a></div> </div>';
                    html +='<div class="panel-body">';
                    html +='<div class="row form-group">';
                    html +='<div class="col-md-6">';
                    html +='<label>เริ่มงาน</label>';
                    html +='<div class="input-group">';
                    html +='<select class="form-control" id="exp_start_month'+row+'" name="exp_start_month[]" aria-describedby="work_exp_start_addon">';
                    html +='<option value=""></option>';
                    for(m=1;m<=12;m++)
                    {
                        html += (value.start_month==m) ? '<option value="'+m+'" selected>'+monthThai(m)+'</option>' : '<option value="'+m+'">'+monthThai(m)+'</option>';
                    }
                    html +='</select>';
                    html +='<span class="input-group-addon" id="work_exp_start_addon">/</span>';
                    html +='<select class="form-control" id="exp_start_year'+row+'" name="exp_start_year[]" aria-describedby="work_exp_start_addon">';
                    html +='<option value=""></option>';
                    for(y=2470;y<=2562;y++)
                    {
                        html += (value.start_year==y) ? '<option value="'+y+'" selected>'+y+'</option>' : '<option value="'+y+'">'+y+'</option>';
                    }
                    html +='</select>';
                    html +='</div>';
                    html +='</div>';
                    html +='<div class="col-md-6">';
                    html +='<label>จนถึง</label>';
                    html +='<div class="input-group">';
                    html +='<select class="form-control" id="exp_end_month'+row+'" name="exp_end_month[]" aria-describedby="work_exp_end_addon'+row+'">';
                    html +='<option value=""></option>';
                    for(m=1;m<=12;m++)
                    {
                        html += (value.end_month==m) ? '<option value="'+m+'" selected>'+monthThai(m)+'</option>' : '<option value="'+m+'">'+monthThai(m)+'</option>';
                    }
                    html +='</select>';
                    html +='<span class="input-group-addon" id="work_exp_end_addon'+row+'">/</span>';
                    html +='<select class="form-control" id="exp_end_year'+row+'" name="exp_end_year[]" aria-describedby="work_exp_end_addon'+row+'">';
                    html +='<option value=""></option>';
                    for(y=2470;y<=2562;y++)
                    {
                        html += (value.end_year==y) ? '<option value="'+y+'" selected>'+y+'</option>' : '<option value="'+y+'">'+y+'</option>';
                    }
                    html +='</select>';
                    html +='</div>';

                    //case present
                        if (row==1)
                        {
                            var still_work = (value.still_working) ? 'checked' : '';
                            html +='<div class="pull-right">';
                            html +='<label class="checkbox-inline">';
                            html +='<input type="checkbox" name="chkIfPresentWork" id="chkIfPresentWork" value="yes" '+still_work+'> จนถึงปัจจุบัน';
                            html +='</label>';
                            html +='</div>';
                        }

                    html +='</div>';
                    html +='</div>';
                    html +='<div class="row form-group">';
                    html +='<div class="col-md-6">';
                    html +='<label>บริษัท/องค์กร</label>';
                    html +='<input value="'+value.company+'" type="text" id="exp_org'+row+'" name="exp_org[]" class="form-control">';
                    html +='</div>';
                    html +='<div class="col-md-6">';
                    html +='<label>ตำแหน่ง</label>';
                    html +='<input value="'+value.position+'" type="text" id="exp_position'+row+'" name="exp_position[]" class="form-control">';
                    html +='</div>';
                    html +='</div>';
                    html +='<div class="row form-group">';
                    html +='<div class="col-md-12">';
                    html +='<label>คำอธิบายหน้าที่การงาน</label>';
                    html +='<textarea id="exp_work_desc'+row+'" name="exp_work_desc[]" class="form-control">'+value.job_description+'</textarea>';
                    html +='</div>';
                    html +='</div>';
                    html +='</div>';
                    html +='</div>';

                    row++;
                });

                $('#box_experience').append(html);

            }else{
                $('#box_experience').empty().append('<div class="haveNoData alert alert-info" role="alert">ไม่มีข้อมูล กรุณาเพิ่มข้อมูล</div>');
            }
            //Add Experience
            if (expert.files.length>0) {

                var html = '';
                var row = 1;

                $.each(expert.files, function( index, value ) {
                    //content is upload files
                    html += '<tr id="row_file'+row+'">';
                    html += '<td><input value="'+value.id+'" type="hidden" name="file_id[]"><input type="hidden" name="new_file[]" value="0">'+value.name+'</td>';
                    html += '<td>'+value.description+'</td>';
                    html += '<td><a href="'+value.url+'" target="_blank">คลิกเพื่อดูไฟล์</a></td>';
                    html += '<td>';
                    html += '<a name="btnDeleteFileSingle" class="btn btn-xs btn-danger" href="javascript:deleteFile(\'row_file'+row+'\','+value.id+');" role="button"><i class="fa fa-minus-circle" aria-hidden="true"></i> ลบ</a>';
                    html += '</td>';
                    html += '</tr>';

                    row++;
                });

                $('#table_file > tbody').empty().append(html);
            }else{
                $('#table_file > tbody').empty().append('<tr class="haveNoData"><td colspan="4"><center>ท่านยังไม่ได้ทำการอัพโหลดไฟล์</center></td></tr>');
            }
        }

    </script>

@stop