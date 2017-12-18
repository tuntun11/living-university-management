{{--Welcome Page หาก user ของ lu นี้มี Role มากกว่า 1 จะRedirect มาหน้านี้--}}
@extends('svms.layouts.landing')

@section('title')
    Living University Management System
@stop

@section('content')

<div class="page-header">
    <h3>กรุณาเลือกการทำงาน</h3>
</div>

<div class="row">
    <div class="col-xs-12 col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><strong>Admin</strong></h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-3">
                        <img src="http://placehold.it/140x140" class="img-rounded" style="width: 100px; height: 100px">
                    </div>
                    <div class="col-sm-9">
                        จัดการข้อมูลพื้นฐานของระบบบริหารจัดการมหาวิทยาลัยที่มีชีวิต
                    </div>
                </div>
            </div>
            <div class="panel-footer clearfix">
                <div class="pull-right">
                    <a href="{{{ URL::to('admin') }}}" class="btn btn-primary">เข้าสู่หน้า Admin</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-6">

    </div>
    <div class="col-xs-12 col-md-6">

    </div>
    <div class="col-xs-12 col-md-6">

    </div>
</div>

@stop
