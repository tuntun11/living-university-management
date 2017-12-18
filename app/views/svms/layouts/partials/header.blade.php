<div class="navbar-header" style="min-width: 250px; background: url('{{ URL::asset('assets/img/svms/bg.png') }}') no-repeat top left;">
   <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
   </button>
   <a class="navbar-brand" href="{{ URL::to('/') }}"> {{ HTML::image('assets/img/logo_mini.png', $alt="Brand", $attributes = array('height' => 30)) }}</a>
   <p class="navbar-text">{{--Living University App--}}</p>
</div>