<?php

/*
|--------------------------------------------------------------------------
| Study Visit (Living University) Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

/** ------------------------------------------
 *  Route model binding
 *  ------------------------------------------
 */
Route::model('user', 'User');
Route::model('role', 'Role');
Route::model('party', 'Party');
Route::model('personnel', 'Personnel');

/** ------------------------------------------
 *  Route constraint patterns
 *  ------------------------------------------
 */
Route::pattern('user', '[0-9]+');
Route::pattern('role', '[0-9]+');
Route::pattern('party', '[0-9]+');
Route::pattern('personnel', '[0-9]+');

Route::pattern('token', '[0-9a-z]+');

/** ------------------------------------------
 *  Only Admin Routes
 *  ------------------------------------------
 */
Route::group(array('prefix' => 'admin', 'before' => 'auth'), function()
{
    #Admin Party
    Route::group(array('prefix' => 'party'), function()
    {
        #Admin Party Objective
        Route::controller('objective', 'AdminPartyObjectiveController');

        #Admin Party Type
        Route::controller('type', 'AdminPartyTypeController');

        #Admin Party Index
        Route::controller('/', 'AdminPartyDashboardController');
    });

    #Transport
    Route::group(array('prefix' => 'transport'), function()
    {
        #Transport Car
        Route::controller('car', 'AdminCarsController');

        #Transport Index
        Route::controller('/', 'AdminCarFacController');
    });

    #Restaurant
    Route::group(array('prefix' => 'restaurant'), function()
    {
        #Restaurant Food
        Route::controller('food', 'AdminRestFoodsController');

        #Restaurant Index
        Route::controller('/', 'AdminRestController');
    });

    #Accommodations
    Route::group(array('prefix' => 'accommodation'), function()
    {
        #Accom Rooms
        Route::controller('room', 'AdminAccomRoomsController');

        #Accom Index
        Route::controller('/', 'AdminAccomController');
    });
	
	#Location Activities
    Route::controller('activities', 'AdminLocationActivitiesController');

    #Conferences
    Route::controller('conference', 'AdminConferenceController');

    #Location Facilities
    Route::controller('facilities', 'AdminLocationFacilitiesController');

    #Tools
    Route::controller('tool', 'AdminToolController');
	
	#Work Types
    Route::controller('work-types', 'AdminWorkTypesController');

    #Special Events
    Route::controller('special-event', 'AdminSpecialEventController');

    #Expert Type
    Route::controller('expert-type', 'AdminExpertTypeController');

    #Location
    Route::controller('location', 'AdminLocationController');

    #Tag
    Route::controller('tag', 'AdminTagController');

    #Department
    Route::controller('departments', 'AdminDepartmentsController');
	
	# Personnel Management
    Route::controller('personnels', 'AdminPersonnelController');

    # User Management
    Route::controller('users', 'AdminPersonnelUsersController');

    # User Role Management
    Route::get('roles/{role}/show', 'AdminRolesController@getShow');
    Route::get('roles/{role}/edit', 'AdminRolesController@getEdit');
    Route::post('roles/{role}/edit', 'AdminRolesController@postEdit');
    Route::get('roles/{role}/delete', 'AdminRolesController@getDelete');
    Route::post('roles/{role}/delete', 'AdminRolesController@postDelete');
    Route::controller('roles', 'AdminRolesController');

    #Expert And Extension Data Management
    //Route::controller('expert', 'AdminPersonnelExpertController');

    # Admin Dashboard
    Route::controller('/', 'AdminDashboardController');

});

/** ------------------------------------------
 *  Only VIP Routes
 *  ------------------------------------------
 */
 
Route::group(array('prefix' => 'vip', 'before' => 'auth'), function()
{
    Route::controller('/', 'VipController');
});


/** ------------------------------------------
 *  Only Reviewer Routes
 *  ------------------------------------------
 */

Route::group(array('prefix' => 'reviewer', 'before' => 'auth'), function()
{
    Route::get('/{party}/review', 'ReviewerController@getReview');

    Route::controller('/', 'ReviewerController');
});

Route::group(array('prefix' => 'mobile', 'before' => 'auth'), function()
{
    Route::get('review/{party}', 'ReviewerController@getReview');
});

/** ------------------------------------------
 *  Only Manager Routes
 *  ------------------------------------------
 */

Route::group(array('prefix' => 'manager', 'before' => 'auth'), function()
{
    Route::controller('/', 'ManagerController');
});

/** ------------------------------------------
 *  Only Project Coordinators Routes
 *  ------------------------------------------
 */

Route::group(array('prefix' => 'coordinator', 'before' => 'auth'), function()
{
    # Schedule Management
    Route::post('/schedule/document', 'ScheduleController@getDocument');
    Route::post('/schedule/location-by-schedule', 'ScheduleController@getLocationBySchedule');
    Route::post('/schedule/tasks', 'ScheduleController@getTasks');
    Route::get('/schedule/{party}/view', 'ScheduleController@getView');
	Route::post('/schedule/search-activities', 'ScheduleController@postSearchActivities');
	Route::get('/schedule/activity', 'ScheduleController@getActivity');
    Route::controller('/schedule', 'ScheduleController');

    # Budget Management
    Route::post('/budget/quotation-and-price-list', 'BudgetController@getQuotationAndPriceList');
    Route::post('/budget/quotation', 'BudgetController@getQuotation');//not used but keep for alternative
    Route::post('/budget/type-calculate', 'BudgetController@getTypeCalculate');
    Route::get('/budget/{party}/view', 'BudgetController@getView');
    Route::controller('/budget', 'BudgetController');

    # Document
    Route::controller('/document', 'DocumentController');
});

/** ------------------------------------------
 *  Routes for Report ; All Role
 *  ------------------------------------------
 */

Route::group(array('prefix' => 'report', 'before' => 'auth'), function()
{
	/*Total Summary Report*/
    Route::group(array('prefix' => 'total'), function()
    {
        /*รายงานแยกพื้นที่ดูงาน*/
        Route::get('party-by-area', 'SummaryReportController@getPartyByArea');
        Route::post('party-by-area', 'SummaryReportController@postPartyByArea');
        /*รายงานแยกประเภท*/
        Route::get('party-by-type', 'SummaryReportController@getPartyByType');
		Route::post('party-by-type', 'SummaryReportController@postPartyByType');
		/*รายงานจำนวนคนดูงาน*/
		Route::get('party-by-participant', 'SummaryReportController@getPartyByParticipant');
		Route::post('party-by-participant', 'SummaryReportController@postPartyByParticipant');
		/*รายงานรายรับ*/
        Route::get('party-by-income', 'SummaryReportController@getPartyByIncome');
		Route::post('party-by-income', 'SummaryReportController@postPartyByIncome');
    });
    /*Frequency Summary Report*/
    Route::group(array('prefix' => 'fq'), function()
    {
        Route::get('location-for-party', 'FrequencyReportController@getIndex');
    });
    /*รายงานรายชื่อบุคลากรและวิทยากรทั้งหมด*/
    Route::get('personnels', 'PersonnelsReportController@getIndex');
    Route::get('filter-personnels', 'PersonnelsReportController@getFilterPersonnels');
    Route::post('excel-personnels', 'PersonnelsReportController@postExcelPersonnels');
    /*รายงานคณะทั้งหมด*/
    Route::get('parties', 'PartiesReportController@getIndex');
    Route::get('filter-parties', 'PartiesReportController@getFilterParties');
	Route::post('excel-parties', 'PartiesReportController@postExcelParties');
    /*รายงานราคา HTML*/
    Route::get('latest-price', 'SalesReportController@getLatestPrice');
    /*index routes*/
    Route::controller('/', 'ReportController');
});

/** ------------------------------------------
 *  Auth Routes -- For All Role
 *  ------------------------------------------
 */

//***for Party View***
//ศทบ 01 ร่าง Document
Route::get('document/travel01/{party}/view', array('before' => 'auth','uses' => 'DocumentController@getTravelDocument'));
//Action Plan ร่าง Document
Route::get('document/action-plan/{party}/view', array('before' => 'auth','uses' => 'DocumentController@getActionPlanDocument'));

//Party
Route::get('party/{encrypt}/editing/{state}', array('uses' => 'PartyController@getEditing'));//This page is session for edit in pending or reviewing
Route::get('party/{encrypt}/success/{state}', array('uses' => 'PartyController@getSuccess'));//This page is use for success step on request or editing page
Route::get('party/{encrypt}/pending', array('uses' => 'PartyController@getPending'));//This page is use for pending to send request.

Route::get('party/{party}/view', array('before' => 'auth','uses' => 'PartyController@getView'));

Route::get('party/search-quick', array('before' => 'auth','uses' => 'PartyController@getSearchQuick'));
Route::get('party/get-by-id', array('before' => 'auth','uses' => 'PartyController@getById'));
Route::get('party/histories', array('before' => 'auth','uses' => 'PartyController@getHistories'));
Route::get('party/history', array('before' => 'auth','uses' => 'PartyController@getHistory'));
Route::get('party/sharepoint', array('before' => 'auth','uses' => 'PartyController@getSharepoint'));
Route::get('party/staffs', array('before' => 'auth','uses' => 'PartyController@getStaffs'));

Route::get('party/delete-file', array('before' => 'auth','uses' => 'PartyController@getDeleteFile'));

Route::post('party/delete-sharepoint', array('before' => 'auth','uses' => 'PartyController@postDeleteSharepoint'));
Route::post('party/create-or-update-sharepoint', array('before' => 'auth','uses' => 'PartyController@postCreateOrUpdateSharepoint'));

Route::post('party/delete-staff', array('before' => 'auth','uses' => 'PartyController@postDeleteStaff'));
Route::post('party/create-or-update-staff', array('before' => 'auth','uses' => 'PartyController@postCreateOrUpdateStaff'));

Route::post('party/upload-files', array('before' => 'auth','uses' => 'PartyController@postUploadFiles'));
Route::post('party/delete', array('before' => 'auth','uses' => 'PartyController@postDelete'));
Route::post('party/create-or-update', array('before' => 'auth','uses' => 'PartyController@postCreateOrUpdate'));
Route::post('party/edit', array('before' => 'auth','uses' => 'PartyController@postEdit'));
Route::post('party/document-json', array('before' => 'auth','uses' => 'PartyController@getDocumentJson'));
Route::post('party/transaction', array('before' => 'auth','uses' => 'PartyController@getTransaction'));

Route::controller('party', 'PartyController');

//***for Expert View***
Route::get('expert/{personnel}/view', array('before' => 'auth','uses' => 'ExpertController@getView'));

Route::controller('expert', 'ExpertController');


/** ------------------------------------------
 *  Non-Auth Routes -- Contributor
 *  ------------------------------------------
 */

//Calendar page
Route::get('calendar', function(){
    return View::make('svms/calendar');
});
Route::get('calendar-events', 'PartyController@getCalendarEvents');
//Requested Page
Route::get('request', 'PartyController@getRequest');
Route::post('request', 'PartyController@postRequest');

/** ------------------------------------------
 *  Frontend Routes
 *  ------------------------------------------
 */

// User reset routes
Route::get('user/reset/{token}', 'UserController@getReset');
// User password reset
Route::post('user/reset/{token}', 'UserController@postReset');
//:: User Account Routes ::
Route::post('user/{user}/edit', 'UserController@postEdit');

//setting route
Route::post('user/setting', array('before' => 'auth','uses' => 'UserController@postSettings'));
//post setting
Route::get('user/setting', array('before' => 'auth','uses' => 'UserController@getSettings'));

//:: User Account Routes ::
Route::post('user/login', 'UserController@postLogin');

# User RESTful Routes (Login, Logout, Register, etc)
Route::controller('user', 'UserController');

//:: Application Routes ::
#for test
Route::controller('test', 'TestController');

//Route::get('test/move-schedule-budget', 'TestController@getMoveScheduleBudget');

# Index Page - Last route, no matches
Route::get('/', array('before' => 'auth|detectLang','uses' => 'HomeController@getIndex'));

#404 Normal
Route::get('404', function(){
    return Response::view('error/404');
});
