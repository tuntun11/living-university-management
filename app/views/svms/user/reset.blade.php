@extends('svms.layouts.landing')

{{-- Web site Title --}}
@section('title')
{{{ Lang::get('user/user.forgot_password') }}} ::
@parent
@stop

{{-- Content --}}
@section('content')
<div class="page-header">
	<h1>ตั้งค่ารหัสผ่านใหม่</h1>
</div>
{{ Confide::makeResetPasswordForm($token)->render() }}
@stop
