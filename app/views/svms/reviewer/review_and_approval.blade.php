{{--หน้า Reviewer manage review and approval--}}
@extends('svms.layouts.default')

@section('title')
    Living University Management System :: :: ตรวจสอบคำร้องและอนุมัติ "{{ $party->name }}"
@stop

@section('extraScripts')
    {{--Use Bootstrap Select2--}}
    {{-- HTML::script('dependencies/select2-4.0.0-beta.3/dist/js/select2.min.js') --}}
    {{-- HTML::style('dependencies/select2-4.0.0-beta.3/dist/css/select2.min.css') --}}
    {{--Use Bootstrap Input--}}
    {{ HTML::script('dependencies/bootstrap-select-1.12.1/dist/js/bootstrap-select.min.js') }}
    {{ HTML::style('dependencies/bootstrap-select-1.12.1/dist/css/bootstrap-select.min.css') }}
    {{--Use Select 2 Theme--}}
    {{ HTML::style('https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.6/select2-bootstrap.min.css') }}
    {{--Use Bootstrap TinyMCE--}}
    {{ HTML::script('dependencies/tinymce/js/tinymce/tinymce.min.js') }}
@stop

@section('extraStyles')
    <style type="text/css">
        .read-box{
            width: 300px !important;
            word-wrap: break-word !important;
            white-space:normal;
        }
    </style>
@stop

@section('header')
    <span class="fa fa-check-square-o"></span>
    ตรวจสอบและอนุมัติคำร้อง
@stop

@section('content')

    {{--Check Count Reviewing--}}
    @if($countReview==0)

        {{--If reviewed all return page complete--}}
        <div class="panel panel-default">
            <div class="panel-body">

                <div class="pull-left"><a class="btn btn-default btn-xs" href="{{ URL::to('/') }}" role="button"><i class="fa fa-home"></i> Dashboard</a></div>

                <div class="clearfix"></div>
                <br/>

                <div id="completeProcess">
                    <div class="alert alert-success" role="alert">
                        <h3 id="completeProcess-title">ทำรายการอนุมัติไปทั้งหมดแล้ว !</h3>
                        <p id="completeProcess-body">
                            <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                            กรุณารอผล E-mail แจ้งเตือนคำร้องขอคณะในคราวถัดไป
                        </p>
                    </div>
                </div>
            </div>
        </div>

    @else

        {{--Combo find คณะยังไม่ได้รีวิว--}}
        <div class="form-group row">
            <div class="col-sm-12">
                <select id="comboViewReviewingParty" name="view_reviewing_party" class="selectpicker show-tick form-control" data-live-search="true" data-width="100%" data-mobile="true">
                    @foreach($reviewings as $reviewing)
                        <option {{ ($reviewing->id==$party->id) ? 'selected' : '' }} value="{{ URL::to('reviewer/'.$reviewing->id.'/review') }}">{{  $reviewing->request_code." ".$reviewing->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($canReview)

            {{-- Form การรีวิว --}}
            <form role="form" method="post" id="formReview">

                <!-- CSRF Token -->
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <!-- ./ csrf token -->

                <input type="hidden" name="_party_id" value="{{ $party->id }}" />

                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <h4>{{ $party->request_code }} {{ $party->name }}</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                @if($party->is_local==1)
                                    <img src="{{ asset('assets/img/flags/th.png') }}" class="img-flag" /></i> คณะในประเทศไทย
                                @else
                                    เป็นคณะมาจาก
                                    @foreach($party->nationals as $country)
                                        <img src="{{ asset('assets/img/flags/'.$country['id'].'.png') }}" class="img-flag" /> {{ $country['name'] }}
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        @if($party->numberOfEditing()>0)
                            <p class="text-danger">หมายเหตุ : ยื่นคำร้องอีกเป็นครั้งที่ {{ (($party->numberOfEditing())+1) }}</p>
                        @endif

                        <hr/>
                        <div class="row">
                            <div class="col-sm-12">
                                <strong>ประเภท</strong> {{ $party->party_type }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <strong>จำนวนผู้เข้าร่วม</strong> {{ $party->people_quantity }} <strong>ท่าน</strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <strong>วันที่มาศึกษาดูงาน</strong> {{ ScheduleController::dateRangeStr($party->start_date, $party->end_date, true) }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <strong>วัตถุประสงค์ที่มาดูงาน</strong>
                                <ul>
                                    @foreach($party['objectives'] as $objective)
                                        <li>{{ $objective->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        {{--Start Initial Hide for Easy UI--}}
                        @if($party->interested || $party->interested!=NULL)
                            <div class="row moreSee" style="display: none;">
                                <div class="col-sm-12">
                                    <strong>ประเด็นที่สนใจ</strong>
                                    <br/>
                                    {{ $party->interested }}
                                </div>
                            </div>
                        @endif

                        @if($party->expected || $party->expected!=NULL)
                            <div class="row moreSee" style="display: none;">
                                <div class="col-sm-12">
                                    <strong>ความคาดหวัง</strong>
                                    <br/>
                                    {{ $party->expected }}
                                </div>
                            </div>
                        @endif

                        @if(count($party->location_bases)>0)
                            <div class="row moreSee" style="display: none;">
                                <div class="col-sm-12">
                                    <strong>พื้นที่ดูงาน</strong>
                                    <ul>
                                        @foreach($party->location_bases as $location)
                                            <li>{{ $location['mflf_area_name'] }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        @if($party->joined)
                            <div class="row moreSee" style="display: none;">
                                <div class="col-sm-12">
                                    <strong>มาศึกษาดูงาน</strong>
                                    @if($party->joined=='never')
                                        <span>ไม่เคย</span>
                                    @elseif($party->joined=='ever')
                                        <span>เคย</span>
                                    @else
                                        <span>ไม่แน่ใจ</span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($party->objective_detail)
                            <div class="row moreSee" style="display: none;">
                                <div class="col-sm-12">
                                    <strong>รายละเอียดเพิ่มเติม</strong>
                                    <p class="read-box">
                                        {{ $party->objective_detail }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($party['coordinators'])
                            <div class="row moreSee" style="display: none;">
                                <div class="col-sm-12">
                                    <strong>ผู้ประสานงานคณะที่มา</strong>
                                    <ul>
                                        @foreach($party['coordinators'] as $coordinator)
                                            <li>
                                                {{ $coordinator['name'] }}
                                                ,มือถือ : {{ $coordinator['mobile'] }}
                                                ,E-mail : {{ $coordinator['email'] }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        @if($party->accommodation_detail || $party->accommodation_detail!=NULL)
                            <div class="row moreSee" style="display: none;">
                                <div class="col-sm-12">
                                    <strong>การจองห้องพัก</strong>
                                    <br/>
                                    {{ $party->accommodation_detail }}
                                </div>
                            </div>
                        @endif

                        @if($party->request_for_lu_personnel)
                            <div class="row moreSee" style="display: none;">
                                <div class="col-sm-12">
                                    <strong>ต้องการการสนับสนุนจาก LU</strong>
                                    <br/>
                                    {{ $party->request_lu_personnel_reason }}
                                </div>
                            </div>
                        @else
                            <div class="row moreSee" style="display: none;">
                                <div class="col-sm-12">
                                    <strong>ต้องการการสนับสนุนจาก LU</strong> ไม่ต้องการ
                                </div>
                            </div>
                        @endif

                        @if($party->paid_method)
                            <div class="row moreSee" style="display: none;">
                                <div class="col-sm-12">
                                    <strong>การจ่ายเงิน</strong> {{ FinancialController::model($party->paid_method, $party->related_budget_code) }}
                                </div>
                            </div>
                        @endif

                        @if($party->request_person_name)
                            <div class="row moreSee" style="display: none;">
                                <div class="col-sm-12">
                                    <strong>ผู้กรอกข้อมูล</strong> {{ $party->request_person_name }} <br/> <strong>กรอกเมื่อ</strong> {{ ScheduleController::dateRangeStr($party->created_at, $party->created_at, true, true) }}
                                </div>
                            </div>
                        @endif

                        {{--End Initial Hide for Easy UI--}}
                        <br/>
                        <div class="row form-group">
                            <div class="col-sm-12">
                                <div class="pull-right">

                                    @if($party->fileUrl())
                                        <a id="btnRequestFile" class="btn btn-xs btn-default" href="{{ $party->fileUrl() }}" target="_blank" role="button"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> ไฟล์ต้นเรื่อง</a> |
                                    @endif

                                    @if($party->fileUrl('travel01'))
                                        <a id="btnTravel01File" class="btn btn-xs btn-default" href="{{ $party->fileUrl('travel01') }}" target="_blank" role="button"><span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span> ไฟล์ศทบ.01</a> |
                                    @endif

                                    <button type="button" id="btnMoreInfo" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> ดูข้อมูลเพิ่มเติม</button>
                                    <button type="button" id="btnHideInfo" class="btn btn-xs btn-default" style="display: none;"><span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span> ซ่อนข้อมูล</button>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>

                        {{--when party have return to edit show it--}}
                        @if (count($party->editNoteHistories())>0)
                            <div class="row form-group">
                                <div class="col-sm-12">
                                    <h5><strong>ประวัติการโต้ตอบ</strong></h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <tbody>
                                                @foreach($party->editNoteHistories() as $history)
                                                    <tr>
                                                        <td class="col-sm-12">
                                                            {{ ($history->status=='editing') ? 'จากผู้ตรวจสอบและอนุมัติ :' : 'จากผู้ยื่นคำร้อง :' }}
                                                            <br/>
                                                            {{ $history->note }}
                                                            <hr>
                                                            <p>ส่งเมื่อ {{ ScheduleController::dateRangeStr($history->created_at, $history->created_at, true, false, 'th', true, true) }}</p>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{--Select Command--}}
                        <div class="row form-group">
                            <div class="col-sm-12">
                                <label id="lblSelectCommand" for="selectCommand">คำสั่งของคณะนี้</label>
                                <br/>
                                <select id="selectCommand" name="selectCommand" class="form-control selectpicker" data-width="100%" data-mobile="true">
                                    <option data-icon="fa fa-thumbs-o-up" value="approval">อนุมัติรับคณะ</option>
                                    <option data-icon="fa fa-undo" value="return">ขอข้อมูลเพิ่มเติมเพื่อตัดสินใจ</option>
                                    <option data-icon="fa fa-thumbs-down" value="refusal">ปฎิเสธการรับคณะ</option>
                                    <option data-icon="fa fa-trash" value="delete">ลบคำร้อง</option>
                                </select>
                            </div>
                        </div>
                        {{--Optional Comment--}}
                        <div class="row">
                            <div class="col-sm-12">
                                <label id="additionText">คำสั่งหรือคำชี้แนะเพิ่มเติม</label>
                                <br/>
                                <textarea id="note" name="note" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-sm-12">
                                <button id="sendApproval" type="button" class="btn btn-success btn-lg btn-block" data-loading-text="กำลังบันทึกการอนุมัติ..." autocomplete="off">
                                    <i class="fa fa-thumbs-o-up" aria-hidden="true"></i>
                                    อนุมัติให้ดำเนินการ
                                </button>

                                {{--alternative button are change by select command.--}}
                                <button style="display: none;" id="sendReturn" type="button" class="btn btn-info btn-lg btn-block" data-loading-text="กำลังส่งเมลขอข้อมูลเพิ่มเติม..." autocomplete="off">
                                    <i class="fa fa-undo" aria-hidden="true"></i>
                                    ขอข้อมูลเพิ่มเติม
                                </button>

                                <button style="display: none;" id="sendRefusal" type="button" class="btn btn-danger btn-lg btn-block" data-loading-text="กำลังแจ้งการปฎิเสธ..." autocomplete="off">
                                    <i class="fa fa-thumbs-down" aria-hidden="true"></i>
                                    ปฎิเสธไม่รับคณะ
                                </button>

                                <button style="display: none;" id="sendDelete" type="button" class="btn btn-default btn-lg btn-block" data-loading-text="กำลังลบข้อมูล..." autocomplete="off">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                    ลบคณะนี้
                                </button>

                            </div>
                        </div>
                    </div>
                </div>

            </form>

            {{--For all success workflow include Approval, Reject or Cancel, Return, Delete--}}
            <div id="successProcess" style="display: none;">
                <div class="alert alert-success" role="alert">
                    <h3 id="successProcess-title"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i> <span id="successProcess-text">ทำรายการอนุมัติสำเร็จ !</span></h3>
                    <p id="successProcess-body">
                        สามารถทำการตรวจสอบและอนุมัติรายการต่อไปได้โดยคลิกค้นหาที่กล่องค้นหาด้านบนและปุ่ม "ดำเนินการต่อ" หรือหากต้องการพักกระบวนการกรุณากดปุ่ม "ออกจากระบบ" ที่ด้านล่าง
                        <br/>
                    <div class="pull-right">
                        <a class="btn btn-sm btn-primary" href="{{ URL::to('reviewer') }}" role="button"><span class="glyphicon glyphicon-step-forward" aria-hidden="true"></span> ดำเนินการต่อ</a>
                        <a class="btn btn-sm btn-default" href="{{ URL::to('user/logout') }}" role="button"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> ออกจากระบบ</a>
                    </div>
                    <div class="clearfix"></div>
                    </p>
                </div>
            </div>

            <script type="text/javascript">
                $(document).ready(function(){
                    tinymce.init({
                        selector:'textarea',
                        menubar: false,
                        statusbar: false,
                        toolbar: [
                            'undo redo | styleselect | bold italic'
                        ],
                        language: 'th_TH'
                    });

                    $('#btnMoreInfo').on('click', function(){
                        $('.moreSee').show();
                        $('#btnHideInfo').show();
                        $(this).hide();
                    });

                    $('#btnHideInfo').on('click', function(){
                        $('.moreSee').hide();
                        $('#btnMoreInfo').show();
                        $(this).hide();
                    });

                    var data = {};

                    //send approval
                    $('#sendApproval').on('click', function(){
                        sendReviewResult('sendApproval', data);
                    });
                    //send refuse or cancel
                    $('#sendRefusal').on('click', function(){
                        sendReviewResult('sendRefusal', data);
                    });
                    //send return
                    $('#sendReturn').on('click', function(){
                        sendReviewResult('sendReturn', data);
                    });
                    //send delete
                    $('#sendDelete').on('click', function(){
                        sendReviewResult('sendDelete', data);
                    });
                });

                function sendReviewResult(action,data)
                {
                    var urlPath = "";
                    var dialogType = BootstrapDialog.TYPE_SUCCESS;
                    var dialogTitle = "";

                    $('#'+action).button('loading');

                    switch (action) {
                        case 'sendApproval' :
                            urlPath = "{{ URL::action('ReviewerController@postReviewerAccept') }}";
                            dialogTitle = "กรุณายืนยันการอนุมัติ";
                            dialogType = BootstrapDialog.TYPE_SUCCESS;
                            break;
                        case 'sendReturn' :
                            urlPath = "{{ URL::action('ReviewerController@postReviewerReturn') }}";
                            dialogTitle = "กรุณายืนยันการส่งคำร้องตีกลับ";
                            dialogType = BootstrapDialog.TYPE_INFO;
                            break;
                        case 'sendRefusal' :
                            urlPath = "{{ URL::action('ReviewerController@postReviewerCancel') }}";
                            dialogTitle = "กรุณายืนยันการปฎิเสธ";
                            dialogType = BootstrapDialog.TYPE_DANGER;
                            break;
                        case 'sendDelete' :
                            urlPath = "{{ URL::action('ReviewerController@postReviewerDelete') }}";
                            dialogTitle = "กรุณายืนยันการลบข้อมูล";
                            dialogType = BootstrapDialog.TYPE_DEFAULT;
                            break;
                    }

                    var warnMissingMessage = "";

                    if (tinyMCE.get('note').getContent()=="")
                    {
                        warnMissingMessage = "<br/><strong><i>คำเตือน ท่านยังไม่ได้ระบุเหตุผลเพิ่มเติมหากท่านต้องการระบุกรุณากดยกเลิกแล้วกรอกในกล่องข้อความด้านบน</i></strong>";
                    }

                    BootstrapDialog.show({
                        type: dialogType,
                        title: dialogTitle,
                        message: 'ท่านต้องการยืนยันคำสั่งหรือไม่ ? '+warnMissingMessage,
                        buttons:
                                [
                                    {
                                        label: 'ยืนยัน',
                                        icon: 'glyphicon glyphicon-ok',
                                        action: function (dialogRef){
                                            postResult(urlPath,data,action);
                                            dialogRef.close();
                                        }
                                    },
                                    {
                                        label: 'ยกเลิก',
                                        icon: 'glyphicon glyphicon-remove',
                                        action: function (dialogRef){
                                            $('#'+action).button('reset');
                                            dialogRef.close();
                                        }
                                    }
                                ]
                    });

                }

                function postResult(urlPath,data,action)
                {
                    $.ajax({
                        type: "POST",
                        url: urlPath,
                        data: {
                            '_token' : $("input[name=_token]").val(),
                            'create_new' : 1,
                            'party_id' : $("input[name=_party_id]").val(),
                            'note' : tinyMCE.get('note').getContent(),
                            'is_cache' : 0
                        },
                        success: function (data) {
                            if (data.status=='success')
                            {
                                //loading reset
                                $('#'+action).button('reset');
                                //update tasks number
                                $('#reviewer-task-number').empty().html(data.tasks.reviewer);
                                //also update other work task
                                @if(Auth::user()->hasRole('manager'))
                                    $('#manager-task-number').empty().html(data.tasks.manager);
                                @endif
                                //fade form out
                                $('#formReview').fadeOut({
                                    'complete' : function ()
                                    {
                                        /*Change message by review action*/
                                        var review_text = "";
                                        switch (action) {
                                            case 'sendApproval' :
                                                review_text = "ทำรายการอนุมัติสำเร็จ !";
                                                break;
                                            case 'sendReturn' :
                                                review_text = "ทำรายการส่งคืนผู้ยื่นคำร้องสำเร็จ !";
                                                break;
                                            case 'sendRefusal' :
                                                review_text = "ทำรายการปฎิเสธคำร้องสำเร็จ !";
                                                break;
                                            case 'sendDelete' :
                                                review_text = "ทำรายการลบคำร้องสำเร็จ !";
                                                break;
                                        }

                                        $('#successProcess-text').html(review_text);
                                        $('#successProcess').show().fadeIn();
                                        $("#comboViewReviewingParty option").eq(0).remove();
                                        //$('#comboViewReviewingParty').prop('selectedIndex',1);
                                        if (data.tasks.reviewer==0)
                                        {
                                            //reload when empty tasks
                                            location.reload();
                                        }
                                    }
                                });
                            }
                            else
                            {
                                errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                                $('#'+action).button('reset');
                            }
                        },
                        dataType: 'json'
                    });
                }

            </script>

        @else

            <div id="warningProcess">
                <div class="alert {{ ($party->status=='editing') ? 'alert-info' : 'alert-warning' }}" role="alert">

                    @if($party->status=='editing')
                        <h3 id="warningProcess-title">คณะนี้กำลังรอข้อมูลเพิ่มเติมเพื่อตัดสินใจกรุณารอการตอบกลับ</h3>
                        <p id="warningProcess-body">
                        <h4>{{ $party->request_code." ".$party->name }}</h4>
                        </p>
                    @else
                        <h3 id="warningProcess-title">รายการนี้ได้ทำการอนุมัติไปแล้ว !</h3>
                        <p id="warningProcess-body">
                        <h4>{{ $party->request_code." ".$party->name }}</h4>
                        หากต้องการแก้ไขผลการอนุมัติกรุณาแจ้งไปยัง Admin ได้ที่นี่ <a href="mailto:luadmin@doitung.org ">luadmin@doitung.org </a>
                        </p>
                    @endif

                </div>
            </div>

        @endif

        {{--Control All State--}}
        <script type="text/javascript">
            $(function(){
                $('#comboViewReviewingParty').on('change', function(){
                    window.location.href = $(this).val();
                });

                $('#selectCommand').on('changed.bs.select', function (e) {
                    if ($(this).val()=='return')
                    {
                        $('#sendApproval, #sendRefusal, #sendDelete').hide().prop('disabled', true);
                        $('#sendReturn').show().prop('disabled', false);
                        $('#additionText').html('สิ่งที่ต้องการให้แก้ไข/เพิ่มเติมหรือคำถาม');
                    }
                    else if($(this).val()=='refusal')
                    {
                        $('#sendApproval, #sendReturn, #sendDelete').hide().prop('disabled', true);
                        $('#sendRefusal').show().prop('disabled', false);
                        $('#additionText').html('เหตุผลที่ปฎิเสธ');
                    }
                    else if($(this).val()=='delete')
                    {
                        $('#sendApproval, #sendRefusal, #sendReturn').hide().prop('disabled', true);
                        $('#sendDelete').show().prop('disabled', false);
                        $('#additionText').html('เหตุผลที่ทำการลบ');
                    }
                    else
                    {
                        $('#sendReturn, #sendRefusal, #sendDelete').hide().prop('disabled', true);
                        $('#sendApproval').show().prop('disabled', false);
                        $('#additionText').html('คำสั่งหรือคำชี้แนะเพิ่มเติม');
                    }
                });
            });
        </script>

    @endif

@stop