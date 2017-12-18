@if ( Session::get('error') )
    <div class="alert alert-danger">{{ Session::get('error') }}</div>
@endif

@if ( Session::get('notice') )
    <div class="alert">{{ Session::get('notice') }}</div>
@endif
<div class="container" style="margin-top:40px">
    <div class="row">
        <div class="col-sm-6 col-md-4 col-md-offset-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong>ลงชื่อเข้าใช้ระบบ</strong>
                </div>
                <div class="panel-body">
                    <form class="form-horizontal" method="POST" action="{{ URL::to('user/login') }}" accept-charset="UTF-8">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <fieldset>
                            <div class="row">
                                <div class="col-sm-12 col-md-10  col-md-offset-1 ">

                                    <div class="form-group">
                                        <div class="input-group">
												<span class="input-group-addon">
													<i class="glyphicon glyphicon-user"></i>
												</span>
                                            <input class="form-control" placeholder="ชื่อผู้ใช้" name="email" id="email" type="text" value="{{ Input::old('email') }}" autofocus required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="input-group">
												<span class="input-group-addon">
													<i class="glyphicon glyphicon-lock"></i>
												</span>
                                            <input class="form-control" placeholder="รหัสผ่าน" name="password" type="password" id="password" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label for="remember">{{ Lang::get('confide::confide.login.remember') }}
                                                <input type="hidden" name="remember" value="0">
                                                <input style="margin-top: -18px;" type="checkbox" name="remember" id="remember" value="1">
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <input type="submit" class="btn btn-lg btn-primary btn-block" value="Log In">
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
                <div class="panel-footer ">
                    ลืมรหัสผ่านใช่หรือไม่ ? <a href="forgot"> คลิกที่นี่ </a>
                </div>
            </div>
        </div>
    </div>
</div>
