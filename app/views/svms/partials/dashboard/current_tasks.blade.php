{{--Current Tasks for Project Coordinator View--}}
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><strong>ภาระงานรับคณะของท่าน</strong></h3>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            {{--Start Data Table Plugin--}}
            @if(count($parties)>0)
                <table id="latest-parties" class="table table-condensed table-hover">
                    <thead>
                    <tr>
                        <th>รหัสลูกค้า</th>
                        <th>ชื่อคณะ/บุคคล</th>
                        <th>จำนวน(คน)</th>
                        <th>ช่วงเวลา</th>
                        <th>วันที่ส่งงาน</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    setlocale(LC_TIME, 'th');
                    ?>
                    @foreach($parties as $party)
                        <tr>
                            <td>{{ $party->customer_code }}</td>
                            <td>{{ $party->name }}</td>
                            <td>{{ $party->people_quantity }}</td>
                            <td>{{ ScheduleController::dateRangeStr($party->start_date, $party->end_date, true) }}</td>
                            <td>{{ Date::createFromDate($party->approved_year, $party->approved_month, $party->approved_date)->diffForHumans() }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group" aria-label="...">
                                    <button type="button" data-toggle="tooltip" data-placement="left" title="แก้ไขข้อมูล" class="btn btn-default" onclick="window.location='{{{ URL::to('party/'.$party->id.'/view') }}}'"><i class="fa fa-pencil"></i></button>
                                    @if($party->canProgram() && $party->is_history==0)
                                        <button type="button" data-toggle="tooltip" data-placement="left" title="สร้างกำหนดการ" class="btn btn-default" onclick="window.location='{{{ URL::to('coordinator/schedule/'.$party->id.'/view') }}}'"><i class="fa fa-calendar-o fa-fw"></i></button>

                                        @if($party->programingPassed())
                                            <button type="button" data-toggle="tooltip" data-placement="left" title="สร้างงบประมาณ" class="btn btn-default" onclick="window.location='{{{ URL::to('coordinator/budget/'.$party->id.'/view') }}}'"><i class="fa fa-money fa-fw"></i></button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="fa fa-exclamation"></i> ท่านยังไม่มีงานรับคณะในขณะนี้
                </div>
            @endif
            {{--Finish Data Table Plugin--}}
        </div>
    </div>
</div>