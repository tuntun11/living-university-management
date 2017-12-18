<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Party extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'parties';
    protected $fillable = ['name', 'is_not_lu_manage'];
    protected $guarded = ['id'];

    /*get request code when contributor fill party request for reviewer*/
    public function getRequestCode()
    {
        //get current year buddha
        $current_year = (date('Y')+543);
        //get 2 last digit
        $year_digit = substr($current_year,2,2);

        sleep(2);// delay execute for 2 second

        //number of request of the year
        $request = $this->whereRaw(DB::raw('MID(request_code, 1, 2) = ?'), array(substr((date('Y')+543),-2)))
                                ->select(DB::raw('MAX(MID(request_code, 3, 4)) AS latest_number'))
                                ->first();//เรียกค่ามากสุด

        //check if count > 0
        $numberOfRequest = intval($request->latest_number);

        $latest_code = ($numberOfRequest==0) ? 1 : ++$numberOfRequest;
        //code string append 2 digit is current year and 4 digit is running number
        //$latest_code = $year_digit.str_pad(strval($latest_code), 6-strlen(strval($latest_code)), '0', STR_PAD_LEFT);
        $latest_code = $year_digit.str_pad(strval($latest_code), (5+(strlen(strval($latest_code))-1))-strlen(strval($latest_code)), '0', STR_PAD_LEFT);

        return $latest_code;
    }

    /*get new customer code when manager approved*/
    public function getCustomerCode($department_id)
    {
        //get department code
        $department_code = DB::table('departments')->where('id', '=', $department_id)->pluck('code');
        //get current year buddha
        $current_year = (date('Y')+543);
        //get 2 last digit
        $year_digit = substr($current_year,2,2);

        sleep(2);// delay execute for 2 second

        //number of request of the year first 2 digit is department code, next 2 digit is year
        $request = $this
            ->whereRaw(DB::raw('MID(customer_code, 1, 1) = ? and MID(customer_code, 2, 2) = ?'), array($department_code, $year_digit))
            ->select(DB::raw('MAX(MID(customer_code, 4, 6)) AS latest_number'))
            ->first();
        //check if count > 0
        $numberOfRequest = intval($request->latest_number);

        $latest_code = ($numberOfRequest==0) ? 1 : ++$numberOfRequest;

        //department code 2 digit and code string append 2 digit is current year and 3 digit is running number
        $latest_code = $department_code.$year_digit.str_pad(strval($latest_code), (4+(strlen(strval($latest_code))-1))-strlen(strval($latest_code)), '0', STR_PAD_LEFT);

        return $latest_code;
    }

    //get party and extension data as object, one party
    public function fullData()
    {
        $data = $this;
        $data['countries'] = $this->getNationArrays();
        $data['is_local'] = (count($data['countries'])==1 && $data['countries'][0]=='th') ? 1 : 0;
        $data['country'] = $this->getNationNames();
        $data['party_type'] = DB::table('party_type')->where('id', '=', $this->party_type_id)->pluck('name');
        $data['objectives'] = PartyRequestObjectives::select('party_objective.name')
            ->leftJoin('party_objective', 'party_request_objectives.party_objective_id', '=', 'party_objective.id')
            ->where('party_request_objectives.party_id', '=', $this->id)->get();
        $data['objective_arrays'] = $this->objectives();
        $data['location_bases'] = $this->getLocationBases();
        $data['location_base_arrays'] = $this->getLocationBaseArrays();
        $data['coordinators'] = $this->coordinators()->get();
        //$data['coordinator'] = $this->assignedCoordinator(true);

        return $data;
    }

    //return attach file check ว่ามี จดหมาย หรือ ศทบ01 หรือป่าว
    public function fileUrl($folder = "request")
    {
        switch($folder)
        {
            case 'travel01' :
                $destinationPath = public_path().'/svms/travel01/'.$this->request_code;
                $fileName = "travel01.pdf";
                break;
            default :
                $destinationPath = public_path().'/svms/request/'.$this->request_code;
                $fileName = "request_letter.pdf";
        }

        $fileUri = $destinationPath."/".$fileName;

        if (file_exists($fileUri))
        {
            $fileUrl = asset('/svms/'.$folder.'/'.$this->request_code.'/'.$fileName);
        }
        else
        {
            $fileUrl = false;
        }

        return $fileUrl;
    }

    //return latest status as object
    public function latestStatusAsObj()
    {
        $obj = $this->statuses()
            ->where('party_statuses.party_id', '=', $this->id)
            ->orderBy('created_at', 'DESC')
            ->first();

        return $obj;
    }

    //return number of reviewer send edit request on editing state
    public function numberOfEditing()
    {
        $numberOfEditing = $this->statuses()
                    ->where('party_statuses.party_id', '=', $this->id)
                    ->whereStatus('editing')
                    ->count();

        return $numberOfEditing;
    }

    //return number of requester send for review on reviewing state
    public function numberOfReviewing()
    {
        $numberOfReviewing = $this->statuses()
            ->where('party_statuses.party_id', '=', $this->id)
            ->whereStatus('reviewing')
            ->count();

        return $numberOfReviewing;
    }

    //return edit notes feed
    public function editNoteHistories($except = false)
    {
        $notes = $this->statuses()
            ->where('party_statuses.party_id', '=', $this->id)
            ->whereIn('status', array('reviewing', 'editing'))
            ->where('revision', '>', 0);

        if ($except)
        {
            $notes = $notes->where('revision', '<>', $except);
        }

        $notes = $notes->orderBy('created_at', 'DESC')
            ->orderBy('revision', 'DESC')
            ->get();

        return $notes;
    }

    //get Count Round date
    public function period()
    {
        $period = count(ScheduleController::dateRangeArray($this->start_date,$this->end_date));

        return $period;
    }

    //get all party with all flow and status, only vip, reviewer, manager, project co(view only) to get ;include history
    //return array object
    public function getAllData()
    {
        /*Join type data*/
        $parties = $this;

        return $parties;
    }

    //get all party with transaction passed or keep it to history
    //return array object
    public function getAllHistory()
    {
        $parties = $this->whereIsHistory(1);

        /*if (Auth::user()->hasRole('contributor'))
        {
            $parties = $parties->where('contributor', '=', Auth::user()->id);
        }*/

        return $parties;
    }

    //return party can review and approval
    public function canReviewAndApproval()
    {
        if (Auth::check() && Auth::user()->hasRole('reviewer'))
        {
            //Find out reviewing
            $party_can_review = $this->whereId($this->id)->whereStatus('reviewing')->first();

            if ($party_can_review)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            //this is not reviewer
            return false;
        }
    }

    //return party can assign project coordinator
    public function canAssignCoordinator()
    {
        if (Auth::check() && Auth::user()->hasRole('manager'))
        {
            //Find out reviewing
            $party_can_assign = $this->whereId($this->id)->whereStatus('reviewed')->first();

            if ($party_can_assign)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            //this is not manager
            return false;
        }
    }

    //get all party ที่ยังไม่จบการรับคณะ with passed manager process
    //return array object
    public function getManagerPassed()
    {
        /*send party with approve from manager*/
        //if manager, reviewer select all
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('reviewer') || Auth::user()->hasRole('manager') || Auth::user()->hasRole('vip'))
        {
            $parties = $this->whereIn('parties.status', array('approved', 'preparing', 'ongoing', 'finishing', 'postpone', 'terminated'));
        }
        //else coordinator select with yourself
        else
        {
            //check if permission can see full calendar.
            if (Auth::user()->canViewFullCalendar())
            {
                $parties = $this->whereIn('parties.status', array('approved', 'preparing', 'ongoing', 'finishing', 'postpone', 'terminated'));
            }
            else
            {
                $parties = $this->leftJoin('lu_manager_assign AS ma', 'parties.id', '=', 'ma.party_id')
                    ->whereIn('parties.status', array('approved', 'preparing', 'ongoing', 'finishing', 'postpone', 'terminated'))
                    ->whereCoordinatorAssigned(Auth::user()->getPersonnel()->id);
            }
        }
        $parties = $parties
                    ->where('parties.is_history', '=', 0);

        return $parties;
    }

    //get all party ที่ยังไม่จบการรับคณะ with passed create program or schedule process
    //return array object
    public function getProgramedPassed()
    {
        /*send party with passed work in schedule or program create*/
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('reviewer') || Auth::user()->hasRole('manager') || Auth::user()->hasRole('vip'))
        {
            $parties = $this->leftJoin('lu_manager_assign AS ma', 'parties.id', '=', 'ma.party_id')
                ->whereIn('parties.status', array('approved', 'preparing', 'ongoing', 'finishing', 'finished', 'postpone', 'terminated'))
                ->whereIn('parties.id', function($query){
                    $query->select('party_id')
                        ->from('lu_schedules')
                        ->where('revision', '>', 0);
                });
        }
        else
        {
            //this is project coordinator
            $parties = $this->leftJoin('lu_manager_assign AS ma', 'parties.id', '=', 'ma.party_id')
                ->whereIn('parties.status', array('approved', 'preparing', 'ongoing', 'finishing', 'finished', 'postpone', 'terminated'))
                ->whereIn('parties.id', function($query){
                    $query->select('party_id')
                        ->from('lu_schedules')
                        ->where('revision', '>', 0);
                });
            //if have not a permission to view full calendar
            if (!Auth::user()->canViewFullCalendar())
            {
                $parties = $parties->whereCoordinatorAssigned(Auth::user()->getPersonnel()->id);
            }
        }

        $parties = $parties
                    ->where('parties.is_history', '=', 0);

        return $parties;
    }

    //get Can Schedule สถานะที่ยังทำกำหนดการได้
    public function canProgram()
    {
        return ($this->whereIn('status', array('approved', 'preparing', 'ongoing', 'finishing', 'postpone', 'terminated'))->where('id', '=', $this->id)->first()) ? true : false;
    }

    //สามารถทำ actions ได้
    public function canActions()
    {
        if ($this->status=='pending' || $this->status=='terminated' || $this->status=='cancelled1' || $this->status=='cancelled2' || $this->status=='finishing' || $this->status=='finished')
        {
            return false;
        }
        return true;
    }

    //get Schedule Passed
    public function programingPassed()
    {
        return ($this->schedules()->where('revision', '>', 0)->count()) ? true : false;
    }

    //get Budgeting Passed
    public function budgetingPassed()
    {
        return ($this->budgets()->where('revision', '>', 0)->count()) ? true : false;
    }

    //get and return accommodations quantity as array
    public function returnAccommodationQuantities($plan)
    {
        $arrays = array();
        $accommodations = LuBudgetDetailTemp::where('party_id', '=', $this->id)
            ->where('expense_type', '=', 'accommodations')
            ->where('is_plan_b', '=', 0)
            ->select('location_id AS task', DB::raw('COUNT(day) AS days'))
            ->groupBy('location_id')
            ->get();

        foreach($accommodations as $accommodation)
        {
            $array = array();
            if($plan=='A')
            {
                $array = $accommodation;
            }
            else
            {
                //find b if have b use it
                $accommodation_plan_b = LuBudgetDetailTemp::where('party_id', '=', $this->id)
                    ->where('expense_type', '=', 'accommodations')
                    ->where('is_plan_b', '=', 1)
                    ->where('master_task_id', '=', $accommodation->task)
                    ->select('location_id AS task', DB::raw('COUNT(day) AS days'))
                    ->groupBy('location_id')
                    ->first();

                if ($accommodation_plan_b)
                {
                    $array['task'] = $accommodation_plan_b->task;
                    $array['days'] = $accommodation_plan_b->days;
                }
                else
                {
                    $array = $accommodation;
                }
            }

            array_push($arrays, $array);
        }

        return $arrays;
    }

    //get and return meals quantity as array
    public function returnMealQuantities($plan)
    {
        $arrays = array();
        $meals = LuBudgetDetail::where('party_id', '=', $this->id)
            ->where('expense_type', '=', 'foods')
            ->whereNotNull('expense_food_meal')
            ->where('is_plan_b', '=', 0)
            ->select('lu_schedule_task_location_id AS task', 'expense_food_meal AS meal', DB::raw('COUNT(expense_food_meal) AS quantity'))
            ->groupBy('expense_food_meal')
            ->get();

        foreach($meals as $meal)
        {
            $array = array();
            if($plan=='A')
            {
                $array = $meal;
            }
            else
            {
                //find b if have b use it
                $meal_plan_b = LuBudgetDetail::where('party_id', '=', $this->id)
                                ->where('expense_type', '=', 'foods')
                                ->whereNotNull('expense_food_meal')
                                ->where('is_plan_b', '=', 1)
                                ->where('master_task_id', '=', $meal->task)
                                ->select('expense_food_meal AS meal', DB::raw('COUNT(expense_food_meal) AS quantity'))
                                ->groupBy('expense_food_meal')
                                ->first();

                if ($meal_plan_b)
                {
                    $array['meal'] = $meal_plan_b->meal;
                    $array['quantity'] = $meal_plan_b->quantity;
                }
                else
                {
                    $array = $meal;
                }
            }

            array_push($arrays, $array);
        }

        return $arrays;
    }

    //get and return location base area
    public function getLocationBases()
    {
        $bases = $this->locationBases()->get();

        $array_bases = array();
        foreach($bases as $base)
        {
            $array_base = array();

            $locate = MflfArea::find($base->mflf_area_id);

            if ($locate)
            {
                $array_base['id'] = $base->id;
                $array_base['mflf_area_id'] = $base->mflf_area_id;
                $array_base['mflf_area_name'] = $locate->name;

                array_push($array_bases, $array_base);
            }

        }

        return $array_bases;
    }

    //get and return location base area as array to set value
    public function getLocationBaseArrays()
    {
        $bases = $this->locationBases()->get();

        $arrays = array();
        foreach($bases as $base)
        {
            array_push($arrays, $base->mflf_area_id);
        }

        return $arrays;
    }

    //get and return location base area as string demeter
    public function getLocationBaseStr()
    {
        $bases = $this->locationBases()->get();

        $strBases = "";
        foreach($bases as $base)
        {
            $locate = MflfArea::find($base->mflf_area_id);

            if ($locate)
            {
                $strBases.= $locate->name.", ";
            }

        }

        if ($strBases!="")
        {
            $strBases = substr($strBases,0,-2);
        }

        return $strBases;
    }

    //get and return nations long text
    public function getNationNames()
    {
        $countries = $this->countries()->get();

        $countries_text = "";
        foreach($countries as $country)
        {
            $country_name =  DB::table('countries')->where('alpha_2', '=', $country->country)->pluck('name');
            $countries_text .= $country_name . ', ';
        }

        return substr($countries_text, 0, -2);
    }

    //get and return nations as array
    public function getNationArrays($hasKey = false)
    {
        $countries = $this->countries()->get();

        $arrays = array();
        foreach($countries as $country)
        {
            if ($hasKey)
            {
                //redefine again
                $arrays = array();
                //Query for more data
                foreach($countries as $country)
                {
                    $array = array();
                    $nation =  DB::table('countries')->where('alpha_2', '=', $country->country)->select('alpha_2', 'name')->first();
                    $array['id'] = $nation->alpha_2;
                    $array['name'] = $nation->name;

                    array_push($arrays, $array);
                }
            }
            else
            {
                array_push($arrays, $country->country);
            }
        }

        return $arrays;
    }

    //for manager or project co return assign to manage boolean
    public function assignmentToManage()
    {
        if (Auth::check())
        {
            if (Auth::user()->hasRole('manager'))
            {
                //always true because is manager
                return true;
            }
            else
            {
                if (Auth::user()->hasRole('project coordinator'))
                {
                    //find out if current party has assigned by manager return true
                    $assignment = LuManagerAssign::where('party_id', '=', $this->id)
                        ->where('coordinator_assigned', '=', Auth::user()->getPersonnel()->id)
                        ->first();

                    if($assignment)
                    {
                        return true;
                    }
                    else
                    {
                        return false;
                    }
                }
                else
                {
                    //if not project co return false
                    return false;
                }
            }
        }
        else
        {
            return false;
        }
    }

    //party type
    public function type()
    {
        return $this->hasOne('PartyType');
    }

    //party statuses
    public function statuses()
    {
        return $this->hasMany('PartyStatuses');
    }

    //party location base for study visit
    public function locationBases()
    {
        return $this->hasMany('LuLocationBases');
    }
	
	//party Overall Staffs
    public function overallStaffs()
    {
        return $this->hasMany('LuOverallStaff');
    }

    //return latest status
    public function latestStatus($lang = 'en')
    {
        return ($lang=='th') ? $this->statusThai($this->status) : $this->status;
    }

    //related with lu schedule transaction
    public function schedules()
    {
        return $this->hasMany('LuSchedule', 'party_id');
    }

    //related with lu budget transaction
    public function budgets()
    {
        return $this->hasMany('LuBudget', 'party_id');
    }

    //related with lu budget details
    public function expenses()
    {
        return $this->hasMany('LuBudgetDetail', 'party_id');
    }

    //related with tags
    public function tags()
    {
        return $this->hasMany('PartyTags', 'party_id');
    }

    //related with country nation region
    public function countries()
    {
        return $this->hasMany('PartyNations', 'party_id');
    }

    //related withs Schedule Document Export
    public function exportedSchedules()
    {
        return $this->hasMany('LuScheduleExport', 'party_id');
    }

    //related with objectives
    public function requestObjectives()
    {
        return $this->hasMany('PartyRequestObjectives', 'party_id');
    }

    //related with sharepoint link
    public function sharepoints()
    {
        return $this->hasMany('PartySharepoint', 'party_id');
    }
	
	//related with send administrator flow
    public function sendToAdministrators()
    {
        return $this->hasMany('PartySendAdministrators', 'party_id');
    }

    //related with upload file link
    public function uploadFiles()
    {
        return $this->hasMany('PartyUploadFiles', 'party_id');
    }

    //related with objectives as array
    public function objectives()
    {
        $arrays = array();

        foreach($this->requestObjectives()->get() as $obj)
        {
            array_push($arrays, $obj->party_objective_id);
        }

        return $arrays;
    }

    //related with coordinators
    public function coordinators()
    {
        return $this->hasMany('PartyCoordinators', 'party_id');
    }

    //return manager decision step of party
    public function assigned()
    {
        return $this->hasOne('LuManagerAssign');
    }

    public function assignedCoordinator($is_short = false, $show_department = false)
    {
        $assigned = LuManagerAssign::where('party_id', '=', $this->id)->first();

        if ($assigned)
        {
            $personnel = Personnel::find($assigned->coordinator_assigned);
            if ($personnel)
            {
                $department_text = ($show_department) ? '<br/>('.$personnel->department->name.')' : '';
                return ($is_short) ? $personnel->shortName().$department_text : $personnel->fullName().$department_text;
            }
            else
            {
                return 'ไม่ทราบรายชื่อผู้ประสานงาน';
            }
        }
		else
		{
			$personnel = Personnel::find($this->project_co);
			if ($personnel)
			{
				$department_text = ($show_department) ? '<br/>('.$personnel->department->name.')' : '';
				return ($is_short) ? $personnel->shortName().$department_text : $personnel->fullName().$department_text;
			}
            else
			{
                //check if have not project co and other has own manage
                if($this->is_not_lu_manage)
                {
                    return 'หน่วยงานรับคณะเอง';
                }
                else
                {
                    return 'ไม่ทราบรายชื่อผู้ประสานงาน';
                }
			}
		}
		
        return 'ยังไม่ได้ระบุผู้ประสานงาน';
    }
	
	public function assignedOverallStaffs($is_short = false)
	{
		$overallStaffs = $this->overallStaffs()
							->with('personnel')
							->with(['works' => function ($query) {
								$query->leftJoin('work_types', 'lu_overall_staff_works.work_type_id', '=', 'work_types.id')
										->orderBy('work_types.priority', 'ASC');
							}])
							->get();			
			
		$staff_work_text = "";

        if (count($overallStaffs)>0)
        {
            foreach($overallStaffs as $overallStaff)
            {
                if ($is_short)
                {
                    if (isset($overallStaff->personnel->first_name))
                    {
                        $staff_work_text .= 'คุณ'.$overallStaff->personnel->first_name.' ';
                    }
                }
                else
                {
                    if (isset($overallStaff->personnel->first_name))
                    {
                        $staff_work_text .= $overallStaff->personnel->prefix.$overallStaff->personnel->first_name.' '.$overallStaff->personnel->last_name.' ';
                    }
                }

                foreach($overallStaff->works as $staffWork)
                {
                    $works_text = "";
                    $works_text .= $staffWork->name.' ';
                }

                $staff_work_text .= "(".$works_text."), ";
            }

            //trim string at last
            $staff_work_text = substr($staff_work_text,0,-2);
        }

		return $staff_work_text;
	}

    /*return date format for party*/
    public function dateFormat($date, $format='d/m/Y')
    {
        return date($format, strtotime($date));
    }

    /*return thai status*/
    public function statusThai($latest_status, $style = false, $revision = 0)
    {
        switch($latest_status){
            case 'pending' :
                $status = 'กำลังทำการกรอกคำร้อง';
                $cls = 'text-info';
                break;
            case 'editing' :
                    if ($revision>0)
                    {
                        $status = 'ร้องขอการแก้ไข/เพิ่มเติมคำร้องศึกษาดูงาน ครั้งที่ '.$revision;
                    }
                    else
                    {
                        $status = 'ร้องขอการแก้ไข/เพิ่มเติมคำร้องศึกษาดูงาน';
                    }
                $cls = 'text-info';
                break;
            //Next case is normal workflow
            case 'reviewing' :
                    if ($revision>1)
                    {
                        $status = 'ยื่นคำร้องเพื่อพิจารณารับคณะ ครั้งที่ '.$revision;
                    }
                    else
                    {
                        $status = 'ยื่นคำร้องเพื่อพิจารณารับคณะ';
                    }
                $cls = 'text-info';
                break;
            case 'reviewed' :
                $status = 'ผ่านการอนุมัติรับคณะ';
                $cls = 'text-info';
                break;
            case 'approved' :
                $status = 'ผ่านขั้นตอนเลือกผู้ประสานงานหลัก';
                $cls = 'text-info';
                break;
            case 'preparing' :
                $status = 'กำลังเตรียมการรับคณะ';
                $cls = 'text-primary';
                break;
            case 'ongoing' :
                $status = 'อยู่ระหว่างการรับคณะ';
                $cls = 'text-primary';
                break;
            case 'finished' :
                $status = 'ดำเนินการรับคณะสำเร็จ(รับชำระเงินแล้ว)';
                $cls = 'text-success';
                break;
            case 'finishing' :
                $status = 'ดำเนินการรับคณะสำเร็จ(ยังไม่ได้รับเงิน)';
                $cls = 'text-success';
                break;
            case 'postpone' :
                $status = 'เลื่อนการดูงานไม่มีกำหนด';
                $cls = 'text-warning';
                break;
            case 'cancelled1' :
                $status = 'ยกเลิกจากผู้ตรวจสอบ';
                $cls = 'text-danger';
                break;
            case 'cancelled2' :
                $status = 'ยกเลิกจากผู้จัดการ';
                $cls = 'text-danger';
                break;
            case 'terminated' :
                $status = 'ยกเลิกการรับคณะหรือคณะไม่มา';
                $cls = 'text-danger';
                break;
            case 'other' :
                $status = 'ส่งให้หน่วยงานอื่นๆรับ';
                $cls = 'text-muted';
                break;
            case 're-schedule' :
                $status = 'เลื่อนวันที่ดูงาน';
                $cls = 'text-warning';
                break;
            default:
                $status = 'ไม่ได้ระบุ';
                $cls = '';
        }

        $strReturn = $status;

        if ($style)
        {
            $strReturn = "<span class='".$cls."'>".$status."</span>";
        }

        return $strReturn;
    }

}