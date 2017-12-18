<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{
    //
});


App::after(function($request, $response)
{
	//
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
	if ( Auth::guest() ) // If the user is not logged in
	{
        	return Redirect::guest('user/login');
	}
});

Route::filter('auth.basic', function()
{
	return Auth::basic();
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
	if (Auth::check()) return Redirect::to('user/login/');
});

/*
|--------------------------------------------------------------------------
| Role Permissions
|--------------------------------------------------------------------------
|
| Access filters based on roles.
|
*/

/*
|--------------------------------------------------------------------------
| Admin Roles
|--------------------------------------------------------------------------
 */

// Check for role on all Admin routes
Entrust::routeNeedsRole( 'admin*', array('admin'), Redirect::to('/') );

// Check for permissions on Admin actions
//Entrust::routeNeedsPermission( 'admin/users*', 'manage_users', Redirect::to('/admin') );
//Entrust::routeNeedsPermission( 'admin/roles*', 'manage_roles', Redirect::to('/admin') );

/*
|--------------------------------------------------------------------------
| VIP Roles
|--------------------------------------------------------------------------
 */

Entrust::routeNeedsRole( 'vip*', array('vip'), Redirect::to('/'), false );

/*
|--------------------------------------------------------------------------
| Reviewer Roles
|--------------------------------------------------------------------------
 */

Entrust::routeNeedsRole( 'reviewer*', array('reviewer'), Redirect::to('/'), false );

/*
|--------------------------------------------------------------------------
| Manager Roles
|--------------------------------------------------------------------------
 */

Entrust::routeNeedsRole( 'manager*', array('manager'), Redirect::to('/'), false );

/*
|--------------------------------------------------------------------------
| Project Coordinators Roles
|--------------------------------------------------------------------------
 */

Entrust::routeNeedsRole( 'coordinator*', array('project coordinator'), Redirect::to('/'), false );

/*
|--------------------------------------------------------------------------
| Contributor Roles
|--------------------------------------------------------------------------
 */

Entrust::routeNeedsRole( 'contributor*', array('contributor'), Redirect::to('/'), false );

/*
|--------------------------------------------------------------------------
| Author Route
|--------------------------------------------------------------------------
 */

Entrust::routeNeedsRole( 'expert*', array('project coordinator', 'manager', 'contributor'), Redirect::to('/'), false );

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
	if (Session::getToken() !== Input::get('csrf_token') &&  Session::getToken() !== Input::get('_token'))
	{
		throw new Illuminate\Session\TokenMismatchException;
	}
});

/*
|--------------------------------------------------------------------------
| Language
|--------------------------------------------------------------------------
|
| Detect the browser language.
|
*/

Route::filter('detectLang',  function($route, $request, $lang = 'auto')
{

    if($lang != "auto" && in_array($lang , Config::get('app.available_language')))
    {
        Config::set('app.locale', $lang);
    }else{
        $browser_lang = !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? strtok(strip_tags($_SERVER['HTTP_ACCEPT_LANGUAGE']), ',') : '';
        $browser_lang = substr($browser_lang, 0,2);
        $userLang = (in_array($browser_lang, Config::get('app.available_language'))) ? $browser_lang : Config::get('app.locale');
        Config::set('app.locale', $userLang);
        App::setLocale($userLang);
    }
});
