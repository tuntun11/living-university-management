<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<html>

<table>
	<tr>
		<td>ชื่อคณะ</td>
		<td>{{ array_get($input, 'name') }}</td>
	</tr>
	<tr>
		<td>คณะมาจาก</td>
		<td>
			
		</td>
	</tr>
	<tr>
		<td>ประเภทคณะ</td>
		<td></td>
	</tr>
	<tr>
		<td>ช่วงจำนวนคน</td>
		<td>{{ array_get($input, 'people_start') . ' ถึง ' .  array_get($input, 'people_end') }}</td>
	</tr>
	<tr>
		<td>ช่วงวันที่เริ่มมา</td>
		<td>{{ array_get($input, 'start_date') . ' ถึง ' .  array_get($input, 'end_date') }}</td>
	</tr>
	<tr>
		<td>วัตถุประสงค์</td>
		<td></td>
	</tr>
	<tr>
		<td>รับคณะโดยแผนก</td>
		<td></td>
	</tr>
	<tr>
		<td>รายได้มาเข้าแผนก</td>
		<td></td>
	</tr>
	<tr>
		<td>ผู้ประสานงานหลัก</td>
		<td></td>
	</tr>
	<tr>
		<td>Tag</td>
		<td></td>
	</tr>
	<tr>
		<td>Status</td>
		<td></td>
	</tr>
</table>

</html>