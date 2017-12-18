<?php

class ManagerController extends BaseController {

    protected $party;

    protected $party_statuses;

    protected $lu_manager_assign;

    protected $assigned_roles;

    protected $personnels;

    public function __construct(Party $party, PartyStatuses $party_statuses, LuManagerAssign $lu_manager_assign, AssignedRoles $assigned_roles, Personnel $personnels)
    {
        parent::__construct();
        $this->party = $party;
        $this->party_statuses = $party_statuses;
        $this->lu_manager_assign = $lu_manager_assign;
        $this->assigned_roles = $assigned_roles;
        $this->personnels = $personnels;
    }

    //return index page
    public function getIndex()
    {
        $departments = Department::whereIsLu(1)->orderBy('priority')->get();
        //add personnels in department
        foreach($departments as $department)
        {
            $department['personnels'] = $department->teams();
        }

        $personnels = $this->personnels->canOperating()->get();

        return View::make('svms/manager', compact('departments', 'personnels'));
    }

    //post transaction of Accept
    public function postManagerAccept()
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
            'department_id'   => 'required|integer'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            // Start transaction!
            DB::beginTransaction();

            try
            {
				//set method
				$method = Input::get('method');
                //query party
                $party = Party::find(Input::get('party_id'));

                //manager transaction assign or change project coordinator
                if (Input::get('not_use_coordinator')=='no' && Input::get('coordinator_assigned'))
                {
                    $manager_assigned = LuManagerAssign::where('party_id', '=', $party->id);

                    $lu_manager_assign = ($manager_assigned->count()>0) ? LuManagerAssign::find($manager_assigned->first()->id) : new LuManagerAssign;

                    $lu_manager_assign->party_id = Input::get('party_id');
                    $lu_manager_assign->coordinator_assigned = Input::get('coordinator_assigned');
                    $lu_manager_assign->created_by = Auth::user()->id;
                    $lu_manager_assign->updated_by = Auth::user()->id;
                    $lu_manager_assign->save();

                    //case use lu power
                    $party->is_not_lu_manage = 0;

                    //find with personnel id
                    $coordinator = Personnel::find(Input::get('coordinator_assigned'));
                }
                else
                {
                    //case not use lu
                    $party->is_not_lu_manage = 1;
                    //set not use co
                    $coordinator = false;
                }

                //update customer code
				if ($method=='accept')
				{
					$party->customer_code = $party->getCustomerCode(Input::get('department_id'));
				}
                //set last stat or status
                $party->status = 'approved';

                //keep status log
                $this->party_statuses->party_id = Input::get('party_id');
                $this->party_statuses->status = 'approved';
                $this->party_statuses->note = Input::get('note');
                $this->party_statuses->coordinator = Input::get('coordinator_assigned');
                $this->party_statuses->created_by = Auth::user()->id;
                $this->party_statuses->updated_by = Auth::user()->id;

                //if have more recipients add that to status
                if (Input::get('more_recipients'))
                {
                    $emails = "";
                    foreach (Input::get('more_recipients') as $email)
                    {
                        $emails .= $email.',';
                    }
                    $emails = substr($emails,0,-1);

                    $this->party_statuses->other_email = $emails;
                }

                if ($this->party_statuses->save() && $party->save())
                {
                    $data = $party->fullData();
                    //get assign coordinator mail
                    if ($coordinator)
                    {
                        $data['coordinator_name'] = $coordinator->fullName();
                        $data['coordinator_mail'] = $coordinator->email;
                        $data['coordinator_department'] = $coordinator->department->name;
                        $data['use_coordinator'] = 1;//true
                    }
                    else
                    {
                        $data['coordinator_name'] = "หน่วยงานต้นเรื่องจะดำเนินการเอง";
                        $data['coordinator_mail'] = "";
                        $data['coordinator_department'] = "";
                        $data['use_coordinator'] = 0;//false
                    }

					$data['coordinator_method'] = $method;
					$data['manager_note'] = Input::get('note');//only attach in mail
                    //send by user
                    $data['manager_name'] = Auth::user()->getFullName();
                    //set array mail to cc
                    $livingUniversities = array();
                    //get reviewer and manager also pc group to attach
                    $reviewer_manager_coordinator_group = $this->assigned_roles->getMailsByRole(array('reviewer', 'manager'), 1);
                    //add lu team to know
                    array_push($livingUniversities, 'lu_team@doitung.org');
                    //and also push request person
                    array_push($livingUniversities, $data->request_person_email);
                    //finally push more recipients also merge
                    //merge living u manager reviewer to known
                    if (Input::get('more_recipients'))
                    {
                        $livingUniversities = array_merge($livingUniversities, $reviewer_manager_coordinator_group, Input::get('more_recipients'));
                    }
                    else
                    {
                        $livingUniversities = array_merge($livingUniversities, $reviewer_manager_coordinator_group);
                    }

                    $data['living_university_mails'] = $livingUniversities;

                    Mail::send('emails.transaction.manager.accept', compact('data'), function($message) use ($data)
                    {
                        //check for mail title
                        if ($data->use_coordinator)
                        {
                            $managerAcceptMailTitle = 'LU : '.$data->name.'ถูกมอบหมายงานให้กับผู้ประสานงานหลัก('.$data->coordinator_name.')';
                        }
                        else
                        {
                            $managerAcceptMailTitle = 'LU : '.$data->name.'ถูกส่งให้หน่วยงานต้นเรื่องแล้ว';
                        }

                        //check for use coordinator
                        if ($data->is_not_lu_manage)
                        {
                            //case is not lu power
                            $message
                                ->to($data->living_university_mails)
                                ->subject($managerAcceptMailTitle);
                        }
                        else
                        {
                            //case normal
                            $message
                                ->to($data->coordinator_mail)
                                ->cc($data->living_university_mails)
                                ->subject($managerAcceptMailTitle);
                        }
                    });

                    //Request Commit and send response
                    DB::commit();

                    //response success
                    $response = array(
                        'status' => 'success',
                        'tasks' => Auth::user()->getCountTasks(),
                        'msg' => 'ส่งภาระงานใหม่สำเร็จแล้ว'
                    );

                    return Response::json( $response );
                }

                //response error
                $response = array(
                    'status' => 'error',
                    'msg' => 'Error ส่งภาระงานไม่สำเร็จ',
                    'error' => $this->lu_manager_assign->error()
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

    //post transaction of Cancel or Reject
    public function postManagerCancel()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            'party_id'   => 'required|integer'
        );

        //send mail to request person

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            //keep latest status
            $party = Party::find(Input::get('party_id'));
            $party->status = 'cancelled2';

            //keep status log
            $this->party_statuses->party_id = Input::get('party_id');
            $this->party_statuses->status = 'cancelled2';
            $this->party_statuses->note = Input::get('note');
            $this->party_statuses->created_by = Auth::user()->id;
            $this->party_statuses->updated_by = Auth::user()->id;

            if ($party->save() && $this->party_statuses->save())
            {
                //send mail cancel response to request person
                $data = $party->fullData();
                $data['cancel_reason'] = Input::get('reason');

                //get lu group to attach
                $data['lu_team_mails'] = 'lu_team@doitung.org';

                Mail::send('emails.transaction.manager.cancel', compact('data'), function($message) use ($data)
                {
                    $message
                        ->to($data->request_person_email)
                        ->cc($data->lu_team_mails)
                        ->subject('LU : '.$data->name.'ถูกยกเลิกการรับคณะ');
                });
                //response success
                $response = array(
                    'status' => 'success',
                    'tasks' => Auth::user()->getCountTasks(),
                    'msg' => 'ยกเลิกรายการสำเร็จแล้ว'
                );

                return Response::json( $response );
            }

            //response error
            $response = array(
                'status' => 'error',
                'msg' => 'Error ยกเลิกรายการไม่สำเร็จ',
                'error' => $this->party_statuses->error()
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

    //return coordinators from personnel
    public function getCoordinators()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        $arrays = array();

        //get role project coordinator only
        $data = $this->personnels->canCoordinate()->get();

        $i = 0;
        foreach($data as $d)
        {
            $on_process = $this->getCoordinateHandling($d->id)->count();

            $arrays[$i]['id'] = $d->id;
            $arrays[$i]['text'] = $d->fullName().' ('.$on_process.' คณะ)';
            $arrays[$i]['on_process'] = $on_process;

            $i++;
        }

        $response = array(
            'data' => $arrays
        );

        return Response::json( $response );
    }

    //return tasks or work of coordinator
    public function getCoordinatorTasks()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        $works = array();
        $work_data = $this->getCoordinateHandling(Input::get( 'coordinator_id' ))->select('name', 'start_date', 'end_date')->orderBy('start_date', 'ASC')->get();

        $w = 0;
        foreach($work_data as $work)
        {
            $works[] = $work;
            $works[$w]['party_range'] = ScheduleController::dateRangeStr($work->start_date, $work->end_date, true, false, 'th');

            $w++;
        }

        $response = array(
            'data' => $works
        );

        return Response::json( $response );
    }

    //return manager count backlog
    public static function countTask()
    {
        return self::managerTask()->count();
    }

    //return data
    public function getData()
    {
        $parties = self::managerTask(Input::get('is_history'), Input::get('is_all'));
        $parties = $parties->orderBy('parties.request_code', 'desc');

        return Datatables::of($parties)

            ->edit_column('name', function($row){
                return HTML::link(
                    URL::to('party/'.$row->id.'/view'),
                    $row->name,
                    array('id' => 'party'.$row->id,
                        'title' => 'คลิกที่ชื่อเพื่อแสดงหรือแก้ไข',
                        'target' => '_self'));
            })

            ->edit_column('type', '{{ DB::table(\'party_type\')->where(\'id\', \'=\', $type)->pluck(\'name\') }}')

            ->edit_column('start_date', function($row){
                return ScheduleController::dateRangeStr($row->start_date, $row->end_date, true, false, 'th');
            })

            ->edit_column('country', function($row){
                return $row->getNationNames();
            })

            ->edit_column('joined', function($row){
                return PartyController::strJoin($row->joined);
            })

            ->edit_column('paid_method', function($row){
                return PartyController::strPaidMethod($row->paid_method, $row->related_budget_code);
            })

            ->add_column('reviewer_name', function($row){

                $latest_reviewer = PartyStatuses::where('party_id', '=', $row->id)
                                    ->where('status', '=', 'reviewed')
                                    ->orderBy('created_at', 'DESC')
                                    ->first();

                if ($latest_reviewer)
                {
                    $user = User::find($latest_reviewer->created_by);
                    $reviewer_name = $user->getFullName();
                }
                else
                {
                    $reviewer_name = '';
                }

                return $reviewer_name;
            })

            ->add_column('reviewed_at', function($row){

                $latest_reviewer = PartyStatuses::where('party_id', '=', $row->id)
                    ->where('status', '=', 'reviewed')
                    ->orderBy('created_at', 'DESC')
                    ->first();

                if ($latest_reviewer)
                {
                    $reviewed_date = ScheduleController::dateRangeStr($latest_reviewer->created_at, $latest_reviewer->created_at, true, false, 'th', true, true);
                }
                else
                {
                    $reviewed_date = '';
                }

                return $reviewed_date;
            })

            ->add_column('objectives', function($row){
                $party_objectives = PartyRequestObjectives::select('party_objective.name')
                    ->leftJoin('party_objective', 'party_request_objectives.party_objective_id', '=', 'party_objective.id')
                    ->where('party_request_objectives.party_id', '=', $row->id)->get();

                $objectives = "";
                foreach($party_objectives as $objective)
                {
                    $objectives .= $objective->name.", ";
                }

                return substr($objectives, 0, -2);
            })

            ->add_column('bases', function($row){
                $party_bases = $row->getLocationBases();

                $bases = "";
                foreach($party_bases as $party_base)
                {
                    $bases .= $party_base['mflf_area_name'].", ";
                }

                $bases = substr($bases, 0, -2);

                return ($bases==false) ? '' : $bases;
            })

            ->add_column('coordinators', function($row){
                $party_coordinators = PartyCoordinators::select('id', 'name', 'mobile', 'email')->where('party_id', '=', $row->id)->get();

                return $party_coordinators;
            })

            ->add_column('file', function($row){
                if($row->fileUrl() && $row->fileUrl('travel01'))
                {
                    return '<a href="'.$row->fileUrl().'">ไฟล์ต้นเรื่อง</a> | <a href="'.$row->fileUrl('travel01').'">ศทบ.01</a>';
                }
                elseif($row->fileUrl())
                {
                    return '<a href="'.$row->fileUrl().'">ไฟล์ต้นเรื่อง</a>';
                }
                elseif($row->fileUrl('travel01'))
                {
                    return '<a href="'.$row->fileUrl('travel01').'">ศทบ.01</a>';
                }
                else
                {
                    return 'ไม่มีเอกสารแนบ';
                }
            })

            ->add_column('actions', function($row){
                //create select control
				$party_description = addslashes(str_replace(array('"', '/'), "", $row->request_code.' '.$row->name.' ('.$row->qty.')'));
                $actions = "";
                if ($row->status=='reviewed')
                {
                    $actions .= '<select id="party_'.$row->id.'" class="form-control" onchange="managerApproval('.$row->id.',\''.$party_description.'\',\''.$row->request_person_email.'\');">';
                    $actions .= "<option value=''>เลือก</option>";
                    $actions .= "<option value='accept'>ดำเนินการรับคณะต่อไป</option>";
                    $actions .= "<option value='cancel'>ปฎิเสธการรับคณะ</option>";
                    $actions .= "</select>";
                }
                else
                {
                    $party = Party::find($row->id);

                    $actions .= 'สถานะ : '.$party->latestStatus('th');
                    if ($row->status=='approved' || $row->status=='preparing')
                    {
                        //แสดงว่าประสานงานโดยใคร
                        $assigned = $party->assignedCoordinator(true);
                        //หากอนุมัติไปแล้วหรือยังดำเนินการอยู่สามารถเปลี่ยนผู้ประสานงานได้
                        $actions .= '<div style="margin: 5px auto;">';
                        $actions .= '<div>ประสานงานโดย : '.$assigned.'</div>';
                        $actions .= "<input id='party_".$row->id."' type='hidden' value='changePeople'>";
                        $actions .= '<a id="change_party_'.$row->id.'" class="btn btn-xs btn-primary" href="javascript:;" role="button" onclick="managerApproval('.$row->id.',\''.$party_description.'\',\''.$row->request_person_email.'\');"><i class="fa fa-exchange"></i> เปลี่ยนผู้ประสานงาน</a>';
                        $actions .= '</div>';
                    }
                }

                return $actions;
            })

            ->add_column('actioned', function($row){
                //create select control
                $party = Party::find($row->id);
                //change project co
                $party_description = addslashes(str_replace(array('"', '/'), "", $row->request_code.' '.$row->name.' ('.$row->qty.')'));
                $actions = "";
                $actions .= 'สถานะ : '.$party->latestStatus('th');
                if ($row->status=='approved' || $row->status=='preparing')
                {
                    //แสดงว่าประสานงานโดยใคร
                    $assigned = $party->assignedCoordinator(true);
                    $actions .= '<div style="margin: 5px auto;">';
                    $actions .= '<div>ประสานงานโดย : '.$assigned.'</div>';
                    $actions .= "<input id='party_".$row->id."' type='hidden' value='changePeople'>";
                    $actions .= '<a id="change_party_'.$row->id.'" class="btn btn-xs btn-primary" href="javascript:;" role="button" onclick="managerApproval('.$row->id.',\''.$party_description.'\',\''.$row->request_person_email.'\');"><i class="fa fa-exchange"></i> เปลี่ยนผู้ประสานงาน</a>';
                    $actions .= '</div>';
                }

                return $actions;
            })

            ->remove_column('id', 'end_date')

            ->make(true);
    }

    //return coordinator task handling
    function getCoordinateHandling($coordinator)
    {
        $coordinator_work = LuManagerAssign::leftJoin('parties AS p', 'lu_manager_assign.party_id', '=', 'p.id')
                    ->whereNotIn('p.status', array('reviewing', 'reviewed', 'other', 'cancelled1', 'cancelled2', 'terminated', 'finishing', 'finished'))
                    ->where('lu_manager_assign.coordinator_assigned', '=', $coordinator);

        return $coordinator_work;
    }

    //return task
    static function managerTask($is_history = 0, $is_all = 0)
    {
        $parties = Party::select('parties.id', 'parties.request_code', 'parties.name', 'parties.party_type_id as type', 'parties.start_date', 'parties.end_date', 'parties.created_at', 'parties.country', 'parties.people_quantity as qty', 'parties.request_person_name', 'parties.request_person_tel', 'parties.request_person_email', 'parties.objective_detail', 'parties.created_at AS created_at', 'parties.status', 'parties.interested', 'parties.expected', 'parties.joined', 'parties.paid_method', 'parties.related_budget_code');
        if ($is_history==1)
        {
            //this is history task
            $parties = $parties->whereNotIn('parties.status',array('reviewed'))
                ->whereIn('parties.id', function($q) use ($is_all){

                    if ($is_all==1)
                    {
                        $q->from('party_statuses AS ps')
                            ->select('ps.party_id')
                            ->where('parties.is_history', '=', 0)
                            ->orderBy('ps.created_at', 'DESC');
                    }
                    else
                    {
                        $q->from('party_statuses AS ps')
                            ->select('ps.party_id')
                            ->where('ps.created_by', '=', Auth::user()->id)
                            ->where('parties.is_history', '=', 0)
                            ->orderBy('ps.created_at', 'DESC');
                    }

                });
        }
        else
        {
            //this is current task
            $parties = $parties->whereStatus('reviewed');
        }

        return $parties;
    }

}