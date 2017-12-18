<h2>สวัสดี {{ $user['personnel_name'] }}</h2>

<p>
    ระบบได้ทำการบันทึกข้อมูลการใช้งานของท่านเรียบร้อยแล้ว ท่านสามารถใช้ระบบได้โดยมี
    <br/><br/>
    ชื่อผู้ใช้ คือ <b>{{ $user['username'] }}</b>
    <br/>
    รหัสผ่าน คือ  <b>{{ $user['usepass'] }}</b>
    <br/><br/>
    โดยทำการเข้าใช้ระบบได้ที่นี่ <a href='{{{ URL::to("user/confirm/{$user['confirmation_code']}") }}}'>
        {{{ URL::to("user/login") }}}
    </a>
</p>