<?php

class ScheduleController extends CoordinatorController {

    protected $party, $location, $lu_schedule, $lu_schedule_task, $lu_schedule_task_location;

    public function __construct(Party $party, Location $location, LuSchedule $lu_schedule, LuScheduleTask $lu_schedule_task, LuScheduleTaskLocation $lu_schedule_task_location)
    {
        parent::__construct();
        $this->party = $party;
        $this->location = $location;
        $this->lu_schedule = $lu_schedule;
        $this->lu_schedule_task = $lu_schedule_task;
        $this->lu_schedule_task_location = $lu_schedule_task_location;
    }

    //return index page
    public function getIndex()
    {
        $parties = $this->selectParties();

        return View::make('svms/schedule/index', compact('parties'));
    }

    //return view
    public function getView($party)
    {
        $parties = $this->selectParties();
        $party_day_ranges = $this->dateRangeArray($party->start_date, $party->end_date);
        $activity_locations = self::areaLocations('A');//Activity
        $sleep_locations = self::areaLocations('S');//Sleep Place

        $count_lang_thai = self::countScheduleLanguage($party->id, 'th');
        $count_lang_english = self::countScheduleLanguage($party->id, 'en');

        $main_lang = ($count_lang_thai >= $count_lang_english) ? 'th' : 'en'; 

        $party['date_count'] = count($party_day_ranges);
        $party['date_range'] = $party_day_ranges;
        $party['date_range_text'] = $this->dateRangeStr($party->start_date, $party->end_date, true);

        return View::make('svms/schedule/index', compact('party', 'parties', 'activity_locations', 'sleep_locations', 'main_lang'));
    }

    //return get schedule document
    public function getDocument()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            'party_id' => 'required|integer'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        if ($validator->passes())
        {
            //find party
            $party = Party::find(Input::get('party_id'));
            if ($document = DocumentController::getSchedule($party))
            {
                //response success
                $response = array(
                    'status' => 'success',
                    'msg' => 'สร้างเอกสารกำหนดการเสร็จแล้ว',
                    'party' => $party->id,
                    'document' => $document
                );

                return Response::json( $response );
            }

            //response error
            $response = array(
                'status' => 'error',
                'msg' => 'ไม่สามารถสร้างเอกสารได้'
            );

            return Response::json( $response );
        }

        //response error
        $response = array(
            'status' => 'error',
            'msg' => 'ข้อมูลไม่ครบถ้วนที่จะออกเอกสาร'
        );

        return Response::json( $response );

    }

    //return json party schedule data
    public function getTasks()
    {
        $lu_tasks = array();

        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            'party_id' => 'required|integer'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        if ($validator->passes())
        {
            //get latest schedule id
            $schedule = LuSchedule::where('party_id', '=', Input::get('party_id'))->orderBy('created_at', 'DESC')->first();
            if ($schedule)
            {
                $title_lang = (Input::get('lang')=='en') ? 'title_en' : 'title_th';
                //plan a = #265a88 , plan b = #c02e2a
                $tasks = LuScheduleTask::where('lu_schedule_id', '=', $schedule->id)
                    ->where('start', '>=', Input::get('start'))
                    ->where('end', '<=', Input::get('end'))
                    ->select(
                        'lu_schedule_tasks.*',
                        DB::raw($title_lang.' AS title'),
                        DB::raw('id AS id'),
                        DB::raw('(CASE WHEN type = "S" THEN DATE_FORMAT(start,"%Y-%m-%d") ELSE start END) AS start'),
                        DB::raw('(CASE WHEN type = "S" THEN DATE_FORMAT(DATE_ADD(end,INTERVAL 1 DAY),"%Y-%m-%d") ELSE end END) AS end'),
                        DB::raw('(CASE WHEN is_plan_b = 0 THEN "#265a88" ELSE "#c02e2a" END) AS color'),
                        DB::raw('(CASE WHEN is_plan_b = 0 THEN "A" ELSE "B" END) AS plan'),
                        DB::raw('DATE_FORMAT(start,"%Y-%m-%d") as event_date'),
                        DB::raw('DATE_FORMAT(start,"%H:%i") as event_time_start'),
                        DB::raw('DATE_FORMAT(end,"%H:%i") as event_time_end'),
                        DB::raw('start as start_date'),
                        DB::raw('end as end_date')
                        //DB::raw('(CASE WHEN type = "S" THEN true ELSE false END) AS allDay')
                        //DB::raw('(SELECT locations.name FROM locations LEFT JOIN lu_schedule_task_locations ON locations.id=lu_schedule_task_locations.location_id WHERE lu_schedule_task_locations.lu_schedule_task_id=lu_schedule_tasks.id) AS locations')
                    );

                $plans = array();

                if (Input::get('plan_a')==='true')
                {
                    array_push($plans, 0);
                }
                if (Input::get('plan_b')==='true')
                {
                    array_push($plans, 1);
                }

                $tasks = $tasks->whereIn('is_plan_b', $plans);

                $tasks = $tasks->get()->toArray();

                //loop to add location
                $n = 0;
                foreach($tasks as $task)
                {
                    $lu_tasks[] = $task;
                    //add minute difference
                    $lu_tasks[$n]['minute_diff'] = $this->checkMinuteDiff($task['start_date'], $task['end_date']);
                    //add hour difference
                    $lu_tasks[$n]['hour_diff'] = $this->checkHourDiff($task['start_date'], $task['end_date']);
                    //add full text day
                    $lu_tasks[$n]['full_date'] = ScheduleController::dateRangeStr($task['start_date'], $task['end_date'], true, false, 'th', true);
                    //add count day
                    $lu_tasks[$n]['count_days'] = count(ScheduleController::dateRangeArray($task['start_date'], $task['end_date']));
                    //find location by schedule task
                    $locations = Location::leftJoin('lu_schedule_task_locations AS tl', 'locations.id', '=', 'tl.location_id')
                        ->where('tl.lu_schedule_task_id', '=', $task['id'])
                        ->select('locations.name', 'locations.id', 'tl.id AS task_location_id')
                        ->get();

                    $lu_locations = "";
                    $lu_location_ids = array();
                    $lu_task_location_ids = array();
                    $lu_activity_locations = array();

                    foreach ($locations as $location)
                    {
                        $lu_locations .= $location->name.',';//add location name
                        array_push($lu_location_ids, $location->id);//add location id
                        array_push($lu_task_location_ids, $location->task_location_id);//add task location id
                        $lu_activity_locations[$location->task_location_id] = $location->id;//add object task location is key , location is value
                    }

                    $lu_tasks[$n]['locations'] = substr($lu_locations, 0, -1);
                    $lu_tasks[$n]['id_locations'] = $lu_location_ids;
                    $lu_tasks[$n]['id_task_locations'] = $lu_task_location_ids;
                    $lu_tasks[$n]['activity_locations'] = $lu_activity_locations;

                    $n++;
                }
            }
        }

        return $lu_tasks;
    }
	
	/*Search Activities and Selection*/
	public function postSearchActivities()
	{
		if (Input::get('search'))
        {
			$lang = Input::get('lang');
			$locations = Input::get('locations');
            //find by query word
            $search = LocationActivities::where(function ($query) use ($lang) {
							if ($lang=='th')
							{
								$query->where('title_th', 'like', '%'.Input::get('search').'%');
							}
							else
							{
								$query->where('title_en', 'like', '%'.Input::get('search').'%');
							}
                        });
			
			/*query for location*/
			if ($locations!='')
			{
				$search = $search->where('location_id', function($q) use ($locations){
					$q->select('id')->from('locations')->whereIn('id', $locations);
				});
			}			
						
			if ($lang=='th')
			{
				$search = $search->select('location_activities.id AS id',  'location_activities.title_th AS name');
			}
			else
			{
				$search = $search->select('location_activities.id AS id',  'location_activities.title_en AS name');
			}			
								
            $search = $search->get()->toJson();

            return $search;
        }

        return array();
	}
	
	/*Get Activity Object*/
	public function getActivity()
	{
		if (Input::get('id'))
		{
			$activity = LocationActivities::find(Input::get('id'));
			
			return $activity;
		}
		
		return array();
	}

    /*Load Location by type*/
    public function getLocationBySchedule()
    {
        $location_lists = array();

        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            'schedule_type' => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        if ($validator->passes())
        {
            $schedule_type = Input::get('schedule_type');
            $location_lists = self::areaLocations($schedule_type);
        }

        return $location_lists;
    }

    /*update time and date by drag drop*/
    public function postCalendarTime()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        $rules = array(
            'id'   => 'required',
            'event' => 'required',
            'calendar'   => 'array'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            //edit in lu_schedule_plans
            $schedule_task = LuScheduleTask::find(Input::get('id'));

            $start = new DateTime(Input::get('start'));
            $end = new DateTime(Input::get('end'));

            if (Input::get('event')=='dd')
            {
                $start->modify(Input::get('calendar.days').' day');
                $start->modify(Input::get('calendar.hours').' hour');
                $start->modify(Input::get('calendar.minutes').' minute');
                $start->modify(Input::get('calendar.months').' month');
                $start->modify(Input::get('calendar.years').' year');
                $schedule_task->start = $start->format('Y-m-d H:i:s');
            }

            $end->modify(Input::get('calendar.days').' day');
            $end->modify(Input::get('calendar.hours').' hour');
            $end->modify(Input::get('calendar.minutes').' minute');
            $end->modify(Input::get('calendar.months').' month');
            $end->modify(Input::get('calendar.years').' year');
            $schedule_task->end = $end->format('Y-m-d H:i:s');

            //check day over event range not allow
            /*if ($this->checkOverTime($schedule_task)==true)
            {
                $response = array(
                    'status' => 'error',
                    'msg' => 'กิจกรรมนี้ไม่สามารถสร้างในวันอื่นที่ไม่ได้กำหนดในคณะนี้ได้'
                );

                return Response::json( $response );
            }*/
            //check duplicate time before
            /*if ($this->checkOverlapEvent($schedule_task)==true)
            {
                $response = array(
                    'status' => 'error',
                    'msg' => 'กิจกรรมนี้ไม่สามารถสร้างเวลาซ้อนกับกิจกรรมอื่นได้'
                );

                return Response::json( $response );
            }*/

            if ($schedule_task->save())
            {
                //response success
                $response = array(
                    'status' => 'success',
                    'msg' => 'สร้างรายการใหม่สำเร็จแล้ว'
                );

                return Response::json( $response );
            }
            //response error
            $response = array(
                'status' => 'error',
                'msg' => 'ไม่สามารถสร้างรายการได้'
            );

            return Response::json( $response );
        }
        //response error
        $response = array(
            'status' => 'error',
            'msg' => $validator->messages()
        );

        return Response::json( $response );
    }

    /*Create Event*/
    public  function postCreate()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        if (Input::get( 'type' )=='S')
        {
            $rules = array(
                'accom_date'   => 'required',
                'party_id'   => 'required|integer',
                'locations' => 'required',
                'plan' => 'required'
            );
        }
        else
        {
            $rules = array(
                'event_date'   => 'required',
                'party_id'   => 'required|integer',
                'type'   => 'required',
                'locations' => 'required',
                'event_time_start' => 'required',
                'event_time_end' => 'required',
                'plan' => 'required'
            );
        }

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            //use db transaction
            DB::beginTransaction();

            try
            {
                //check latest revision to get schedule id, if init create new revision 0
                $check_revision = LuSchedule::where('party_id', '=', Input::get('party_id'))->count();
                if ($check_revision > 0)
                {
                    //get latest version
                    $lu_schedule = LuSchedule::where('party_id', '=', Input::get('party_id'))->orderBy('created_at', 'DESC')->first();
                }
                else
                {
                    //create new entry
                    $lu_schedule = new LuSchedule;
                    $lu_schedule->party_id = Input::get('party_id');
                    $lu_schedule->revision = 0;
                    $lu_schedule->created_by = Auth::user()->id;
                    $lu_schedule->updated_by = Auth::user()->id;
                    $lu_schedule->save();

                    //update status to กำลังเตรียมการรับคณะ
                    $party = Party::find(Input::get('party_id'));
                    $status = 'preparing';
                    PartyController::updateStatus($party, $status);
                }

                //create schedule task
                $task = new LuScheduleTask;
                //set for keep location
                $locations = array();
                //define time
                if (Input::get( 'type' )=='S')
                {
                    $start_date = Input::get('accom_date');

                    $night = strval(intval(Input::get('accom_night'))-1);//delete 1 day because include start day
                    $end_date = date_add(date_create(Input::get('accom_date')),date_interval_create_from_date_string($night." days"));

                    $task['remark'] = Input::get('remark');
                }
                else
                {
                    $start_date = Input::get('event_date').' '.Input::get('event_time_start');
                    $end_date = Input::get('event_date').' '.Input::get('event_time_end');
                    //declare only activity
                    $task['title_th'] = Input::get('title_th');
                    $task['title_en'] = Input::get('title_en');
                    $task['note_th'] = Input::get('note_th');
                    $task['note_en'] = Input::get('note_en');
                }

                //add location to array
                foreach(Input::get('locations') as $location)
                {
                    array_push($locations, $location);
                }

                //create new schedule task
                $task['lu_schedule_id'] = $lu_schedule->id;
                $task['start'] = $start_date;
                $task['end'] = $end_date;
                $task['is_plan_b'] = (Input::get('plan')=='B') ? 1 : 0;
                $task['location_activity_id'] = Input::get('location_activity_id');
                $task['type'] = Input::get( 'type' );
                $task['created_by'] = Auth::user()->id;
                $task['updated_by'] = Auth::user()->id;
                $task->save();
               
                //save task location
                foreach ($locations as $l)
                {
                    $task_location = new LuScheduleTaskLocation;
                    $task_location->lu_schedule_task_id	= $task->id;
                    $task_location->location_id = $l;
                    $task_location->save();
                }

                //Request Commit and send response
                DB::commit();
                //response success
                $response = array(
                    'status' => 'success',
                    'msg' => 'สร้างรายการใหม่สำเร็จแล้ว'
                );

                return Response::json( $response );
            }
            catch(\Exception $e)
            {
                DB::rollback();

                $response = array(
                    'status' => 'error',
                    'msg' => $e->getMessage(),
                );

                return Response::json( $response );
            }
        }
        //response error
        $response = array(
            'status' => 'error',
            'msg' => $validator->messages()
        );

        return Response::json( $response );
    }

    /*Edit Event Plan A or B Only*/
    public function postEdit()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        //id in this case is lu_schedule_plans.id
        if (Input::get( 'type' )=='S')
        {
            $rules = array(
                'id'   => 'required|integer',
                'accom_date'   => 'required',
                'locations' => 'required',
            );
        }
        else
        {
            $rules = array(
                'id'   => 'required|integer',
                'event_date'   => 'required',
                'locations' => 'required',
                'event_time_start' => 'required',
                'event_time_end' => 'required',
            );
        }

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $schedule_task = LuScheduleTask::find(Input::get('id'));
            if($schedule_task)
            {
                if (Input::get( 'type' )=='S')
                {
                    $start_date = Input::get('accom_date');
                    $night = strval(intval(Input::get('accom_night'))-1);//delete 1 day because include start day
                    $end_date = date_add(date_create(Input::get('accom_date')),date_interval_create_from_date_string($night." days"));
                    //redeclare value
                    $end_date = date_format($end_date,"Y-m-d");
                    //declare for remark
                    $schedule_task->remark = Input::get('remark');
                }
                else
                {
                    $start_date = Input::get('event_date').' '.Input::get('event_time_start');
                    $end_date = Input::get('event_date').' '.Input::get('event_time_end');
                    //declare only activity
                    $schedule_task->title_th = Input::get('title_th');
                    $schedule_task->note_th = Input::get('note_th');
                    $schedule_task->title_en = Input::get('title_en');
                    $schedule_task->note_en = Input::get('note_en');
                }

                $schedule_task->start = $start_date;
                $schedule_task->end = $end_date;
                $schedule_task->is_plan_b = (Input::get('plan')=='B') ? 1 : 0;
                $schedule_task->updated_at = date('Y-m-d H:i:s');

                if($schedule_task->save())
                {
                    //หากมีกรณีแก้ไขพวก Location เกิดขึ้น
                    $old_locations = json_decode(Input::get('old_locations'));//this is old location
                    $selected_locations = Input::get('locations');//this is selected location

                    //$activated_locations = Input::get('active_locations');//this is activated location
                    //$task_locations = Input::get('task_locations');//this is task locations

                    $compare_for_add_locations = array_diff($selected_locations, $old_locations); //get array ready for add new
                    $compare_for_del_locations = array_diff($old_locations, $selected_locations); //get array ready for delete

                    //Check ว่าหากมีการเปลี่ยนแปลงว่าเพิ่ม
                    if (count($compare_for_add_locations) > 0)
                    {
                        foreach($compare_for_add_locations as $add_location)
                        {
                            $task_location = new LuScheduleTaskLocation;
                            $task_location->lu_schedule_task_id	= $schedule_task->id;
                            $task_location->location_id = $add_location;
                            $task_location->save();
                        }
                    }
                    //Check ว่าหากมีการเปลี่ยนแปลงว่าลบ
                    if (count($compare_for_del_locations) > 0)
                    {
                        foreach($compare_for_del_locations as $del_location)
                        {
                            $task_location = LuScheduleTaskLocation::where('lu_schedule_task_id', '=', $schedule_task->id)
                                            ->where('location_id', '=', $del_location);
                            //foreach to get task_location_id
                            $array_tasks = array();
                            foreach($task_location->get() as $task)
                            {
                                array_push($array_tasks, $task->id);
                            }
                            //and delete budget
                            LuBudgetDetail::whereIn('lu_schedule_task_location_id', $array_tasks)->delete();
                            LuBudgetDetailTemp::whereIn('lu_schedule_task_location_id', $array_tasks)->delete();
                            //and also delete task location at last
                            $task_location->delete();
                        }
                    }

                    //response success
                    $response = array(
                        'status' => 'success',
                        'msg' => 'บันทึกรายการสำเร็จแล้ว'
                    );

                    return Response::json( $response );
                }

                //response error
                $response = array(
                    'status' => 'error',
                    'msg' => 'ไม่สามารถบันทึกรายการได้'
                );

                return Response::json( $response );
            }
            //response error
            $response = array(
                'status' => 'error',
                'msg' => 'รายการถูกลบไปแล้ว'
            );

            return Response::json( $response );
        }
        //response error from validate
        $response = array(
            'status' => 'error',
            'msg' => $validator->messages()
        );

        return Response::json( $response );
    }

    /*post to copy a cell of accommodation or event*/
    public function postCopy()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            'party_id'   => 'required|integer',
            'copy'   => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            DB::beginTransaction();

            try
            {
                $schedule_task = new LuScheduleTask;
                $schedule_task->lu_schedule_id = Input::get('lu_schedule_id');

                if (Input::get( 'type' )=='S')
                {
                    $start_date = Input::get('copy.accom_date');
                    $night = strval(intval(Input::get('copy.accom_night'))-1);//delete 1 day because include start day
                    $end_date = date_add(date_create(Input::get('copy.accom_date')),date_interval_create_from_date_string($night." days"));
                    //redeclare value
                    $end_date = date_format($end_date,"Y-m-d");
                    //declare for remark
                    $schedule_task->remark = Input::get('remark');
                    $schedule_task->type = Input::get( 'type' );
                }
                else
                {
                    $start_date = Input::get('copy.event_date').' '.Input::get('copy.event_time_start');
                    $end_date = Input::get('copy.event_date').' '.Input::get('copy.event_time_end');
                    //declare only activity
                    $schedule_task->title_th = Input::get('title_th');
                    $schedule_task->note_th = Input::get('note_th');
                    $schedule_task->title_en = Input::get('title_en');
                    $schedule_task->note_en = Input::get('note_en');
                    $schedule_task->type = Input::get( 'type' );
                }

                $schedule_task->start = $start_date;
                $schedule_task->end = $end_date;
                $schedule_task->is_plan_b = (Input::get('copy.plan')=='B') ? 1 : 0;
                $schedule_task->created_by = Auth::user()->id;
                $schedule_task->updated_by = Auth::user()->id;

                if($schedule_task->save())
                {
                    //loop fill in locations
                    foreach(Input::get('locations') as $location)
                    {
                        $task_location = new LuScheduleTaskLocation;
                        $task_location->lu_schedule_task_id	= $schedule_task->id;
                        $task_location->location_id = $location;
                        $task_location->save();
                    }

                    //Request Commit and send response
                    DB::commit();
                    //response success
                    $response = array(
                        'status' => 'success',
                        'msg' => 'คัดลอกรายการสำเร็จแล้ว'
                    );

                    return Response::json( $response );
                }
                //exception when not save
                throw new Exception("ไม่สามารถทำการคัดลอกข้อมูลได้");
            }
            catch(\Exception $e)
            {
                DB::rollback();

                $response = array(
                    'status' => 'error',
                    'msg' => $e->getMessage(),
                );

                return Response::json( $response );
            }
        }
        //response error from validate
        $response = array(
            'status' => 'error',
            'msg' => $validator->messages()
        );

        return Response::json( $response );
    }

    /*Delete Event*/
    public function postDelete()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            'id' => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            //use db transaction
            DB::beginTransaction();

            try
            {
                $schedule_task = LuScheduleTask::find(input::get('id'));
                //create array task location
                $task_locations = array();
                foreach($schedule_task->taskLocations()->get()->toArray() as $task_location)
                {
                    array_push($task_locations, $task_location['id']);
                }
                //also delete budget if have
                $budget_detail = LuBudgetDetail::whereIn('lu_schedule_task_location_id', $task_locations);
                $budget_detail->delete();

                if ($schedule_task->taskLocations()->delete())
                {
                    if ($schedule_task->delete())
                    {
                        //Request Commit and send response
                        DB::commit();
                        //finally adjust budget if have
                        //LuBudget::adjust(Input::get('party_id'));
                        //response success
                        $response = array(
                            'status' => 'success',
                            'msg' => 'ลบสำเร็จแล้ว'
                        );

                        return Response::json( $response );
                    }
                }

                throw new Exception("ไม่สามารถทำการลบข้อมูลได้");
            }
            catch(\Exception $e)
            {
                DB::rollback();

                $response = array(
                    'status' => 'error',
                    'msg' => $e->getMessage(),
                );

                return Response::json( $response );
            }
        }
        //response error
        $response = array(
            'status' => 'error',
            'msg' => $validator->messages()
        );

        return Response::json( $response );
    }

    //return party select2
    function selectParties()
    {
        /*send party with approve from manager*/
        $parties = $this->party->getManagerPassed()
            ->select(array('parties.id', 'parties.customer_code', 'parties.name', 'parties.people_quantity'))
            ->get();

        return $parties;
    }

    //function check datetime as minute diff
    function checkMinuteDiff($start, $end)
    {
        $start_date = new DateTime($start);
        $end_date = new DateTime($end);

        return $start_date->diff($end_date)->format("%i");
    }

    //function check datetime as hour diff
    function checkHourDiff($start, $end)
    {
        $start_date = new DateTime($start);
        $end_date = new DateTime($end);

        return $start_date->diff($end_date)->format("%H");
    }

    //function check duplicate or overlap event
    function checkOverlapEvent($plan)
    {
        $checkOverlap = LuSchedulePlan::leftJoin('lu_schedules AS l', 'lu_schedule_plans.lu_schedule_id', '=', 'l.id')
            ->where('l.party_id', '=', $plan->event->party_id)
            ->where('lu_schedule_plans.plan', '=', $plan->plan)
            ->where('start', '<=', $plan->end)
            ->where('end', '>=', $plan->start)
            ->whereNotIn('lu_schedule_plans.id', array($plan->id));

        if ($plan->event->type=='S')
        {
            $checkOverlap = $checkOverlap->where('l.type', '=', 'S');
        }
        else
        {
            $checkOverlap = $checkOverlap->where('l.type', '<>', 'S');
        }

        $checkOverlap = $checkOverlap->count();

        //dd(DB::getQueryLog());
        //if have overlap cannot pass
        if ($checkOverlap > 0)
        {
           return true;
        }
        //can pass this criteria
        return false;
    }

    //function check time over party define
    function checkOverTime($plan)
    {
        //alogorithm check start date over range not allow
        $party = Party::find($plan->event->party_id);

        if (in_array(substr($plan->end, 0, 10), $this->dateRangeArray($party->start_date,$party->end_date)))
        {
            //can pass this criteria
            return false;
        }
       //not pass because
        return true;
    }

    //function return array with area and locations
    public static function areaLocations($schedule_type = 'A')
    {
        $area_locations = array();
        //select areas to grouping
        $areas = MflfArea::all();
        foreach($areas as $area)
        {
            $area_locations[$area->name] = array();
            //select location in area
            $location_in_areas = Location::leftjoin('mflf_locations', 'mflf_locations.location_id', '=', 'locations.id')
                ->leftjoin('provinces', 'provinces.PROVINCE_ID', '=', 'locations.province')
                ->leftjoin('amphurs', 'amphurs.AMPHUR_ID', '=', 'locations.city')
                ->leftjoin('districts', 'districts.DISTRICT_ID', '=', 'locations.district')
                ->select(array('locations.id AS id', DB::raw("CONCAT(locations.name, ' ต.', districts.DISTRICT_NAME, ' อ.', amphurs.AMPHUR_NAME, ' จ.', provinces.PROVINCE_NAME) AS text")))
                ->where('mflf_locations.mflf_area_id', '=', $area->id);

            switch($schedule_type)
            {
                case 'S' :
                    //sleep
                    $location_in_areas = $location_in_areas->where('locations.is_accommodation', '=', 1)->get();
                    break;
                default:
                    //activities return all
                    $location_in_areas = $location_in_areas->get();
            }

            $area_locations[$area->name] = $location_in_areas;
        }

        //select location not in mflf development area
        $area_locations['สถานที่อื่นๆ'] = array();
        $location_out_areas = Location::leftjoin('provinces', 'provinces.PROVINCE_ID', '=', 'locations.province')
            ->leftjoin('amphurs', 'amphurs.AMPHUR_ID', '=', 'locations.city')
            ->leftjoin('districts', 'districts.DISTRICT_ID', '=', 'locations.district')
            ->select(array('locations.id AS id', DB::raw("CONCAT(locations.name, ' ต.', districts.DISTRICT_NAME, ' อ.', amphurs.AMPHUR_NAME, ' จ.', provinces.PROVINCE_NAME) AS text")))
            ->whereNotIn('locations.id', function($q){
                $q->select('location_id')->from('mflf_locations');
            })
            ->get();

        $area_locations['สถานที่อื่นๆ'] = $location_out_areas;

        return $area_locations;
    }

    //function check if have another plan
    public static function haveAnotherPlan($party_id)
    {
        $have_b_plan = LuScheduleTask::leftJoin('lu_schedules as s', 's.id', '=', 'lu_schedule_tasks.lu_schedule_id')
                        ->where('s.party_id', '=', $party_id)
                        ->whereNotIn('lu_schedule_tasks.type', array('S'))
                        ->where('lu_schedule_tasks.is_plan_b', '=', 1)
                        ->count();

        return $have_b_plan;
    }

    //return date range is array
    public static function dateRangeArray($startDate, $endDate)
    {
        $dateRanges = array();

        $begin = new DateTime( $startDate );
        $end = new DateTime(date('Y-m-d', strtotime($endDate . ' + 1 day')));

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);

        foreach ( $period as $dt )
        {
            $dateRanges[] = $dt->format( "Y-m-d" );
        }

        return $dateRanges;
    }

    //return string date thai format
    public static function dateRangeStr($startDate, $endDate, $yearThai = false, $weekDay = false, $lang = 'th', $dateTime = false, $minimize = false)
    {
        $strYearStart = date("Y",strtotime($startDate));
        $strMonthStart = date("n",strtotime($startDate));
        $strDayStart = date("j",strtotime($startDate));
        $strWeekdayStart = date("w",strtotime($startDate));
        $strHourStart = date("H",strtotime($startDate));
        $strMinuteStart = date("i",strtotime($startDate));

        $strYearEnd = date("Y",strtotime($endDate));
        $strMonthEnd = date("n",strtotime($endDate));
        $strDayEnd = date("j",strtotime($endDate));
        $strWeekdayEnd = date("w",strtotime($endDate));
        $strHourEnd = date("H",strtotime($endDate));
        $strMinuteEnd = date("i",strtotime($endDate));
        /*if minimize*/
        if ($minimize)
        {
            $strMonthCut = Array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
            $strWeekdayCut = Array("","จ.","อ.","พ.","พฤ.","ศ.","ส.","อ.");
        }
        else
        {
            $strMonthCut = Array("","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
            $strWeekdayCut = Array("","จันทร์","อังคาร","พุธ","พฤหัส","ศุกร์","เสาร์","อาทิตย์");
        }

        /*set month*/
        $strMonthThaiStart = ($lang=='th') ? $strMonthCut[$strMonthStart] : date("F",strtotime($startDate));
        $strMonthThaiEnd = ($lang=='th') ? $strMonthCut[$strMonthEnd] : date("F",strtotime($endDate));
        /*set weekday*/
        $strWeekdayThaiStart = ($lang=='th') ? $strWeekdayCut[$strWeekdayStart] : date("l",strtotime($startDate));
        $strWeekdayThaiEnd = ($lang=='th') ? $strWeekdayCut[$strWeekdayEnd] : date("l",strtotime($endDate));

        if ($yearThai){
            $strYearStart = $strYearStart+543;
            $strYearEnd = $strYearEnd+543;
        }

        if ($weekDay){
            $strDayStart = $strWeekdayThaiStart.' '.$strDayStart;
            $strDayEnd = $strWeekdayThaiEnd.' '.$strDayEnd;
        }

        //dateTime use in only one day range
        $strTimeRange = '';
        if ($dateTime){
            $strTimeStart = $strHourStart.':'.$strMinuteStart;
            $strTimeEnd = $strHourEnd.':'.$strMinuteEnd;
            if ($strTimeStart==$strTimeEnd) {
                $strTimeRange = ' เวลา '.$strTimeStart.' น.';
            }else{
                $strTimeRange = ' เวลา '.$strTimeStart.'-'.$strTimeEnd.' น.';
            }
        }

        //if minimize is true truncate it.
        $strYearStart = ($minimize) ? substr($strYearStart, 2, 2) : $strYearStart;
        $strYearEnd = ($minimize) ? substr($strYearEnd, 2, 2) : $strYearEnd;

        if ($strYearStart==$strYearEnd)
        {
            if ($strMonthStart==$strMonthEnd)
            {
                if ($strDayStart==$strDayEnd)
                {
                    return $strDayEnd.' '.$strMonthThaiEnd.' '.$strYearEnd.$strTimeRange;
                }
                return $strDayStart.' - '.$strDayEnd.' '.$strMonthThaiEnd.' '.$strYearEnd;
            }
            return $strDayStart.' '.$strMonthThaiStart.' - '.$strDayEnd.' '.$strMonthThaiEnd.' '.$strYearEnd;
        }
        return $strDayStart.' '.$strMonthThaiStart.' '.$strYearStart.' - '.$strDayEnd.' '.$strMonthThaiEnd.' '.$strYearEnd;
    }

    //return count of language task in schedule task
    public static function countScheduleLanguage($party_id, $lang = 'th')
    {
        $schedule_task_count = LuScheduleTask::leftJoin('lu_schedules', 'lu_schedules.id', '=', 'lu_schedule_tasks.lu_schedule_id')
                                ->where('lu_schedules.party_id', '=', $party_id)
                                ->where('lu_schedule_tasks.type', '=', 'A');

        if ($lang=='th')
        {
            $schedule_task_count = $schedule_task_count->where('lu_schedule_tasks.title_th', '<>', '');
        }   
        else
        {
            $schedule_task_count = $schedule_task_count->where('lu_schedule_tasks.title_en', '<>', '');
        }   

         $schedule_task_count = $schedule_task_count->count();             
        
        return $schedule_task_count;
    }

}