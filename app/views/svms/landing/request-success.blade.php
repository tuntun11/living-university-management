@extends('svms.layouts.landing')

@section('title')
    @parent :: ท่านได้ยื่นคำร้องคณะดูงานสำเร็จแล้ว
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

    <div class="panel panel-success">
        <div class="panel-heading">
            <h1 class="panel-title">
                @if($state === 'editByRequest')
                    <i class="fa fa-check-circle" aria-hidden="true"></i> ท่านได้ทำการแก้ไขข้อมูลและยื่นคำร้องคณะดูงานอีกครั้งสำเร็จแล้ว
                @elseif($state === 'editByYourself')
                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i> ท่านได้ทำการแก้ไขข้อมูลสำเร็จแล้ว
                @else
                    {{--case : Input form and save pending state--}}
                    <i class="fa fa-envelope-o" aria-hidden="true"></i> ท่านได้ทำกรอกคำร้องแล้วเพื่อเตรียมที่จะยื่นคำร้อง กรุณาตรวจสอบคำร้องหากถูกต้องกรุณากดยืนยันส่งคำร้องแต่หากต้องการแก้ไขสามารถกดปุ่มแก้ไขได้
                @endif
            </h1>
        </div>
        <div class="panel-body">

            {{--Show Latest Edit Note to send reviewing--}}
            @if($state === 'editByRequest')
                <div class="alert alert-info" role="alert">
                    <h5><strong>สิ่งที่แก้ไข/เพิ่มเติม :</strong></h5>
                    <p>{{ $party->statuses()->whereStatus('reviewing')->orderBy('created_at', 'desc')->pluck('note') }}</p>
                </div>
            @endif

            <form class="form-horizontal" role="form" id="formRequestSuccess">

                <!-- CSRF Token -->
                <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                <!-- ./ csrf token -->

                <!-- Encrypt -->
                <input type="hidden" name="encrypt" value="{{ (isset($party)) ? $party->encrypt : 'null' }}" />
                <!-- ./ encrypt -->

                @if($state === 'editByRequest' || $state === 'editByYourself')
                    <div class="form-group">
                        <label class="col-sm-3 control-label">รหัสคำร้อง</label>
                        <div class="col-sm-9">
                            <p class="form-control-static">{{ $party->request_code }}</p>
                        </div>
                    </div>
                @endif

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
                        <div class="pull-right">
                            <button id="btnCalendar" class="btn btn-lg btn-primary" type="button"><i class="fa fa-calendar" aria-hidden="true"></i> ดูปฎิทินคณะดูงาน</button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script type="text/javascript">

        $(function () {

            $('#btnCalendar').on('click', function(e){
                window.onbeforeunload = null;
                window.location.href = "{{ URL::to('calendar') }}";
            });

        });

    </script>

@stop