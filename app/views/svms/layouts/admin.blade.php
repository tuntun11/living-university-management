{{--use for : Living University Admin Panel--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="{{{ asset('assets/ico/favicon.png') }}}">

    <title> Living University Management Admin Panel ::
        @section('title')
            Dashboard
        @show
    </title>

    {{--Use Jquery 1.11.2 (Support IE8 or Higher)--}}
    {{ HTML::script('assets/js/jquery.min.js') }}
    {{--Use Bootstrap Framework--}}
    {{ HTML::style('bootstrap/css/bootstrap.min.css') }}
    {{ HTML::style('bootstrap/css/bootstrap-theme.min.css') }}
    {{ HTML::script('bootstrap/js/bootstrap.min.js') }}
    {{--Custom Bootstrap Navbar--}}
    {{-- HTML::script('assets/css/projects/customNavbar.css') --}}
    {{--Use Js Route--}}
    {{ HTML::script('dependencies/js/laroute.js') }}
    {{--Use Font Awesome--}}
    {{ HTML::style('dependencies/font-awesome-4.7.0/css/font-awesome.min.css') }}
	{{-- Extension Javascript --}}
    {{ HTML::script('assets/js/svms.js') }}

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    {{ HTML::script('https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js') }}
    {{ HTML::script('https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js') }}
    <![endif]-->

    @yield('extraScripts', '')

    @yield('extraStyles', '')

    {{--Heap Analytics--}}
    {{--<script type="text/javascript">
        window.heap=window.heap||[],heap.load=function(e,t){window.heap.appid=e,window.heap.config=t=t||{};var n=t.forceSSL||"https:"===document.location.protocol,a=document.createElement("script");a.type="text/javascript",a.async=!0,a.src=(n?"https:":"http:")+"//cdn.heapanalytics.com/js/heap-"+e+".js";var o=document.getElementsByTagName("script")[0];o.parentNode.insertBefore(a,o);for(var r=function(e){return function(){heap.push([e].concat(Array.prototype.slice.call(arguments,0)))}},p=["clearEventProperties","identify","setEventProperties","track","unsetEventProperty"],c=0;c<p.length;c++)heap[p[c]]=r(p[c])};
        heap.load("1189722819");
    </script>--}}
    
</head>

<body>

<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">{{ HTML::image('assets/img/logo_mini.png', $alt="Brand", $attributes = array('height' => 30)) }}</a>
            <p class="navbar-text">Living University Management :: Admin Panel</p>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav pull-right">
                <li><a href="{{{ URL::to('/') }}}"><i class="fa fa-tachometer"></i> </i> Dashboard</a></li>
                <li><a href="{{{ URL::to('admin') }}}"><i class="fa fa-cogs"></i> System Menu</a></li>
                <li class="divider-vertical"></li>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <span class="glyphicon glyphicon-user"></span> {{{ Auth::user()->username }}}	<span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                       {{-- <li><a href="{{{ URL::to('user/settings') }}}"><span class="glyphicon glyphicon-wrench"></span> Settings</a></li>--}}
                        <li class="divider"></li>
                        <li><a href="{{{ URL::to('user/logout') }}}"><span class="glyphicon glyphicon-share"></span> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>

<div class="container" style="margin: 30px auto;">

   @yield('content')

</div><!-- /.container -->

</body>
</html>
