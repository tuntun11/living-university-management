{{--ยกเลิกใช้--}}
@extends('site.layouts.default')
{{-- Web site Title --}}
@section('title')
    ระบบงาน KLC Back Office ของมูลนิธิแม่ฟ้าหลวง ในพระบรมราชูปถัมภ์
@stop

{{-- Content --}}
@section('content')

    <div class="row">
        <div class="col-sm-6 col-md-4">
            <div class="thumbnail">
                {{--<img src="..." alt="...">--}}
                <div class="caption">
                    <h3>มหาวิทยาลัยมีชีวิต</h3>
                    <p>เข้าสู่ระบบบริหารและจัดการ</p>
                    <p>
                        <div class="pull-right"><a href="{{{ URL::to('lu/welcome') }}}" class="btn btn-primary" role="button">เข้าสู่ระบบ</a></div>
                        <div class="clearfix"></div>
                    </p>
                </div>
            </div>
        </div>
    </div>

@stop
