{{--This is use bootstrap theme : sb_admin_2--}}
{{--use for : Living University Program--}}
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="{{{ asset('assets/ico/favicon.png') }}}">

    <title>
        @section('title')
            Living University Management
        @show
    </title>

    {{--This is project custom style--}}
    {{ HTML::style('assets/css/projects/svms.css') }}
    {{--Use Jquery 1.11.2 (Support IE8 or Higher)--}}
    {{ HTML::script('assets/js/jquery.min.js') }}
    {{--Use Bootstrap Framework--}}
    {{ HTML::style('bootstrap/css/bootstrap.min.css') }}
    {{ HTML::style('bootstrap/css/bootstrap-theme.min.css') }}
    {{ HTML::script('bootstrap/js/bootstrap.min.js') }}
    {{--Custom Bootstrap Style--}}
    {{-- HTML::style('assets/css/projects/customNavBar.css') --}}
    {{-- Use Bootstrap Color Alert --}}
    {{ HTML::style('dependencies/bootstrap3-dialog-master/dist/css/bootstrap-dialog.min.css') }}
    {{ HTML::script('dependencies/bootstrap3-dialog-master/dist/js/bootstrap-dialog.min.js') }}
    {{-- Use Bootstrap Typeahead --}}
    {{ HTML::script('dependencies/bootstrap-ajax-typeahead-master/js/bootstrap-typeahead.js') }}
    {{--Use Js Route--}}
    {{ HTML::script('dependencies/js/laroute.js') }}
    {{--Use Font Awesome--}}
    {{ HTML::style('dependencies/font-awesome-4.7.0/css/font-awesome.min.css') }}
    {{--Use metisMenu--}}
    {{ HTML::style('dependencies/metisMenu/dist/metisMenu.min.css') }}
    {{ HTML::script('dependencies/metisMenu/dist/metisMenu.min.js') }}
    {{--Use Custom Theme--}}
    {{ HTML::style('sb-admin-2/css/sb-admin-2.css') }}
    {{ HTML::script('sb-admin-2/js/sb-admin-2.js') }}
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

<div id="wrapper">

    <!-- Navigation -->
    <nav id="lu-bootstrap-menu" class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        @include('svms.layouts.partials.header')
        <!-- /.navbar-header -->

        @include('svms.layouts.partials.top')
        <!-- /.navbar-top-links -->

        @include('svms.layouts.partials.side')
        <!-- /.navbar-static-side -->
    </nav>

    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="page-header">
                    <h3>
                        @section('header')
                            หน้าควบคุม
                        @show
                    </h3>
                </div>
            </div>
            <!-- /.col-lg-12 -->
        </div>

        <div class="row">
            @yield('content')
        </div>
        {{-- /content in current page --}}
    </div>
    <!-- /#page-wrapper -->

</div>

<footer class="footer">
    <div class="container">
        <p class="text-muted">ระบบบริหารจัดการมหาวิทยาลัยที่มีชีวิต Version 2.170308 &copy; {{ @date('Y').' มูลนิธิแม่ฟ้าหลวงในพระบรมราชูปถัมภ์'; }}</p>
    </div>
</footer>

</body>
</html>

<script type="text/javascript">
    $(document).ready(function(){
        /*this is search icon*/
        $('#search-quick').typeahead({
            items : 5,
            ajax: {
                url: "{{ URL::to('party/search-quick') }}",
                method: "get",
                loadingClass: "loading-circle"
            },
            highlighter: function(item){
                if (item!=null)
                {
                    return "<div style='white-space:normal !important; word-wrap:break-word;'>" + item + "</div>";
                }
                else
                {
                    return false;
                }
            },
            onSelect: function(item) {
                if (item!=null)
                {
                    window.location.href = 'http://lu.maefahluang.org:8080/svms/public/party/' + item.value + '/view';
                }
                else
                {
                    return false;
                }
            }
        });
    });
</script>

