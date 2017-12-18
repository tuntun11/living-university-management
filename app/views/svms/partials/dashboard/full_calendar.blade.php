{{--Full Calendar View for Dashboard--}}
@if(Request::getQueryString()=='location')
    {{--Show alternate view is location--}}
    @include('svms.partials.dashboard.calendar.location')
@else
    {{--Show default view is status (the first ever)--}}
    @include('svms.partials.dashboard.calendar.status')
@endif
