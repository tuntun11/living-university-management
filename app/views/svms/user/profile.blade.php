@extends('svms.layouts.landing')

{{-- Web site Title --}}
@section('title')
    ข้อมูลส่วนตัวของท่าน ::
    @parent
@stop

@section('header')
    <span class="fa fa-user"></span>
    ข้อมูลของท่าน
@stop

{{-- Content --}}
@section('content')

    @if(Session::get('status')==='success')
        <div class="alert alert-success" role="alert">{{ Session::get('msg') }}</div>
    @endif

    @if(Session::get('status')==='error')
        <div class="alert alert-danger" role="alert">{{ Session::get('msg') }}</div>
    @endif

    <div class="panel panel-default">
        <div class="panel-body">

            <form class="form-horizontal" method="post" action="{{ URL::action('UserController@postSettings') }}">

                <!-- CSRF Token -->
                <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                <!-- ./ csrf token -->

                <div class="form-group">
                    <label class="col-sm-2 control-label">คำนำ(ไทย)</label>
                    <div class="col-sm-4">
                        <select class="form-control" id="comboPrefix" name="prefix" required>
                            <option value="">เลือก</option>
                            <option {{{ ($user->getPerson($user->id)->prefix=='นางสาว') ? 'selected' : '' }}} value="นางสาว">นางสาว</option>
                            <option {{{ ($user->getPerson($user->id)->prefix=='นาง') ? 'selected' : '' }}} value="นาง">นาง</option>
                            <option {{{ ($user->getPerson($user->id)->prefix=='นาย') ? 'selected' : '' }}} value="นาย">นาย</option>
                            <option {{{ ($user->getPerson($user->id)->prefix=='ม.ล.') ? 'selected' : '' }}} value="ม.ล.">ม.ล.</option>
                            <option {{{ ($user->getPerson($user->id)->prefix=='ม.ร.ว.') ? 'selected' : '' }}} value="ม.ร.ว.">ม.ร.ว.</option>
                            <option {{{ ($user->getPerson($user->id)->prefix=='ดร.') ? 'selected' : '' }}} value="ดร.">ดร.</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">ชื่อแรก(ไทย)</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="inputFirstName" name="first_name" value="{{ $user->getPerson($user->id)->first_name }}" required/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">ชื่อสกุล(ไทย)</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="inputLastName" name="last_name" value="{{ $user->getPerson($user->id)->last_name }}" required/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">Prefix</label>
                    <div class="col-sm-4">
                        <select class="form-control" id="comboPrefixEn" name="prefix_en">
                            <option value=""></option>
                            <option {{{ ($user->getPerson($user->id)->prefix_en=='Miss') ? 'selected' : '' }}} value="Miss">Miss</option>
                            <option {{{ ($user->getPerson($user->id)->prefix_en=='Mrs.') ? 'selected' : '' }}} value="Mrs.">Mrs.</option>
                            <option {{{ ($user->getPerson($user->id)->prefix_en=='Mr.') ? 'selected' : '' }}} value="Mr.">Mr.</option>
                            <option {{{ ($user->getPerson($user->id)->prefix_en=='M.R.') ? 'selected' : '' }}} value="M.R.">M.R.</option>
                            <option {{{ ($user->getPerson($user->id)->prefix_en=='M.L.') ? 'selected' : '' }}} value="M.L.">M.L.</option>
                            <option {{{ ($user->getPerson($user->id)->prefix_en=='Dr.') ? 'selected' : '' }}} value="Dr.">Dr.</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">First Name</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="inputFirstNameEn" name="first_name_en" value="{{ $user->getPerson($user->id)->first_name_en }}"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">Last Name</label>
                    <div class="col-sm-10">
                       <input type="text" class="form-control" id="inputLastNameEn" name="last_name_en" value="{{ $user->getPerson($user->id)->last_name_en }}"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">Mobile</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" value="{{ $user->getPerson($user->id)->mobile }}" id="inputMobile" name="mobile">
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-lg btn-success"><i class="fa fa-floppy-o"></i> Save</button>
                    </div>
                </div>

            </form>

        </div>
    </div>
@stop
