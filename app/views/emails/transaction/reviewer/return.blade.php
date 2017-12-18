{{-- อีเมลส่งกลับให้ Requester กรอกข้อมูลมาให้ครบ --}}
<table width="800" border="1">
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">วันที่และเวลา</td>
        <td valign="top">{{ ScheduleController::dateRangeStr(date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), true, true, 'th', true) }}</td>
    </tr>
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">ผู้ตรวจสอบ</td>
        <td valign="top">{{ $data['reviewer_name'] }}</td>
    </tr>

    @if($data['number_next_editing']==1)
        <tr>
            <td style="background-color: #EEEEEE;" valign="top" width="200">ข้อความจากผู้ตรวจสอบ</td>
            <td valign="top">{{ $data['note'] }}</td>
        </tr>
    @else
        <tr>
            <td style="background-color: #EEEEEE;" valign="top" width="200">ข้อความล่าสุดจากผู้ตรวจสอบ</td>
            <td valign="top">{{ $data['note'] }}</td>
        </tr>
        <tr>
            <td style="background-color: #EEEEEE;" valign="top" width="200">ประวัติการโต้ตอบ</td>
            <td valign="top">
                <table border="1">
                    <thead>
                        <tr>
                            <th width="15%">ครั้ง</th>
                            <th width="35%">จาก</th>
                            <th width="50%">ข้อความ</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $num = 0;
                    ?>
                        @foreach($data['histories'] as $history)
                            <tr>
                                <td {{ ($num%2==0) ? 'bgcolor="#eeeeee"' : 'bgcolor="#ffffff"' }} width="15%">{{ $history['revision'] }}</td>
                                <td {{ ($num%2==0) ? 'bgcolor="#eeeeee"' : 'bgcolor="#ffffff"' }} width="35%">{{ ($history['status']=='editing') ? 'ผู้ตรวจสอบและอนุมัติ' : 'ผู้ยื่นคำร้อง' }}</td>
                                <td {{ ($num%2==0) ? 'bgcolor="#eeeeee"' : 'bgcolor="#ffffff"' }} width="50%">{{ $history['note']." เมื่อ ".ScheduleController::dateRangeStr($history['created_at'], $history['created_at'], true, false, 'th', true) }}</td>
                            </tr>
                            <?php
                            $num++;
                            ?>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    @endif

    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">สถานะ</td>
        <td valign="top" style="color: green;"><strong>ขอข้อมูลเพิ่มเติมเพื่อตัดสินใจ ครั้งที่ {{ $data['number_next_editing'] }}</strong></td>
    </tr>
</table>

<p>
    สามารถเข้าไปกรอกข้อมูลเพิ่มเติมเพื่อประโยชน์ในการตัดสินใจได้ที่นี่  <a href='{{ URL::to("party/".$data['encrypt']."/editing/editByRequest") }}'>{{ URL::to("party/".$data['encrypt']."/editing/editByRequest") }}</a>
</p>