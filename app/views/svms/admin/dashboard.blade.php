{{--Dashboard Admin รวมของ Project Living University --}}
@extends('svms.layouts.admin')

@section('title')
    All
@stop

@section('content')

    <div class="page-header">
        <h3><i class="fa fa-cogs"></i> System Menu เมนูการตั้งค่าและข้อมูลพื้นฐานระบบ</h3>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-users" aria-hidden="true"></i>
                         ข้อมูลบุคลากรและผู้ใช้ระบบ</h3>
                </div>
                <div class="panel-body">
                    <ul class="list-unstyled">
						<li><a href="{{ URL::to('admin/personnels') }}">รายชื่อบุคลากร/วิทยากร</a></li>
						<li><a href="{{ URL::to('admin/users') }}">ผู้ใช้ระบบ</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-trophy" aria-hidden="true"></i>
                         ข้อมูลวิทยากร</h3>
                </div>
                <div class="panel-body">
                    <ul class="list-unstyled">
                        <li><a href="{{ URL::to('admin/expert-type') }}">ประเภทวิทยากร/ค่าบรรยาย</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-map-marker" aria-hidden="true"></i>
                         ข้อมูลสถานที่</h3>
                </div>
                <div class="panel-body">
                    <ul class="list-unstyled">
                        <li><a href="{{ URL::to('admin/location') }}">สถานที่/ที่พัก/ร้านอาหาร/สถานที่จัดกิจกรรม</a></li>
                        <li><a href="{{{ URL::to('admin/conference') }}}">ราคาห้องประชุม/สถานที่จัดกิจกรรม</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-cutlery" aria-hidden="true"></i>
                         ข้อมูลอาหาร</h3>
                </div>
                <div class="panel-body">
                    <ul class="list-unstyled">
                        <li><a href="{{ URL::to('admin/restaurant/food') }}">รายการอาหาร</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-location-arrow" aria-hidden="true"></i>
                         ข้อมูลสถานที่พัก</h3>
                </div>
                <div class="panel-body">
                    <ul class="list-unstyled">
                        <li><a href="{{ URL::to('admin/accommodation/room') }}">รายการห้องพัก/ที่พัก</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-car" aria-hidden="true"></i>
                         ข้อมูลการใช้รถยนต์</h3>
                </div>
                <div class="panel-body">
                    <ul class="list-unstyled">
                        <li><a href="{{ URL::to('admin/transport') }}">ผู้ให้บริการ</a></li>
                        <li><a href="{{ URL::to('admin/transport/car') }}">รายการประเภทรถยนต์</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-shopping-cart" aria-hidden="true"></i>
                        ข้อมูลราคาสินค้าต่างๆ</h3>
                </div>
                <div class="panel-body">
                    <ul class="list-unstyled">
                        <li style="display: none;"><a href="{{ URL::to('admin/facilities') }}">วัสดุอุปกรณ์ของสถานที่</a></li>
                        <li><a href="{{ URL::to('admin/tool') }}">วัสดุอุปกรณ์การเรียนรู้และอื่นๆ</a></li>
                        <li><a href="{{ URL::to('admin/special-event') }}">กิจกรรมพิเศษ</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-wrench" aria-hidden="true"></i>
                         ข้อมูลตั้งต้นของคณะดูงาน</h3>
                </div>
                <div class="panel-body">
                    <ul class="list-unstyled">
                        <li><a href="{{ URL::to('admin/party/type') }}">ประเภท</a></li>
                        <li><a href="{{ URL::to('admin/party/objective') }}">วัตถุประสงค์</a></li>
                        <li><a href="{{ URL::to('admin/tag') }}">ป้ายกำกับ Tag</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-cog" aria-hidden="true"></i>
                         ข้อมูลตั้งต้นอื่นๆ</h3>
                </div>
                <div class="panel-body">
                    <ul class="list-unstyled">
                        <li><a href="{{ URL::to('admin/departments') }}">แผนก/หน่วยงาน</a></li>
                        <li><a href="{{ URL::to('admin/activities') }}">กิจกรรมการรับคณะ</a></li>
                        <li><a href="{{ URL::to('admin/work-types') }}">ภาระงานของบุคลากร</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>


@stop