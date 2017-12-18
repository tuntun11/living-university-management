{{--Dashboard Report Service รวมของ Project Living University --}}
@extends('svms.layouts.reporting')

@section('title')
    All
@stop

@section('content')

    <div class="page-header">
        <h3><i class="fa fa-bar-chart"></i> รายงานระบบ</h3>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-users"></i> รายงานคณะดูงาน</h3>
                </div>
                <div class="panel-body">
                    <ul class="list-unstyled">
                        <li><a href="{{{ URL::to('report/parties') }}}"><i class="fa fa-file-excel-o"></i> รายชื่อคณะ</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-graduation-cap"></i> รายงานบุคลากรและวิทยากร</h3>
                </div>
                <div class="panel-body">
                    <ul class="list-unstyled">
                        <li><a href="{{{ URL::to('report/personnels') }}}"><i class="fa fa-file-excel-o"></i> รายนามบุคลากรและวิทยากร</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-shopping-cart"></i> รายงานการขาย</h3>
                </div>
                <div class="panel-body">
                    <ul class="list-unstyled">
                        <li><a href="{{{ URL::to('report/latest-price') }}}"><i class="fa fa-file-o"></i> ราคาล่าสุด</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-pie-chart"></i> รายงานสรุป</h3>
                </div>
                <div class="panel-body">
                    <ul class="list-unstyled">
                        <li style="display: none;"><a href="{{{ URL::to('report/total/party-by-area') }}}"><i class="fa fa-file-o"></i> จำนวนคณะแยกตามพื้นที่ดูงาน(ดอยตุง-น่าน)</a></li>
                        <li><a href="{{{ URL::to('report/total/party-by-type') }}}"><i class="fa fa-file-o"></i> จำนวนคณะแยกตามประเภท</a></li>
                        <li><a href="{{{ URL::to('report/total/party-by-participant') }}}"><i class="fa fa-file-o"></i> จำนวนคนเข้าดูงาน</a></li>
                        <li><a href="{{{ URL::to('report/total/party-by-income') }}}"><i class="fa fa-file-o"></i> รายรับ</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-md-6">

        </div>
    </div>

@stop