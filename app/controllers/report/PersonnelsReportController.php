<?php

class PersonnelsReportController extends ReportController {

    protected $personnels;

    public function __construct(Personnel $personnels)
    {
        parent::__construct();
        $this->personnels = $personnels;
    }

    //return index page
    public function getIndex()
    {
        //get values to selector
        $statuses = Personnel::listStatuses();
        $expert_types = ExpertType::all();
        $countries = DB::table('countries')->select('name AS text', 'alpha_2 AS id')->get();
        $ethnics = DB::table('ethnic')->select('name AS text', 'id')->get();
        $mflfAreas = MflfArea::select('id', 'name')->get();

        return View::make('summaries.personnels.excel_all', compact('statuses', 'expert_types', 'countries', 'ethnics', 'mflfAreas'));
    }

    public function getFilterPersonnels()
    {
        $parties = $this->returnReportPersonnels(Input::all());

        return json_encode($parties);
    }

    /*get excel document*/
    public function postExcelPersonnels()
    {
        $parties = $this->returnReportPersonnels(Input::all());
        $input = Input::all();

        $latest_time = date('d_m_Y_H_i');

        //return all as excel format
        $excel = Excel::create('parties_list_' . $latest_time , function($excel) use ($parties, $input) {

            $total_result = count($parties);

            $excel->sheet('แสดงรายการ '.$total_result.' รายการ', function($sheet) use ($parties) {

                $sheet->fromArray($parties, null, 'A1', true);

                $sheet->row(1, array(
                    'ID', 'รหัสคำร้อง', 'รหัสการเงิน', 'รหัสลูกค้า', 'ชื่อคณะ', 'มาจาก', 'ประเภท' , 'หมายเหตุ', 'จำนวนคน', 'เริ่มวันที่', 'สิ้นสุดวันที่', 'ผู้ที่ส่งคำร้องจากระบบ', 'เบอร์ติดต่อผู้ที่ส่งคำร้อง', 'อีเมลผู้ที่ส่งคำร้อง', 'การจองห้องพัก', 'ประเด็นที่สนใจเป็นพิเศษ', 'ความคาดหวังในการศึกษาดูงาน', 'เคยเข้าร่วมศึกษาดูงาน ', 'สถานะล่าสุดของคณะ', 'กรอกย้อนหลัง', 'ผู้ที่กรอก', 'ผู้ประสานงานดูแลคณะ', 'รายได้สุทธิ', 'รายจ่ายสุทธิ', 'การปรับปรุงรายได้', 'แผนงานที่ใช้', 'การชำระเงิน', 'ขอใช้บุคลากร lu', 'เหตุผลที่ใช้บุคลากร lu', 'ไม่ใช้บุคลากร lu', 'สร้างเมื่อ', 'แก้ไขเมื่อ', 'ลบเมื่อ', 'วัตถุประสงค์', 'ผู้ประสานงานคณะที่มา', 'Staff', 'Location Base'
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

        })
            ->download('xlsx');

        return Response::download($excel);
    }

    /*return object personnels*/
    function returnReportPersonnels($input)
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
        $bases = array_get($input, 'bases');
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

        if ($bases!="")
        {
            $parties = $parties->whereIn('id', function($q) use ($bases){
                $q->select('party_id')->from('lu_location_bases')->whereIn('mflf_area_id', $bases);
            });
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
            //list objectives
            $objectives = PartyRequestObjectives::select('party_objective.name')
                ->leftJoin('party_objective', 'party_request_objectives.party_objective_id', '=', 'party_objective.id')
                ->where('party_request_objectives.party_id', '=', $party_id)->get();

            $parties[$i]['country'] = $party->getNationNames();
            $parties[$i]['party_type_id'] = DB::table('party_type')->where('id', '=', $parties[$i]['party_type_id'])->pluck('name');

            $parties[$i]['joined'] = PartyController::strJoin($parties[$i]['joined']);
            $parties[$i]['paid_method'] = PartyController::strPaidMethod($parties[$i]['paid_method'], $parties[$i]['related_budget_code']);

            $parties[$i]['status'] = $party->latestStatus('th');

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
                    $coordinator_texts.= $coordinator->name . ' mobile:' . $coordinator->mobile . ' email:' . $coordinator->email . ' , ';
                }

                $coordinator_texts = substr($coordinator_texts, 0, -2);
            }

            $parties[$i]['coordinators'] = $coordinator_texts;

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

            $bases = "";
            foreach($party_bases as $party_base)
            {
                $bases .= $party_base['mflf_area_name'].", ";
            }

            $parties[$i]['location_bases'] = substr($bases, 0, -2);

            $i++;
        }

        return $parties;
    }

}