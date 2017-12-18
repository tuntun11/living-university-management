{{-- อีเมลส่งให้ Project Co ทำงานในการดีลต่อไป --}}
<table width="800" border="1">
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">วันที่และเวลา</td>
        <td valign="top">{{ ScheduleController::dateRangeStr(date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), true, true, 'th', true) }}</td>
    </tr>
	<tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">ผู้อนุมัติ</td>
        <td valign="top">{{ $data['manager_name'] }}</td>
    </tr>
		<tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">คำสั่งเพิ่มเติม</td>
        <td valign="top">{{ $data['manager_note'] }}</td>
    </tr>
	<tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">สถานะ</td>
        <td valign="top" style="color: green;"><strong>มอบหมายงาน</strong></td>
    </tr>
	<tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">ผู้ประสานงานหลัก</td>
        <td valign="top">{{ $data['coordinator_name'] }} {{ ($data['coordinator_department']=="") ? "" : ", แผนก".$data['coordinator_department'] }}</td>
    </tr>
	@if($data['coordinator_method']=='changePeople')
		<tr>
			<td style="background-color: #EEEEEE;" valign="top" width="200">หมายเหตุ</td>
			<td valign="top">เปลี่ยนตัวผู้ประสานงานหลัก</td>
		</tr>
	@endif
</table>
	
<br/>
<h3>รายละเอียดของคณะ</h3>
<table width="800" border="1">
	<tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">รหัสลูกค้า</td>
        <td valign="top">{{ $data['customer_code'] }}</td>
    </tr>
	<tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">ชื่อคณะ/บุคคล</td>
        <td valign="top">{{ $data['name'] }}</td>
    </tr>
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">มาจากประเทศ</td>
        <td valign="top">{{ $data['country'] }}</td>
    </tr>
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">ประเภท</td>
        <td valign="top">{{ $data['party_type'] }}</td>
    </tr>
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">จำนวนผู้เข้าร่วม</td>
        <td valign="top">{{ $data['people_quantity'].' คน' }}</td>
    </tr>
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">ช่วงวันที่มา</td>
        <td valign="top">{{ ScheduleController::dateRangeStr($data['start_date'],$data['end_date']) }}</td>
    </tr>
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">วัตถุประสงค์การมา</td>
        <td valign="top">
            <ul>
                @foreach($data['objectives'] as $objective)
                    <li>{{ $objective->name }}</li>
                @endforeach
            </ul>
        </td>
    </tr>
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">ประเด็นที่สนใจเป็นพิเศษ</td>
        <td valign="top">{{ $data['interested'] }}</td>
    </tr>
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">ความคาดหวังในการศึกษาดูงาน</td>
        <td valign="top">{{ $data['expected'] }}</td>
    </tr>
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">พื้นที่ศึกษาดูงาน</td>
        <td valign="top">
            <ul>
                @foreach($data['location_bases'] as $base)
                    <li>{{ $base['mflf_area_name'] }}</li>
                @endforeach
            </ul>
        </td>
    </tr>
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">เคยเข้าร่วมศึกษาดูงาน</td>
        <td valign="top">{{ PartyController::strJoin($data['joined']) }}</td>
    </tr>
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">รายละเอียดเพิ่มเติม</td>
        <td valign="top">{{ $data['objective_detail'] }}</td>
    </tr>
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">ผู้ประสานงานของคณะที่มา</td>
        <td valign="top">
            <ul>
                @foreach($data['coordinators'] as $coordinator)
                    <li>{{ $coordinator->name.' อีเมล :'.$coordinator->email.' เบอร์โทร :'.$coordinator->mobile }}</li>
                @endforeach
            </ul>
        </td>
    </tr>

    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">ต้องการใช้ห้องพัก</td>
        <td valign="top">
            {{{ ($data['accommodation_detail']==null) ? 'ไม่ต้องการ' : $data['accommodation_detail'] }}}
        </td>
    </tr>

    {{--Start Extend request for lu personnel--}}
    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">ต้องการสนับสนุนบุคลากรจากมหาวิทยาลัยที่มีชีวิต</td>
        <td valign="top">
            {{{ ($data['request_for_lu_personnel']) ? 'ต้องการ' : 'ไม่ต้องการ' }}}
            {{{ ($data['request_lu_personnel_reason']) ? ' '.$data['request_lu_personnel_reason'] : '' }}}
        </td>
    </tr>
    {{--End Extend request for lu personnel--}}

    <tr>
        <td style="background-color: #EEEEEE;" valign="top" width="200">การชำระเงิน</td>
        <td valign="top">{{ PartyController::strPaidMethod($data['paid_method'], $data['related_budget_code']) }}</td>
    </tr>

</table>
<p>
    ผู้ประสานงานหลักคณะสามารถดำเนินการเตรียมรับคณะต่อได้ที่  <a href='{{{ URL::to("party/" . $data['id'] . "/view") }}}'>
        {{{ URL::to("party/" . $data['id'] . "/view") }}}
    </a>
</p>