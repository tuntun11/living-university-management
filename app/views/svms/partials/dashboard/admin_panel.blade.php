{{--System Admin Only--}}
<div class="well">
    <h2>
        ยินดีต้อนรับผู้ดูแลระบบบริหารจัดการ Living University
        <div class="small" style="margin-top: 10px;">ชื่อผู้ใช้ {{ Auth::user()->username }}</div>
    </h2>
    <div class="fluid-container">
        <div class="pull-right">
            <a href="{{{ URL::to('admin') }}}" class="btn btn-primary btn-lg" role="button"><i class="fa fa-cogs"></i> เข้าสู่การตั้งค่าข้อมูล</a>
        </div>
    </div>
    <div class="clearfix"></div>
</div>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><strong>สถิติการใช้ระบบ</strong></h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-2 control-label">จำนวนผู้ใช้งานทั้งหมด</label>
                <div class="col-sm-10">
                    <p class="form-control-static">{{ User::all()->count() }} ท่าน</p>
                </div>
            </div>

        </form>
    </div>
</div>