@extends('svms.layouts.landing')

@section('title')
    @parent ::แบบฟอร์มกรอกข้อมูลการร้องขอคณะดูงาน
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
    {{--Use Tiny MCE--}}
    {{ HTML::script('dependencies/tinymce/js/tinymce/tinymce.min.js') }}
    {{--Use Tiny MCE placeholder--}}
    {{ HTML::script('dependencies/tinymce-placeholder-master/placeholder/plugin.min.js') }}
@stop

@section('extraStyles')
    <style type="text/css">
        .img-flag {
            margin-top: -3px;
        }
    </style>
@stop

@section('header')
    @if(isset($party))
        @if($party->latestStatus()=='pending')
            <span class="fa fa-pencil"></span>
            แก้ไขข้อมูลการร้องขอคณะดูงาน
        @elseif($party->latestStatus()=='editing')
            <span class="fa fa-envelope-o"></span>
            ร้องขอการแก้ไขข้อมูลการร้องขอคณะดูงานจากผู้อนุมัติ ครั้งที่ {{ $party->numberOfEditing() }}
        @else
            <span class="fa fa-envelope"></span>
            แบบฟอร์มกรอกข้อมูลการร้องขอคณะดูงาน
        @endif
    @else
        <span class="fa fa-envelope"></span>
        แบบฟอร์มกรอกข้อมูลการร้องขอคณะดูงาน
    @endif
@stop

@section('content')

    <div class="panel panel-default">
        <div class="panel-body">

            <form class="form-horizontal" role="form" id="formParty" enctype="multipart/form-data">

                @if(isset($party))
                    {{--This case edit by yourself before send to reviewer status:pending--}}
                    @if($party->latestStatus()=='pending')
                        <div class="alert alert-info" role="alert">
                            <strong>สถานะ :</strong>
                            สร้างคำร้องคณะศึกษาดูงานแล้ว กำลังจะยื่นคำร้องให้ผู้ตรวจสอบ
                        </div>
                        <!-- State -->
                        <input type="hidden" name="state" value="editByYourself" />
                        <!-- ./ State -->
                    @endif
                    {{--This is case for pending state edit by request status:editing--}}
                    @if($party->latestStatus()=='editing')
                        <div class="alert alert-info" role="alert">
                            <strong>สถานะ :</strong>
                            ขอให้แก้ไข/เพิ่มเติมข้อมูลจาก{{ $party->review_person }} ครั้งที่ {{ $party->numberOfEditing() }}
                            <br/>
                            <strong>ความเห็น/ข้อมูลที่ผู้ตรวจสอบขอให้เพิ่มเติม :</strong> {{ $party->review_note }}
                            <br/>
                            <strong>ข้อมูลแก้ไข/เพิ่มเติมจากผู้ยื่นคำร้อง :</strong>
                            <div>
                                <textarea id="textareaEditNote" name="edit_response_note" class="form-control" placeholder="กรุณาบอกสิ่งที่ท่านได้แก้ไขในแบบฟอร์มนี้" required></textarea>
                            </div>
                        </div>
                        <!-- State -->
                        <input type="hidden" name="state" value="editByRequest" />
                        <!-- ./ State -->
                    @endif
                @else
                    <div class="alert alert-danger" role="alert">
                        <h4><i class="fa fa-exclamation-circle" aria-hidden="true"></i> ข้อควรปฎิบัติ</h4>
                        <ol>
                            <li>กรุณาตรวจสอบ"<a href="{{ URL::to('calendar') }}">ปฎิทินคณะดูงาน</a>"ก่อนกรอกช่วงวันที่ดูงาน</li>
                            <li>กรุณากรอกข้อมูลในช่องที่มีเครื่องหมาย * ให้ครบถ้วน</li>
                            <li>กรุณาแนบไฟล์ ศทบ.01 ที่มีลายเซ็นกำกับและมีรหัสงานรับรองค่าใช้จ่ายที่ถูกต้องเพื่อที่จะได้อำนวยความสะดวกแก่ผู้ปฎิบัติงานในการติดต่อประสานงาน</li>
                            <li>กรุณาสอบถามทางคณะว่าต้องการที่พักหรือไม่ หากคณะต้องการที่พักให้ระบุในหัวข้อ <strong>"ต้องการใช้ห้องพัก"</strong> เลือก <strong>"ใช่"</strong> พร้อมกรอกรายละเอียด</li>
                            <li>กรุณากรอกรายละเอียดเพิ่มเติมให้ชัดเจน เช่น หากมีการเตรียมการเรื่องยานยนต์และอาหารเรียบร้อยแล้ว หรือหมายกำหนดการคร่าวๆกรุณาแจ้งมาในรายละเอียด เป็นต้น</li>
                        </ol>
                    </div>
                    <!-- State -->
                    <input type="hidden" name="state" value="firstRequest" />
                    <!-- ./ State -->
                @endif

                <!-- CSRF Token -->
                <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                <!-- ./ csrf token -->

                <!-- Encrypt -->
                <input type="hidden" name="encrypt" value="{{ (isset($party)) ? $party->encrypt : 'null' }}" />
                <!-- ./ encrypt -->

                <div class="form-group">
                    <label for="inputName" class="col-sm-3 control-label">ชื่อคณะดูงาน *</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="name" id="inputName" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="comboCountry" class="col-sm-3 control-label">มาจาก *</label>
                    <div class="col-sm-9">
                        <label class="radio-inline">
                            <input type="radio" name="radioFromCountry" id="radioFromCountry1" value="th" checked="checked"> <i><img src="{{ asset('assets/img/flags/th.png') }}" class="img-flag" /></i> ในประเทศไทย
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="radioFromCountry" id="radioFromCountry2" value="other"> ประเทศอื่นๆ
                        </label>
                        <div id="divCountrySelect" style="margin-top: 5px; display: none;">
                            <select class="form-control" name="countries[]" id="comboCountry" multiple="multiple" style="width: 100%">
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="comboType" class="col-sm-3 control-label">ประเภทคณะ *</label>
                    <div class="col-sm-9">
                        <select class="form-control" name="party_type_id" id="comboType">
                            <option value="" selected disabled>เลือก</option>
                            @foreach($partyTypes as $partyType)
                                <option value="{{ $partyType->ID }}">{{ $partyType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="numberQuantity" class="col-sm-3 control-label">จำนวนผู้เข้าร่วม *</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="number" class="form-control" name="people_quantity" id="numberQuantity" max="9999" value="1" min="1" aria-describedby="numberQuantity-addon" required>
                            <span class="input-group-addon" id="numberQuantity-addon">คน</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputDateStart" class="col-sm-3 control-label">ช่วงวันที่มา *</label>
                    <div class="col-sm-3">
                        <div class='input-group date' id='dateStart'>
                            <input type='text' class="form-control" data-date-format="DD/MM/YYYY" name="start_date" id="inputDateStart" placeholder="เริ่มวันที่" required />
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class='input-group date' id='dateEnd'>
                            <input type='text' class="form-control" data-date-format="DD/MM/YYYY" name="end_date" id="inputDateEnd" placeholder="ถึงวันที่" required />
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="comboObjective" class="col-sm-3 control-label">วัตถุประสงค์การมา *</label>
                    <div class="col-sm-9">
                        <select class="form-control" name="objectives[]" id="comboObjective" multiple="multiple" style="width: 100%" required>
                            @foreach($partyObjectives as $partyObjective)
                                <option value="{{ $partyObjective->id }}">{{ $partyObjective->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputInterested" class="col-sm-3 control-label">ประเด็นที่สนใจเป็นพิเศษ *</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="interested" id="inputInterested" placeholder="" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputExpected" class="col-sm-3 control-label">ความคาดหวังในการศึกษาดูงาน *</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="expected" id="inputExpected" placeholder="กรุณาระบุความคาดหวังในมุมมองของคณะศึกษาดูงาน" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="comboLocationBase" class="col-sm-3 control-label">พื้นที่ศึกษาดูงาน <br/>(สามารถระบุได้มากกว่า 1 พื้นที่)</label>
                    <div class="col-sm-9">
                        <select class="form-control" name="location_bases[]" id="comboLocationBase" multiple="multiple" style="width: 100%" required>
                            @foreach($mflfAreas as $mflfArea)
                                <option value="{{ $mflfArea->id }}" {{{ ($mflfArea->id==1) ? 'selected' : '' }}}>{{ $mflfArea->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">เคยเข้าร่วมศึกษาดูงาน</label>
                    <div class="col-sm-9">
                        <label class="radio-inline">
                            <input type="radio" name="joined" id="radioJoined1" value="never"> ครั้งแรก
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="joined" id="radioJoined2" value="ever"> เคยมาเข้าร่วมแล้ว
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="joined" id="radioJoined3" value="null" checked> ไม่แน่ใจ
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="textareaMoreObjective" class="col-sm-3 control-label">รายละเอียดเพิ่มเติม</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" name="objective_detail" id="textareaMoreObjective" rows="4">{{ (isset($party)) ? $party->objective_detail : "" }}</textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label for="fileAddition" class="col-sm-3 control-label">แนบไฟล์อ้างอิง เช่น จดหมายต้นเรื่อง เป็นต้น (pdf เท่านั้น)</label>
                    <div class="col-sm-9">
                        @if(isset($party))
                            @if($party->fileUrl())
                                <div class="alert alert-success" role="alert"><i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                                    ท่านได้ทำการอัพโหลดไฟล์ต้นเรื่องไปแล้ว สามารถตรวจสอบได้<a href="{{ $party->fileUrl() }}" target="_blank">ที่นี่</a>หากต้องการอัพโหลดไฟล์ใหม่กรุณาคลิกที่กล่องอัพโหลดด้านล่าง</div>
                            @else
                                <div class="alert alert-warning" role="alert"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                    ท่านยังไม่ได้อัพโหลดไฟล์ต้นเรื่อง กรุณาอัพโหลดโดยคลิกที่กล่องอัพโหลดด้านล่าง</div>
                            @endif
                        @endif
                        <input type="file" name="file" id="fileAddition">
                    </div>
                </div>

                {{--เพิ่มเติม ศทบ01 ต้องเพิ่มก็ต่อเมื่อไม่ใช่ทีมงาน lu หรือธุรการ--}}
                @if(Auth::check())
                    @if(!Auth::user()->canFastTrack())
                        <div class="form-group">
                            <label for="fileTravel01" class="col-sm-3 control-label">แนบไฟล์ศทบ.01 (pdf เท่านั้น) *</label>
                            <div class="col-sm-9">
                                @if(isset($party))
                                    @if($party->fileUrl('travel01'))
                                        <div class="alert alert-success" role="alert"><i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                                            ท่านได้ทำการอัพโหลดไฟล์ศทบ.01 สามารถตรวจสอบได้<a href="{{ $party->fileUrl('travel01') }}" target="_blank">ที่นี่</a>หากต้องการอัพโหลดไฟล์ใหม่กรุณาคลิกที่กล่องอัพโหลดด้านล่าง</div>
                                    @else
                                        <div class="alert alert-warning" role="alert"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                            ท่านยังไม่ได้อัพโหลดไฟล์ศทบ.01 กรุณาอัพโหลดโดยคลิกที่กล่องอัพโหลดด้านล่าง</div>
                                    @endif
                                @endif
                                <input type="file" name="travel01_file" id="fileTravel01" required>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="form-group">
                        <label for="fileTravel01" class="col-sm-3 control-label">แนบไฟล์ศทบ.01 (pdf เท่านั้น) *</label>
                        <div class="col-sm-9">
                            @if(isset($party))
                                @if($party->fileUrl('travel01'))
                                    <div class="alert alert-success" role="alert"><i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                                        ท่านได้ทำการอัพโหลดไฟล์ศทบ.01 สามารถตรวจสอบได้<a href="{{ $party->fileUrl('travel01') }}" target="_blank">ที่นี่</a>หากต้องการอัพโหลดไฟล์ใหม่กรุณาคลิกที่กล่องอัพโหลดด้านล่าง</div>
                                @else
                                    <div class="alert alert-warning" role="alert"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                        ท่านยังไม่ได้อัพโหลดไฟล์ศทบ.01 กรุณาอัพโหลดโดยคลิกที่กล่องอัพโหลดด้านล่าง</div>
                                @endif

                                <input type="file" name="travel01_file" id="fileTravel01" {{ ($party->fileUrl('travel01')) ? '' : 'required' }}>
                            @else
                                <input type="file" name="travel01_file" id="fileTravel01" required>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    <label class="col-sm-3 control-label">ผู้ประสานงานของคณะที่มา<br/>(ต้องการอย่างน้อย 1 ท่าน)</label>
                    <div class="col-sm-9">
                        <table class="table table-bordered table-hover" id="tab_logic">
                            <thead>
                            <tr >
                                <th class="text-center">
                                    ชื่อ - สกุล *
                                </th>
                                <th class="text-center">
                                    E-mail
                                </th>
                                <th class="text-center">
                                    เบอร์มือถือ *
                                </th>
                                <th class="text-center">
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                                @if(isset($party))
                                    @for($c=0;$c<count($party['coordinators']);$c++)
                                        <tr id='addr{{ $c }}'>
                                            <td>
                                                <input type="text" name='coor_name[]' value="{{ $party['coordinators'][$c]['name'] }}" class="form-control" required/>
                                            </td>
                                            <td>
                                                <input type="email" name='coor_email[]' value="{{ $party['coordinators'][$c]['email'] }}" class="form-control"/>
                                            </td>
                                            <td>
                                                <input type="text" name='coor_mobile[]' value="{{ $party['coordinators'][$c]['mobile'] }}" class="form-control" required/>
                                            </td>
                                            <td></td>
                                        </tr>
                                    @endfor
                                    <tr id='addr{{ count($party['coordinators']) }}'></tr>
                                @else
                                    <tr id='addr0'>
                                        <td>
                                            <input type="text" name='coor_name[]'  placeholder='' class="form-control" required/>
                                        </td>
                                        <td>
                                            <input type="email" name='coor_email[]' placeholder='' class="form-control"/>
                                        </td>
                                        <td>
                                            <input type="text" name='coor_mobile[]' placeholder='' class="form-control" required/>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr id='addr1'></tr>
                                @endif
                            </tbody>
                        </table>
                        <a id="add_contact" class="btn btn-default pull-right"><span class="fa fa-plus"></span> เพิ่มผู้ประสานงาน</a>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">ต้องการใช้หรือจองห้องพัก ?<br/>(หาก"ใช่"กรอกข้อมูลเพิ่มเติมด้านล่าง)</label>
                    <div class="col-sm-9">
                        <label class="radio-inline">
                            <input name="request_accommodation" type="radio" id="request_accommodation_yes" value="yes"> ใช่
                        </label>
                        <label class="radio-inline">
                            <input name="request_accommodation" type="radio" id="request_accommodation_no" value="no" checked> ไม่ใช่
                        </label>

                        <div id="request_accommodation_info" style="display: none;">
                            <textarea placeholder="กรุณากรอกที่พักที่ต้องการจอง เพื่อระบุแนบไปใน Email" name="request_accommodation_information" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                {{--Addition Request for lu personnel--}}
                <div class="form-group">
                    <label class="col-sm-3 control-label">ต้องการสนับสนุนบุคลากรจากมหาวิทยาลัยที่มีชีวิต ?</label>
                    <div class="col-sm-5">
                        <select class="form-control" name="request_for_lu_personnel" id="request_for_lu_personnel">
                            <option value="no">ไม่ต้องการ</option>
                            <option value="yes">ต้องการการสนับสนุน</option>
                        </select>
                    </div>
                </div>
                {{--Addition Request for lu personnel Job Select--}}
                <div id="request_for_lu_personnel_yes" style="display: none;" class="form-group">
                    <label class="col-sm-3 control-label">ระบุภาระงานที่ต้องการให้สนับสนุน *</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" id="request_lu_personnel_reason" name="request_lu_personnel_reason" placeholder="กรุณาระบุให้ชัดเจน เช่น ต้องการการบรรยาย หรือจัดการการศึกษาดูงาน"></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-3 col-sm-3 control-label">การชำระเงิน *</label>
                    <div class="col-md-5 col-sm-9">
                        <select class="form-control" name="paid_method" id="paid_method" required>
                            <option value="">กรุณาเลือก</option>
                            @foreach(array_keys($models) as $model)
                                <option value="{{ $model }}">{{ $models[$model] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-9" id="divOtherCode" style="display: none;">
                        <input type="text" id="other_code" name="other_code" class="form-control" maxlength="3" placeholder="รหัสหน่วยงานอื่น"/>
                    </div>
                    <div class="col-md-3 col-sm-9" id="divDonateCode" style="display: none;">
                        <input type="text" id="donate_code" name="donate_code" class="form-control" maxlength="9" placeholder="รหัสที่รับผิดชอบค่าใช้จ่ายทั้งหมด"/>
                    </div>
                    <div class="col-md-4 col-sm-9" id="divAbsorbCode" style="display: none;">
                        <input type="text" id="absorb_code" name="absorb_code" class="form-control" placeholder="รหัสที่ช่วยสนับสนุน ใส่ , เมื่อมีมากกว่า 1 รหัส"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">ผู้กรอกข้อมูล *</label>
                    <div class="col-sm-9" style="margin-left: -15px;">
                        <div class="col-sm-4">
                            @if(isset($party))
                                <input type="text" class="form-control" name="request_person_name" id="inputContName" placeholder="ชื่อ - สกุล" required>
                            @else
                                @if(isset($request_person) && Auth::check())
                                    <input type="text" class="form-control" name="request_person_name" id="inputContName" placeholder="ชื่อ - สกุล" value="{{ $request_person->getPersonnel()->first_name.' '.$request_person->getPersonnel()->last_name }}" required>
                                @else
                                    <input type="text" class="form-control" name="request_person_name" id="inputContName" placeholder="ชื่อ - สกุล" required>
                                @endif
                            @endif
                        </div>
                        <div class="col-sm-4" style="">
                            <div class="input-group">
                                @if(isset($party))
                                    <input type="text" name="request_person_email" id="inputContEmail" class="form-control" placeholder="E-mail" aria-describedby="inputContEmail-addon" required>
                                    <span class="input-group-addon" id="inputContEmail-addon">@doitung.org</span>
                                @else
                                    @if(isset($request_person) && Auth::check())
                                        <input type="text" name="request_person_email" id="inputContEmail" class="form-control" placeholder="E-mail" aria-describedby="inputContEmail-addon" value="{{ current(explode("@", $request_person->getPersonnel()->email)) }}" required>
                                        <span class="input-group-addon" id="inputContEmail-addon">@doitung.org</span>
                                    @else
                                        <input type="text" name="request_person_email" id="inputContEmail" class="form-control" placeholder="E-mail" aria-describedby="inputContEmail-addon" required>
                                        <span class="input-group-addon" id="inputContEmail-addon">@doitung.org</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-4" style="">
                            <input type="text" class="form-control" name="request_person_tel" id="inputContTel" placeholder="เบอร์ติดต่อกลับ(ภายใน)">
                        </div>
                    </div>
                </div>

                <div class="form-group pull-right">
                    <div class="col-sm-12">
                        <button type="button" class="btn btn-danger btn-lg collapse">
                            <span class="fa fa-undo" aria-hidden="true"></span>
                            ยกเลิก
                        </button>

                        <button id="submitForm" type="submit" class="btn btn-primary btn-lg" data-loading-text="กำลังบันทึกกรุณารอสักครู่..." autocomplete="off">
                            @if(isset($party))
                                <i class="fa fa-pencil" aria-hidden="true"></i>
                                แก้ไขคำร้อง
                            @else
                                <span class="fa fa-paper-plane" aria-hidden="true"></span>
                                ส่งคำร้อง
                            @endif
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script type="text/javascript">
        $(function () {

            var countries = {{ json_encode($countries) }};

            /*Add contact*/
            $("#add_contact").click(function(e){

                e.preventDefault();

                var i= $('#tab_logic > tbody > tr:has(td)').size();

                $('#addr'+i).html("<td><input name='coor_name[]' type='text' class='form-control' /> </td> <td><input  name='coor_email[]' type='email' class='form-control'></td><td><input  name='coor_mobile[]' type='text' class='form-control'></td> <td><button type='button' row='"+i+"' class='del_contact btn btn-link' onclick='delContract("+i+");'>ลบ</button></td>");

                $('#tab_logic').append('<tr id="addr'+(i+1)+'"></tr>');
                i++;

            });

            /*Validate Plugin*/
            $.validator.addMethod('filesize', function(value, element, param) {
                // param = size (en bytes)
                // element = element to validate (<input>)
                // value = value of the element (file name)
                return this.optional(element) || (element.files[0].size <= param)
            });

            /*Check paid method if donate*/
            $('#paid_method').on('change', function(e){
                e.preventDefault();

                if ($(this).val()=='other')
                {
                    $('#divOtherCode').show();
                    $('#divDonateCode').hide();
                    $('#divAbsorbCode').hide();
                }
                else if ($(this).val()=='donate')
                {
                    $('#divDonateCode').show();
                    $('#divOtherCode').hide();
                    $('#divAbsorbCode').hide();
                }
                else if ($(this).val()=='absorb')
                {
                    $('#divAbsorbCode').show();
                    $('#divDonateCode').hide();
                    $('#divOtherCode').hide();
                }
                else
                {
                    //case อื่นๆ นอกจาก 912 = 100%
                    $('#divDonateCode').hide();
                    $('#divOtherCode').hide();
                    $('#divAbsorbCode').hide();
                }
                //delete old value
                $('#donate_code').val('');
                $('#other_code').val('');
                $('#absorb_code').val('');
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

            /*check need accom*/
            $('input[name=request_accommodation]').on('change', function(e){
                e.preventDefault();

                if ($('#request_accommodation_yes').is(':checked'))
                {
                    $('#request_accommodation_info').show();
                }
                else
                {
                    $('#request_accommodation_info').hide();
                }
            });

            /*check if required lu personnel*/
            $('#request_for_lu_personnel').on('change', function(e){
                if ($(this).val()=='yes')
                {
                    $('#request_for_lu_personnel_yes').show();
                    $('#request_lu_personnel_reason').focus();
                }
                else
                {
                    $('#request_for_lu_personnel_yes').hide();
                }
            });

            //validate form
            $("#formParty").validate({
                rules: {
                    fileAddition: {
                        filesize: 512
                    }
                },
                messages: {
                    fileAddition: {
                        accept: "โปรดระบุเป็นไฟล์ pdf เท่านั้น",
                        filesize: "โปรดระบุไฟล์ไม่เกิน 500 KB"
                    }
                },
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
                        url: "{{ URL::to('request') }}",
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
                                $('#formParty').resetForm().clearForm();
                                $('#comboObjective').select2("val", "");
                                $('#comboCountry').select2("val", "");

                                //successAlert('ทำการส่งคำร้องสำเร็จ !', data.msg);//now is not used

                                /*When Request or Edit Success then redirect to notification page*/
                                window.location.href = data.url;
                            }
                            else
                            {
                                errorAlert('ส่งคำร้องไม่สำเร็จ !', data.msg);
                            }
                        }  // post-submit callback
                    };

                    $('form').ajaxSubmit(options);

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

            /*Control Input Country*/
            $("#comboCountry").select2({
                data: countries,
                templateResult: formatCountry,
                templateSelection: formatCountry
            });
            /*Control Multiple Input Objective*/
            $('#comboObjective').select2({
                'placeholder' : 'สามารถเลือกได้มากกว่า 1 วัตถุประสงค์'
            });
            /*Control Multiple Input Area*/
            $('#comboLocationBase').select2({
                'placeholder' : 'สามารถเลือกได้มากกว่า 1 พื้นที่ศึกษาดูงาน'
            });
        });

        /*Enable TinyMce Rich Text*/
        tinymce.init({
            selector:'textarea#textareaMoreObjective',
            language: 'th_TH',
            menubar: false
        });

        /*template select*/
        function formatCountry (countries) {
            var public_path = 'http://lu.maefahluang.org:8080/svms/public';
            if (!countries.id) { return countries.text; }
            var countryFormat = '<span><img src="'+public_path+'/assets/img/flags/' + countries.id + '.png" class="img-flag" /> ' + countries.text + '</span>';

            return countryFormat;
        }

        /*Delete Contact*/
        function delContract(id)
        {
            $('#addr'+id).html('');
        }

        /*When Dom is ready and editing by reviewer*/
        @if(isset($party))
            $(document).ready(function()
            {
                var name = "{{ $party->name }}";
                var is_local = {{ $party->is_local }};
                var countries = {{ json_encode($party->countries) }};
                var party_type = {{ $party->party_type_id }};
                var people_quantity = {{ $party->people_quantity }};
                var date_start = "{{ $party->dateFormat($party->start_date) }}";
                var date_end = "{{ $party->dateFormat($party->end_date) }}";
                var objectives = {{ json_encode($party->objective_arrays) }};
                var interested = "{{ $party->interested }}";
                var expected = "{{ $party->expected }}";
                var joined = "{{ $party->joined }}";
                var location_bases = {{ json_encode($party->location_base_arrays) }};
                var accommodation_detail = "{{ $party->accommodation_detail }}";
                var request_for_lu = {{ $party->request_for_lu_personnel }};
                var request_lu_personnel_reason = "{{ $party->request_lu_personnel_reason }}";
                var paid_method = "{{ $party->paid_method }}";
                var related_budget_code = "{{ $party->related_budget_code }}";
                var request_person_name = "{{ $party->request_person_name }}";
                var request_person_tel = "{{ $party->request_person_tel }}";
                var request_person_email = "{{ $party->request_person_email }}";

                $('input[name=name]').val(name);
                if (is_local)
                {
                    $('#radioFromCountry1').prop('checked', true).change();
                }
                else
                {
                    $('#radioFromCountry2').prop('checked', true).change();
                    $('#comboCountry').val(countries).change();
                }
                $('#comboType').val(party_type).change();
                $('#numberQuantity').val(people_quantity);
                $('#inputDateStart').val(date_start);
                $('#inputDateEnd').val(date_end);
                $('#comboObjective').val(objectives).change();
                $('#inputInterested').val(interested);
                $('#inputExpected').val(expected);
                $('#comboLocationBase').val(location_bases).change();
                if (joined=='never')
                {
                    $('#radioJoined1').prop('checked', true).change();
                }
                else if(joined=='ever')
                {
                    $('#radioJoined2').prop('checked', true).change();
                }
                else
                {
                    $('#radioJoined3').prop('checked', true).change();
                }
                /*set for accommodation detail*/
                if (accommodation_detail=="" || accommodation_detail=="NULL")
                {
                    $('#request_accommodation_no').prop('checked', true).change();
                }
                else
                {
                    $('#request_accommodation_yes').prop('checked', true).change();
                    $('textarea[name=request_accommodation_information]').val(accommodation_detail);
                }
                /*set for support lu*/
                if (request_for_lu)
                {
                    $('#request_for_lu_personnel').val('yes').change();
                    $('#request_lu_personnel_reason').val(request_lu_personnel_reason);
                }
                else
                {
                    $('#request_for_lu_personnel').val('no').change();
                }
                /*set paid method*/
                $('#paid_method').val(paid_method).change();
                $('#'+paid_method+'_code').val(related_budget_code);
                /*finally set requester*/
                $('#inputContName').val(request_person_name);
                if (request_person_email)
                {
                    var extract_person_email = request_person_email.split("@");

                    if (extract_person_email.length>0)
                    {
                        $('#inputContEmail').val(extract_person_email[0]);
                    }
                }
                $('#inputContTel').val(request_person_tel);

                $("textarea").blur();
            });
        @endif
    </script>

@stop