<?php

class BudgetController extends CoordinatorController {

    protected $party;

    public function __construct(Party $party)
    {
        parent::__construct();
        $this->party = $party;
    }

    //return index page
    public function getIndex()
    {
        $parties = $this->selectParties();

        return View::make('svms/budget/index', compact('parties'));
    }

    //return view page
    public function getView($party)
    {
        //select Party with success program
        $parties = $this->selectParties();
        //query date or date range
        $dates = ScheduleController::dateRangeArray($party->start_date, $party->end_date);
        //if have plan b send it.
        $count_plan_b = ScheduleController::haveAnotherPlan($party->id);
        //array plan to create budgeting template
        $plans = ($count_plan_b==0) ? array('a') : array('a', 'b');
        //static locations
        $locations = ScheduleController::areaLocations();
        //static load all facilities
        //$all_facilities = self::allDataFacilities();
        $all_facilities = array();
		
        //budget type for select except accommodation and location_facility; Addition if also manager authorize to add other
		if(Auth::user()->hasRole('manager'))
		{
			$budget_types = DB::table('lu_budget_types')->whereNotIn('name', array('accommodations', 'location_facilities'))->orderBy('priority', 'ASC')->get();
		}
		else
		{
			//else not is manager not allow to add other
			$budget_types = DB::table('lu_budget_types')->whereNotIn('name', array('other', 'accommodations', 'location_facilities'))->orderBy('priority', 'ASC')->get();
		}
      
        //static load car facilitator
        $car_facilitators = CarFacilitator::all();
        //static load department
        $departments = Department::whereNotIn('id', array(1))->orderBy('id', 'ASC')->get();

        return View::make('svms/budget/index', compact('party', 'parties', 'dates', 'count_plan_b', 'plans', 'locations', 'car_facilitators', 'departments', 'budget_types'));
    }

    //get JSON Absorb data
    public function getAbsorbBudget()
    {
        $party_id = Input::get('party_id');
        //if have plan b send it.
        $count_plan_b = ScheduleController::haveAnotherPlan($party_id);
        //array plan to create budgeting template
        $plans = ($count_plan_b==0) ? array('A') : array('A', 'B');

        $arrays = array();

        foreach($plans as $plan)
        {
            $arrays[$plan] = $this->calculateAbsorbBudget($party_id, $plan);
        }

        return $arrays;
    }

    //get JSON data All off selected in first tab
    public function getBudgetTaskByPlanAndDate()
    {
        //get latest schedule id
        $schedule = LuSchedule::where('party_id', '=', Input::get( 'party_id' ))->orderBy('created_at', 'DESC')->first();

        return $this->taskWithBudgetByPlanAndDate(Input::get( 'party_id' ), $schedule->id, Input::get( 'plan' ), Input::get( 'date' ));
    }

    //get JSON Facility by condition select combo box query only id, text
    public function getFacilityByCondition()
    {
        $facility_type = Input::get('facility_type');
        $location_id = Input::get('location_id');
        $facilitator_id = Input::get('facilitator_id');//for car only

        switch($facility_type)
        {
            case 'accommodations' :
                $items = Accommodation::where('location_id', '=', $location_id)->select('id AS id', 'name AS text')->get();
                break;
            case 'cars' :
                $items = Cars::where('car_facilitator_id', '=', $facilitator_id)->select('id AS id', 'name AS text')->get();
                break;
            case 'foods' :
                $items = Food::where('location_id', '=', $location_id)->select('id AS id', 'name AS text')->get();
                break;
            case 'personnels' :
                $items = PersonnelType::orderBy('priority', 'ASC')->select('id AS id', 'name AS text')->get();
                break;
            case 'tools' :
                $items = Tools::orderBy('name', 'ASC')->select('id AS id', 'name AS text')->get();
                break;
            case 'conferences' :
                //สถานที่ประชุม
                $items = Location::whereIsConference(1)
                    ->whereIn('id', function($q){
                        $q->select('location_id')->from('location_rates');
                    })
                    ->select('id AS id', 'name AS text')
                    ->get();
                break;
            case 'special_events' :
                $items = SpecialEvents::orderBy('name', 'ASC')->select('id AS id', 'name AS text')->get();
                break;
            default:
                $items = array();
        }

        return $items;
    }

    //get JSON Facility information example unit, price rate by facility id
    public function getFacilityInfoById()
    {
        $facility_type = Input::get('facility_type');
        $facility_id = Input::get('facility_id');

        switch($facility_type)
        {
            case 'accommodations' :
                $item = Accommodation::find($facility_id);
                break;
            case 'cars' :
                $item = Cars::find($facility_id);
                $item['rates'] = $item->rates;
                break;
            case 'foods' :
                $item = Food::find($facility_id);
                break;
            case 'personnels' :
                $item = PersonnelType::find($facility_id);
                $item['rates'] = $item->rates;
                break;
            case 'tools' :
                $item = Tools::find($facility_id);
                break;
            case 'conferences' :
                $item = Location::find($facility_id);
                $item['rates'] = $item->rates;
                break;
            case 'special_events' :
                $item = SpecialEvents::find($facility_id);
                break;
            default:
                $item = null;
        }

        return ($item) ? $item : null;
    }

    //save create budget
    public function postCreateOrUpdateBudget()
    {
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        DB::beginTransaction();

        try
        {
            $budget_detail_id = Input::get('expense_budget_id');
            $budget_type = Input::get('expense_type_selected');
			$return_div = Input::get('expense_main_div');//this is use for scroll back

            $budget = (Input::get('expense_new')=='true') ? new LuBudgetDetail : LuBudgetDetail::find($budget_detail_id);

            if ($budget_type=='accommodations')
            {
                $budget->expense_id = Input::get('expense_accommodation_item');
                $budget->cost_price = Input::get('expense_accommodation_item_cost');
                $budget->sale_price = Input::get('expense_accommodation_item_sale');
                $budget->qty = Input::get('expense_accommodation_item_quantity');
            }
            else
            {
                if ($budget_type=='foods')
                {
                    $budget->expense_food_meal = Input::get('expense_food_meal');
                }
                /*Check if rated type example personnels,cars or conferences fill in rate too*/
                if ($budget_type=='personnels' || $budget_type=='cars' || $budget_type=='conferences')
                {
                    $budget->expense_rate = Input::get('expense_facility_rates');
                }
                /*Check if not other fill in ID*/
                if ($budget_type=='other')
                {
                    $budget->expense_text = Input::get('expense_facility_item');
                }
                else
                {
                    if ($budget_type=='personnels' || $budget_type=='cars')
                    {
                        /*fix this for some item type*/
                        $budget->expense_id = Input::get('expense_item_in_budget');
                    }
                    else
                    {
                        /*fix this for rest*/
                        $budget->expense_id = Input::get('expense_facility_item');
                    }
                }

                $budget->cost_price = Input::get('expense_facility_item_cost');
                $budget->sale_price = Input::get('expense_facility_item_sale');
                $budget->qty = Input::get('expense_facility_item_quantity');
            }

            $budget->party_id = Input::get('expense_party');
            $budget->lu_schedule_task_location_id = Input::get('expense_task_location');
            $budget->day = Input::get('expense_date');
            $budget->expense_type = $budget_type;
            $budget->is_plan_b = (Input::get('expense_plan')=='A') ? 0 : 1;
            $budget->master_task_id = Input::get('expense_master_task');
            $budget->created_by = Auth::user()->id;
            $budget->updated_by = Auth::user()->id;

            if($budget->save())
            {
                //Request Commit and send response
                DB::commit();

                //response success
                $response = array(
                    'status' => 'success',
					'div' => $return_div,
                    'msg' => 'ทำการบันทึกงบประมาณสำเร็จแล้ว'
                );

                return Response::json( $response );
            }
            else
            {
                throw new Exception("ไม่สามารถทำการบันทึกงบประมาณได้");
            }
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

    //save create damage
    public function postCreateOrUpdateDamage()
    {
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        DB::beginTransaction();

        try
        {
            $budget_detail_id = Input::get('damage_budget_id');
			$return_div = Input::get('damage_main_div');//this is use for scroll back

            $budget = (Input::get('damage_new')=='true') ? new LuBudgetDetail : LuBudgetDetail::find($budget_detail_id);

            $budget->party_id = Input::get('damage_party');
            $budget->lu_schedule_task_location_id = Input::get('damage_task_location');
            $budget->day = Input::get('damage_date');
            $budget->is_plan_b = (Input::get('damage_plan')=='A') ? 0 : 1;
            $budget->expense_type = 'is_damage';
            $budget->master_task_id = Input::get('damage_master_task');
            $budget->damage_text = Input::get('damage_activity_item');
            $budget->cost_price = Input::get('damage_activity_item_cost');
            $budget->sale_price = Input::get('damage_activity_item_sale');
            $budget->qty = Input::get('damage_activity_item_quantity');
            $budget->is_damage = 1;
            $budget->created_by = Auth::user()->id;
            $budget->updated_by = Auth::user()->id;

            if($budget->save())
            {
                //Request Commit and send response
                DB::commit();

                //response success
                $response = array(
                    'status' => 'success',
					'div' => $return_div,
                    'msg' => 'ทำการบันทึกงบประมาณสำเร็จแล้ว'
                );

                return Response::json( $response );
            }
            else
            {
                throw new Exception("ไม่สามารถทำการบันทึกงบประมาณได้");
            }
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

    //delete expense item
    public function postDeleteBudgetAndDamage()
    {
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        DB::beginTransaction();

        try
        {
            $budget_detail_id = Input::get('id');
            $budget = LuBudgetDetail::find($budget_detail_id);

            if($budget->delete())
            {
                //Request Commit and send response
                DB::commit();

                //response success
                $response = array(
                    'status' => 'success',
                    'msg' => 'ทำการบันทึกงบประมาณสำเร็จแล้ว'
                );

                return Response::json( $response );
            }
            else
            {
                throw new Exception("ไม่สามารถทำการบันทึกงบประมาณได้");
            }
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

    //get JSON Facility for update
    public function getSelectedFacilityById()
    {
        $budget_detail_id = Input::get('budget_detail_id');
        //query budget detail
        $budget_detail = LuBudgetDetail::find($budget_detail_id);

        if (!$budget_detail)
        {
            //return budget is deleted as success for reload
            $response = array(
                'status' => 'error',
                'msg' => 'รายการนี้ได้ถูกลบไปแล้ว',
                'reload' => true
            );
            return Response::json( $response );
        }
        else
        {
            $facility_type = $budget_detail->expense_type;
            $facility_id = $budget_detail->expense_id;

            switch($facility_type)
            {
                case 'accommodations' :
                    $item = Accommodation::find($facility_id);
                    break;
                case 'cars' :
                    $item = Cars::find($facility_id);
                    $item['facilitator'] = $item->facilitator;
                    break;
                case 'foods' :
                    $item = Food::find($facility_id);
                    break;
                case 'personnels' :
                    $item = PersonnelType::find($facility_id);
                    break;
                case 'tools' :
                    $item = Tools::find($facility_id);
                    break;
                case 'conferences' :
                    $item = Location::find($facility_id);
                    break;
                case 'special_events' :
                    $item = SpecialEvents::find($facility_id);
                    break;
                default:
                    $item = null;
            }

            $budget_detail['item'] = $item;
            //return ajax facility information
            $response = array(
                'status' => 'success',
                'data' => $budget_detail,
                'reload' => true
            );
            return Response::json( $response );
        }
    }

    //get JSON damage
    public function getSelectedDamageById()
    {
        $budget_detail_id = Input::get('budget_detail_id');
        //query budget detail
        $budget_detail = LuBudgetDetail::find($budget_detail_id);

        if (!$budget_detail)
        {
            //return budget is deleted as success for reload
            $response = array(
                'status' => 'error',
                'msg' => 'รายการนี้ได้ถูกลบไปแล้ว',
                'reload' => true
            );
            return Response::json( $response );
        }

        //return
        $response = array(
            'status' => 'success',
            'data' => $budget_detail,
            'reload' => true
        );
        return Response::json( $response );
    }

    //return calculate and group facility by type and item
    public function getTypeCalculate()
    {
        //array keep data
        $data = array();
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        $rules = array(
            'party_id' => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
			//Find out if create or save LubudgetDetail Or Lubudget is have permission to access
			$have_budget_detail = LuBudgetDetail::where('party_id', '=', Input::get('party_id'))->count();
			
			if ($have_budget_detail==0)
			{
				//return zero data
				$response = array(
					'status' => 'error',
					'msg' => 'ท่านยังไม่ได้ทำการลงงบประมาณ กรุณาลงงบประมาณก่อน'
			    );
			   return Response::json( $response );
			}
			
            //First if have anyone plan b task allow to keep plan b in array
            $count_plan_b = ScheduleController::haveAnotherPlan(Input::get('party_id'));

            //Query All Budget Type
            $budget_types = DB::table('lu_budget_types')->get();

            //set Parameter to add return calculate select
            $numberOfBudgetPlanA = 0;
            $numberOfBudgetPlanB = 0;
            $numberOfDamagePlanA = 0;
            $numberOfDamagePlanB = 0;

            foreach($budget_types as $budget_type)
            {
                $party_calculates = array();

                //calculate group
                $party_calculates['A'] = $this->calculateBudgetByType(Input::get('party_id'), 'A', $budget_type->name);
                //save number of A
                $numberOfBudgetPlanA += count($party_calculates['A']);

                if ($count_plan_b>0)
                {
                    $party_calculates['B'] = $this->calculateBudgetByType(Input::get('party_id'), 'B', $budget_type->name);
                    //save number of B
                    $numberOfBudgetPlanB += count($party_calculates['B']);
                }

                //push in data array
                $data[$budget_type->name] = $party_calculates;
            }

            //also add damage ค่าใช้จ่าย ค่าเสียหาย
            $data['damages']['A'] = $this->calculateBudgetDamage(Input::get('party_id'), 'A');
            $numberOfDamagePlanA = count($data['damages']['A']);
            if ($count_plan_b>0)
            {
                $data['damages']['B'] = $this->calculateBudgetDamage(Input::get('party_id'), 'B');
                $numberOfDamagePlanB = count($data['damages']['B']);
            }

            //Also add ค่าบริหารจัดการ และส่วนลด
            $budgeting = LuBudget::where('party_id', '=', Input::get('party_id'))->orderBy('created_at', 'DESC')->first();

            $data['charge'] = ($budgeting) ? $budgeting->charge : 5;
            $data['discount'] = ($budgeting) ? $budgeting->discount : 0;
            $data['num_budget_a'] = $numberOfBudgetPlanA;
            $data['num_budget_b'] = $numberOfBudgetPlanB;
            $data['num_damage_a'] = $numberOfDamagePlanA;
            $data['num_damage_b'] = $numberOfDamagePlanB;
            $data['have_other_plan'] = ($count_plan_b>0) ? 1 : 0;

            //return calculated data
            $response = array(
                'status' => 'success',
                'data' => $data
            );
            return Response::json( $response );
        }
        //response error
       $response = array(
            'status' => 'error',
            'msg' => 'ไม่สามารถคำนวนงบประมาณได้'
       );
       return Response::json( $response );
    }

    /*function calculate budget by type to confirm*/
    function calculateBudgetByType($party_id, $plan, $type)
    {
        //set plan
        $is_plan_b = ($plan=='B') ? 1 : 0;
        //setting return data
        $calculates = array();

        //find location to create array header
        if ($type=='accommodations' || $type=='foods' || $type=='location_facilities')
        {
            //algorithm first find all main plan (A) to loop but if calculate b find b first if not have b use a
            $locations = $this->locationBudget($party_id, $type)
                ->where('t.is_plan_b', '=', $is_plan_b)
                ->select('tl.location_id AS location_id', 'l.name AS location_name', 'lu_budget_details.master_task_id AS master_task_id', 'lu_budget_details.lu_schedule_task_location_id AS task_location_id', 't.id AS task_id')
                ->groupBy('tl.location_id', 'l.name', 'lu_budget_details.master_task_id', 'lu_budget_details.lu_schedule_task_location_id', 't.id')
                ->get();

            foreach($locations as $location)
            {
                //set location
                $location_id = $location->location_id;
                $location_name = $location->location_name;

                //setting array keys
                $calculates[$location_name] = array();

                //select plan a before
                //return calculated value group by plan, type, item
                switch($type){
                    case 'accommodations' :
                        $data = LuBudgetDetail::leftJoin('accommodations AS a', 'lu_budget_details.expense_id', '=', 'a.id')
                                ->leftJoin('lu_schedule_task_locations AS tl', 'lu_budget_details.lu_schedule_task_location_id', '=', 'tl.id')
                                ->leftJoin('locations AS l', 'tl.location_id', '=', 'l.id')
                                ->where('lu_budget_details.party_id', '=', $party_id)
                                ->where('tl.location_id', '=', $location_id)
                                ->whereExpenseType('accommodations')
                                ->whereIsDamage(0)
                                ->where('lu_budget_details.is_plan_b', '=', $is_plan_b)
                                ->select(
                                'lu_budget_details.expense_id AS expense_id',
                                'lu_budget_details.cost_price AS cost',
                                'lu_budget_details.sale_price AS sale',
                                'a.name AS expense_name',
                                'a.unit AS expense_unit',
                                'lu_budget_details.qty AS quantity'
                            )
                            ->groupBy(
                                'lu_budget_details.expense_id',
                                'lu_budget_details.cost_price',
                                'lu_budget_details.sale_price',
                                'lu_budget_details.qty',
                                'a.name'
                            );
                        break;
                    case 'foods' :
                        $data = LuBudgetDetail::leftJoin('foods AS f', 'lu_budget_details.expense_id', '=', 'f.id')
                                ->leftJoin('lu_schedule_task_locations AS tl', 'lu_budget_details.lu_schedule_task_location_id', '=', 'tl.id')
                                ->leftJoin('locations AS l', 'tl.location_id', '=', 'l.id')
                                ->where('lu_budget_details.party_id', '=', $party_id)
                                ->where('tl.location_id', '=', $location_id)
                                ->whereExpenseType('foods')
                                ->whereIsDamage(0)
                                ->where('lu_budget_details.is_plan_b', '=', $is_plan_b)
                                ->select(
                                    'lu_budget_details.expense_id AS expense_id',
                                    'lu_budget_details.cost_price AS cost',
                                    'lu_budget_details.sale_price AS sale',
                                    'lu_budget_details.expense_food_meal AS expense_food_meal',
                                    'f.name AS expense_name',
                                    'f.unit AS expense_unit',
                                    'lu_budget_details.qty AS quantity'
                                )
                                ->groupBy(
                                    'lu_budget_details.expense_id',
                                    'lu_budget_details.cost_price',
                                    'lu_budget_details.sale_price',
                                    'lu_budget_details.qty',
                                    'f.name'
                                );
                        break;
                    case 'location_facilities' :
                        $data = LuBudgetDetail::leftJoin('location_facilities AS l', 'lu_budget_details.expense_id', '=', 'l.id')
                            ->leftJoin('lu_schedule_task_locations AS tl', 'lu_budget_details.lu_schedule_task_location_id', '=', 'tl.id')
                            ->leftJoin('locations AS l', 'tl.location_id', '=', 'l.id')
                            ->where('lu_budget_details.party_id', '=', $party_id)
                            ->where('tl.location_id', '=', $location_id)
                            ->whereExpenseType('location_facilities')
                            ->whereIsDamage(0)
                            ->where('lu_budget_details.is_plan_b', '=', $is_plan_b)
                            ->select('lu_budget_details.cost_price AS cost',
                                    'lu_budget_details.sale_price AS sale',
                                    'l.name AS expense_name',
                                    'l.unit AS expense_unit',
                                    DB::raw('SUM(lu_budget_details.qty) AS quantity')
                                )
                                ->groupBy(
                                    'lu_budget_details.expense_id',
                                    'lu_budget_details.cost_price',
                                    'lu_budget_details.sale_price',
                                    'lu_budget_details.qty',
                                    'l.name'
                                );
                        break;
                }

                $data = $data->get()->toArray();

                $n = 0;

                if (count($data)>0)
                {
                    foreach($data as $cal)
                    {
                        $calculates[$location_name][] = $cal;

                        //for calculate accommodation only
                        if ($type=='accommodations')
                        {
                            //find day
                            $accommodation_day = count($this->returnArrayDayUse($party_id, $plan, $type, $calculates[$location_name][$n]['expense_id'], $cal['quantity'], false));

                            $calculates[$location_name][$n]['day'] = $accommodation_day;
                            $calculates[$location_name][$n]['item_total_sale'] = ($cal['sale']*$cal['quantity'])*$accommodation_day;
                            $calculates[$location_name][$n]['item_total_cost'] = ($cal['cost']*$cal['quantity'])*$accommodation_day;
                        }
                        else if($type=='foods')
                        {
                            $food_meal_qty = count($this->returnArrayDayUse($party_id, $plan, $type, $calculates[$location_name][$n]['expense_id'], $cal['quantity'], false));

                            $calculates[$location_name][$n]['expense_food_meal_name'] = $this->foodMealName($calculates[$location_name][$n]['expense_food_meal']);
                            $calculates[$location_name][$n]['day'] = $food_meal_qty;//นับมื้อ
                            $calculates[$location_name][$n]['item_total_sale'] = ($cal['sale']*$cal['quantity'])*$food_meal_qty;
                            $calculates[$location_name][$n]['item_total_cost'] = ($cal['cost']*$cal['quantity'])*$food_meal_qty;
                        }
                        else
                        {
                            //for another is flat rate
                            $calculates[$location_name][$n]['item_total_sale'] = $cal['sale']*$cal['quantity'];
                            $calculates[$location_name][$n]['item_total_cost'] = $cal['cost']*$cal['quantity'];
                        }

                        $calculates[$location_name][$n]['item_total_profit'] = ($calculates[$location_name][$n]['item_total_sale'])-($calculates[$location_name][$n]['item_total_cost']);

                        $n++;
                    }
                }

            }
        }
        else if($type=='cars')
        {
            //query plan a always
            $facilitators = $this->carFacilitatorBudget($party_id)
                ->where('t.is_plan_b', '=', $is_plan_b)
                ->select('cf.id AS car_facilitator_id', 'cf.name AS car_facilitator_name', 'lu_budget_details.lu_schedule_task_location_id AS task_location_id', 't.id AS task_id')
                ->groupBy('cf.id')
                ->get();

            foreach ($facilitators as $facilitator)
            {
                //create array header before
                $car_facilitator_id = $facilitator->car_facilitator_id;
                $car_facilitator_name = $facilitator->car_facilitator_name;

                //setting array keys
                $calculates[$car_facilitator_name] = array();

                $data = LuBudgetDetail::leftJoin('cars AS c', 'lu_budget_details.expense_id', '=', 'c.id')
                    ->where('lu_budget_details.party_id', '=', $party_id)
					->where('c.car_facilitator_id', '=', $car_facilitator_id)
                    ->whereExpenseType('cars')
                    ->whereIsDamage(0)
                    ->where('lu_budget_details.is_plan_b', '=', $is_plan_b)
                    ->select(
                        'lu_budget_details.sale_price AS sale',
                        'lu_budget_details.cost_price AS cost',
                        'lu_budget_details.expense_id AS expense_id',
                        'lu_budget_details.expense_rate AS expense_rate',
                        'c.name AS expense_name',
                        'c.unit AS expense_unit',
                        'lu_budget_details.qty AS quantity'
                    )
                    ->groupBy(
                        'lu_budget_details.expense_id',
                        'lu_budget_details.expense_rate',
                        'lu_budget_details.sale_price',
                        'lu_budget_details.cost_price',
                        'lu_budget_details.qty'
                    );

                $data = $data->get()->toArray();

                $n = 0;

                if (count($data)>0)
                {
                    foreach($data as $cal)
                    {
                        $calculates[$car_facilitator_name][] = $cal;

                        //calculate difference between start and end in day
                        $car_use_in_day = count($this->returnArrayDayUse($party_id, $plan, $type, $calculates[$car_facilitator_name][$n]['expense_id'], $cal['quantity'], $calculates[$car_facilitator_name][$n]['expense_rate']));

                        $calculates[$car_facilitator_name][$n]['day'] = $car_use_in_day;
                        //query rate
                        $car_rate = CarRates::find($cal['expense_rate']);

                        $car_sale_rate = ($cal['sale']==0) ? $car_rate->sale_price : $cal['sale'];
                        $car_cost_rate = ($cal['cost']==0) ? $car_rate->cost_price : $cal['cost'];

                        //redeclare name by add rate
                        $calculates[$car_facilitator_name][$n]['rate'] = ($car_rate) ? $car_rate->name : 'ไม่สามารถระบุได้';

                        $calculates[$car_facilitator_name][$n]['sale'] = $car_sale_rate;
                        $calculates[$car_facilitator_name][$n]['cost'] = $car_cost_rate;
                        //for calculate by decision rate
                        $calculates[$car_facilitator_name][$n]['item_total_sale'] = (($car_sale_rate)*$cal['quantity'])*$car_use_in_day;
                        $calculates[$car_facilitator_name][$n]['item_total_cost'] = (($car_cost_rate)*$cal['quantity'])*$car_use_in_day;

                        $calculates[$car_facilitator_name][$n]['item_total_profit'] = ( $calculates[$car_facilitator_name][$n]['item_total_sale'])-( $calculates[$car_facilitator_name][$n]['item_total_cost']);

                        $n++;
                    }
                }
            }
        }
		else if($type=='other')
		{
			//other query by text	
			$task_locations = $this->taskBudget($party_id, $type)
                ->where('t.is_plan_b', '=', $is_plan_b)
                ->select(
					'lu_budget_details.master_task_id AS master_task_id', 
					'lu_budget_details.lu_schedule_task_location_id AS task_location_id', 
					't.id AS task_id', 
					'lu_budget_details.expense_text AS expense_text',
					'lu_budget_details.qty AS qty',
					'lu_budget_details.sale_price AS sale_price', 
					'lu_budget_details.cost_price AS cost_price'
				)
                ->groupBy(
					'lu_budget_details.expense_text', 
					'lu_budget_details.qty',
					'lu_budget_details.sale_price', 
					'lu_budget_details.cost_price'
				)
                ->get();
				
			$n = 0;
				
            foreach ($task_locations as $location)
            {
				$data = LuBudgetDetail::where('party_id', '=', $party_id)
					->whereExpenseType('other')
					->whereExpenseText($location->expense_text)
					->whereQty($location->qty)
					->whereSalePrice($location->sale_price)
					->whereCostPrice($location->cost_price)
					->whereIsDamage(0)
                    ->where('is_plan_b', '=', $is_plan_b)
                    ->select(
					'cost_price AS cost',
					'sale_price AS sale',
					'expense_text AS expense_name',
					DB::raw('SUM(qty) AS quantity')
				)
				->groupBy(
					'expense_text',
					'sale_price',
					'cost_price'
				);
				
				$data = $data->get()->toArray();

				if (count($data)>0)
				{
					foreach($data as $cal)
					{
						//for another is flat rate
						$calculates[] = $cal; //select in sql
						$calculates[$n]['item_total_sale'] = $cal['sale']*$cal['quantity'];
						$calculates[$n]['item_total_cost'] = $cal['cost']*$cal['quantity'];
						$calculates[$n]['expense_unit'] = 'รายการ';
						$calculates[$n]['item_total_profit'] = ($calculates[$n]['item_total_sale'])-($calculates[$n]['item_total_cost']);
					}
				}	
				$n++;
			} 
		}
		else if($type=='personnels' || $type=='conferences')
		{
			//have rate example personnel and conference	
			$task_locations = $this->taskBudget($party_id, $type)
                ->where('t.is_plan_b', '=', $is_plan_b)
                ->select(
					'lu_budget_details.master_task_id AS master_task_id', 
					'lu_budget_details.lu_schedule_task_location_id AS task_location_id', 
					't.id AS task_id', 
					'lu_budget_details.expense_id AS expense_id',
					'lu_budget_details.expense_rate AS expense_rate', 
					'lu_budget_details.qty AS qty',
					'lu_budget_details.sale_price AS sale_price', 
					'lu_budget_details.cost_price AS cost_price'
				)
                ->groupBy(
					'lu_budget_details.expense_id', 
					'lu_budget_details.expense_rate',
					'lu_budget_details.qty',
					'lu_budget_details.sale_price', 
					'lu_budget_details.cost_price'
				)
                ->get();
				
			$n = 0;
				
            foreach ($task_locations as $location)
            {
				switch($type)
                {
                    case 'personnels' :
                        //special expert group by personnel type not person
                        $data = LuBudgetDetail::leftJoin('personnel_types AS pt', 'lu_budget_details.expense_id', '=', 'pt.id')
                            ->where('lu_budget_details.party_id', '=', $party_id)
                            ->whereExpenseType('personnels')
							->whereExpenseId($location->expense_id)
							->whereExpenseRate($location->expense_rate)
							->whereQty($location->qty)
							->whereSalePrice($location->sale_price)
							->whereCostPrice($location->cost_price)
                            ->whereIsDamage(0)
                            ->where('lu_budget_details.is_plan_b', '=', $is_plan_b)
                            ->select
                            (
                                'lu_budget_details.sale_price AS sale',
                                'lu_budget_details.cost_price AS cost',
                                'pt.id AS expense_id',
                                'pt.name AS expense_name',
                                'lu_budget_details.expense_rate AS expense_rate',
                                'pt.unit AS expense_unit',
                                'lu_budget_details.qty AS quantity'
                            )
                            ->groupBy(
                                'pt.id',
                                'lu_budget_details.sale_price',
                                'lu_budget_details.cost_price',
                                'lu_budget_details.expense_rate',
                                'lu_budget_details.qty'
                            )
                            ->orderBy('pt.priority', 'ASC');
                        break;
                    case 'conferences' :
                        $data = LuBudgetDetail::leftJoin('locations AS c', 'lu_budget_details.expense_id', '=', 'c.id')
                            ->where('lu_budget_details.party_id', '=', $party_id)
                            ->whereExpenseType('conferences')
							->whereExpenseId($location->expense_id)
							->whereExpenseRate($location->expense_rate)
							->whereQty($location->qty)
							->whereSalePrice($location->sale_price)
							->whereCostPrice($location->cost_price)
                            ->whereIsDamage(0)
                            ->where('lu_budget_details.is_plan_b', '=', $is_plan_b)
                            ->select
							(
								'lu_budget_details.sale_price AS sale',
								'lu_budget_details.cost_price AS cost',
								'lu_budget_details.expense_id AS expense_id',
								'c.name AS expense_name',
								'lu_budget_details.expense_rate AS expense_rate',
								'lu_budget_details.qty AS quantity'
							)
                            ->groupBy(
                                'lu_budget_details.expense_id',
                                'lu_budget_details.sale_price',
                                'lu_budget_details.cost_price',
                                'lu_budget_details.expense_rate',
                                'lu_budget_details.qty',
                                'c.name'
                            );
                        break;
                }
				
				$data = $data->get()->toArray();

				if (count($data)>0)
				{
					foreach($data as $cal)
					{
						$calculates[] = $cal;

						if ($type=='personnels')
						{
							//calculate difference between start and end in day
							$personnel_use_in_day = count($this->returnArrayDayUse($party_id, $plan, $type, $cal['expense_id'], $cal['quantity'], $cal['expense_rate']));
							$calculates[$n]['day'] = $personnel_use_in_day;
							//query rate by start use time to end use time
							$personnel_type_rate = PersonnelTypeRates::find($cal['expense_rate']);
							//if finder rate
							$personnel_sale_rate = ($cal['sale']==0) ? $personnel_type_rate->sale_price : $cal['sale'];
							$personnel_cost_rate = ($cal['cost']==0) ? $personnel_type_rate->cost_price : $cal['cost'];

							$calculates[$n]['sale'] = $personnel_sale_rate;
							$calculates[$n]['cost'] = $personnel_cost_rate;
							//redeclare name by add rate
							$calculates[$n]['rate'] = $personnel_type_rate->name;
							//for calculate by decision rate
							$calculates[$n]['item_total_sale'] = ($personnel_sale_rate*$cal['quantity'])*$personnel_use_in_day;
							$calculates[$n]['item_total_cost'] = ($personnel_cost_rate*$cal['quantity'])*$personnel_use_in_day;
						}
						
						if ($type=='conferences')
						{
							//get count use day for เหมาวัน
							$conference_use_in_day = count($this->returnArrayDayUse($party_id, $plan, $type, $calculates[$n]['expense_id'], $cal['quantity'], $cal['expense_rate']));
							//query rate by start use time to end use time
							$conference_rate = LocationRates::find($cal['expense_rate']);
							
							if ($conference_rate)
							{
								$conference_sale_rate = ($cal['sale']==0) ? $conference_rate->sale_price : $cal['sale'];
								$conference_cost_rate = ($cal['cost']==0) ? $conference_rate->cost_price : $cal['cost'];
								$conference_rate_name = $conference_rate->name;
							}
							else
							{
								$conference_sale_rate = $cal['sale'];
								$conference_cost_rate = $cal['cost'];
								$conference_rate_name = '';
							}
							
							//set per conference rate
							$calculates[$n]['sale'] = $conference_sale_rate;
							$calculates[$n]['cost'] = $conference_cost_rate;
							$calculates[$n]['day'] = $conference_use_in_day;

							//redeclare name by add rate
							$calculates[$n]['rate'] = $conference_rate_name;

							$calculates[$n]['expense_unit'] = "ห้อง";
							//for calculate by decision rate
							$calculates[$n]['item_total_sale'] = ($conference_sale_rate)*$conference_use_in_day;
							$calculates[$n]['item_total_cost'] = ($conference_cost_rate)*$conference_use_in_day;
						}
						
						$calculates[$n]['item_total_profit'] = ($calculates[$n]['item_total_sale'])-($calculates[$n]['item_total_cost']);
					}
				}
				$n++;
			}
		}
        else
        {
            //else tools or special events	
			$task_locations = $this->taskBudget($party_id, $type)
                ->where('t.is_plan_b', '=', $is_plan_b)
                ->select(
					'lu_budget_details.master_task_id AS master_task_id', 
					'lu_budget_details.lu_schedule_task_location_id AS task_location_id', 
					't.id AS task_id', 
					'lu_budget_details.expense_id AS expense_id',
					'lu_budget_details.qty AS qty'
				)
                ->groupBy(
					'lu_budget_details.expense_id',
					'lu_budget_details.qty'
				)
                ->get();
				
			$n = 0;
				
            foreach ($task_locations as $location)
            {
                switch($type)
                {
                    case 'tools' :
						$data = LuBudgetDetail::leftJoin('tools AS t', 'lu_budget_details.expense_id', '=', 't.id')
							->where('lu_budget_details.party_id', '=', $party_id)
							->whereExpenseType('tools')
							->whereExpenseId($location->expense_id)
							->whereQty($location->qty)
							->whereIsDamage(0)
                            ->where('lu_budget_details.is_plan_b', '=', $is_plan_b)
                            ->select(
                                'lu_budget_details.cost_price AS cost',
                                'lu_budget_details.sale_price AS sale',
                                't.name AS expense_name',
                                't.unit AS expense_unit',
                                DB::raw('SUM(lu_budget_details.qty) AS quantity')
                            )
							->groupBy(
								'lu_budget_details.expense_id',
								'lu_budget_details.sale_price',
								'lu_budget_details.cost_price',
								't.name'
							);
                    break;
                    case 'special_events' :
                        $data = LuBudgetDetail::leftJoin('special_events AS se', 'lu_budget_details.expense_id', '=', 'se.id')
                            ->where('lu_budget_details.party_id', '=', $party_id)
                            ->whereExpenseType('special_events')
							->whereExpenseId($location->expense_id)
							->whereQty($location->qty)
                            ->whereIsDamage(0)
                            ->where('lu_budget_details.is_plan_b', '=', $is_plan_b)
                            ->select(
                                'lu_budget_details.cost_price AS cost',
                                'lu_budget_details.sale_price AS sale',
                                'se.name AS expense_name',
                                DB::raw('SUM(lu_budget_details.qty) AS quantity')
                            )
                            ->groupBy(
                                'lu_budget_details.expense_id',
                                'lu_budget_details.sale_price',
                                'lu_budget_details.cost_price',
                                'se.name'
                            );
                        break;
                }
				
				$data = $data->get()->toArray();

				if (count($data)>0)
				{
					foreach($data as $cal)
					{
						$calculates[] = $cal;

						//for another is flat rate
						$calculates[$n]['item_total_sale'] = $cal['sale']*$cal['quantity'];
						$calculates[$n]['item_total_cost'] = $cal['cost']*$cal['quantity'];

						if ($type=='special_events')
						{
							$calculates[$n]['expense_unit'] = 'กิจกรรม';
						}
							
						$calculates[$n]['item_total_profit'] = ($calculates[$n]['item_total_sale'])-($calculates[$n]['item_total_cost']);
					}
				}
				$n++;
            }
        }

        return $calculates;
    }

    /*function return damage*/
    function calculateBudgetDamage($party_id, $plan)
    {
        //set plan
        $is_plan_b = ($plan=='B') ? 1 : 0;
		/*Setting return data*/
        $calculates = array();
		/*At First Loop task location to check*/		
		$task_locations = $this->taskBudget($party_id, false, true)
                ->where('t.is_plan_b', '=', $is_plan_b)
                ->select('lu_budget_details.master_task_id AS master_task_id', 'lu_budget_details.lu_schedule_task_location_id AS task_location_id', 't.id AS task_id')
                ->groupBy('lu_budget_details.id')
                ->get();		

		$d = 0;		
		foreach ($task_locations as $location)
		{
			$damages = LuBudgetDetail::where('party_id', '=', $party_id)
                    ->whereIsDamage(1)
                    ->where('is_plan_b', '=', $is_plan_b)
                    ->get();
			
			if (count($damages)>0)
			{
				foreach($damages as $damage)
				{
					$calculates[$d] = $damage;
					
					$d++;
				}
			}
		}
		
        return $calculates;
    }

    /*This function is Return Use day's Facility AS Array example array('2015-01-01', '2016-01-01')*/
    /*Use for accommodations ,conference, car, personnel or expert*/
    /*For food as meal*/
    function returnArrayDayUse($party_id, $plan, $type, $expense_id, $qty, $expense_rate = false)
    {
        $arrays = array();

        //set plan
        $is_plan_b = ($plan=='B') ? 1 : 0;

        if($type=='accommodations' || $type=='personnels' || $type=='conferences' || $type=='cars')
        {
            $data = LuBudgetDetail::leftJoin('lu_schedule_task_locations AS tl', 'lu_budget_details.lu_schedule_task_location_id', '=', 'tl.id')
                ->leftJoin('lu_schedule_tasks AS t', 'tl.lu_schedule_task_id', '=', 't.id')
                ->where('lu_budget_details.party_id', '=', $party_id)
                ->where('lu_budget_details.expense_id', '=', $expense_id)
                ->where('lu_budget_details.expense_type', '=', $type)
                ->where('lu_budget_details.qty', '=', $qty);

            if ($expense_rate!=false)
            {
                $data = $data->where('lu_budget_details.expense_rate', '=', $expense_rate);
            }

            //change plan it.
            $data = $data->where('lu_budget_details.is_plan_b', '=', $is_plan_b);

            if ($type=='accommodations')
            {
				//for long day activity
                $data = $data->select('t.start AS start_date', 't.end AS end_date')
                ->first();

                $arrays = ScheduleController::dateRangeArray($data->start_date, $data->end_date);
				
				//if select array = 1 re-check if user add same data each day
				if (count($arrays)==1)
				{
					//empty array before
					$arrays = array();
					//select by day use
					$days = LuBudgetDetail::leftJoin('lu_schedule_task_locations AS tl', 'lu_budget_details.lu_schedule_task_location_id', '=', 'tl.id')
					->leftJoin('lu_schedule_tasks AS t', 'tl.lu_schedule_task_id', '=', 't.id')
					->where('lu_budget_details.party_id', '=', $party_id)
					->where('lu_budget_details.expense_id', '=', $expense_id)
					->where('lu_budget_details.expense_type', '=', $type)
					->where('lu_budget_details.qty', '=', $qty);

					if ($expense_rate!=false)
					{
						$days = $days->where('lu_budget_details.expense_rate', '=', $expense_rate);
					}

					$days = $days->where('lu_budget_details.is_plan_b', '=', $is_plan_b);
					$days = $days->select(
							DB::raw('DATE(t.start) AS day')
						)
						->groupBy(DB::raw('DATE(t.start)'))
						->get();

					foreach($days as $d)
					{
						array_push($arrays, $d->day);
					}
				}
            }
            else
            {
                //for every day activity
                $data = $data->select(
                    DB::raw('DATE(t.start) AS day')
                )
                ->groupBy(DB::raw('DATE(t.start)'))
                ->get();

                foreach($data as $d)
                {
                    array_push($arrays, $d->day);
                }
            }
			
        }
        else if($type=='foods')
        {
            $data = LuBudgetDetail::leftJoin('lu_schedule_task_locations AS tl', 'lu_budget_details.lu_schedule_task_location_id', '=', 'tl.id')
				->leftJoin('lu_schedule_tasks AS t', 'tl.lu_schedule_task_id', '=', 't.id')
				->where('lu_budget_details.party_id', '=', $party_id)
				->where('lu_budget_details.expense_id', '=', $expense_id)
				->where('lu_budget_details.expense_type', '=', $type)
				->where('lu_budget_details.qty', '=', $qty)
                ->where('lu_budget_details.is_plan_b', '=', $is_plan_b)
                ->select(
                    DB::raw('lu_budget_details.expense_food_meal AS meal')
                )
                ->groupBy(DB::raw('DATE(t.start)'), 'lu_budget_details.expense_food_meal')
                ->get();

            foreach($data as $m)
            {
                array_push($arrays, $m->meal);
            }
        }

        return $arrays;
    }

    /*function keep selected budget*/
    function keepSelectedBudget($input, $type)
    {
        //get array to loop fill in temp
        $data = null;
        $budgets = array_get($input, $type);

        foreach($budgets as $budget)
        {
            //create new
            $detail = new LuBudgetDetail;
            //set cost and sale
            $budget_cost = 0;
            $budget_sale = 0;
            //assign data
            $detail->party_id = array_get($input, 'party_id');
            $detail->lu_schedule_task_location_id = $budget['task_location'];
            $detail->expense_id = (isset($budget['id'])) ? $budget['id'] : 0;
            $detail->expense_type = $type;
            $detail->expense_food_meal = (isset($budget['meal'])) ? $budget['meal'] : null;
            $detail->cost_price = $budget_cost;
            $detail->sale_price = $budget_sale;
            $detail->qty = (isset($budget['qty'])) ? $budget['qty'] : 0;
            $detail->is_plan_b = ($budget['plan']=='B') ? 1 : 0;
            $detail->master_task_id = $budget['plan'];
            $detail->is_non_charge = ($budget['charge']==1) ? 0 : 1;
            $detail->created_by = Auth::user()->id;
            $detail->updated_by = Auth::user()->id;
            //save temp
            $detail->save();
        }
    }

    /*function to get task ready to create budget*/
    function  taskWithBudgetByPlanAndDate($party_id, $schedule_id ,$plan, $date)
    {
        $tasks = array();
        /*set start and end. Example start 2016-10-01 00:00:00 , end = + 1day*/
        $start_day = date_format(date_create($date), 'Y-m-d H:i:s');
        $end_day = date_format(date_add(date_create($date), date_interval_create_from_date_string('1 days')), 'Y-m-d H:i:s');

        /*For Sleep or Accommodation (S) find if in range*/
        $a = 0;
        $task_accommodations = array();
        $schedule_accommodations = LuScheduleTask::where('lu_schedule_id', '=', $schedule_id)
            ->where('is_plan_b', '=', ($plan=='a') ? 0 : 1)//non capital
            ->where('start', '<=', $start_day)
            ->where('end', '>=', $start_day)
            ->where('type', '=', 'S')//only sleep
            ->get();

        foreach($schedule_accommodations as $schedule_accommodation)
        {
            $task_accommodations[] = $schedule_accommodation;
            //set array data
            $task_accommodations[$a]['task_locations'] = array();

            foreach($schedule_accommodation->taskLocations as $task_accommodation)
            {
                $task_accommodations[$a]['task_locations'] = $task_accommodation;

                $accommodation_location = Location::find($task_accommodation->location_id);
                $task_accommodations[$a]['task_locations']['location_name'] = ($accommodation_location) ? $accommodation_location->name : '';
                //find and set array selected facility
                //1 รายได้
                $task_accommodations[$a]['task_locations']['used_items'] = self::returnSettingBudget($plan, $party_id, $task_accommodation->id, 'S');
                //2 ค่าใช้จ่าย
                $task_accommodations[$a]['task_locations']['damage_items'] = self::returnDamageBudget($plan, $party_id, $task_accommodation->id);
                //$task_accommodations[$a]['task_locations']['damage_items'] = array();
            }

            $a++;
        }

        $tasks['accommodations'] = $task_accommodations;

        /*For Activities (A)*/
        $t = 0;
        $task_schedules = array();
        $schedule_tasks = LuScheduleTask::where('lu_schedule_id', '=', $schedule_id)
            ->where('is_plan_b', '=', ($plan=='a') ? 0 : 1)//non capital
            ->where('start', '>=', $start_day)
            ->where('end', '<=', $end_day)
            ->where('type', '=', 'A')//only activity
            ->orderBy(DB::raw('DATE_FORMAT(start,"%H:%i")'), 'ASC')//order by start time
            ->get();

        foreach($schedule_tasks as $schedule_task)
        {
            $task_schedules[] = $schedule_task;
            $task_schedules[$t]['time_start'] = date_format(date_create($schedule_task->start),"H:i");
            $task_schedules[$t]['time_end'] = date_format(date_create($schedule_task->end),"H:i");
            //set array data
            $task_schedules[$t]['task_locations'] = array();

            foreach($schedule_task->taskLocations as $task_location)
            {
                $task_schedules[$t]['task_locations'] = $task_location;

                $activity_location = Location::find($task_location->location_id);
                $task_schedules[$t]['task_locations']['location_name'] = ($activity_location) ? $activity_location->name : '';
                //find and set array selected facility
                //1 รายได้
                $task_schedules[$t]['task_locations']['used_items'] = self::returnSettingBudget($plan, $party_id, $task_location->id, 'A');
                //2 ค่าใช้จ่าย
                $task_schedules[$t]['task_locations']['damage_items'] = self::returnDamageBudget($plan, $party_id, $task_location->id);
                //$task_schedules[$t]['task_locations']['damage_items'] = array();
            }

            $t++;
        }

        $tasks['activities'] = $task_schedules;

        return $tasks;
    }

    //static function to generate all facilities data ข้อมูลราคาสินค้าทั้งหมด
    public static function allDataFacilities()
    {
        //set array to keep and return budget items
        $facilities = array();

        //for grouping type except location facility เพราะยังไม่ได้ใช้
        $types = DB::table('lu_budget_types')
            ->whereNotIn('name', array('location_facilities', 'other'))
            ->orderBy('priority', 'ASC')->get();

        $count_all_items = 0;//keep number of item;

        foreach($types as $type)
        {
            //set array type to keep
            $facilities[$type->name] = array();

            if ($type->name=='accommodations')
            {
                //if some facilities have relate with location
                $locations = Location::whereIsAccommodation(1)
                            ->whereIn('id', function($q){
                                $q->select('location_id')->from('accommodations');
                            })
                            ->get();

                foreach($locations as $location)
                {
                    $facilities[$type->name][$location->name] = Accommodation::where('location_id', '=', $location->id)->get()->toArray();
                }

            }
            else if($type->name=='foods')
            {
                //if some facilities have relate with location
                $locations = Location::whereIsRestaurant(1)
                            ->whereIn('id', function($q){
                                $q->select('location_id')->from('foods');
                            })
                            ->get();

                foreach($locations as $location)
                {
                    $facilities[$type->name][$location->name] = Food::where('location_id', '=', $location->id)->get()->toArray();
                }
            }
            else if($type->name=='cars')
            {
                //else if cars have facilitator
                $facilitators = CarFacilitator::whereIn('id', function($q){
                                    $q->select('car_facilitator_id')->from('cars');
                                })
                                ->get();

                foreach($facilitators as $facilitator)
                {
                    $cars = Cars::where('car_facilitator_id', '=', $facilitator->id)->get();

                    $c = 0;
                    foreach($cars as $car)
                    {
                        $car[$c] = $car;
                        $car[$c]['rates'] = CarRates::where('car_id', '=', $car->id)->get();

                        $c++;
                    }

                    $facilities[$type->name][$facilitator->name] = $cars;
                }
            }
            else
            {
                switch($type->name)
                {
                    case 'conferences' :
                        $items = Location::with('rates')
                            ->whereIn('id', function($q){
                                $q->select('location_id')->from('location_rates')->where('deleted_at', '=', null);
                            })
                            ->get()
                            ->toArray();
                        //also query rates
                        break;
                    case 'personnels' :
                        $items = PersonnelType::with('rates')->orderBy('priority', 'DESC')->get()->toArray();
                        //also query rates
                        break;
                    case 'tools' :
                        $items = Tools::orderBy('name', 'ASC')->get()->toArray();
                        break;
                    case 'special_events' :
                        $items = SpecialEvents::orderBy('name', 'ASC')->get()->toArray();
                        break;
                }

                //push data in to array
                $facilities[$type->name] = $items;

            }

        }

        $facilities['count_all'] = $count_all_items;//Keep item to display

        return $facilities;
    }

    //return get quotation document
    public function getQuotationAndPriceList()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            'party_id' => 'required|integer',
            'en' => 'required|boolean'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        if ($validator->passes())
        {
            //find party
            $party = Party::find(Input::get('party_id'));
            $en = Input::get('en');
            $budget = Input::get('budget');
            //selective budgeting to create item list price too
            $items = Input::get('items');

            //initial saved budget before
            $saveBudget = self::saveBudgetQuotation($party, $budget);
            if ($saveBudget)
            {
                $document = DocumentController::getQuotationAndPriceList($party, $items, $en);
                if ($document)
                {
                    //response success
                    $response = array(
                        'status' => 'success',
                        'msg' => 'สร้างเอกสารใบเสนอราคาเสร็จแล้ว',
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
                'msg' => 'ไม่สามารถบันทึกข้อมูล'
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

    //static save budget sale and cost to create quotation
    public static function saveBudgetQuotation($party, $budget)
    {
        //find used budget in party
        $lu_budget_id = LuBudget::where('party_id', '=', $party->id)->pluck('id');
        $lu_budget = ($lu_budget_id) ? LuBudget::find($lu_budget_id) : new LuBudget;

        $lu_schedule = LuSchedule::where('party_id', '=', $party->id)->orderBy('created_at', 'DESC')->first();

        $lu_budget->party_id = $party->id;
        $lu_budget->lu_schedule_id = $lu_schedule->id;

        foreach(array_keys($budget) as $key)
        {
            $lu_budget[$key] = $budget[$key];
        }
        //save to database
        if($lu_budget->save())
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    //static return array list of budget select มีรายได้เท่านั้น
    public static function returnSettingBudget($plan, $party_id, $task_location_id, $type = 'A')
    {
        //set array to keep and return budget items
        $budgets = array();

        //for grouping type
        $types = DB::table('lu_budget_types');

        //filter by schedule type
        if ($type=='A')
        {
            //is activity
            $types = $types->whereNotIn('name', array('accommodations'));
        }
        else
        {
            //is sleep or accommodations
            $types = $types->whereIn('name', array('accommodations'));
        }

        $types = $types->orderBy('priority', 'ASC')->get();

        $count_items = 0;//keep number of item;

        foreach($types as $type)
        {
            //set array type to keep
            $budgets[$type->name] = array();

            //query select by type exactly
            $selected_items = LuBudgetDetail::where('party_id', '=', $party_id)
                ->where('lu_schedule_task_location_id', '=', $task_location_id)
                ->whereIsNonCharge(0)
                ->whereIn('expense_type', array($type->name))
                ->whereIsDamage(0)
                ->where('is_plan_b', '=', ($plan=='a') ? 0 : 1)
                ->get();

            //also return facility data example name, unit, price and if have many rates return its.
            $i = 0;//index

            if (count($selected_items)>0)
            {
                //หากมีข้อมูลถึงจะ Loop
                foreach ($selected_items as $selected_item)
                {
                    $selected_items[$i] = $selected_item;

                    switch($selected_item->expense_type)
                    {
                        case 'accommodations' :
                            $item = Accommodation::find($selected_item->expense_id);
                            $have_rates = false;
                            break;
                        case 'cars' :
                            $item = Cars::find($selected_item->expense_id);
                            $have_rates = true;
                            break;
                        case 'foods' :
                            $item = Food::find($selected_item->expense_id);
                            $have_rates = false;
                            break;
                        case 'personnels' :
                            $item = PersonnelType::find($selected_item->expense_id);
                            $have_rates = true;
                            break;
                        case 'tools' :
                            $item = Tools::find($selected_item->expense_id);
                            $have_rates = false;
                            break;
                        case 'conferences' :
                            $item = Location::find($selected_item->expense_id);
                            if ($item)
                            {
                                $item->unit = 'ห้อง';
                            }
                            $have_rates = true;
                            break;
                        case 'special_events' :
                            $item = SpecialEvents::find($selected_item->expense_id);
                            if ($item)
                            {
                                $item->unit = 'คน';
                            }
                            $have_rates = false;
                            break;
                        case 'other' :
                            $item = new stdClass();
                            $item->name = $selected_item->expense_text;
                            $item->unit = '';
                            $have_rates = false;
                            break;
                        default:
                            $item = null;
                    }

                    if ($item)
                    {
                        if ($have_rates)
                        {
                            //find rate cars
                            if ($selected_item->expense_type=='cars')
                            {
                                $rate = CarRates::find($selected_item->expense_rate);
                            }
                            //find rate conferences
                            if ($selected_item->expense_type=='conferences')
                            {
                                $rate = LocationRates::find($selected_item->expense_rate);
                            }
                            //find rate personnels
                            if ($selected_item->expense_type=='personnels')
                            {
                                $rate = PersonnelTypeRates::find($selected_item->expense_rate);
                            }
                            //set price
							if ($selected_item)
							{
								$set_sale_price = $selected_item->sale_price;
								$set_cost_price = $selected_item->cost_price;
							}
							else
							{
								$set_sale_price = $rate->sale_price;
								$set_cost_price = $rate->cost_price;
							}
                      
                            $selected_items[$i]['expense_rate_name'] = ($rate) ? $rate->name : '';
                        }
						else if ($selected_item->expense_type == 'other')
						{
							$set_sale_price = $selected_item->sale_price;
                            $set_cost_price = $selected_item->cost_price;
						}
                        else
                        {
                            $set_sale_price = ($selected_item->sale_price==0) ? $item->sale_price : $selected_item->sale_price;
                            $set_cost_price = ($selected_item->cost_price==0) ? $item->cost_price : $selected_item->cost_price;
                        }

                        if ($selected_item->expense_type=='foods')
                        {
                            $selected_items[$i]['expense_food_meal_name'] = self::foodMealName($selected_item->expense_food_meal);
                        }

                        $selected_items[$i]['expense_type'] = $selected_item->expense_type;
                        $selected_items[$i]['expense_name'] = $item->name;
                        $selected_items[$i]['expense_unit'] = $item->unit;
                        $selected_items[$i]['sale'] = number_format($set_sale_price, 2, '.', ',');
                        $selected_items[$i]['cost'] = number_format($set_cost_price, 2, '.', ',');
                    }
                    else
                    {
                        $selected_items[$i]['expense_type'] = null;
                        $selected_items[$i]['expense_name'] = 'ไม่ได้ระบุรายการ';
                        $selected_items[$i]['expense_unit'] = '';
                        $selected_items[$i]['sale'] = 0;
                        $selected_items[$i]['cost'] = 0;
                    }
                    $selected_items[$i]['quantity'] = $selected_items[$i]['qty'];

                    array_push($budgets[$type->name], $selected_items[$i]);

                    $i++;
                    $count_items +=$i;
                }
            }

        }

        $budgets['count'] = $count_items;//Keep item to display

        return $budgets;
    }

    //static return array list of damage ค่าเสียหาย ค่าใช้จ่ายจากการรับคณะ
    public static function returnDamageBudget($plan, $party_id, $task_location_id)
    {
        //query select damages
        $damage_items = LuBudgetDetail::where('party_id', '=', $party_id)
            ->where('lu_schedule_task_location_id', '=', $task_location_id)
            ->whereIsNonCharge(0)
            ->whereIsDamage(1)
            ->where('is_plan_b', '=', ($plan=='a') ? 0 : 1)
            ->select('lu_budget_details.*', DB::raw('(lu_budget_details.sale_price)*qty AS item_total_sale'))
            ->get();

        return $damage_items;
    }

    //return meal for count
    public static function isMeal($strDate)
    {
        //calculate time range for return meal string
        $strHour= date("H",strtotime($strDate));

        if ($strHour>=6 && $strHour<=9)
        {
            return 'breakfast';
        }

        if ($strHour>=10 && $strHour<=11)
        {
            return 'break_morning';
        }

        if ($strHour>=12 && $strHour<=13)
        {
            return 'lunch';
        }

        if ($strHour>=14 && $strHour<=16)
        {
            return 'break_afternoon';
        }

        if ($strHour>=17 && $strHour<=19)
        {
            return 'dinner';
        }

        if ($strHour>=20 && $strHour<=23)
        {
            return 'night';
        }

        return null;
    }

    //return party select2
    function selectParties()
    {
        /*send party with at least one schedule */
        $parties = $this->party->getProgramedPassed()
            ->select(array('parties.id', 'parties.customer_code', 'parties.name', 'parties.people_quantity'))
            ->get();

        return $parties;
    }

    //return thai date format
    function dateThai($strDate)
    {
        $strYear = date("Y",strtotime($strDate))+543;
        $strMonth= date("n",strtotime($strDate));
        $strDay= date("j",strtotime($strDate));
        $strHour= date("H",strtotime($strDate));
        $strMinute= date("i",strtotime($strDate));
        //$strSeconds= date("s",strtotime($strDate));
        $strMonthCut = Array("","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤษจิกายน","ธันวาคม");
        $strMonthThai=$strMonthCut[$strMonth];
        return "$strDay $strMonthThai $strYear, $strHour:$strMinute น.";
    }
    
    //return days as int
    function dateCountInt($start, $end)
    {
        $start_ts = strtotime($start);
        $end_ts = strtotime($end);

        $diff = $end_ts - $start_ts;

        $date_diff = ceil($diff / 86400);

        return (int)$date_diff;
    }

    //return diff time as float
    function hourDiffFloat($start, $end)
    {
        $time_start = new DateTime(date('Y-m-d H:i:s', strtotime($start)));
        $time_end = new DateTime(date('Y-m-d H:i:s', strtotime($end)));
        $time_diff = $time_end->diff($time_start)->h.'.'.$time_end->diff($time_start)->i;
        return (float)$time_diff;
    }

    //return date dif as day
    function dateDiff($time1, $time2, $precision = 6)
    {
        // If not numeric then convert texts to unix timestamps
        if (!is_int($time1)) {
            $time1 = strtotime($time1);
        }
        if (!is_int($time2)) {
            $time2 = strtotime($time2);
        }

        // If time1 is bigger than time2
        // Then swap time1 and time2
        if ($time1 > $time2) {
            $ttime = $time1;
            $time1 = $time2;
            $time2 = $ttime;
        }

        // Set up intervals and diffs arrays
        $intervals = array('year','month','day','hour','minute','second');
        $diffs = array();

        // Loop thru all intervals
        foreach ($intervals as $interval) {
            // Create temp time from time1 and interval
            $ttime = strtotime('+1 ' . $interval, $time1);
            // Set initial values
            $add = 1;
            $looped = 0;
            // Loop until temp time is smaller than time2
            while ($time2 >= $ttime) {
                // Create new temp time from time1 and interval
                $add++;
                $ttime = strtotime("+" . $add . " " . $interval, $time1);
                $looped++;
            }

            $time1 = strtotime("+" . $looped . " " . $interval, $time1);
            $diffs[$interval] = $looped;
        }

        $count = 0;
        $times = array();
        // Loop thru all diffs
        foreach ($diffs as $interval => $value) {
            // Break if we have needed precission
            if ($count >= $precision) {
                break;
            }
            // Add value and interval
            // if value is bigger than 0
            if ($value > 0) {
                // Add s if value is not 1
                if ($value != 1) {
                    $interval .= "s";
                }

                // Add value and interval to times array
                $times[] = $value . " " . $interval;

                $count++;
            }
        }

        // Return string with times
        return implode(", ", $times);
    }

    //return location for facility
    function locationBudget($party_id, $type)
    {
        return LuBudgetDetail::leftJoin('lu_schedule_task_locations AS tl', 'lu_budget_details.lu_schedule_task_location_id', '=', 'tl.id')
            ->leftJoin('lu_schedule_tasks AS t', 'tl.lu_schedule_task_id', '=', 't.id')
            ->leftJoin('locations AS l', 'tl.location_id', '=', 'l.id')
            ->where('lu_budget_details.party_id', '=', $party_id)
            ->where('lu_budget_details.expense_type', '=', $type)
            ->where('lu_budget_details.is_damage', '=', 0);
    }
	
	//return task for facility
    function taskBudget($party_id, $type = false, $is_damage = false)
    {
        $data = LuBudgetDetail::leftJoin('lu_schedule_task_locations AS tl', 'lu_budget_details.lu_schedule_task_location_id', '=', 'tl.id')
				->leftJoin('lu_schedule_tasks AS t', 'tl.lu_schedule_task_id', '=', 't.id')
				->where('lu_budget_details.party_id', '=', $party_id);
				
		if ($is_damage)
		{
			$data = $data->where('lu_budget_details.is_damage', '=', 1);
		}
		else
		{
			$data = $data->where('lu_budget_details.expense_type', '=', $type)
					->where('lu_budget_details.is_damage', '=', 0);
		}
		
		return $data;
    }

    //return car facility
    function carFacilitatorBudget($party_id)
    {
        return LuBudgetDetail::leftJoin('lu_schedule_task_locations AS tl', 'lu_budget_details.lu_schedule_task_location_id', '=', 'tl.id')
            ->leftJoin('lu_schedule_tasks AS t', 'tl.lu_schedule_task_id', '=', 't.id')
            ->leftJoin('cars AS c', 'lu_budget_details.expense_id', '=', 'c.id')
            ->leftJoin('car_facilitators AS cf', 'c.car_facilitator_id', '=', 'cf.id')
            ->where('lu_budget_details.party_id', '=', $party_id)
            ->where('lu_budget_details.expense_type', '=', 'cars');
    }

    //return budget type name
    public static function budgetTypeName($type = null)
    {
        $name = "";
        switch($type){
            case 'accommodations' :
                $name = "ห้องพัก";
                break;
            case 'cars' :
                $name = "ยานพาหนะ";
                break;
            case 'conferences' :
                $name = "ห้องประชุม";
                break;
            case 'foods' :
                $name = "อาหาร";
                break;
            case 'personnels' :
                $name = "วิทยากรและบุคลากร";
                break;
            case 'tools' :
                $name = "วัสดุและอุปกรณ์ประกอบการเรียนรู้";
                break;
            case 'special_events' :
                $name = "กิจกรรมพิเศษ";
                break;
        }

        return $name;
    }

    //return food meal
    public static function foodMealName($meal = null)
    {
        $name = "";
        switch($meal){
            case 'breakfast' :
                $name = "มื้อเช้า";
                break;
            case 'lunch' :
                $name = "มื้อเที่ยง";
                break;
            case 'dinner' :
                $name = "มื้อเย็น";
                break;
            case 'break_morning' :
                $name = "เบรกเช้า";
                break;
            case 'break_afternoon' :
                $name = "เบรกบ่าย";
                break;
            case 'night' :
                $name = "มื้อดึก";
                break;
        }

        return $name;
    }

}