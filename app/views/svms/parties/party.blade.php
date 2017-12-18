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
        .none-icon{
            background: none;
        }
        .img-flag {
            margin-top: -5px;
        }
        .tab-pane{
            margin: 10px;
            min-height: 500px;
        }
        .delFileBtn{
            color: #ffffff;
            background-color: #777777;
        }
        a.delFileBtn{
            color: #ffffff !important;
        }
        .jstree-default a {
            white-space:normal !important; height: auto;
            margin-right: 15px;
        }
        .jstree-anchor {
            height: auto !important;
        }
        .jstree-default li > ins {
            vertical-align:top;
        }
        .jstree-leaf {
            height: auto;
        }
        .jstree-leaf a{
            height: auto !important;
        }
    </style>
@stop

@section('header')
    <span class="fa fa-users"></span>
    @if($party->customer_code)
        {{ $party->customer_code }}
    @endif
        {{ $party->name }}
@stop

@section('content')

    <?php
    $count_plan_b = ScheduleController::haveAnotherPlan($party->id);
    ?>
    <div class="col-xs-12 col-md-8">
        <div class="panel panel-primary">
            <div class="panel-heading"><strong>จัดการข้อมูลคณะ</strong>
                @if(Auth::check())
                    @if(Auth::user()->hasRole('project coordinator'))
                        <div class="pull-right">
                            <div class="btn-group">
                                @if($party->canProgram() && $party->is_history==0)
                                    <a href="{{ URL::to('coordinator/schedule/' . $party->id . '/view') }}" class="btn btn-default btn-xs"><i class="logo-label fa fa-calendar-o fa-fw"></i> <span class="text-lg">กำหนดการ</span></a>
                                    @if($party->programingPassed())
                                        <a href="{{ URL::to('coordinator/budget/' . $party->id . '/view') }}" class="btn btn-default btn-xs"><i class="logo-label fa fa-money fa-fw"></i> <span class="text-lg">งบประมาณ</span></a>
                                    @endif
                                @endif
                            </div>
                            {{--Configurartion action when is customer and not terminate--}}
                            @if($party->canActions())
                                <div class="btn-group">
                                    <button class="btn btn-danger btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-gear" aria-hidden="true"></i> ดำเนินการ <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if($party->status=='postpone')
                                            <li><a href="javascript:changeStatus('{{ $party->id }}','preparing');"><i class="fa fa-undo" aria-hidden="true"></i> คณะกลับมาดูงาน</a></li>
                                        @else
                                            <li><a href="javascript:openActions('postpone');"><i class="fa fa-lg fa-angle-double-right" aria-hidden="true"></i> เลื่อนกำหนดการ</a></li>
                                            <li><a href="javascript:openActions('terminated');"><i class="fa fa-ban" aria-hidden="true"></i> ยกเลิกคณะดูงาน</a></li>
                                        @endif
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
            <div class="panel-body">
                {{--
                //Holding ไว้ก่อน
                @if(Auth::check())
                    @if($party->is_history==0)
                        <div class="btn-group pull-right">
                            @if($party->canProgram())
                                <a href="{{{ URL::to('document/travel01/' . $party->id . '/view') }}}" target="_blank" class="btn btn-success btn-sm"><i class="fa fa-file-pdf-o"></i> ศทบ 01</a>
                            @endif

                            @if($party->budgetingPassed())
                                <a href="{{{ URL::to('document/action-plan/' . $party->id . '/view') }}}" class="btn btn-success btn-sm"><i class="fa fa-file-excel-o"></i> Action Plan(ร่าง)</a>
                            @endif
                        </div>
                        <div class="clearfix"></div>
                    @endif
                @endif
                --}}

                <div role="tabpanel">

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#main" aria-controls="main" role="tab" data-toggle="tab"><i class="fa fa-home"></i> <span class="text-lg">ข้อมูลหลัก</span></a></li>
						<li role="presentation"><a id="party_staff" href="#staff" aria-controls="staff" role="tab" data-toggle="tab"><i class="fa fa-male"></i> <span class="text-lg">ข้อมูลทีมบุคลากร</span></a></li>
                        <li role="presentation"><a id="party_transaction" href="#transaction" aria-controls="transaction" role="tab" data-toggle="tab"><i class="fa fa-history"></i> <span class="text-lg">ประวัติการดำเนินการ</span></a></li>
                        <li role="presentation"><a id="party_sharepoint" href="#sharepoint" aria-controls="sharepoint" role="tab" data-toggle="tab"><i class="fa fa-share-alt-square"></i> <span class="text-lg">เชื่อมต่อคลังข้อมูล</span></a></li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane fade in active" id="main">
                            <form class="form-horizontal" role="form" id="formEditParty">

                                <!-- CSRF Token -->
                                <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                                <!-- ./ csrf token -->

                                <input type="hidden" name="_party_id" value="{{ $party->id }}" />

                                @if($party->request_code)

                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">รหัสคำร้อง :</label>
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control" disabled value="{{ $party->request_code }}">
                                        </div>
                                    </div>

                                @endif

                                @if($party->customer_code)

                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">รหัสลูกค้า :</label>
                                        <div class="col-sm-3">
                                            <div class="input-group">
                                                <span class="input-group-addon" id="inputCustomerCode-addon">{{ ($party->budget_code=='912' || $party->budget_code=='') ? '912' : $party->budget_code }}</span>
                                                <input type="text" class="form-control" name="customer_code" id="inputCustomerCode"  value="{{ $party->customer_code }}" aria-describedby="inputCustomerCode-addon" readonly>
                                            </div>
                                        </div>
                                    </div>

                                @endif

                                <div class="form-group">
                                    <label for="inputName" class="col-sm-3 control-label">ชื่อคณะดูงาน :</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="name" id="inputName" value="{{ $party->name }}" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="comboCountry" class="col-sm-3 control-label">มาจาก :</label>
                                    <div class="col-sm-9">
                                        <label class="radio-inline">
                                            <input type="radio" name="radioFromCountry" id="radioFromCountry1" value="th" {{{ ($party->is_local==1) ? 'checked' : '' }}}> <i><img src="{{ asset('assets/img/flags/th.png') }}" class="img-flag" /></i> ในประเทศไทย
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="radioFromCountry" id="radioFromCountry2" value="other" {{{ ($party->is_local==0) ? 'checked' : '' }}}> ประเทศอื่นๆ
                                        </label>
                                        <div id="divCountrySelect" style="margin-top: 5px; {{{ ($party->is_local==1) ? 'display: none;' : '' }}}">
                                            <select class="form-control" name="countries[]" id="comboCountry" multiple="multiple" style="width: 100%">
                                                @if($party->is_local==0)
                                                    @foreach($party->nations as $nation)
                                                        <option value="{{ $nation['id'] }}" selected="selected">{{ $nation['text'] }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="comboType" class="col-sm-3 control-label">ประเภทคณะ :</label>
                                    <div class="col-sm-9">
                                        <select class="form-control" name="party_type_id" id="comboType">
                                            @foreach($partyTypes as $partyType)
                                                <option value="{{ $partyType->ID }}" {{{ $partyType->ID===$party->party_type_id ? 'selected="selected"' : '' }}}>{{ $partyType->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="numberQuantity" class="col-sm-3 control-label">จำนวนผู้เข้าร่วม :</label>
                                    <div class="col-sm-3">
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="people_quantity" id="numberQuantity" max="9999" value="{{ $party->people_quantity }}" min="1" aria-describedby="numberQuantity-addon" required>
                                            <span class="input-group-addon" id="numberQuantity-addon">คน</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="inputDateStart" class="col-sm-3 control-label">ช่วงวันที่มา :</label>
                                    <div class="col-sm-3">
                                        <div class='input-group date' id='dateStart'>
                                            <input type='text' class="form-control" data-date-format="DD/MM/YYYY" name="start_date" id="inputDateStart" placeholder="เริ่มวันที่" value="{{ date("d/m/Y", strtotime($party->start_date)) }}" required {{ ($party->canActions()) ? 'readonly' : '' }} />
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                            </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class='input-group date' id='dateEnd'>
                                            <input type='text' class="form-control" data-date-format="DD/MM/YYYY" name="end_date" id="inputDateEnd" placeholder="ถึงวันที่" value="{{ date("d/m/Y", strtotime($party->end_date)) }}" required {{ ($party->canActions()) ? 'readonly' : '' }} />
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="comboObjective" class="col-sm-3 control-label">วัตถุประสงค์การมา :</label>
                                    <div class="col-sm-9">
                                        <select class="form-control" name="objectives[]" id="comboObjective" multiple="multiple" style="width: 100%">
                                            @foreach($partyObjectives as $partyObjective)
                                                <option value="{{ $partyObjective->id }}">{{ $partyObjective->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="inputInterested" class="col-sm-3 control-label">ประเด็นที่สนใจ :</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="interested" id="inputInterested" placeholder="" value="{{ $party->interested }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="inputExpected" class="col-sm-3 control-label">ความคาดหวัง :</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="expected" id="inputExpected" placeholder="" value="{{ $party->expected }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="comboLocationBase" class="col-sm-3 control-label">พื้นที่ดูงาน</label>
                                    <div class="col-sm-9">
                                        <select class="form-control" name="location_bases[]" id="comboLocationBase" multiple="multiple" style="width: 100%">
                                            @foreach($mflfAreas as $mflfArea)
                                                <option value="{{ $mflfArea->id }}">{{ $mflfArea->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label">เคยเข้าร่วมดูงาน :</label>
                                    <div class="col-sm-9">
                                        <label class="radio-inline">
                                            <input type="radio" name="joined" id="radioJoined1" value="never" {{{ ($party->joined=='never') ? 'checked' : '' }}}> ครั้งแรก
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="joined" id="radioJoined2" value="ever" {{{ ($party->joined=='ever') ? 'checked' : '' }}}> เคยมาเข้าร่วมแล้ว
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="joined" id="radioJoined3" value="null" {{{ ($party->joined=='null') ? 'checked' : '' }}}> ไม่แน่ใจ
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="textareaMoreObjective" class="col-sm-3 control-label">รายละเอียดเพิ่มเติม :</label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control" name="objective_detail" id="textareaMoreObjective">{{ $party->objective_detail }}</textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="comboTagParty" class="col-sm-3 control-label">Tag ของคณะ :</label>
                                    <div class="col-sm-9">
                                        <select title="สามารถพิมพ์ tag ที่ต้องการเข้าไปใหม่ได้" class="form-control" name="tags[]" id="comboTagParty" multiple="multiple" style="width: 100%">
                                            @foreach($tags as $tag)
                                                <option value="{{ $tag->tag }}">{{ $tag->tag }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label">ผู้ประสานงานคณะ :</label>
                                    <div class="col-sm-9">
                                        <table class="table table-bordered table-hover" id="tab_logic">
                                            <thead>
                                            <tr >
                                                <th class="text-center">
                                                    ชื่อ - สกุล
                                                </th>
                                                <th class="text-center">
                                                    E-mail
                                                </th>
                                                <th class="text-center">
                                                    เบอร์มือถือ
                                                </th>
                                                <th class="text-center">
                                                </th>
                                            </tr>
                                            </thead>
                                            <tbody>

                                                <?php
                                                $i = 0;
                                                ?>
                                                @foreach($party->contacts as $contact)
                                                    <tr id='addr{{ $i }}'>
                                                        <td>
                                                            <input type="hidden" name='coor_id[]' value="{{ $contact->id }}"/>
                                                            <input type="text" name='coor_name[]'  placeholder='' class="form-control" value="{{ $contact->name }}" required/>
                                                        </td>
                                                        <td>
                                                            <input type="email" name='coor_email[]' placeholder='' class="form-control" value="{{ $contact->email }}" />
                                                        </td>
                                                        <td>
                                                            <input type="text" name='coor_mobile[]' placeholder='' class="form-control" value="{{ $contact->mobile }}" />
                                                        </td>
                                                        <td>
                                                            @if($i>0)
                                                                <button type='button' row='{{ $i }}' class='del_contact btn btn-link' onclick='delContract({{ $i }});'>ลบ</button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <?php
                                                    $i++;
                                                    ?>
                                                @endforeach
                                                <tr id='addr{{ $i }}'></tr>

                                            </tbody>
                                        </table>
                                        <a id="add_contact" class="btn btn-default pull-right btn-sm"><span class="fa fa-plus"></span> เพิ่มผู้ประสานงาน</a>
                                    </div>
                                </div>

                                {{--Reservation Accommodation--}}
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">การใช้ห้องพัก :</label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control" id="accommodation_detail" name="accommodation_detail">{{ $party->accommodation_detail }}</textarea>
                                    </div>
                                </div>

                                {{--Addition Request for lu personnel--}}
                                <div class="form-group">
                                    <label class="col-md-3 col-sm-3 control-label">บุคลากร LU ?</label>
                                    <div class="col-md-9 col-sm-9">
                                        <label class="radio-inline">
                                            <input name="request_for_lu_personnel" type="radio" id="request_for_lu_personnel_yes" value="yes" checked> ต้องการ
                                        </label>
                                        <label class="radio-inline">
                                            <input name="request_for_lu_personnel" type="radio" id="request_for_lu_personnel_no" value="no"> ไม่ต้องการ
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 col-sm-3 control-label">เหตุผลที่ต้องการบุคลากร LU *</label>
                                    <div class="col-md-9 col-sm-9">
                                        <textarea class="form-control" id="request_lu_personnel_reason" name="request_lu_personnel_reason">{{ $party->request_lu_personnel_reason }}</textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 col-sm-3 control-label">การชำระเงิน *</label>
                                    <div class="col-md-9 col-sm-9">
                                        <select class="form-control" name="paid_method" id="paid_method">
                                            @foreach(array_keys($models) as $model)
                                                <option {{ ($party->paid_method==$model) ? 'selected' : '' }} value="{{ $model }}">{{ $models[$model] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{--Related budget with Paid Method--}}
                                <div class="form-group" id="divOtherCode" style="{{ ($party->paid_method=='other') ? '' : 'display: none;' }}">
                                    <label class="col-md-3 col-sm-3 control-label"></label>
                                    <div class="col-md-3 col-sm-9">
                                        <input type="text" id="other_code" name="other_code" class="form-control" maxlength="3" placeholder="รหัสหน่วยงานอื่น" value="{{ $party->related_budget_code }}"/>
                                    </div>
                                </div>
                                <div class="form-group" id="divDonateCode" style="{{ ($party->paid_method=='donate') ? '' : 'display: none;' }}">
                                    <label class="col-md-3 col-sm-3 control-label"></label>
                                    <div class="col-md-5 col-sm-9">
                                        <input type="text" id="donate_code" name="donate_code" class="form-control" maxlength="9" placeholder="รหัสที่รับผิดชอบค่าใช้จ่ายทั้งหมด" value="{{ $party->related_budget_code }}"/>
                                    </div>
                                </div>
                                <div class="form-group" id="divAbsorbCode" style="{{ ($party->paid_method=='absorb') ? '' : 'display: none;' }}">
                                    <label class="col-md-3 col-sm-3 control-label"></label>
                                    <div class="col-md-9 col-sm-9">
                                        <input type="text" id="absorb_code" name="absorb_code" class="form-control" placeholder="รหัสที่ช่วยสนับสนุน ใส่ , เมื่อมีมากกว่า 1 รหัส" value="{{ $party->related_budget_code }}"/>
                                    </div>
                                </div>

                                <div class="form-group pull-right">
                                    <div class="col-sm-12">
                                        <button id="submitForm" type="submit" class="btn btn-primary" data-loading-text="กำลังบันทึก..." autocomplete="off">
                                            <span class="fa fa-save" aria-hidden="true"></span>
                                            บันทึกข้อมูล
                                        </button>
                                    </div>
                                </div>

                            </form>
                        </div>
						
						<div role="tabpanel" class="tab-pane fade" id="staff">
							{{--staff grid--}}
                            <div class="alert alert-info" role="alert">
                                <div class="pull-right">
                                    <button id="addStaffWork" type="button" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> เพิ่มบุคลากรที่ร่วมงาน</button>
                                </div>
                                แถบข้อมูลนี้วัตถุประสงค์เพื่อจัดเก็บข้อมูลบุคคลากรที่มีส่วนร่วมในการรับคณะ
                            </div>

                            <form id="form_party_staff">
                                <!-- CSRF Token -->
                                <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                                <!-- Transaction Feed -->
                                <table id="table_party_staff" class="table table-condensed table-striped">
                                    <thead>
                                    <tr>
                                        <th class="col-md-5">ชื่อบุคลากร</th>
                                        <th class="col-md-5">ภาระงาน</th>
                                        <th class="col-md-2">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </form>
                            {{--staff grid--}}
                        </div>

                        <div role="tabpanel" class="tab-pane fade" id="transaction">
                            <form id="form_party_transaction">
                                <!-- CSRF Token -->
                                <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                                <!-- Transaction Feed -->
                                <table id="table_party_transaction" class="table table-condensed">
                                    <thead>
                                    <tr>
                                        <th class="col-md-3">การดำเนินการ(สถานะ)</th>
                                        <th class="col-md-3">โดย</th>
                                        <th class="col-md-3">วันที่และเวลา</th>
                                        <th class="col-md-3">หมายเหตุ</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </form>
                        </div>

                        <div role="tabpanel" class="tab-pane fade" id="sharepoint">
                            {{--sharepoint grid--}}
                            <div class="alert alert-info" role="alert">
                                <div class="pull-left">
                                    แถบข้อมูลนี้วัตถุประสงค์เพื่อจัดเก็บ url ของระบบคลังข้อมูล archive เพื่อสะดวกในการเข้าถึงและจัดเก็บแบ่งเป็นตามคณะดูงานไป
                                </div>
                                <div class="pull-right">
                                    <button id="addSharepoint" type="button" class="btn btn-xs btn-warning"><i class="fa fa-plus"></i> เพิ่ม Link Archive</button>
                                </div>
                                <div class="clearfix"></div>
                            </div>

                            <form id="form_party_sharepoint">
                                <!-- CSRF Token -->
                                <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                                <!-- Transaction Feed -->
                                <table id="table_party_sharepoint" class="table table-condensed table-striped">
                                    <thead>
                                    <tr>
                                        <th class="col-md-2">ประเภท</th>
                                        <th class="col-md-5">Title</th>
                                        <th class="col-md-2">Url</th>
                                        <th class="col-md-3">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </form>
                            {{--sharepoint grid--}}
                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>

    <div id="panel_party_summarize" class="col-xs-12 col-md-4">
        {{--สถานะของคณะ box--}}
        <div class="panel panel-success">
            <div class="panel-heading"><strong>สถานะของคณะ</strong></div>
            <div class="panel-body">
                @if($party->status=='reviewing')
                    {{ $party->statusThai($party->status, true, $party->numberOfReviewing()) }}
                @elseif($party->status=='editing')
                    {{ $party->statusThai($party->status, true, $party->numberOfEditing()) }}
                @elseif($party->status=='pending' || $party->status=='reviewed' || $party->status=='cancelled1' || $party->status=='cancelled2' || $party->status=='terminated' || $party->status=='postpone')
                    {{ $party->statusThai($party->status, true) }}
                @else
                    <select id='party_status_selected' class='form-control'>
                        <option {{ ($party->status=='approved') ? 'selected' : '' }} value='approved'>ผ่านอนุมัติแล้ว</option>
                        <option {{ ($party->status=='preparing') ? 'selected' : '' }} value='preparing'>เตรียมการรับคณะ</option>
                        <option {{ ($party->status=='ongoing') ? 'selected' : '' }} value='ongoing'>ระหว่างการรับคณะ</option>
                        <option {{ ($party->status=='finished') ? 'selected' : '' }} value='finished'>ดำเนินการสำเร็จ(ชำระเงินแล้ว)</option>
                        <option {{ ($party->status=='finishing') ? 'selected' : '' }} value='finishing'>ดำเนินการสำเร็จ(ยังไม่ได้รับเงิน)</option>
                    </select>
                @endif
            </div>
        </div>

        {{--รายได้รวม จะมองไม่เห็นในส่วนนี้หากยังไม่บันทึกสถานะเป็นดำเนินการและจ่ายเงินแล้ว--}}
        @if($party->budgetingPassed())
            <div class="panel panel-info">
                <div class="panel-heading"><strong>สรุปรายได้สุทธิ</strong></div>
                <div class="panel-body">
                    @if($party->is_history)
                        <div class="input-group">
                            <input id="party_summary_income" type="number" value="{{ $party->summary_income }}" class="form-control" aria-describedby="party_summary_income-addon">
                            <span class="input-group-addon" id="party_summary_income-addon">บาท</span>
                        </div>
                        <div style="margin-top: 10px; display: none;" class="pull-right" id="input-history-summary-income">
                            <button id="submit-summary-income" type="button" class="btn btn-default">บันทึกรายได้</button>
                        </div>
                    @else
                        <?php
                            if ($party->income_edited_by==null || $party->income_edited_by=='')
                            {
                                $budget = $party->budgets->first();
                                if ($budget)
                                {
                                    $summaryIncome = ($party->final_plan=='A') ? $budget->grand_total_a : $budget->grand_total_b;
                                }
                                else
                                {
                                    $summaryIncome = $party->summary_income;
                                }
                            }
                            else
                            {
                                $summaryIncome = $party->summary_income;
                            }
                        ?>

                        @if($count_plan_b>0)

                            <label class="radio-inline">
                                <input type="radio" name="final_plan" id="final_plan_a" value="500" {{{ ($party->final_plan=='A') ? 'checked' : '' }}}> <span class="text-primary">ใช้แผน A</span>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="final_plan" id="final_plan_b" value="{{ $budget->grand_total_b }}" {{{ ($party->final_plan=='B') ? 'checked' : '' }}}> <span class="text-danger">ใช้แผน B</span>
                            </label>

                            <div class="input-group" style="margin-top: 10px;">
                                <input id="party_summary_income" type="number" value="{{ $summaryIncome }}" class="form-control" aria-describedby="party_summary_income-addon">
                                <span class="input-group-addon" id="party_summary_income-addon">บาท</span>
                            </div>
                            <div style="margin-top: 10px; display: none;" class="pull-right" id="input-history-summary-income">
                                <button id="submit-summary-income" type="button" class="btn btn-default">บันทึกรายได้</button>
                            </div>
                        @else
                            <div class="input-group">
                                <input id="party_summary_income" type="number" value="{{ $summaryIncome }}" class="form-control" aria-describedby="party_summary_income-addon">
                                <span class="input-group-addon" id="party_summary_income-addon">บาท</span>
                            </div>
                            <div style="margin-top: 10px; display: none;" class="pull-right" id="input-history-summary-income">
                                <button id="submit-summary-income" type="button" class="btn btn-default">บันทึกรายได้</button>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
			
		@else
			
			@if(Auth::user()->hasRole('manager') || $party->status=='finished')
				<div class="panel panel-info">
					<div class="panel-heading"><strong>สรุปรายได้สุทธิ</strong></div>
					<div class="panel-body">
						<div class="input-group">
							<input id="party_summary_income" type="number" value="{{ $party->summary_income }}" class="form-control" aria-describedby="party_summary_income-addon">
							<span class="input-group-addon" id="party_summary_income-addon">บาท</span>
						</div>
						 <p class="help-block">Manager เท่านั้นที่สามารถแก้ไขงบประมาณได้</p>
						<div style="margin-top: 10px; display: none;" class="pull-right" id="input-history-summary-income">
							<button id="submit-summary-income" type="button" class="btn btn-default">บันทึกรายได้</button>
						</div>
				    </div>
				</div>	
			@endif
		
        @endif

        {{--ประสานงานหลักโดย--}}
       <div class="panel panel-info">
            <div class="panel-heading"><strong>ผู้ประสานงานหลัก</strong></div>
            <div class="panel-body">
                @if($party->is_history)
                    <select id='party_coordinator_selected' class='form-control'>
                        <option value=''>ยังไม่ได้ระบุผู้ประสานงาน</option>
                        @foreach($coordinators as $coordinator)
                            <option {{ ($party->project_co===$coordinator->personnel_id) ? 'selected' : '' }} value='{{ $coordinator->personnel_id }}'>{{ $coordinator->fullName().'('.$coordinator->department->code.')' }}</option>
                        @endforeach
                    </select>
                @else
                    {{ $party->assignedCoordinator(false, true) }}
                @endif
            </div>
        </div>

        {{--กล่องไฟล์จากระบบ box--}}
        <div class="panel panel-info">
            <div class="panel-heading"><strong>คลังเอกสาร</strong>
                @if(Auth::check())
                    @if(Auth::user()->hasRole('manager') || Auth::user()->hasRole('project coordinator'))
                        <div class="pull-right">
                            <button id="uploadFiles" class="btn btn-xs btn-primary" type="button"><i class="fa fa-upload"></i> อัพโหลดไฟล์</button>
                        </div>
                    @endif
                @endif
            </div>
            <div class="panel-body">
                <div id="folder_tree" class=""></div>
            </div>
            <div class="panel-footer">
                <small>กรุณาคลิกเม้าส์ที่ไฟล์ขวาเพื่อเรียกดู Menu</small>
            </div>
        </div>

    </div>
	
	{{--Start Form Modal Staff--}}
    <form id="formModalStaff" role="form">

        <!-- CSRF Token -->
        <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
        <!-- ./ csrf token -->

        <input type="hidden" id="partyStaff" name="partyStaff" value="{{ $party->id }}">
        <input type="hidden" id="staff_id" name="staff_id" value="0">

        <div id="modalStaff" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa fa-male"></i> บุคลากรที่ทำงานให้แก่คณะ {{ $party->customer_code." ".$party->name }}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="comboPersonnel">บุคลากร *</label>
                            <select style="width:100%;" class="form-control" id="comboPersonnel" name="comboPersonnel" required>
                            </select>
                        </div>

						<div class="form-group">
                            <label for="comboWorks">ภาระงาน (สามารถเลือกได้มากกว่า 1 งาน) *</label>
                            <select style="width:100%;" multiple="multiple" class="form-control" id="comboWorks" name="comboWorks" required>
								@foreach($work_types as $work_type)
									<option value="{{ $work_type->id }}">{{ $work_type->name }}</option>
								@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="submitFormModalStaff" data-loading-text="กำลังบันทึก..." type="button" class="btn btn-primary">บันทึก</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {{--End Form Modal Staff--}}

    {{--Start Form Modal Upload--}}
    <form id="formModalUpload" role="form" method="post" action="{{ URL::action('PartyController@postUploadFiles') }}" enctype="multipart/form-data">

        <!-- CSRF Token -->
        <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
        <!-- ./ csrf token -->

        <input type="hidden" name="partyUpload" value="{{ $party->id }}">

        <div id="modalUpload" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa fa-upload"></i> อัพโหลดไฟล์ของคณะ</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="inputFile">ไฟล์ *</label>
                            <input type="file" id="inputFile" name="inputFile" placeholder="" required>
                            <p class="help-block">ชื่อของไฟล์จะถูกเปลี่ยนแปลงโดยจะเพิ่มด้วยวันที่อัพโหลดหน้าชื่อเดิม</p>
                        </div>
                        <div class="form-group">
                            <label for="comboUploadFolder">โฟลเดอร์ที่เก็บ *</label>
                            <select class="form-control" id="comboUploadFolder" name="comboUploadFolder" required>
                                <option selected disabled value="">--เลือกโฟลเดอร์--</option>
                                @if($party->customer_code)
                                    <option value="request">จดหมายคำร้อง</option>
                                    <option value="schedule">โปรแกรมดูงาน</option>
                                    <option value="quotation">ใบเสนอราคา</option>
                                    <option value="action_plan">Action Plan</option>
                                    <option value="travel01">ศทบ 01</option>
                                    <option value="assess">แบบประเมิน</option>
                                    <option value="report">รายงานสรุป</option>
                                    <option value="other">อื่นๆ</option>
                                @else
                                    <option value="request">จดหมายคำร้อง</option>
                                    <option value="schedule">โปรแกรมดูงาน</option>
                                    <option value="travel01">ศทบ 01</option>
                                    <option value="other">อื่นๆ</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="submitFormModalUpload" data-loading-text="Uploading..." type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {{--End Form Modal Upload--}}

    {{--Start Form Modal modalSharepoint--}}
    <form id="formModalSharepoint" role="form">

        <!-- CSRF Token -->
        <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
        <!-- ./ csrf token -->

        <input type="hidden" id="partySharepoint" name="partySharepoint" value="{{ $party->id }}">
        <input type="hidden" id="sharepoint_id" name="sharepoint_id" value="0">

        <div id="modalSharepoint" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa fa-share-alt-square"></i> Sharepoint</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="comboSharepointType">ประเภท *</label>
                            <select class="form-control" id="comboSharepointType" name="comboSharepointType" required>
                                <option selected disabled value="">--เลือก--</option>
                                <option value="images">images</option>
                                <option value="media">media</option>
                                <option value="publications">publications</option>
                                <option value="other">other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="inputSharepointTitle">Title</label>
                            <input type="text" class="form-control" id="inputSharepointTitle" name="inputSharepointTitle">
                        </div>

                        <div class="form-group">
                            <label for="inputSharepointUrl">Url *</label>
                            <input type="url" class="form-control" id="inputSharepointUrl" name="inputSharepointUrl" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="submitFormModalSharepoint" data-loading-text="กำลังบันทึก..." type="button" class="btn btn-primary">บันทึก</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {{--End Form Modal modalSharepoint--}}

    {{--Start Form Modal action to do something with it.--}}
    <form id="formModalActions" role="form">

        <!-- CSRF Token -->
        <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
        <!-- ./ csrf token -->

        {{--Selected Actions--}}
        <input type="hidden" id="actionSelected"/>

        <div id="modalActions" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="titleAction">{{--Action Title--}}</h4>
                    </div>
                    <div class="modal-body">
                        <span id="action-error"></span>
                        {{--Notice--}}
                        @if($party->period()>1)
                            <div id="re-schedule-warning" class="alert alert-warning alert-dismissible fade in" role=alert> <button type=button class=close data-dismiss=alert aria-label=Close><span aria-hidden=true>&times;</span></button> คำเตือน : หากทำการเลื่อนกำหนดการจำนวนวันน้อยกว่า {{ $party->period() }} วัน หากวันที่ท่านได้ทำกำหนดการหรืองบประมาณเอาไว้วันท้ายสุดจะหายไป ยกตัวอย่าง คณะมาวันที่ 10-12 ตุลาคม จำนวน 3 วัน ถูกเลื่อนมาเป็นวันที่ 10-11 พฤศจิกายน
                                จำนวน 2 วัน ข้อมูลกำหนดการวันที่ 10-11 ตุลาคมจะถูกย้ายมาแต่ข้อมูลวันที่ 12 ตุลาคมจะหายไป</div>
                        @endif
                        {{--Case actions is postpone--}}
                        <div id="actionPostpone">
                            <div class="form-group">
                                <label class="radio-inline">
                                    <input type="radio" name="radioPostponeType" id="radioPostponeType1" value="re-schedule" checked> เลื่อนแบบมีกำหนดการชัดเจน
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="radioPostponeType" id="radioPostponeType2" value="postpone"> เลื่อนไม่มีกำหนด
                                </label>
                            </div>
                            <div id="re-schedule" class="form-group">
                                <div class="row">
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <div class='input-group date' id='postponeStart'>
                                            <input type='text' class="form-control" data-date-format="DD/MM/YYYY" name="postpone_start_date" id="inputPostponeStart" placeholder="เริ่มวันที่"/>
                                                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                                                </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <div class='input-group date' id='postponeEnd'>
                                            <input type='text' class="form-control" data-date-format="DD/MM/YYYY" name="postpone_end_date" id="inputPostponeEnd" placeholder="ถึงวันที่"/>
                                                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                                                </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                        <span class="text-info">กำหนดการเดิมคือวันที่ {{ ScheduleController::dateRangeStr($party->start_date,$party->end_date,true,false) }} จำนวน {{ $party->period() }} วัน</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{--Case actions is terminated--}}
                        <div id="actionTerminated">
                            {{--Currently not used--}}
                        </div>
                        {{--Default Value--}}
                        <div class="form-group">
                            <label for="textareaActionNote">เหตุผล *</label>
                            <textarea class="form-control" name="action_note" id="textareaActionNote"></textarea>
                        </div>

                        <p class="pull-right text-warning">หมายเหตุ : อีเมลจะถูกส่งไปให้แก่ผู้กรอกคำร้องและทีม LU</p>
                        <div class="clearfix"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="pull-left">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times" aria-hidden="true"></i> ยกเลิก</button>
                        </div>
                        <button id="submitFormModalAction" data-loading-text="กำลังบันทึกและส่งเมลแจ้งเตือน..." type="button" class="btn btn-success"><i class="fa fa-check" aria-hidden="true"></i> ยืนยัน</button>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>

    </form>

    {{--Show Text Read Modal--}}
    <div id="textReadModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div id="textReadModalContent" class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function(){
            /*Retrieve flash when upload and alert this*/
            @if(Session::get('status')==='success')
                var successMsg = "{{ Session::get('msg') }}";
                successAlert('บันทึกรายการสำเร็จ !', successMsg);
            @endif

            @if(Session::get('status')==='error')
                var errorMsg = "{{ Session::get('msg') }}";
                errorAlert('บันทึกไม่สำเร็จ !', errorMsg);
            @endif

            /*Initial Trigger*/
            $('#paid_method').change();
        });

        $(function () {

            /*if vip or reviewer do not change anything*/
            @if(Auth::user()->hasRole('vip') || Auth::user()->hasRole('contributor'))
                $('input, textarea').prop('readOnly', true);
                $('select').prop('disabled', true);
                $('#add_contact, #submitForm').hide();
            @endif

            /*if manager OR project coordinator role can edit*/
            @if(Auth::user()->hasRole('manager') || Auth::user()->hasRole('project coordinator'))
                $('input, textarea').prop('readOnly', false);
                $('select').prop('disabled', false);
                $('#add_contact, #submitForm').show();
                /*lock edit summary income if user is project co*/
                @if(Auth::user()->hasRole('project coordinator'))
                    $('#party_summary_income').prop('readOnly', true);
                @endif
                @if(Auth::user()->hasRole('manager'))
                    $('#party_summary_income').prop('readOnly', false);
                @endif
            @endif

            /*if have plan b only*/
            @if($count_plan_b>0)
                /*control for select final plan and summary income select*/
                $('input[name=final_plan]').on('change', function(){
                    if($('#final_plan_a').is(':checked'))
                    {
                        $('#party_summary_income').val($('#final_plan_a').val());
                    }
                    else
                    {
                        $('#party_summary_income').val($('#final_plan_b').val());
                    }
                });
            @endif

            var countries = {{ json_encode($countries) }};
			var personnels = {{ json_encode($personnels) }};

            /*load document*/
            loadDocumentJson($('input[name=_party_id]').val());

            /*Add contact*/
            $("#add_contact").click(function(e){

                //$( "#no-coordinator" ).remove();

                e.preventDefault();

                var i= $('#tab_logic > tbody > tr:has(td)').size();

                $('#addr'+i).html("<td><input name='coor_name[]' type='text' class='form-control' /> </td> <td><input  name='coor_email[]' type='email' class='form-control'></td><td><input  name='coor_mobile[]' type='text' class='form-control'></td> <td><button type='button' row='"+i+"' class='del_contact btn btn-link' onclick='delContract("+i+");'>ลบ</button></td>");

                $('#tab_logic').append('<tr id="addr'+(i+1)+'"></tr>');
                i++;

            });

            /*Check paid method if donate*/
            $('#paid_method').on('change', function(e){
                e.preventDefault();

                if ($(this).val()=='other')
                {
                    $('#divOtherCode').show();
                    $('#divDonateCode').hide();
                    $('#divAbsorbCode').hide();
                    $('#donate_code').val('');
                    $('#absorb_code').val('');
                }
                else if ($(this).val()=='donate')
                {
                    $('#divDonateCode').show();
                    $('#divOtherCode').hide();
                    $('#divAbsorbCode').hide();
                    $('#other_code').val('');
                    $('#absorb_code').val('');
                }
                else if ($(this).val()=='absorb')
                {
                    $('#divAbsorbCode').show();
                    $('#divDonateCode').hide();
                    $('#divOtherCode').hide();
                    $('#donate_code').val('');
                    $('#other_code').val('');
                }
                else
                {
                    //case อื่นๆ นอกจาก 912 = 100%
                    $('#divDonateCode').hide();
                    $('#divOtherCode').hide();
                    $('#divAbsorbCode').hide();
                    $('#donate_code').val('');
                    $('#other_code').val('');
                    $('#absorb_code').val('');
                }
            });

            /*Control Input Country*/
            $("#comboCountry").select2({
                data: countries,
                templateResult: formatCountry,
                templateSelection: formatCountry
            });
			
			/*Control Input Personnel Work*/
            $("#comboPersonnel").select2({
                data: personnels,
				placeholder: "กรุณาเลือกบุคลากร",
                templateResult: formatPersonnel,
                templateSelection: formatPersonnel
            });
			
			/*Control Work Type Combo*/
			$("#comboWorks").select2({
				placeholder: "กรุณาเลือกงาน"
            });

            /*Control Multiple Input Area*/
            $('#comboLocationBase').select2({
                'placeholder' : 'สามารถเลือกได้มากกว่า 1 พื้นที่ศึกษาดูงาน'
            });

            /*trigger show button on keydown*/
            $( "#party_summary_income" ).keydown(function( event ) {
                if ($(this).val()==undefined || $(this).val()=="" || $(this).val()==0)
                {
                    $('#input-history-summary-income').hide();
                }
                else
                {
                    $('#input-history-summary-income').show();
                }
            });

            //trigger change
            $('input[name=radioFromCountry]').trigger('change');

            /*Control Multiple Input Objective*/
            var objectives = [];
            @foreach($party->requestObjectives()->get() as $requestObjective)
                objectives.push({{ $requestObjective->party_objective_id }});
            @endforeach

            $('#comboObjective').select2().val(objectives).change();

            /*Control Multiple Input Location Base*/
            var location_bases = [];
            @foreach($party->getLocationBaseArrays() as $base)
               location_bases.push({{ $base }});
            @endforeach

            $('#comboLocationBase').select2().val(location_bases).change();

            /*Control Multiple Input Tags*/
            var tags = [];
            @foreach($party->tags()->get()->toArray() as $tag)
                var t = '{{ $tag['tag'] }}';
                tags.push(t);
            @endforeach

            $('#comboTagParty').select2({  tags: true }).val(tags).change();

            /*Validate Plugin*/
            $.validator.addMethod('filesize', function(value, element, param) {
                // param = size (en bytes)
                // element = element to validate (<input>)
                // value = value of the element (file name)
                return this.optional(element) || (element.files[0].size <= param)
            });

            $("#formEditParty").validate({
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
                        url: "{{ URL::action('PartyController@postEdit') }}",
                        dataType:  'json',
						data: {
							'obj_detail_desc' : tinyMCE.get('textareaMoreObjective').getContent()
						},
                        beforeSubmit:  function(){
                            //ตรวจสอบก่อนว่าใส่รหัสเงินหรือปล่าว
                            var paid_method = $('#paid_method').val();

                            if (paid_method=='other')
                            {
                                if ($('#other_code').val()=='' || $('#other_code').val()==undefined)
                                {
                                    warningAlert('ข้อมูลไม่ครบถ้วน !', 'กรุณากรอกรหัสหน่วยงาน 3 หลัก ยกเว้น 912');
                                    $('#submitForm').button('reset');
                                    $('#other_code').focus();
                                    return false;
                                }
                                else
                                {
                                    //check ต่อว่าหากต้องการบุคลากร LU ให้กรอกภาระงาน
                                    if ($('#request_for_lu_personnel').val()=='yes')
                                    {
                                        if ($('#request_lu_personnel_reason').val()=='' || $('#request_lu_personnel_reason').val()==undefined)
                                        {
                                            warningAlert('ข้อมูลไม่ครบถ้วน !', 'กรุณาระบุภาระงานที่ต้องการให้สนับสนุน');
                                            $('#submitForm').button('reset');
                                            return false;
                                        }
                                        else
                                        {
                                            //ข้อมูลครบถ้วน !!!
                                            $('#submitForm').prop('disabled', true);
                                            return true;
                                        }
                                    }
                                    else
                                    {
                                        //หากไม่ต้องการบุคลากรให้ true ไปเลย
                                        $('#submitForm').prop('disabled', true);
                                        return true;
                                    }
                                }
                            }
                            else if (paid_method=='donate')
                            {
                                if ($('#donate_code').val()=='' || $('#donate_code').val()==undefined)
                                {
                                    warningAlert('ข้อมูลไม่ครบถ้วน !', 'กรุณากรอกรหัสหน่วยงานที่สนับสนุนงบประมาณ');
                                    $('#submitForm').button('reset');
                                    $('#donate_code').focus();
                                    return false;
                                }
                                else
                                {
                                    //check ต่อว่าหากต้องการบุคลากร LU ให้กรอกภาระงาน
                                    if ($('#request_for_lu_personnel').val()=='yes')
                                    {
                                        if ($('#request_lu_personnel_reason').val()=='' || $('#request_lu_personnel_reason').val()==undefined)
                                        {
                                            warningAlert('ข้อมูลไม่ครบถ้วน !', 'กรุณาระบุภาระงานที่ต้องการให้สนับสนุน');
                                            $('#submitForm').button('reset');
                                            return false;
                                        }
                                        else
                                        {
                                            //ข้อมูลครบถ้วน !!!
                                            $('#submitForm').prop('disabled', true);
                                            return true;
                                        }
                                    }
                                    else
                                    {
                                        //หากไม่ต้องการบุคลากรให้ true ไปเลย
                                        $('#submitForm').prop('disabled', true);
                                        return true;
                                    }
                                }
                            }
                            else if (paid_method=='absorb')
                            {
                                if ($('#absorb_code').val()=='' || $('#absorb_code').val()==undefined)
                                {
                                    warningAlert('ข้อมูลไม่ครบถ้วน !', 'กรุณากรอกรหัสหน่วยงานที่ช่วยออกค่าใช้จ่าย');
                                    $('#submitForm').button('reset');
                                    $('#absorb_code').focus();
                                    return false;
                                }
                                else
                                {
                                    //check ต่อว่าหากต้องการบุคลากร LU ให้กรอกภาระงาน
                                    if ($('#request_for_lu_personnel').val()=='yes')
                                    {
                                        if ($('#request_lu_personnel_reason').val()=='' || $('#request_lu_personnel_reason').val()==undefined)
                                        {
                                            warningAlert('ข้อมูลไม่ครบถ้วน !', 'กรุณาระบุภาระงานที่ต้องการให้สนับสนุน');
                                            $('#submitForm').button('reset');
                                            return false;
                                        }
                                        else
                                        {
                                            //ข้อมูลครบถ้วน !!!
                                            $('#submitForm').prop('disabled', true);
                                            return true;
                                        }
                                    }
                                    else
                                    {
                                        //หากไม่ต้องการบุคลากรให้ true ไปเลย
                                        $('#submitForm').prop('disabled', true);
                                        return true;
                                    }
                                }
                            }
                            else
                            {
                                //check ต่อว่าหากต้องการบุคลากร LU ให้กรอกภาระงาน
                                if ($('#request_for_lu_personnel').val()=='yes')
                                {
                                    if ($('#request_lu_personnel_reason').val()=='' || $('#request_lu_personnel_reason').val()==undefined)
                                    {
                                        warningAlert('ข้อมูลไม่ครบถ้วน !', 'กรุณาระบุภาระงานที่ต้องการให้สนับสนุน');
                                        $('#submitForm').button('reset');
                                        return false;
                                    }
                                    else
                                    {
                                        //ข้อมูลครบถ้วน !!!
                                        $('#submitForm').prop('disabled', true);
                                        return true;
                                    }
                                }
                                else
                                {
                                    //หากไม่ต้องการบุคลากรให้ true ไปเลย
                                    $('#submitForm').prop('disabled', true);
                                    return true;
                                }
                            }
                        },  // pre-submit callback
                        success: function(data){
                            $('#submitForm').button('reset');

                            $('#submitForm').prop('disabled', false);
                            if (data.status==='success')
                            {
                                //alert success
                                successAlert('บันทึกรายการสำเร็จ !', data.msg);
                                //change budget code
                                $('#inputCustomerCode-addon').html(data.party.budget_code);
                            }
                            else
                            {
                                //alert error
                                errorAlert('บันทึกไม่สำเร็จ !', data.msg);
                            }
                        }  // post-submit callback
                    };

                    $('#formEditParty').ajaxSubmit(options);

                }
            });
            /*Control Date Range*/
            $('#dateStart, #dateEnd, #postponeStart, #postponeEnd').datetimepicker({
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

            $("#postponeStart").on("dp.change",function (e) {

                var old_range_count = parseInt("{{ $party->period() }}");

                $('#postponeEnd').data("DateTimePicker").setMinDate(moment(e.date).add(old_range_count-1,'days'));
                $('#postponeEnd').data("DateTimePicker").setValue(moment(e.date).add(old_range_count-1,'days'));
            });
            $("#postponeEnd").on("dp.change",function (e) {
                $('#postponeStart').data("DateTimePicker").setMaxDate(e.date);
            });

            $('#party_coordinator_selected').select2();

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

            /*post edit*/
            $('#party_status_selected').on('change', function()
            {
                changeStatus($('input[name=_party_id]').val(),$(this).val());
            });
            /*Edit Coordinator for history*/
            $('#party_coordinator_selected').on('change', function(){
                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('PartyController@postCoordinatorHistory') }}",
                    data: {
                        '_token' : $('input[name=_token]').val(),
                        '_party_id' : $('input[name=_party_id]').val(),
                        'project_co' : $(this).val()
                    },
                    success: function (data) {
                        if (data.status=='success')
                        {
                            successAlert('ทำรายการสำเร็จ !', data.msg);
                        }
                        else
                        {
                            //else not success reset old value
                            errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                        }
                    },
                    dataType: 'json'
                });
            });
            /*Edit Summary Income for history*/
            $('#submit-summary-income').on('click', function(e){

                @if($count_plan_b>0)
                    var final_plan = 'A';
                @else
                    var final_plan = ($('#final_plan_a').is(':checked')) ? 'A' : 'B';
                @endif

                $.ajax({
                    type: "POST",
                    url: "{{ URL::action('PartyController@postSummaryIncomeHistory') }}",
                    data: {
                        '_token' : $('input[name=_token]').val(),
                        '_party_id' : $('input[name=_party_id]').val(),
                        'summary_income' : $('#party_summary_income').val(),
                        'final_plan' : final_plan
                    },
                    success: function (data) {

                        $('#input-history-summary-income').hide();

                        if (data.status=='success')
                        {
                            successAlert('ทำรายการสำเร็จ !', data.msg);
                        }
                        else
                        {
                            //else not success reset old value
                            errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                        }
                    },
                    dataType: 'json'
                });
            });

            /*check active tab when click tab load data*/
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                //check tab load party transaction history
                if ($(e.target).attr('id')=='party_transaction') {
                    loadTransactionHistories({{ $party->id }});
                }
                //check tab load party sharepoint
                if ($(e.target).attr('id')=='party_sharepoint') {
                    loadSharepoints({{ $party->id }});
                }
				//check tab load party overall staff
                if ($(e.target).attr('id')=='party_staff') {
                    loadStaffs({{ $party->id }});
                }
            });

            /*click for upload panel*/
            $('#uploadFiles').on('click', function(e){
                $('#modalUpload').modal('show');
            });

            /*click to show sharepoint */
            $('#addSharepoint').on('click', function(e){
                $('#sharepoint_id').val(0);//set zero if create new
                $('#modalSharepoint').modal('show');
            });
			
			/*click to show บุคลากรที่ทำงาน */
            $('#addStaffWork').on('click', function(e){
                $('#staff_id').val(0);//set zero if create new
                $('#modalStaff').modal('show');
            });

            /*submit form sharepoint*/
            $('#submitFormModalSharepoint').on('click', function(){

                $('#submitFormModalSharepoint').button('loading');

                $.ajax({
                    type: "POST",
                    url: "{{ URL::to('party/create-or-update-sharepoint') }}",
                    data: {
                        '_token' : $('input[name=_token]').val(),
                        'id' : $('#sharepoint_id').val(),
                        'party_id' : $('#partySharepoint').val(),
                        'type' : $('#comboSharepointType').val(),
                        'title' : $('#inputSharepointTitle').val(),
                        'url' : $('#inputSharepointUrl').val()
                    },
                    success: function (data) {

                        $('#submitFormModalSharepoint').button('reset');
                        $('#modalSharepoint').modal('hide');

                        if (data.status=='success')
                        {
                            successAlert('ทำรายการสำเร็จ !', data.msg);

                            loadSharepoints(data.party_id);
                        }
                        else
                        {
                            //else not success reset old value
                            errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                        }
                    },
                    dataType: 'json'
                });
            });
			
			/*submit form Staff Work*/
            $('#submitFormModalStaff').on('click', function(){

                $('#submitFormModalStaff').button('loading');

                $.ajax({
                    type: "POST",
                    url: "{{ URL::to('party/create-or-update-staff') }}",
                    data: {
                        '_token' : $('input[name=_token]').val(),
                        'id' : $('#staff_id').val(),
                        'party_id' : $('#partyStaff').val(),
                        'personnel_id' : $('#comboPersonnel').val(),
                        'works' : $('#comboWorks').val()
                    },
                    success: function (data) {

                        $('#submitFormModalStaff').button('reset');
                        $('#modalStaff').modal('hide');

                        if (data.status=='success')
                        {
                            successAlert('ทำรายการข้อมูลบุคลากรสำเร็จ !', data.msg);

                            loadStaffs(data.party_id);
                        }
                        else
                        {
                            //else not success reset old value
                            errorAlert('ทำรายการข้อมูลบุคลากรไม่สำเร็จ !', data.msg);
                        }
                    },
                    dataType: 'json'
                });
            });

            /*Submit uploading files*/
            $('#formModalUpload').submit(function(){
                //waiting button
                $('#submitFormModalUpload').button('loading');
            });

            /*Change Postpone Type*/
            $('input[type=radio][name=radioPostponeType]').on('change', function(){
                if ($('input[type=radio][name=radioPostponeType]:checked').val()=='postpone')
                {
                    //เคสเลื่อนไม่มี-กำหนดการ
                    $('#re-schedule').hide();
                    $('#inputPostponeStart, #inputPostponeEnd').val('');
                    $('#actionSelected').val('postpone');
                }
                else
                {
                    //เคสเลื่อนแบบมีกำหนดการ
                    $('#re-schedule').show();
                    $('#actionSelected').val('re-schedule');
                }
            });

            /*submit form actions for this party*/
            $('#submitFormModalAction').on('click', function(){

                //check when re-schedule new date accquire start and end date
                if ($('#actionSelected').val()=='re-schedule')
                {
                    if ($('#inputPostponeStart').val()=='' || $('#inputPostponeEnd').val()=='')
                    {
                        $('#action-error').empty().append('<div class="alert alert-danger" role="alert">กรุณากรอกวันที่เลื่อนกำหนดการใหม่ !</div>');
                        return false;
                    }
                }

                //check force fill in reason
                if (tinyMCE.get('textareaActionNote').getContent().trim().length == 0)
                {
                    $('#action-error').empty().append('<div class="alert alert-danger" role="alert">กรุณากรอกเหตุผล !</div>');
                    return false;
                }

                $('#submitFormModalAction').button('loading');

                $.ajax({
                    type: "POST",
                    url: "{{ URL::to('party/actions') }}",
                    data: {
                        '_token' : $('form#formModalActions > input[name=_token]').val(),
                        'party_id' : '{{ $party->id }}',
                        'action' : $('#actionSelected').val(),
                        'new_start_date' : $('#inputPostponeStart').val(),
                        'new_end_date' : $('#inputPostponeEnd').val(),
                        'note' : tinyMCE.get('textareaActionNote').getContent()
                    },
                    success: function (data) {

                        $('#submitFormModalAction').button('reset');
                        $('#modalActions').modal('hide');

                        if (data.status=='success')
                        {
                            successAlert('ทำรายการสำเร็จ !', data.msg);
                            location.reload();
                        }
                        else
                        {
                            //else not success reset old value
                            errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                        }
                    },
                    dataType: 'json'
                });
            });

        });
		
		/*Enable TinyMce Rich Text*/
		tinymce.init({ 
			selector:'textarea#textareaMoreObjective',
            setup: function (editor) {
                editor.addButton('largeview', {
                    text: 'มุมมองเต็ม',
                    icon: false,
                    onclick: function ()
                    {
                        $('#textReadModal').modal('show');
                        $('#textReadModalContent').empty().html(editor.getContent());
                    }
                });
            },
            menubar: false,
            statusbar: false,
            toolbar: [
                'undo redo | styleselect | bold italic | largeview'
            ],
			language: 'th_TH'
		});

        tinymce.init({
            selector:'textarea#textareaActionNote',
            menubar: false,
            statusbar: false,
            toolbar: [
                'undo redo | bold italic'
            ],
            language: 'th_TH'
        });

        /*function update status by combo*/
        function changeStatus(party_id,status)
        {
            $.ajax({
                type: "POST",
                url: "{{ URL::action('PartyController@postStatus') }}",
                data: {
                    '_token' : $('input[name=_token]').val(),
                    '_party_id' : party_id,
                    'status' : status
                },
                success: function (data) {
                    if (data.status=='success')
                    {
                        successAlert('ทำรายการสำเร็จ !', 'เปลี่ยนสถานะเรียบร้อยแล้ว');
                        //$(this).prop('disabled', false);
                        if (status=='finished')
                        {
                            location.reload();
                        }
                    }
                    else
                    {
                        //else not success reset old value
                        errorAlert('ทำรายการไม่สำเร็จ !', 'บันทึกไม่สำเร็จ');
                        //$(this).prop('disabled', false);
                        $('#party_status_selected').val('{{ $party->status }}');
                    }
                },
                dataType: 'json'
            });
        }

        /*function load party transaction*/
        function loadTransactionHistories(party_id)
        {
            $.ajax({
                type: "POST",
                url: "{{ URL::action('PartyController@getTransaction') }}",
                data: {
                    '_token' : $('input[name=_token]').val(),
                    'party_id' : party_id
                },
                success: function (data) {
                    $('#table_party_transaction > tbody').empty();
                    $(data).each(function(index, item){
                        var html = '';
                        html += '<tr>';
                        html += '<td>' + item.name + '</td>';
                        html += '<td>' + item.by + '</td>';
                        html += '<td>' + item.day + '</td>';
                        html += '<td>' + item.reason + '</td>';
                        html += '</tr>';
                        $('#table_party_transaction > tbody').append(html);
                    });
                },
                dataType: 'json'
            });
        }

        /*function load party sharepoint*/
        function loadSharepoints(party_id)
        {
            $.ajax({
                url: "{{ URL::to('party/sharepoint') }}",
                data: {
                    '_token' : $('input[name=_token]').val(),
                    'party_id' : party_id
                },
                success: function (data) {
                    $('#table_party_sharepoint > tbody').empty();
                    $(data).each(function(index, item){
                        var html = '';
                        html += '<tr>';
                        html += '<td>' + item.type + '</td>';
                        html += '<td>' + item.title + '</td>';
                        html += '<td><a href="' + item.url + '" target="_blank">คลิกเพื่อชม</a></td>';
                        html += '<td><a class="btn btn-xs btn-info" href="javascript:openEditSharepoint('+item.id+', \''+item.type+'\', \''+item.title+'\', \''+item.url+'\');" role="button"><i class="fa fa-pencil"></i> แก้ไข</a> <a class="btn btn-xs btn-danger" href="javascript:;" onclick="openDeleteSharepoint('+item.id+', \''+item.title+'\');" role="button"><i class="fa fa-minus"></i> ลบ</a></td>';
                        html += '</tr>';
                        $('#table_party_sharepoint > tbody').append(html);
                    });
                },
                dataType: 'json'
            });
        }
		
		/*function load party staff*/
		function loadStaffs(party_id)
		{
			$.ajax({
                url: "{{ URL::to('party/staffs') }}",
                data: {
                    '_token' : $('input[name=_token]').val(),
                    'party_id' : party_id
                },
                success: function (data) {
                    $('#table_party_staff > tbody').empty();
                    $(data).each(function(index, item){
                        var html = '';
						var staff_name = item.personnel.prefix + item.personnel.first_name + ' ' + item.personnel.last_name;
                        html += '<tr>';
                        html += '<td>' + staff_name + '</td>';
						
						var array_works = [];
						var text_works = "";
						$(item.works).each(function(inx, work){
							 text_works += work.name + ', ';
							 
							 array_works.push(work.work_type_id);
						});
						
						html += '<td>'+text_works.substring(0, text_works.length-2)+'</td>';
                        html += '<td><a class="btn btn-xs btn-info" href="javascript:openEditStaff('+item.id+', '+item.personnel_id+', \''+array_works+'\');" role="button"><i class="fa fa-pencil"></i> แก้ไข</a> <a class="btn btn-xs btn-danger" href="javascript:;" onclick="openDeleteStaff('+item.id+', \''+staff_name+'\');" role="button"><i class="fa fa-minus"></i> ลบ</a></td>';
                        html += '</tr>';
                        $('#table_party_staff > tbody').append(html);
                    });
                },
                dataType: 'json'
            });
		}

		/*Edit Popup Sharepoint*/
        function openEditSharepoint(id, type, title, url)
        {
            //open modal
            $('#modalSharepoint').modal('show');
            //set parameter
            $('#sharepoint_id').val(id);
            $('#comboSharepointType').val(type).change();
            $('#inputSharepointTitle').val(title);
            $('#inputSharepointUrl').val(url);
        }
		
		/*Edit popup staff*/
		function openEditStaff(id, personnel_id, works)
		{
			//open modal
            $('#modalStaff').modal('show');
			 //set parameter
            $('#staff_id').val(id);
            $('#comboPersonnel').val(personnel_id).change();
			//set array value before change
			var id_works = [];
			var w = works.split(',');
			$(w).each(function(index, work){
				id_works.push(work);
			});
			
			$('#comboWorks').select2().val(id_works).change();
		}

		/*delete sharepoint*/
        function openDeleteSharepoint(id, title)
        {
            var a = confirm("ท่านต้องการลบข้อมูล " + title + " นี้หรือไม่");

            if (a)
            {
                $.ajax({
                    type: "POST",
                    url: "{{ URL::to('party/delete-sharepoint') }}",
                    data: {
                        '_token' : $('input[name=_token]').val(),
                        'id' : id,
                        'party_id' : $('#partySharepoint').val()
                    },
                    success: function (data) {
                        if (data.status=='success')
                        {
                            successAlert('ทำรายการสำเร็จ !', data.msg);

                            loadSharepoints(data.party_id);
                        }
                        else
                        {
                            //else not success reset old value
                            errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                        }
                    },
                    dataType: 'json'
                });
            }
            else
            {
                return false;
            }

        }
		
		/*delete overall work*/
        function openDeleteStaff(id, title)
        {
            var a = confirm("ท่านต้องการลบภาระงานบุคลากร " + title + " นี้หรือไม่");

            if (a)
            {
                $.ajax({
                    type: "POST",
                    url: "{{{ URL::to('party/delete-staff') }}}",
                    data: {
                        '_token' : $('input[name=_token]').val(),
                        'id' : id,
                        'party_id' : $('#partyStaff').val()
                    },
                    success: function (data) {
                        if (data.status=='success')
                        {
                            successAlert('ทำรายการสำเร็จ !', data.msg);

                            loadStaffs(data.party_id);
                        }
                        else
                        {
                            //else not success reset old value
                            errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                        }
                    },
                    dataType: 'json'
                });
            }
            else
            {
                return false;
            }

        }

        /*open overall task to action*/
        function openActions(action)
        {
            //show a modal
            $('#modalActions').modal('show');
            $('#action-error').empty();
            //set action type
            $('#actionSelected').val(action);
            //set a title
            var actionTitle = "";
            //check to show a title and show a form
            switch (action)
            {
                case 'terminated' :
                    actionTitle = "<span class='fa fa-ban'></span> ยกเลิกคณะดูงาน";
                    $('#actionTerminated').show();
                    $('#actionPostpone').hide();
                    @if($party->period()>1)
                        $('#re-schedule-warning').alert('close');
                    @endif
                    break;
                default :
                    actionTitle = "<span class='fa fa-lg fa-angle-double-right'></span> เลื่อนกำหนดการ";
                    $('#actionPostpone').show();
                    $('#actionTerminated').hide();
                    $('input[type=radio][name=radioPostponeType]').change();
                    @if($party->period()>1)
                        $('#re-schedule-warning').alert();
                    @endif
            }
            //show a title
            $('#titleAction').html(actionTitle);
        }
		
        /*function load party document*/
        function loadDocumentJson(party_id)
        {
            $.ajax({
                type: "POST",
                url: "{{ URL::action('PartyController@getDocumentJson') }}",
                data: {
                    '_token' : $('input[name=_token]').val(),
                    'party_id' : party_id
                },
                success: function (data) {
                    //use bootstrap treeview show
                    var data = data.data;

                    if (data.length>0)
                    {
                        var tree = $('#folder_tree').jstree({
                            'core' :
                            {
                                'data' : data
                            } ,
                            "plugins" : [ "contextmenu" ],
                            "contextmenu":{
                                "items": function($node) {

                                    @if(Auth::check())
                                        @if(Auth::user()->hasRole('manager'))
                                            return {
                                                "View": {
                                                    "icon" : "fa fa-eye",
                                                    "separator_before": false,
                                                    "separator_after": false,
                                                    "label": "เรียกดู",
                                                    "action": function (obj) {
                                                        window.location.href = $node.a_attr.href;
                                                    }
                                                },
                                                "Remove": {
                                                    "icon" : "fa fa-trash-o",
                                                    "separator_before": false,
                                                    "separator_after": false,
                                                    "label": "ลบไฟล์นี้",
                                                    "action": function (obj) {
                                                        deleteFile($node.a_attr.rel,$node.a_attr.id);
                                                    }
                                                }
                                            };
                                        @elseif(Auth::user()->hasRole('project coordinator') || Auth::user()->hasRole('reviewer'))
                                            return {
                                                "View": {
                                                    "icon" : "fa fa-eye",
                                                    "separator_before": false,
                                                    "separator_after": false,
                                                    "label": "เรียกดู",
                                                    "action": function (obj) {
                                                        window.location.href = $node.a_attr.href;
                                                    }
                                                }
                                            };
                                        @else
                                            return {};
                                        @endif
                                    @endif

                                }
                            }
                        })
                        .on("activate_node.jstree", function(e,data){
                           //window.location.href = data.node.a_attr.href;
                        })
                        .on('select_node.jstree', function (e, data) {
                            //window.location.href = data.node.a_attr.href;
                        });
                    }
                    else
                    {
                        $('#folder_tree').html('<i>ไม่มีเอกสารในระบบ</i>');
                    }
                },
                dataType: 'json'
            });
        }

        /*template select*/
        function formatCountry (countries) {
            var public_path = 'http://lu.maefahluang.org:8080/svms/public';
            if (!countries.id) { return countries.text; }
            var countryFormat = '<span><img src="'+public_path+'/assets/img/flags/' + countries.id + '.png" class="img-flag" /> ' + countries.text + '</span>';

            return countryFormat;
        }
		
		function formatPersonnel (personnels) {
            if (!personnels.id) { return personnels.text; }
            var template = '<span>' + personnels.text + '</span>';

            return template;
        }

        /*Delete Contact*/
        function delContract(id)
        {
            $('#addr'+id).html('');
        }

        /*Delete File*/
        function deleteFile(strPath, fileId)
        {
            var buttons = [{
                label: 'ตกลง',
                cssClass: 'btn-primary',
                action: function(dialogItself){
                    dialogItself.close();

                    $.ajax({
                        url: "{{ URL::action('PartyController@getDeleteFile') }}",
                        data:
                        {
                            'party_id' : {{ $party->id }},
                            'path' : strPath,
                            'file_id' : fileId
                        }
                    }).done(function(data) {
                        if(data.status==='success')
                        {
                            successAlert('ทำรายการสำเร็จ !', data.msg);

                            setTimeout(function(){
                                location.reload();
                            }, 1000);
                        }
                        else
                        {
                            errorAlert('ทำรายการไม่สำเร็จ !', data.msg);
                        }
                    });
                }
            }, {
                label: 'ยกเลิก',
                action: function(dialogItself){
                    dialogItself.close();
                }
            }];

            warningButton('กรุณายืนยันคำสั่ง', 'ท่านต้องการลบไฟล์นี้หรือไม่', buttons);

        }

    </script>

@stop