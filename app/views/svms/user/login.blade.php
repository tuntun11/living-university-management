@extends('svms.layouts.landing')

{{-- Web site Title --}}
@section('title')
{{{ Lang::get('user/user.login') }}} ::
@parent
@stop

{{-- Content --}}
@section('content')
{{ Confide::makeLoginForm()->render() }}
@stop
