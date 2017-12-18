@extends('svms.layouts.landing')

@section('title')
    @parent :: ท่านได้กรอกคำร้องสำเร็จแล้ว !
@stop

@section('extraScripts')

@stop

@section('extraStyles')
    <style type="text/css">
        .text-nowrap {
            white-space: nowrap;
        }
    </style>
@stop

@section('header')

@stop

@section('content')

    <div id="wrapperPendingSend" class="panel panel-default">
        <div class="panel-heading">
            <h1 id="titlePendingSend" class="panel-title">
                <i class="fa fa-users" aria-hidden="true"></i> ข้อมูลคณะศึกษาดูงานเตรียมยื่นส่งคำร้อง
            </h1>
        </div>
        <div class="panel-body">
            {{--Alert pending send--}}
            <div id="alertPendingSend" class="alert alert-warning alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <p>
                    <h5><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <strong>ท่านยังไม่ได้ทำการส่งข้อมูลให้กับผู้ตรวจสอบทำการอนุมัติ กรุณาตรวจสอบข้อมูลของท่านแล้วทำตามขั้นตอนดังต่อไปนี้</strong></h5>
                    <ul>
                        <li>หากท่านตรวจสอบข้อมูลแล้วยืนยันการส่งข้อมูลให้กับผู้ตรวจสอบ กรุณากดปุ่ม <strong>"ยืนยันการส่งคำร้อง"</strong> ทางขวาของหน้าจอ โดยที่ข้อมูลจะไม่สามารถแก้ไขได้อีกเมื่อทำการส่งคำร้องแล้ว</li>
                        <li>หากท่านตรวจสอบข้อมูลแล้วไม่ถูกต้องหรือต้องการแก้ไขข้อมูลก่อนส่ง กรุณากดปุ่ม <strong>"แก้ไขข้อมูล"</strong> ทางซ้ายของหน้าจอ</li>
                    </ul>
                </p>
            </div>

            <form class="form-horizontal" role="form" id="formRequestPending">

                <!-- CSRF Token -->
                <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                <!-- ./ csrf token -->

                <!-- Encrypt -->
                <input type="hidden" id="encrypt" name="encrypt" value="{{ $encrypt }}" />
                <!-- ./ encrypt -->

                <!-- State -->
                <input type="hidden" id="state" name="state" value="firstRequest" />
                <!-- ./ state -->

                <div class="form-group">
                    <label class="col-sm-3 control-label">คณะดูงาน</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">{{ $party->name }}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">มาจากประเทศ</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">
                            @if($party->is_local==1)
                                <img src="{{ asset('assets/img/flags/th.png') }}" class="img-flag" /></i> ไทย
                            @else
                                @foreach($party->nationals as $country)
                                    <img src="{{ asset('assets/img/flags/'.$country['id'].'.png') }}" class="img-flag" /> {{ $country['name'] }}
                                @endforeach
                            @endif
                        </p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">ประเภทคณะ</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">{{ $party->party_type }}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">จำนวนผู้เข้าร่วม(คน)</label>
                    <div class="col-sm-3">
                        <p class="form-control-static">{{ $party->people_quantity }}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">ช่วงวันที่มา</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">{{ ScheduleController::dateRangeStr($party->start_date, $party->end_date, true) }}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">วัตถุประสงค์การมา</label>
                    <div class="col-sm-9">
                        <ul class="form-control-static">
                            @foreach($party['objectives'] as $objective)
                                <li>{{ $objective->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">ประเด็นที่สนใจเป็นพิเศษ</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">{{ $party->interested }}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">ความคาดหวังในการศึกษาดูงาน</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">{{ $party->expected }}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">พื้นที่ศึกษาดูงาน</label>
                    <div class="col-sm-9">
                        <ul class="form-control-static">
                            @foreach($party->location_bases as $location)
                                <li>{{ $location['mflf_area_name'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">เคยเข้าร่วมศึกษาดูงาน</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">
                            @if($party->joined=='never')
                                <span>ไม่เคย</span>
                            @elseif($party->joined=='ever')
                                <span>เคย</span>
                            @else
                                <span>ไม่แน่ใจ</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">รายละเอียดเพิ่มเติม</label>
                    <div class="col-sm-9">
                        <p class="form-control-static text-nowrap">{{ $party->objective_detail }}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="fileAddition" class="col-sm-3 control-label">ไฟล์อ้างอิง</label>
                    <div class="col-sm-9">
                        @if($party->fileUrl())
                            <a id="btnFile" class="btn btn-sm btn-default" href="{{ $party->fileUrl() }}" target="_blank" role="button"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> ดูไฟล์ต้นเรื่อง</a>
                        @else
                            <p class="form-control-static text-danger">ไม่ได้แนบ</p>
                        @endif
                    </div>
                </div>

                @if($party->fileUrl('travel01'))
                    <div class="form-group">
                        <label for="fileTravel01" class="col-sm-3 control-label">ไฟล์ ศทบ.01</label>
                        <div class="col-sm-9">
                            <a id="btnFileTravel01" class="btn btn-sm btn-default" href="{{ $party->fileUrl('travel01') }}" target="_blank" role="button"><i class="fa fa-suitcase" aria-hidden="true"></i> ดูไฟล์ศทบ.01</a>
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    <label class="col-sm-3 control-label">ผู้ประสานงานของคณะที่มา</label>
                    <div class="col-sm-9">
                        <ul class="form-control-static">
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

                <div class="form-group">
                    <label class="col-sm-3 control-label">ต้องการใช้หรือจองห้องพัก</label>
                    <div class="col-sm-9">
                        @if($party->accommodation_detail || $party->accommodation_detail!=NULL)
                            {{ $party->accommodation_detail }}
                        @else
                            <p class="form-control-static text-danger">ไม่ต้องการ</p>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">ต้องการสนับสนุนบุคลากรจากมหาวิทยาลัยที่มีชีวิต</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">
                            @if($party->request_for_lu_personnel)
                                ต้องการ {{ $party->request_lu_personnel_reason }}
                            @else
                                ไม่ต้องการ
                            @endif
                        </p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 col-sm-3 control-label">การชำระเงิน</label>
                    <div class="col-md-5 col-sm-9">
                        <p class="form-control-static">{{ FinancialController::model($party->paid_method, $party->related_budget_code) }}</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">ผู้กรอกข้อมูล</label>
                    <div class="col-sm-9">
                        <p class="form-control-static">
                            {{ $party->request_person_name }} กรอกเมื่อ {{ ScheduleController::dateRangeStr($party->created_at, $party->created_at, true, true) }}
                        </p>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-12">
                        {{--Before Send Button--}}
                        <div class="pull-left pending">
                            <a id="btnEdit" class="btn btn-lg btn-default" href="{{ URL::to('party/'.$encrypt.'/editing/editByYourself') }}" role="button"><i class="fa fa-pencil" aria-hidden="true"></i> แก้ไขข้อมูล</a>
                        </div>
                        <div class="pull-right pending">
                            <button id="btnSendRequest" data-loading-text="กำลังส่งคำร้องให้ผู้ตรวจสอบอนุมัติผล..." class="btn btn-lg btn-success" type="button"><i class="fa fa-envelope-o" aria-hidden="true"></i> ยืนยันการส่งคำร้อง</button>
                        </div>
                        {{--After Send Button--}}
                        <div class="pull-right reviewing" style="display: none;">
                            <button id="btnCalendar" class="btn btn-lg btn-primary" type="button"><i class="fa fa-calendar" aria-hidden="true"></i> ดูปฎิทินคณะดูงาน</button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script type="text/javascript">

        window.onbeforeunload = function (event)
        {
            var message = 'Important: Please click on \'Save\' button to leave this page.';
            if (typeof event == 'undefined') {
                event = window.event;
            }
            if (event) {
                event.returnValue = message;
            }
            return message;
        };

        $(function () {

            //click for go back edit
            $('#btnEdit').on('click', function(){
                window.onbeforeunload = null;
            });

            //submit send mail to reviewer
            $('#btnSendRequest').on('click', function(){
                //check never use before close event
                window.onbeforeunload = null;
                //disabling button
                $(this).button('loading');
                //post ajax confirm send to reviewer
                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('PartyController@postRequestConfirm') }}",
                    data: {
                        '_token' : $("input[name=_token]").val(),
                        'encrypt' : $('#encrypt').val(),
                        'state' : $('#state').val()
                    },
                    success: function (data) {
                        $(this).button('reset');

                        //if success alert with button to close page
                        if (data.status=='success')
                        {
                            var buttons = [
                                {
                                    icon: 'fa fa-check-circle',
                                    label: 'รับทราบ',
                                    action: function(dialogItself){
                                        dialogItself.close();
                                        //change message to show how success
                                        $('#wrapperPendingSend').removeClass('panel-default').addClass('panel-success');
                                        $('#titlePendingSend').empty().append('<i class="fa fa-check-circle" aria-hidden="true"></i> คณะนี้ได้ทำการส่งข้อมูลไปยังผู้ตรวจสอบเรียบร้อยแล้ว');
                                        $('#alertPendingSend').hide();
                                        $('.pending').hide();
                                        $('.reviewing').show();
                                    }
                                }
                            ];
                            successButton('ทำรายการสำเร็จ !', data.msg, buttons);//assign alert box
                        }
                        else
                        {
                            errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                        }
                    },
                    dataType: 'json'
                });
            });

            $('#btnCalendar').on('click', function(e){
                window.onbeforeunload = null;
                window.location.href = "{{ URL::to('calendar') }}";
            });

        });

    </script>

@stop