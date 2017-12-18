{{-- อีเมลส่งให้ทุกท่านใน loop ว่าคณะยกเลิกไม่มาดูงานแล้ว --}}
<table width="800" border="1">
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">วันที่และเวลา</td>
        <td valign="top">{{ ScheduleController::dateRangeStr(date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), true, true, 'th', true) }}</td>
    </tr>
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">ผู้ส่งเรื่อง</td>
        <td valign="top">{{ $data['sender_name'] }}</td>
    </tr>
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">ชื่อคณะ</td>
        <td valign="top" style="color: green;">{{ $data['name'] }}</td>
    </tr>
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">สถานะของคณะ</td>
        <td valign="top" style="color: green;"><strong><font color="#c12e2a">ขอยกเลิกศึกษาดูงาน</font></strong></td>
    </tr>
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">เหตุผล</td>
        <td valign="top" style="color: green;">{{ $data['note'] }}</td>
    </tr>
</table>
