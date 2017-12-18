<?php

class PartiesReportController extends ReportController {

    protected $party;

    public function __construct(Party $party)
    {
        parent::__construct();
        $this->party = $party;
    }

    //return index page
    public function getIndex()
    {
        //get values to selector
        $countries = DB::table('countries')->select('name AS text', 'alpha_2 AS id')->get();
        $types = PartyType::select('id', 'name')->orderBy('name', 'ASC')->get();
        $objectives = PartyObjective::select('id', 'name')->orderBy('name', 'ASC')->get();
        $financial_departments = Department::whereIsRevenue(1)->get();
        $service_departments = Department::whereIsLu(1)->get();
        $partyTags = DB::table('party_tag')->select('tag');
        $tags = DB::table('tags')->select('tag')->union($partyTags)->get();
        $personnels = Personnel::leftJoin('personnel_users AS pu', 'personnels.id', '=', 'pu.personnel_id')
            ->leftJoin('users AS u', 'pu.user_id', '=', 'u.id')
            ->leftJoin('assigned_roles AS ar', 'u.id', '=', 'ar.user_id')
            ->whereIn('ar.role_id', array(2))
            ->select('personnels.*')
            ->get();	
		$statuses = array('reviewing', 'reviewed', 'approved', 'preparing', 'ongoing', 'finished', 'finishing', 'postpone', 'cancelled1', 'cancelled2', 'terminated');
        //get table mflf_areas
        $mflfAreas = MflfArea::select('id', 'name')->get();
        //get locations to select
        $locations = ScheduleController::areaLocations();

        return View::make('summaries.parties.excel_all', compact('countries', 'types', 'objectives', 'financial_departments', 'service_departments', 'personnels', 'tags', 'statuses', 'mflfAreas', 'locations'));
    }

    public function getFilterParties()
    {
		$parties = $this->returnReportParties(Input::all());
		
		return json_encode($parties);
    }
	
	/*get excel document*/
	public function postExcelParties()
    {
        $parties = $this->returnReportParties(Input::all());
		$input = Input::all();

        $latest_time = date('d_m_Y_H_i');
		
		//return all as excel format
        $excel = Excel::create('parties_list_' . $latest_time , function($excel) use ($parties, $input) {

			$total_result = count($parties);
		
            $excel->sheet('แสดงรายการ '.$total_result.' รายการ', function($sheet) use ($parties) {

                $sheet->fromArray($parties, null, 'A1', true);

                $sheet->row(1, array(
                    'ID', 'รหัสคำร้อง', 'รหัสการเงิน', 'รหัสลูกค้า', 'ชื่อคณะ', 'มาจาก', 'ประเภท' , 'ข้อความเพิ่มเติม', 'จำนวนคน', 'เริ่มวันที่', 'สิ้นสุดวันที่', 'ผู้ที่ส่งคำร้องจากระบบ', 'เบอร์ติดต่อผู้ที่ส่งคำร้อง', 'อีเมลผู้ที่ส่งคำร้อง', 'การจองห้องพัก', 'ประเด็นที่สนใจเป็นพิเศษ', 'ความคาดหวังในการศึกษาดูงาน', 'เคยเข้าร่วมศึกษาดูงาน ', 'สถานะล่าสุดของคณะ', 'กรอกย้อนหลัง', 'ผู้ที่กรอก', 'ผู้ประสานงานดูแลคณะ', 'รายได้สุทธิ', 'รายจ่ายสุทธิ', 'การปรับปรุงรายได้', 'แผนงานที่ใช้', 'การชำระเงิน', 'ขอใช้บุคลากร lu', 'เหตุผลที่ใช้บุคลากร lu', 'lu ไม่ได้รับคณะเอง', 'รหัสเงินที่เกี่ยวข้อง', 'สร้างเมื่อ', 'แก้ไขเมื่อ', 'ลบเมื่อ', 'วัตถุประสงค์', 'ชื่อผู้ประสานงานคณะที่มา', 'เบอร์โทรผู้ประสานงานคณะที่มา', 'email ผู้ประสานงานคณะที่มา', 'Staff', 'Location Base', 'จำนวนวันดูงาน'
                ));

                // Freeze first row
                $sheet->freezeFirstRow();

                //set top row style
                $sheet->row(1, function($row) {

                    // call cell manipulation methods
                    $row->setBackground('#EEEEEE')
                        ->setFont(array(
                            'bold'       =>  true
                        ));

                });

            });
			
			/*$excel->sheet('เงื่อนไขการค้นหา', function($sheet) use ($input) {
				
				$sheet->loadView('reports.parties.excel.export_criteria', array('input' => $input));
				
			});*/

        })
        ->download('xlsx');

        return Response::download($excel);
	}
	
	/*return object parties*/
	function returnReportParties($input)
	{
		//get input
        $start_date = array_get($input, 'start_date');
        $end_date = array_get($input, 'end_date');
		$name = array_get($input, 'name');
        $countries = array_get($input, 'countries');
		$party_types = array_get($input, 'party_types');
        $people_start = array_get($input, 'people_start');
		$people_end = array_get($input, 'people_end');
		$objectives = array_get($input, 'objectives');
        $area = array_get($input, 'area');
        $bases = array_get($input, 'bases');
        $locations = array_get($input, 'locations');
		$services = array_get($input, 'services');
		$incomes = array_get($input, 'incomes');
		$coordinators = array_get($input, 'coordinators');
		$tags = array_get($input, 'tags');
		$statuses = array_get($input, 'statuses');
		
		//query by criteria
		$parties = Party::orderBy('customer_code', 'DESC');

        if ($start_date!="")
        {
            $parties = $parties->where('start_date', '>=', $start_date);
        }

        if ($end_date!="")
        {
            $parties = $parties->where('end_date', '<=', $end_date);
        }
		
		if ($name!="")
        {
            $parties = $parties->where('name', 'like', '%'.$name.'%');
        }
		
		if ($countries!="")
        {
			$parties = $parties->whereIn('id', function($q) use ($countries){
				$q->select('party_id')->from('party_nations')->whereIn('country', $countries);
			});
        }
		
		if ($party_types!="")
        {
            $parties = $parties->whereIn('party_type_id', $party_types);
        }
		
		if ($people_start!="")
        {
            $parties = $parties->where('people_quantity', '>=', $people_start);
        }
		
		if ($people_end!="")
        {
            $parties = $parties->where('people_quantity', '<=', $people_end);
        }

		if ($objectives!="")
        {
            $parties = $parties->whereIn('id', function($q) use ($objectives){
				$q->select('party_id')->from('party_request_objectives')->whereIn('party_objective_id', $objectives);
			});
        }

        if ($area=='base')
        {
            if ($bases!="")
            {
                $parties = $parties->whereIn('id', function($q) use ($bases){
                    $q->select('party_id')->from('lu_location_bases')->whereIn('mflf_area_id', $bases);
                });
            }
        }
        else
        {
            if ($locations!="")
            {
                $parties = $parties->whereIn('id', function($q) use ($locations){
                    $q->select('party_id')
                        ->from('lu_schedules')
                        ->leftJoin('lu_schedule_tasks AS tasks', 'lu_schedules.id', '=', 'tasks.lu_schedule_id')
                        ->leftJoin('lu_schedule_task_locations AS locations', 'tasks.id', '=', 'locations.lu_schedule_task_id')
                        ->whereIn('locations.location_id', $locations);
                });
            }
        }

		if ($services!="")
        {
           $parties = $parties->whereIn(DB::raw('LEFT(customer_code, 2)'), $services);
        }
		
		if ($incomes!="")
        {
           $parties = $parties->whereIn('budget_code', $incomes);
        }
		
		if ($coordinators!="")
        {
		   $parties = $parties->whereIn('id', function($q) use ($coordinators){
			   $q->select('party_id')->from('lu_manager_assign')->whereIn('coordinator_assigned', $coordinators);
		   });
		   $parties = $parties->orWhereIn('project_co', $coordinators);
        }
		
		if ($tags!="")
        {
            $parties = $parties->whereIn('id', function($q) use ($tags){
				$q->select('party_id')->from('party_tag')->whereIn('tag', $tags);
			});
        }
		
		if ($statuses!="")
        {
           $parties = $parties->whereIn('status', $statuses);
        }

        $parties = $parties->get();
        //re-generate array
        $i = 0;
        foreach ($parties as $party)
        {
            //keep real id
            $party_id = $party->id;
            $startDate = $parties[$i]['start_date'];
            $endDate = $parties[$i]['end_date'];
            //list objectives
            $objectives = PartyRequestObjectives::select('party_objective.name')
                ->leftJoin('party_objective', 'party_request_objectives.party_objective_id', '=', 'party_objective.id')
                ->where('party_request_objectives.party_id', '=', $party_id)->get();

            $parties[$i]['country'] = $party->getNationNames();
            $parties[$i]['party_type_id'] = DB::table('party_type')->where('id', '=', $parties[$i]['party_type_id'])->pluck('name');

            $parties[$i]['objective_detail'] = str_replace("&nbsp;"," ",strip_tags($parties[$i]['objective_detail'])); //remove html tag from text and also replace &nbsp; to " "

            $parties[$i]['start_date'] = date("d/m/Y", strtotime($startDate));
            $parties[$i]['end_date'] = date("d/m/Y", strtotime($endDate));

            $parties[$i]['joined'] = PartyController::strJoin($parties[$i]['joined']);
            $parties[$i]['paid_method'] = PartyController::strPaidMethod($parties[$i]['paid_method'], $parties[$i]['related_budget_code']);

            $parties[$i]['status'] = $party->latestStatus('th');

            $parties[$i]['is_history'] = ($parties[$i]['is_history']==0) ? 'yes' : 'no';

            $parties[$i]['is_not_lu_manage'] = ($parties[$i]['is_not_lu_manage']==0) ? 'yes' : 'no';

            $contributor = User::find($parties[$i]['contributor']);

            if ($contributor)
            {
                $parties[$i]['contributor'] = $contributor->getFullName();
            }

            $obj_texts = "";
            if($objectives)
            {
                foreach($objectives as $objective)
                {
                    $obj_texts.= $objective->name.', ';
                }
            }

            $parties[$i]['objectives'] = substr($obj_texts, 0, -2);

            $coordinator_texts = "";
            $coordinators = $party->coordinators()->get();
            if($coordinators)
            {
                foreach($coordinators as $coordinator)
                {
                    $parties[$i]['coordinator_name'] = $coordinator->name;
                    $parties[$i]['coordinator_mobile'] = $coordinator->mobile;
                    $parties[$i]['coordinator_email'] = $coordinator->email;
                }
            }
            else
            {
                $parties[$i]['coordinator_name'] = "";
                $parties[$i]['coordinator_mobile'] = "";
                $parties[$i]['coordinator_email'] = "";
            }

            //ผู้ประสานงานและรับผิดชอบดูแลคณะเป็นหลัก
			if ($parties[$i]['project_co']==NULL || $parties[$i]['project_co']==0)
			{
				$assigned = LuManagerAssign::where('party_id', '=', $parties[$i]['id'])->first();
				if ($assigned)
				{
					$personnel = Personnel::find($assigned->coordinator_assigned);
				}
				else
				{
					$personnel = false;
				}
			}
			else
			{
				$personnel = Personnel::find($parties[$i]['project_co']);
			}  
			$parties[$i]['project_co'] = ($personnel) ? $personnel->fullName() : '';

            if ($parties[$i]['summary_income']==null || $parties[$i]['summary_income']==0)
            {
                $incomeApproved = 'ยังไม่สรุปรายได้';
            }
            else
            {
                $incomeApproved = 'รายได้ตรงตาม Quotation';
            }

            $parties[$i]['request_for_lu_personnel'] = ($parties[$i]['request_for_lu_personnel']==1) ? 'ต้องการ' : 'ไม่ต้องการ';

            $parties[$i]['income_edited_by'] = ($parties[$i]['income_edited_by']==null || $parties[$i]['income_edited_by']=='') ? $incomeApproved : User::find($parties[$i]['income_edited_by'])->getFullName();
			
			/*addition staff*/
			$parties[$i]['staff_works'] = $party->assignedOverallStaffs();

            /*addition location base*/
            $party_bases = $party->getLocationBases();

            if (count($party_bases)>0)
            {
                $bases = "";
                foreach($party_bases as $party_base)
                {
                    $bases .= $party_base['mflf_area_name'].", ";
                }

                $parties[$i]['location_bases'] = substr($bases, 0, -2);
            }
            else
            {
                $parties[$i]['location_bases'] = '';
            }

            //summary date
            $parties[$i]['period_days'] = $this->visitPeriod($startDate,$endDate);

            $i++;
        }
		
		return $parties;
	}

    function visitPeriod($startDate, $endDate)
    {
        $period = count(ScheduleController::dateRangeArray($startDate,$endDate));

        return $period;
    }

}