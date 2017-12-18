<?php

class PartyController extends CoordinatorController {

    protected $party;

    protected $party_coordinators;

    protected $party_request_objectives;

    protected $party_tags;

    protected $party_nations;

    protected $party_statuses;

    protected $assigned_roles;

    public function __construct(Party $party, PartyCoordinators $party_coordinators , PartyRequestObjectives $party_request_objectives, PartyTags $party_tags, PartyNations $party_nations, PartyStatuses $party_statuses, AssignedRoles $assigned_roles)
    {
        parent::__construct();
        $this->party = $party;
        $this->party_coordinators = $party_coordinators;
        $this->party_request_objectives = $party_request_objectives;
        $this->party_tags = $party_tags;
        $this->party_nations = $party_nations;
        $this->party_statuses = $party_statuses;
        $this->assigned_roles = $assigned_roles;
    }

    public function getIndex()
    {
        //select party with managing
        return View::make('svms.parties.index');
    }

    /*quick search*/
    public function getSearchQuick()
    {
        if (Input::get('query'))
        {
            //find by query word
            $search = Party::select('parties.id AS id', DB::raw('CONCAT(parties.customer_code," ",parties.name) AS name'))
                        ->where(function ($query) {
                            $query->where('request_code', 'like', '%'.Input::get('query').'%')
                                ->orWhere('customer_code', 'like', '%'.Input::get('query').'%')
                                ->orWhere('name', 'like', '%'.Input::get('query').'%');
                        })
                        ->orderBy('parties.created_at', 'DESC')
                        ->get()
                        ->toJson();

            return $search;
        }

        return array();
    }

    public function getHistory()
    {
        //select past party (history of party)
        $countries = DB::table('countries')->select('name AS text', 'alpha_2 AS id')->get();
        $partyTypes = PartyType::select('ID', 'name')->orderBy('name', 'ASC')->get();
        $partyObjectives = PartyObjective::select('id', 'name')->orderBy('name', 'ASC')->get();

        $coordinators = $this->projectCoordinators();

        return View::make('svms.parties.history', array('countries' => $countries, 'partyTypes' => $partyTypes, 'partyObjectives' => $partyObjectives, 'coordinators' => $coordinators));
    }

    public function getView($party)
    {
        //get table countries
        $countries = DB::table('countries')->select('name AS text', 'alpha_2 AS id')
            //->whereNotIn('alpha_2', array('th'))
            ->get();
			
		//get table personnels
        $personnels = Personnel::select('id', DB::raw('CONCAT(prefix,first_name," ",last_name) AS text'))->orderBy('text', 'ASC')->get();
		
		//get table staff works
        $work_types = WorkType::all();
		
        //get table party_type
        $partyTypes = PartyType::select('ID', 'name')->orderBy('name', 'ASC')->get();
        //get table party_objective
        $partyObjectives = PartyObjective::select('id', 'name')->orderBy('name', 'ASC')->get();
        //get table tags
        $partyTags = DB::table('party_tag')->select('tag');
        $tags = DB::table('tags')->select('tag')->union($partyTags)->get();
        //get table mflf_areas
        $mflfAreas = MflfArea::select('id', 'name')->get();
        //model คิดตัง
        $models = FinancialController::models();

        //add extra parameter
        $is_local = 0;
        $nations = array();
        $party_countries = $party->countries()->get();
        foreach ($party_countries as $party_country)
        {
            $nation = array();
            if ($party_country->country=='th')
            {
                $is_local = 1;
            }
            $nation['id'] = $party_country->country;
            $nation['text'] = DB::table('countries')->where('alpha_2', '=', $party_country->country)->pluck('name');

            array_push($nations, $nation);
        }
        $party['nations'] = $nations;
        $party['is_local'] = $is_local;
        $party['contacts'] = $party->coordinators()->get();

        //get coordinator if history
        if($party->is_history)
        {
            $coordinators = $this->projectCoordinators();
        }
        else
        {
            $coordinators = array();
        }

        return View::make('svms.parties.party', array('party' => $party, 'countries' => $countries, 'personnels' => $personnels, 'work_types' => $work_types, 'partyTypes' => $partyTypes, 'partyObjectives' => $partyObjectives, 'tags' => $tags, 'coordinators' => $coordinators, 'mflfAreas' => $mflfAreas, 'models' => $models) );

    }

    /*get ajax json document*/
    public function getDocumentJson()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        $party = Party::find(Input::get('party_id'));

        if (!$party)
        {
            $response = array(
                'status' => 'error',
                'msg' => 'ไม่สามารถเรียกข้อมูลจากคณะที่ถูกลบไปแล้ว'
            );

            return Response::json( $response );
        }

        $data = array();
        $folders = self::folders();

        $file_stocks = array();

        foreach(array_keys($folders) as $folder)
        {
            $files = self::documents($party, $folder);
            $folderName = $folders[$folder];

            $documents = array();

            foreach ($files as $file)
            {
                //set folder url by original
                $document = array();
                $document['text'] = $file->name;
                $document['a_attr'] = array(
                    'href' => $file->url,
                    'rel' => $file->path,
                    'id' => $file->id
                );
                $document['li_attr'] = array(
                    'class' => 'wrap-in-tree'
                );
                $document['icon'] = self::strFileTypeClass($file->type);

                array_push($documents, $document);
            }

            $file_stocks['text'] = $folderName;
            $file_stocks['icon'] = "fa fa-folder-o";
            $file_stocks['children'] = $documents;

            array_push($data, $file_stocks);
        }

        $response = array(
            'data' => $data
        );

        return Response::json( $response );
    }

    /*post upload files*/
    public function postUploadFiles()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            'partyUpload'    => 'required',
            'inputFile'   => 'required',
            'comboUploadFolder'   => 'required'
        );

        //Query party
        $party = Party::find(Input::get( 'partyUpload' ));
        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);
        // Check if the form validates with success
        if ($validator->passes())
        {
            if (Input::hasFile('inputFile'))
            {
                $finalFolder = ($party->customer_code=="") ? $party->request_code : $party->customer_code;
                //assign destination path
                $destinationPath = public_path().'/svms/'.Input::get( 'comboUploadFolder' ).'/'.$finalFolder;
                //assign file new name
                $fileName = date('dmYHis').'_'.Input::file('inputFile')->getClientOriginalName();
                //assign url
                $fileUrl = asset('svms/'.Input::get( 'comboUploadFolder' ).'/'.$finalFolder.'/'.$fileName);

                if(!file_exists($destinationPath))
                {
                    //create folder
                    mkdir($destinationPath, 0777);
                }

                //move file to upload
                if(Input::file('inputFile')->move($destinationPath, $fileName))
                {
                    //find a file type
                    $findNewFileStr = explode('.', $fileName);
                    $fileType = end($findNewFileStr);
                    //add log to database
                    $upload = new PartyUploadFiles;
                    $upload->party_id = $party->id;
                    $upload->name = $fileName;
                    $upload->url = $fileUrl;
                    $upload->path = $destinationPath.'/'.$fileName;
                    $upload->type = $fileType;
                    $upload->folder = Input::get( 'comboUploadFolder' );
                    $upload->created_by = Auth::user()->id;
                    $upload->updated_by = Auth::user()->id;
                    $upload->save();
                    //return success
                    return Redirect::to('party/'.$party->id.'/view')->with('status', 'success')
                            ->with('msg', 'แนบไฟล์ '.$fileName.' สำเร็จแล้ว !');
                }
                //return error
                return Redirect::to('party/'.$party->id.'/view')->with('status', 'error')
                    ->with('msg', 'แนบไฟล์ไม่สำเร็จ');
            }
            //return error
            return Redirect::to('party/'.$party->id.'/view')->with('status', 'error')
                ->with('msg', 'ไม่ได้แนบไฟล์ กรุณาแนบไฟล์ก่อนอัพโหลด');
        }
        //return error
        return Redirect::to('party/'.$party->id.'/view')->with('status', 'error')
            ->with('msg', 'Error กรุณาทำการอัพโหลดอีกครั้ง หรือติดต่อ Admin');
    }

    /*post actions for a party*/
    public function postActions()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            'party_id'    => 'required',
            'action'   => 'required',
            'note'   => 'required'
        );

        //Query party
        $party = Party::find(intval(Input::get('party_id')));

        if (!$party)
        {
            throw new Exception("ข้อมูลคณะได้ทำการถูกลบไปแล้ว !");
        }

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);
        // Check if the form validates with success
        if ($validator->passes())
        {
            // Start transaction!
            DB::beginTransaction();

            try
            {
                //default extra param values
                $params = false;
                //if re - scheduling if have schedule or budget transfer it to new date
                if (Input::get('action')=='re-schedule')
                {
                    //keep old start and end date
                    $old_start_date = $party->start_date;
                    $old_end_date = $party->end_date;
                    $old_ranges = ScheduleController::dateRangeArray($old_start_date,$old_end_date);
                    //keep new date range values
                    $convert_start = explode('/', Input::get('new_start_date'));
                    $convert_end = explode('/', Input::get('new_end_date'));
                    $new_start_date = $convert_start[2]."-".$convert_start[1]."-".$convert_start[0];
                    $new_end_date = $convert_end[2]."-".$convert_end[1]."-".$convert_end[0];
                    //get an extra params
                    $params = array('start_date' => $new_start_date, 'end_date' => $new_end_date);
                    //Set a new date in party
                    $party->start_date = $new_start_date;
                    $party->end_date = $new_end_date;
                    $new_ranges = ScheduleController::dateRangeArray($new_start_date,$new_end_date);
                    //Find schedule_id
                    $schedule = LuSchedule::where('party_id', '=', $party->id)->first();

                    //case pass schedule
                    if ($party->programingPassed())
                    {
                        //update activity schedule
                        $act = 0;

                        foreach($old_ranges as $old_date)
                        {
                            //set a new value
                            $new_activity_date = $new_ranges[$act];
                            //update a new value
                            LuScheduleTask::where('lu_schedule_id', '=', $schedule->id)
                                            ->whereRaw('DATE(start) = "'.$old_date.'"')
                                            ->where('type', '=', 'A')
                                            ->update([
                                                'start' => DB::raw('CONCAT("'.$new_activity_date.' ",TIME(start))'),
                                                'end' => DB::raw('CONCAT("'.$new_activity_date.' ",TIME(end))')
                                            ]);

                            $act++;
                        }

                        //update accommodation : loop accommodation from query
                        $accommodations = LuScheduleTask::where('lu_schedule_id', '=', $schedule->id)
                                            ->where('type', '=', 'S')
                                            ->get();

                        if($accommodations)
                        {
                            $accom = 0;

                            foreach($accommodations as $accommodation)
                            {
                                //1.count stay date
                                $stay_count = count(ScheduleController::dateRangeArray($accommodation->start,$accommodation->end));

                                //2.set new start and end
                                $new_accommodation_start = $new_ranges[$accom];
                                $new_accommodation_end = date_format(date_add(date_create($new_accommodation_start),date_interval_create_from_date_string((($stay_count)-1)." days")),"Y-m-d");

                                //3.update stay
                                LuScheduleTask::where('lu_schedule_id', '=', $schedule->id)
                                    ->where('id', '=', $accommodation->id)
                                    ->where('type', '=', 'S')
                                    ->update([
                                        'start' => DB::raw('CONCAT("'.$new_accommodation_start.' ","00:00:00")'),
                                        'end' => DB::raw('CONCAT("'.$new_accommodation_end.' ","00:00:00")')
                                    ]);

                                $accom += $stay_count;//set index by data count
                            }
                        }
                    }
                    //case pass budget
                    if ($party->budgetingPassed())
                    {
                        //define default index
                        $bud = 0;
                        //update only date in budget
                        foreach($old_ranges as $old_date)
                        {
                            //set a new value
                            $new_budget_date = $new_ranges[$bud];

                            LuBudgetDetail::where('party_id', '=', $party->id)
                                ->where('day','=',$old_date)
                                ->update([
                                    'day' => $new_budget_date
                                ]);

                            $bud++;
                        }
                    }
                }

                //update latest status
                $party_status = self::updateStatus($party, Input::get('action'), Input::get('note'), $params);

                if($party->save() && $party_status)
                {
                    //set a data to send mail
                    $data = $party;
                    $data['sender_name'] = Auth::user()->getFullName();
                    $data['note'] = Input::get('note');

                    //set a addition data for only re-schedule
                    if (Input::get('action')=='re-schedule')
                    {
                        $data['old_schedule'] = ScheduleController::dateRangeStr($old_start_date, $old_end_date, true, false, 'th', true);
                        $data['new_schedule'] = ScheduleController::dateRangeStr($new_start_date, $new_end_date, true, false, 'th', true);
                    }

                    //set a mail template by actions
                    switch(Input::get('action'))
                    {
                        case 're-schedule' :
                            $mailTemplate = 'emails.transaction.actions.re-schedule';
                            $strMailHeader = 'LU : '.$data->name.'เปลี่ยนแปลงกำหนดการดูงาน';
                            break;
                        case 'postpone' :
                            $mailTemplate = 'emails.transaction.actions.postpone';
                            $strMailHeader = 'LU : '.$data->name.'เลื่อนกำหนดการดูงานไม่มีกำหนด';
                            break;
                        default :
                            $mailTemplate = 'emails.transaction.actions.terminated';
                            $strMailHeader = 'LU : '.$data->name.'ได้ยกเลิกศึกษาดูงาน';
                    }

                    $data['mail_title'] = $strMailHeader;

                    //check send a mail to send recipients
                    Mail::send($mailTemplate, compact('data'), function($message) use ($data)
                    {
                        $message
                            ->to($data->request_person_email)
                            ->cc('lu_team@doitung.org')
                            ->subject($data->mail_title);
                    });

                    if(count(Mail::failures()) > 0)
                    {
                        throw new Exception("ไม่สามารถทำการบันทึกได้เนื่องจากส่งเมลไม่สำเร็จ");
                    }
                    else
                    {
                        //Request Commit and send response
                        DB::commit();
                        //response success when data save and mail sent
                        $response = array(
                            'status' => 'success',
                            'msg' => 'ทำการบันทึกข้อมูลสำเร็จแล้ว'
                        );

                        return Response::json( $response );
                    }
                }
                $response = array(
                    'status' => 'error',
                    'msg' => 'ไม่สามารถบันทึกได้'
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
        //response validate error
        $response = array(
            'status' => 'error',
            'msg' => $validator->messages()
        );

        return Response::json( $response );
    }

    /*delete party file permanent*/
    public function getDeleteFile()
    {
        if (unlink(Input::get( 'path' )))
        {
            //also delete database
            PartyUploadFiles::find(Input::get( 'file_id' ))->delete();
            //return a message
            $response = array(
                'status' => 'success',
                'msg' => 'ทำการลบไฟล์สำเร็จแล้ว !'
            );

            return Response::json( $response );
        }
        else
        {
            $response = array(
                'status' => 'error',
                'msg' => 'Error ไม่สามารถลบไฟล์ได้'
            );

            return Response::json( $response );
        }
    }

    /*Post Edit Party Status*/
    public function postStatus()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            '_party_id' => 'required|integer',
            'status'   => 'required',
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            //update latest status
            $party = Party::find(Input::get( '_party_id' ));
            $party_status = self::updateStatus($party, Input::get( 'status' ));

            if($party_status)
            {
                $response = array(
                    'status' => 'success',
                    'msg' => 'ทำการบันทึกข้อมูลสำเร็จแล้ว'
                );

                return Response::json( $response );
            }
            $response = array(
                'status' => 'error',
                'msg' => 'ไม่สามารถบันทึกได้'
            );

            return Response::json( $response );
        }
        $response = array(
            'status' => 'error',
            'msg' => $validator->messages()
        );

        return Response::json( $response );
    }

    /*Post History of Coordinator*/
    public function postCoordinatorHistory()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            '_party_id' => 'required|integer',
            'project_co'   => 'required',
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            //update
            $party = Party::find(Input::get( '_party_id' ));
            $party->project_co = Input::get( 'project_co' );

            if($party->save())
            {
                $response = array(
                    'status' => 'success',
                    'msg' => 'ระบุผู้ประสานงานหลักแล้ว'
                );

                return Response::json( $response );
            }
            $response = array(
                'status' => 'error',
                'msg' => 'ไม่สามารถบันทึกได้'
            );

            return Response::json( $response );
        }
        $response = array(
            'status' => 'error',
            'msg' => $validator->messages()
        );

        return Response::json( $response );
    }

    /*Post รายได้ of Coordinator*/
    public function postSummaryIncomeHistory()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            '_party_id' => 'required|integer',
            'summary_income'   => 'required',
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            //update
            $party = Party::find(Input::get( '_party_id' ));
            $party->summary_income = Input::get( 'summary_income' );
            $party->income_edited_by = Auth::user()->id;//mark ไว้ว่าใครแก้ ซึ่งปกติมี manager เท่านั้นที่แก้ได้

            if($party->save())
            {
                $response = array(
                    'status' => 'success',
                    'msg' => 'บันทึกรายได้รวมแล้ว'
                );

                return Response::json( $response );
            }
            $response = array(
                'status' => 'error',
                'msg' => 'ไม่สามารถบันทึกได้'
            );

            return Response::json( $response );
        }
        $response = array(
            'status' => 'error',
            'msg' => $validator->messages()
        );

        return Response::json( $response );
    }

    /*get party data with ajax*/
    public function getById()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        $data = Party::find(Input::get('id'));

        if(!$data)
        {
            $response = array(
                'status' => 'error',
                'msg' => 'ไม่สามารถดึงข้อมูลได้หรือข้อมูลอาจถูกลบไปแล้ว'
            );

            return Response::json( $response );
        }

        $data = $data->fullData();

        $response = array(
            'data' => $data
        );

        return Response::json( $response );
    }

    /*Delete with soft*/
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
        if ($validator->passes()) {
            //mark as delete for party keep other data to revive after time
            $party = Party::find(input::get('id'));

            if ($party->delete())
            {
                //response success
                $response = array(
                    'status' => 'success',
                    'msg' => 'ลบสำเร็จแล้ว'
                );

                return Response::json( $response );
            }
            //response error
            $response = array(
                'status' => 'error',
                'msg' => 'Error ลบไม่สำเร็จ',
                'error' => $party->error()
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

    /*Create without transaction*/
    public function postCreateOrUpdate()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        //save new data or update
        DB::beginTransaction();

        try {
            $party = (Input::get('old_id')=='' || Input::get('old_id')==NULL) ? new Party : Party::find(Input::get('old_id'));
            $party->customer_code = Input::get('customer_code');
            $party->name = Input::get('name');
            $party->party_type_id = Input::get('party_type_id');
            $party->objective_detail = Input::get('objective_detail');
            $party->people_quantity = Input::get('people_quantity');
            $party->start_date = Input::get('start_date');
            $party->end_date = Input::get('end_date');
            $party->status = 'finished';
            $party->is_history = 1;//always keep it to history
            $party->contributor = Auth::user()->id;//keep user id

            $party->project_co = Input::get('project_co');
            $party->summary_income = Input::get('summary_income');
            /*add for request for personnel*/
            $party->request_for_lu_personnel = (Input::get('request_for_lu_personnel')=='yes') ? 1 : 0;

            if($party->save())
            {
                //save flow when create new
                if (Input::get('old_id')=='' || Input::get('old_id')==NULL)
                {
                    $this->party_statuses->party_id = $party->id;
                    $this->party_statuses->status = 'finished';
                    $this->party_statuses->contributor = Auth::user()->getPersonnel()->id;
                    $this->party_statuses->created_by = Auth::user()->id;
                    $this->party_statuses->updated_by = Auth::user()->id;
                    $this->party_statuses->save();
                }
                //delete and save new country
                $party->countries()->delete();
                $this->setNations(Input::get('radioFromCountry'), $party, Input::get('countries'));

                //save objective
                $objectives = array();
                //Remove old objective data
                $party->requestObjectives()->delete();
                //Objective Data
                $ojt = Input::get('objectives');
                for($j=0;$j<count($ojt);$j++)
                {
                    $objective = new PartyRequestObjectives;
                    $objective->party_objective_id = $ojt[$j];
                    $objective->party_id = $party->id;

                    array_push($objectives, $objective);
                }

                //party edit status not change
                if ($party->requestObjectives()->saveMany($objectives))
                {
                    //Request Commit and send response
                    DB::commit();

                    $response = array(
                        'status' => 'success',
                        'msg' => 'ทำการบันทึกข้อมูลสำเร็จแล้ว'
                    );

                    return Response::json( $response );
                }
                else
                {
                    throw new Exception("ไม่สามารถทำการบันทึกข้อมูลได้");
                }
            }
            else
            {
                throw new Exception("ไม่สามารถทำการบันทึกข้อมูลได้");
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

    /*Edit Request or Create*/
    public function postEdit()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            '_party_id' => 'required|integer',
            'name'   => 'required|max:255',
            'party_type_id' => 'required|integer',
            'objectives' => 'required',
            'interested' => 'max:1000',
            'expected' => 'max:1000'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            // Start transaction!
            DB::beginTransaction();

            try {
                // Validate, then create if valid
                $party = Party::find(Input::get('_party_id'));
                if(!$party)
                {
                    return Redirect::to('404');
                }
                // Create a request of party
                //$party->customer_code = Input::get('customer_code'); //ใช้ชั่วคราวพอเข้าปี 59 จะตัดการแก้ไขส่วนนี้ออก

                $party->name = Input::get('name');
               // $party->country = Input::get('country');
                $party->party_type_id = Input::get('party_type_id');
                $party->objective_detail = Input::get('obj_detail_desc');
                $party->people_quantity = Input::get('people_quantity');
                //$party->financial_department_id = Input::get('financial');

                $start_date = explode('/', Input::get('start_date'));
                $end_date = explode('/', Input::get('end_date'));
                $party->start_date = $start_date[2].'-'.$start_date[1].'-'.$start_date[0];
                $party->end_date = $end_date[2].'-'.$end_date[1].'-'.$end_date[0];

                //additional data
                $party->interested = Input::get('interested');
                $party->expected = Input::get('expected');
                $party->joined = Input::get('joined');

                //add paid method
                $party->paid_method = Input::get('paid_method');
                //paid method save
                if (Input::get('paid_method')=='other')
                {
                    $party->budget_code = Input::get('other_code');
                }
                else if(Input::get('paid_method')=='donate')
                {
                    $party->budget_code = '912';
                    $party->related_budget_code = Input::get('donate_code');
                }
                else if(Input::get('paid_method')=='absorb')
                {
                    $party->budget_code = '912';
                    $party->related_budget_code = Input::get('absorb_code');
                }
                else
                {
                    //default is full
                    $party->budget_code = '912';
                    $party->related_budget_code = '';
                }
                //add detail accommodation
                $party->accommodation_detail = Input::get('accommodation_detail');
                //add request personnel
                $party->request_for_lu_personnel = (Input::get('request_for_lu_personnel')=='yes') ? 1 : 0;
                $party->request_lu_personnel_reason = (Input::get('request_for_lu_personnel')=='yes') ? Input::get('request_lu_personnel_reason') : '';

                if($party->save())
                {
                    //delete and save new country
                    $party->countries()->delete();
                    $this->setNations(Input::get('radioFromCountry'), $party, Input::get('countries'));

                    //save objective and contacts
                    $coordinators = array();
                    $objectives = array();
                    $tags = array();
                    $locationBases = array();

                    //Remove old contact data
                    $party->coordinators()->delete();
                    //Re Add contact Data
                    for($i=0;$i<count(Input::get('coor_name'));$i++)
                    {
                        //if name = "" not add
                        if(Input::get('coor_name.'.$i)!="")
                        {
                            $coordinator = new PartyCoordinators;

                            $coordinator->name = Input::get('coor_name.'.$i);
                            $coordinator->mobile = Input::get('coor_mobile.'.$i);
                            $coordinator->email = Input::get('coor_email.'.$i);
                            $coordinator->party_id = $party->id;

                            array_push($coordinators, $coordinator);
                        }
                    }
                    //Remove old objective data
                    $party->requestObjectives()->delete();
                    //Objective Data
                    $ojt = Input::get('objectives');
                    for($j=0;$j<count($ojt);$j++)
                    {
                        $objective = new PartyRequestObjectives;
                        $objective->party_objective_id = $ojt[$j];
                        $objective->party_id = $party->id;

                        array_push($objectives, $objective);
                    }

                    /*Manage Mflf Area Data*/
                    $areas = Input::get('location_bases');
                    if (count($areas)>0)
                    {
                        //***Delete Old Data Step***
                        $party->locationBases()->delete();
                        //***Add Data Step***
                        //loop for keep it in aray
                        for($a=0;$a<count($areas);$a++)
                        {
                            $area = new LuLocationBases;
                            $area->party_id = $party->id;
                            $area->mflf_area_id = $areas[$a];
                            $area->created_by = Auth::user()->id;
                            $area->updated_by = Auth::user()->id;

                            array_push($locationBases, $area);
                        }
                        //save to transaction database
                        $party->locationBases()->saveMany($locationBases);
                    }

                    //Tag Data
                    $tag = Input::get('tags');

                    if (count($tag)>0)
                    {
                        //Remove old tag data if have
                        $party->tags()->delete();
                        for($k=0;$k<count($tag);$k++)
                        {
                            $partyTag = new PartyTags;
                            $partyTag->tag = $tag[$k];
                            $partyTag->party_id = $party->id;
                            $partyTag->created_by = Auth::user()->id;
                            $partyTag->updated_by = Auth::user()->id;

                            array_push($tags, $partyTag);
                        }
                        $party->tags()->saveMany($tags);
                    }

                    //party edit status not change
                    if ($party->coordinators()->saveMany($coordinators) && $party->requestObjectives()->saveMany($objectives))
                    {
                        //Request Commit and send response
                        DB::commit();

                        $response = array(
                            'status' => 'success',
                            'party' => $party,
                            'msg' => 'ทำการบันทึกข้อมูลสำเร็จแล้ว'
                        );

                        return Response::json( $response );
                    }
                    else
                    {
                        throw new Exception("ไม่สามารถทำการบันทึกข้อมูลได้");
                    }
                }
                else
                {
                    throw new Exception("ไม่สามารถทำการบันทึกข้อมูลได้");
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
        //response error
        $response = array(
            'status' => 'error',
            'msg' => $validator->messages()
        );

        return Response::json( $response );
    }

    public function getRequest()
    {
        //get table countries except thailand
        $countries = DB::table('countries')->select('name AS text', 'alpha_2 AS id')
                    //->whereNotIn('alpha_2', array('th'))
                    ->get();
        //get table party_type
        $partyTypes = PartyType::select('ID', 'name')->get();
        //get table party_objective
        $partyObjectives = PartyObjective::select('id', 'name')->get();
        //get table mflf_areas
        $mflfAreas = MflfArea::select('id', 'name')->get();
        //model คิดตัง
        $models = FinancialController::models();

        //check if now auth return request data too.
        if (Auth::check())
        {
            $request_person = Auth::user();
        }
        else
        {
            $request_person = false;
        }

        return View::make('svms/request', array('countries' => $countries, 'partyTypes' => $partyTypes, 'partyObjectives' => $partyObjectives, 'mflfAreas' => $mflfAreas, 'models' => $models, 'request_person' => $request_person));
    }

    //return page editing info by reviewer
    public function getEditing($encrypt, $state = 'editByRequest')
    {
        //decrypt for party id
        $decrypted = Crypt::decrypt($encrypt);
        //find party object
        $party = Party::find($decrypted);
        if (!$party)
        {
            return Redirect::to('404');
        }

        //get table countries except thailand
        $countries = DB::table('countries')->select('name AS text', 'alpha_2 AS id')
            //->whereNotIn('alpha_2', array('th'))
            ->get();
        //get table party_type
        $partyTypes = PartyType::select('ID', 'name')->get();
        //get table party_objective
        $partyObjectives = PartyObjective::select('id', 'name')->get();
        //get table mflf_areas
        $mflfAreas = MflfArea::select('id', 'name')->get();
        //model คิดตัง
        $models = FinancialController::models();
        //get full data to show.
        $party = $party->fullData();
        $party['encrypt'] = $encrypt;
        //get review data to description
        $reviewObj = $party->latestStatusAsObj();
        $party['review_person'] = ($reviewObj->created_by==1) ? 'Administrator' : User::find($reviewObj->created_by)->getShortName();
        $party['review_note'] = $reviewObj->note;
        //set request person
        $request_person = false;

        //set view to edit
        return View::make('svms/request', array('party' => $party, 'countries' => $countries, 'partyTypes' => $partyTypes, 'partyObjectives' => $partyObjectives, 'mflfAreas' => $mflfAreas, 'models' => $models, 'request_person' => $request_person, 'state' => $state));
    }

    //this page is show success step on reviewing step
    public function getSuccess($encrypt, $state = 'firstRequest')
    {
        //decrypt for party id
        $decrypted = Crypt::decrypt($encrypt);
        //find party object
        $party = Party::find($decrypted);
        //return when not find party
        if (!$party)
        {
            return Redirect::to('404');
        }

        //set full data
        $party = $party->fullData();
        $party['nationals'] = $party->getNationArrays(true);

        //set view to success
        return View::make('svms/landing/request-success', compact('encrypt', 'party', 'state'));
    }

    //this page is for main post party controller
    public function postRequest()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            'name'   => 'required|max:255',
            'party_type_id' => 'required|integer',
            'objectives' => 'required',
            'request_person_name' => 'required|max:255',
            'request_person_tel' => 'required|max:30',
            'request_person_email' => 'required|max:100',
            'interested' => 'max:1000',
            'expected' => 'max:1000',
            "file" => "mimes:pdf"
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);
        // Check if the form validates with success
        if ($validator->passes())
        {
            // Start transaction!
            DB::beginTransaction();

            try {
                //Retrieve a state
                $state = Input::get('state');
                //At start check this is first request or pending to edit state
                $party = ($state=='firstRequest') ? $this->party : Party::find(Crypt::decrypt(Input::get('encrypt')));
                //Check if party id not valid return 404
                if (!$party)
                {
                    throw new Exception("คณะนี้อาจถูกลบไปแล้วกรุณาแจ้งผู้ดูแลระบบ");
                }

                // Validate, then create if valid
                // Create a request of party case if first then create request_code too.
                //Note :
                //firstRequest = requester post request for a party but not yet confirm and send mail.
                //editByRequest = requester edit by request from reviewer finally send mail alert.
                //editByYourself = .... edit before send request

                //This is first request (also edit by yourself before submit send to reviewer)
                if ($state=='firstRequest')
                {
                    //set a once request code when initial
                    $party->request_code = $this->party->getRequestCode();
                    $party->status = 'pending';
                }
                //Editing by request state
                if($state=='editByRequest')
                {
                    $party->status = 'reviewing';
                }

                //หากไม่ได้ทำการสร้างคำร้องใหม่เป็นการแก้ไขคำร้อง จะต้องมีการเคลียร์ข้อมูล
                if ($state!='firstRequest')
                {
                    //When edit delete old data before
                    $party->countries()->delete();//countries
                    $party->requestObjectives()->delete();//objectives
                    $party->coordinators()->delete();//coordinators
                    $party->locationBases()->delete();//location bases
                }

                //Add or Edit meta data
                $party->name = Input::get('name');
                $party->party_type_id = Input::get('party_type_id');
                $party->objective_detail = Input::get('obj_detail_desc');
                $party->people_quantity = Input::get('people_quantity');

                $start_date = explode('/', Input::get('start_date'));
                $end_date = explode('/', Input::get('end_date'));
                $party->start_date = $start_date[2].'-'.$start_date[1].'-'.$start_date[0];
                $party->end_date = $end_date[2].'-'.$end_date[1].'-'.$end_date[0];

                $party->request_person_name = Input::get('request_person_name');
                $party->request_person_email = Input::get('request_person_email').'@doitung.org';
                $party->request_person_tel = Input::get('request_person_tel');

                //additional data
                $party->interested = Input::get('interested');
                $party->expected = Input::get('expected');
                $party->joined = Input::get('joined');

                //add paid method
                $party->paid_method = Input::get('paid_method');
                //paid method save
                if (Input::get('paid_method')=='other')
                {
                    $party->budget_code = Input::get('other_code');
                }
                else if(Input::get('paid_method')=='donate')
                {
                    $party->budget_code = '912';
                    $party->related_budget_code = Input::get('donate_code');
                }
                else if(Input::get('paid_method')=='absorb')
                {
                    $party->budget_code = '912';
                    $party->related_budget_code = Input::get('absorb_code');
                }
                else
                {
                    //default is full
                    $party->budget_code = '912';
                    $party->related_budget_code = '';
                }

                //add request for lu personnel
                $party->request_for_lu_personnel = (Input::get('request_for_lu_personnel')=='yes') ? 1 : 0;
                $party->request_lu_personnel_reason = (Input::get('request_for_lu_personnel')=='yes') ? Input::get('request_lu_personnel_reason') : '';
                //add accommodation detail
                $party->accommodation_detail = (Input::get('request_accommodation')==='yes') ? Input::get('request_accommodation_information') : null;

                //Save meta data
                if($party->save())
                {
                    $txtEditNote = "";
                    //save party status log when firstRequest or editByRequest
                    if ($state=='firstRequest' || $state=='editByRequest')
                    {
                        $this->party_statuses->party_id = $party->id;
                        $this->party_statuses->status = ($state==='firstRequest') ? 'pending' : 'reviewing';
                        //save note when re-send information to reviewer
                        if($state==='editByRequest')
                        {
                            $txtEditNote = Input::get('edit_response_note');
                            $this->party_statuses->note = $txtEditNote;
                            //set a revision by request to edit
                            $this->party_statuses->revision = $party->numberOfEditing();//latest edit
                        }
                        $this->party_statuses->start_date = $party->start_date;//log start date
                        $this->party_statuses->end_date = $party->end_date;//log end date
                        $this->party_statuses->created_by = 1;//admin is first
                        $this->party_statuses->updated_by = 1;//admin is first
                        $this->party_statuses->save();//save a log
                    }

                    //save a more countries
                    $this->setNations(Input::get('radioFromCountry'), $party, Input::get('countries'));

                    //then create directory name= request_code and put renamed letter file
                    if (Input::hasFile('file'))
                    {
                        //assign destination path
                        $destinationPath = public_path().'/svms/request/'.$party->request_code;
                        //assign file new name
                        $fileName = "request_letter.pdf";
                        //assign url
                        $fileUrl = asset('svms/request/'.$party->request_code.'/'.$fileName);

                        //1. check destination path exists
                        if(!file_exists($destinationPath))
                        {
                            //1.1 create directory name by request code
                            mkdir($destinationPath, 0777);
                        }
                        //2. save file in to directory
                        Input::file('file')->move($destinationPath, $fileName);

                        //3. add log to database
                        $upload = new PartyUploadFiles;
                        $upload->party_id = $party->id;
                        $upload->name = $fileName;
                        $upload->url = $fileUrl;
                        $upload->path = $destinationPath.'/'.$fileName;
                        $upload->type = 'pdf';
                        $upload->folder = 'request';
                        $upload->created_by = (Auth::check()) ? Auth::user()->id : 1;
                        $upload->updated_by = (Auth::check()) ? Auth::user()->id : 1;
                        $upload->save();
                    }

                    //if not auth also create a dir = request_code and put renamed travel01
                    if (!Auth::check())
                    {
                        if (Input::hasFile('travel01_file'))
                        {
                            //assign destination path
                            $destinationTravel01Path = public_path().'/svms/travel01/'.$party->request_code;
                            //assign file new name
                            $fileTravel01Name = "travel01.pdf";
                            //assign url
                            $fileTravel01Url = asset('svms/travel01/'.$party->request_code.'/'.$fileTravel01Name);

                            //1. check destination path exists
                            if(!file_exists($destinationTravel01Path))
                            {
                                //1.1 create directory name by request code
                                mkdir($destinationTravel01Path, 0777);
                            }
                            //2. save file in to directory
                            Input::file('travel01_file')->move($destinationTravel01Path, $fileTravel01Name);

                            //3. add log to database
                            $upload01 = new PartyUploadFiles;
                            $upload01->party_id = $party->id;
                            $upload01->name = $fileTravel01Name;
                            $upload01->url = $fileTravel01Url;
                            $upload01->path = $destinationTravel01Path.'/'.$fileTravel01Name;
                            $upload01->type = 'pdf';
                            $upload01->folder = 'travel01';
                            $upload01->created_by = 1;
                            $upload01->updated_by = 1;
                            $upload01->save();
                        }
                    }

                    //save objective and contacts
                    $coordinators = array();
                    $objectives = array();
                    $locationBases = array();

                    //Coordinators Data
                    if (count(Input::get('coor_name'))>0)
                    {
                        for($i=0;$i<count(Input::get('coor_name'));$i++)
                        {
                            //if name = "" not add
                            if(Input::get('coor_name.'.$i)!="")
                            {
                                $coordinator = new PartyCoordinators;

                                $coordinator->name = Input::get('coor_name.'.$i);
                                $coordinator->mobile = Input::get('coor_mobile.'.$i);
                                $coordinator->email = Input::get('coor_email.'.$i);
                                $coordinator->party_id = $party->id;

                                array_push($coordinators, $coordinator);
                            }
                        }
                    }

                    //Objective Data
                    $ojt = Input::get('objectives');
                    if (count($ojt)>0)
                    {
                        for($j=0;$j<count($ojt);$j++)
                        {
                            $objective = new PartyRequestObjectives;
                            $objective->party_objective_id = $ojt[$j];
                            $objective->party_id = $party->id;

                            array_push($objectives, $objective);
                        }
                    }

                    //Mflf Area Data
                    $areas = Input::get('location_bases');
                    if (count($areas)>0)
                    {
                        //loop for keep it in aray
                        for($a=0;$a<count($areas);$a++)
                        {
                            $area = new LuLocationBases;
                            $area->party_id = $party->id;
                            $area->mflf_area_id = $areas[$a];
                            $area->created_by = 1;
                            $area->updated_by = 1;

                            array_push($locationBases, $area);
                        }
                    }

                    if ($party->coordinators()->saveMany($coordinators) && $party->requestObjectives()->saveMany($objectives) && $party->locationBases()->saveMany($locationBases))
                    {
						//Request Commit and send response
                        DB::commit();

                        /*Encrypt check encrypt*/
                        $encrypt = Input::get('encrypt');
                        if ($encrypt=='null')
                        {
                            $encrypt = Crypt::encrypt($party->id);
                        }

                        /*Set Url to Return*/
                        $redirectUrl = ($state=='firstRequest' || $state=='editByYourself') ? 'party/'.$encrypt.'/pending' : 'party/'.$encrypt.'/success/'.$state;

                        /*Send force mail alert If this state is edit_by_request*/
                        if ($state=='editByRequest')
                        {
                            //Send and Check mail sent.
                            if(!$this->confirmSendRequest($party,$encrypt,$state,$txtEditNote))
                            {
                                throw new Exception("ไม่สามารถส่ง Email แจ้งไปยัง Reviewer ได้กรุณาติดต่อผู้ดูแลระบบ");
                            }
                        }

						/*return success*/
                        $response = array(
                            'status' => 'success',
                            'msg' => 'ทำการบันทึกสำเร็จแล้ว',
                            'url' => URL::to($redirectUrl)
                        );

                        return Response::json( $response );
                    }
                    else
                    {
                        throw new Exception("ไม่สามารถบันทึกคำร้องได้");
                    }
                }
                else
                {
                    throw new Exception("ไม่สามารถบันทึกคำร้องได้");
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
        //response validate error
        $response = array(
            'status' => 'error',
            'msg' => $validator->messages()
        );

        return Response::json( $response );
    }

    //return page state is pending (before send to reviewer)
    public function getPending($encrypt)
    {
        //decrypt for party id
        $decrypted = Crypt::decrypt($encrypt);
        //find party object
        $party = Party::find($decrypted);
        //return when not find party
        if (!$party)
        {
            return Redirect::to('404');
        }
        //check if party is not pending state
        if ($party->latestStatus()!='pending')
        {
            return Redirect::to('404');
        }

        //set full data
        $party = $party->fullData();
        $party['nationals'] = $party->getNationArrays(true);

        //set view to success
        return View::make('svms/landing/request-pending', compact('encrypt', 'party'));
    }

    //Post submit send request to reviewer when state firstRequest only
    public function postRequestConfirm()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Start transaction!
        DB::beginTransaction();

        // Declare the rules for the form validation
        $rules = array(
            'encrypt'   => 'required',
            'state' => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);
        // Check if the form validates with success
        if ($validator->passes())
        {
            try
            {
                $encrypt = Input::get('encrypt');
                $state = Input::get('state');
                $party = Party::find(Crypt::decrypt($encrypt));
                //return when not find out party
                if (!$party)
                {
                    return Redirect::to('404');
                }
                //check state if not firstRequest redirect to 404
                if ($state!='firstRequest')
                {
                    return Redirect::to('404');
                }

                //set party status to reviewing (Allow reviewer to review and approve)
                $party->status = 'reviewing';

                //log status to reviewing step
                $this->party_statuses->party_id = $party->id;
                $this->party_statuses->status = 'reviewing';
                $this->party_statuses->start_date = $party->start_date;
                $this->party_statuses->end_date = $party->end_date;
                $this->party_statuses->note = 'ยื่นคำร้องดูงานครั้งแรก';
                $this->party_statuses->created_by = 1;
                $this->party_statuses->updated_by = 1;

                //Save status and Log
                if($party->save() && $this->party_statuses->save())
                {
                    //Request Commit and send response
                    DB::commit();

                    //When finish a save.Send a mail request to Reviewer
                    if(!$this->confirmSendRequest($party,$encrypt,$state))
                    {
                        throw new Exception("ไม่สามารถส่ง Email แจ้งไปยัง Reviewer ได้กรุณาติดต่อผู้ดูแลระบบ");
                    }
                    //return success process
                    $response = array(
                        'status' => 'success',
                        'msg' => 'ทำการส่งคำร้องไปยังผู้มีอำนาจตรวจสอบสำเร็จแล้ว'
                    );

                    return Response::json( $response );
                }
                else
                {
                    throw new Exception("ไม่สามารถยืนยันการส่งคำร้องได้ กรุณาติดต่อผู้ดูแลระบบ");
                }
            }
            catch (\Exception $e)
            {
                DB::rollback();

                $response = array(
                    'status' => 'error',
                    'msg' => $e->getMessage(),
                );

                return Response::json($response);
            }
        }

        //response validate error
        $response = array(
            'status' => 'error',
            'msg' => $validator->messages()
        );

        return Response::json( $response );
    }

    //this function use for confirm request state and change
    // 1.1.pending to reviewing when first request
    // or 1.2.editing to reviewing when edit by reviewer request.
    function confirmSendRequest($party,$encrypt,$state,$edit_note = "")
    {
        //Send Email To Reviewer Group and Requester
        $data = $party->fullData();

        //Check if first or editing add more information in this email
        $pending_number = $party->numberOfEditing();

        $data['first_request'] = ($state=='firstRequest') ? 1 : 0;
        $data['pending_number'] = $pending_number;
        $data['edit_note'] = $edit_note;
        $data['email_title'] = ($state=='firstRequest') ? 'LU : '.$party->name.'ยื่นคำร้องขอศึกษาดูงาน' : 'LU : '.$party->name.'ได้แก้ไขเพิ่มเติมคำร้องศึกษาดูงาน ครั้งที่'.$pending_number;
        $data['encrypt'] = $encrypt;

        //Get email list from reviewer list
        $data['mails'] = $this->assigned_roles->getMailsByRole(array('reviewer'));

        //Check original file exist
        $data['request_file'] = $party->fileUrl();
        $data['travel01_file'] = $party->fileUrl('travel01');

        Mail::send('emails.transaction.request', compact('data'), function($message) use ($data)
        {
            $message
                ->to($data->mails)
                ->cc($data->request_person_email, 'lu_team@doitung.org')
                ->subject($data->email_title);

            if ($data['request_file'])
            {
                $message->attach($data['request_file']);
            }

            if ($data['travel01_file'])
            {
                $message->attach($data['travel01_file']);
            }
        });

        //check mail failure or success
        if(count(Mail::failures()) > 0)
        {
            return false;
        }

        //if not fail return true
        return true;
    }

    //return json party event of customer
    public function getEvents()
    {
        $events = $this->party->getManagerPassed()
            ->select('parties.id AS id',
                DB::raw('CASE WHEN (parties.customer_code!="") THEN CONCAT(parties.customer_code, " ", parties.name, "(", parties.people_quantity, ")") ELSE CONCAT(parties.name, "(", parties.people_quantity, ")") END AS title'),
                'parties.start_date AS start',
                DB::raw('DATE_ADD(parties.end_date, INTERVAL 1 DAY) AS end'))
            ->get();
        $events = $events->toJson();

        return $events;
    }

    //return json all party event
    public function getAllEvents()
    {
        //บวกวันที่ไปอีก 1 เดือนหัวท้าย
        $start_date = date_add(date_create(Input::get('start')),date_interval_create_from_date_string("-1 months"));
        $end_date = date_add(date_create(Input::get('end')),date_interval_create_from_date_string("+1 months"));
        //load data
        $array = array();
        $events = $this->party->getAllData()
            ->select
            (
                'parties.id AS id', 'parties.is_not_lu_manage',
                DB::raw('CASE WHEN (parties.customer_code!="") THEN CONCAT(parties.customer_code, " ", parties.name, "(", parties.people_quantity, ")") ELSE CONCAT(parties.name, "(", parties.people_quantity, ")") END AS title'),
                'parties.start_date AS start',
                DB::raw('DATE_ADD(parties.end_date, INTERVAL 1 DAY) AS end'),
                DB::raw('(
                    CASE parties.status
                    WHEN "reviewing" THEN "#2aaad1"
                    WHEN "reviewed" THEN "#2aaad1"
                    WHEN "approved" THEN "#2aaad1"
                    WHEN "finishing" THEN "#409440"
                    WHEN "finished" THEN "#409440"
                    WHEN "postpone" THEN "#eb9316"
                    WHEN "terminated" THEN "#c12e2a"
                    WHEN "cancelled1" THEN "#c12e2a"
                    WHEN "cancelled2" THEN "#c12e2a"
                    ELSE "#245581"
                    END
                    )AS color')
            )
            ->where('parties.start_date', '>=', date_format($start_date,"Y-m-d"))
            ->where('parties.end_date', '<=', date_format($end_date,"Y-m-d"));

        //filter dealing step
        if (Input::get('is_dealing')==='true')
        {
            array_push($array, 'reviewing', 'reviewed', 'approved');
        }
        //filter dealt or is customer step
        if (Input::get('is_dealt')==='true')
        {
            array_push($array, 'preparing', 'ongoing');
        }
        //filter finished
        if (Input::get('is_finish')==='true')
        {
            array_push($array, 'finishing', 'finished');
        }
        //filter postpone group
        if (Input::get('is_postpone')==='true')
        {
            array_push($array, 'postpone');
        }
        //filter all cancelled step
        if (Input::get('is_cancelled')==='true')
        {
            array_push($array, 'terminated', 'cancelled1', 'cancelled2');
        }

        $events = $events->whereIn('parties.status', $array);

        //filtering with personnels
        if (Input::get('personnels')!=="")
        {
            $personnels = Input::get('personnels');
            $events = $events->whereIn('parties.id', function($q) use ($personnels){
                $q->select('party_id')
                    ->from('lu_manager_assign')
                    ->whereIn('coordinator_assigned', $personnels);
            });
        }

        $events = $events->get();

        foreach($events as $event)
        {
            $event['project_coordinator'] = $event->assignedCoordinator(true);
            $event['base'] = $event->getLocationBaseStr();
        }

        return Response::json($events);
    }

    //return json all party on location base
    function getAllLocations()
    {
        //บวกวันที่ไปอีก 1 เดือนหัวท้าย
        $start_date = date_add(date_create(Input::get('start')),date_interval_create_from_date_string("-1 months"));
        $end_date = date_add(date_create(Input::get('end')),date_interval_create_from_date_string("+1 months"));
        //load data
        $array = array();
        $events = $this->party->getAllData()
            ->select
            (
                'parties.id AS id', 'parties.is_not_lu_manage',
                DB::raw('CASE WHEN (parties.customer_code!="") THEN CONCAT(parties.customer_code, " ", parties.name, "(", parties.people_quantity, ")") ELSE CONCAT(parties.name, "(", parties.people_quantity, ")") END AS title'),
                'parties.start_date AS start',
                DB::raw('DATE_ADD(parties.end_date, INTERVAL 1 DAY) AS end')
            )
            ->where('parties.start_date', '>=', date_format($start_date,"Y-m-d"))
            ->where('parties.end_date', '<=', date_format($end_date,"Y-m-d"));

        //filter ดอยตุง รวมไปถึงปูนะ ปางมะหัน
        if (Input::get('dt')==='true')
        {
            array_push($array, 1, 2, 3);
        }
        //filter น่าน
        if (Input::get('nan')==='true')
        {
            array_push($array, 4);
        }
        //filter ดอยตุง + น่าน (คณะที่ไปทั้งสองที่ รวมไปถึงปูนะกับปางมะหัน)
        if (Input::get('all')==='true')
        {
            array_push($array, 1, 2, 3, 4);
        }
        //filter มา กทม.
        if (Input::get('bkk')==='true')
        {
            array_push($array, 5);
        }
        //filter all
        if (Input::get('other')==='true')
        {
            array_push($array, 1, 2, 3, 4, 5);
        }

        $array = array_unique($array);

        //cut non-use value by un-check value
        if (Input::get('dt')==='false')
        {
            $array = array_diff($array, array(1, 2, 3));
        }
        if (Input::get('nan')==='false')
        {
            $array = array_diff($array, array(4));
        }
        if (Input::get('bkk')==='false')
        {
            $array = array_diff($array, array(5));
        }

        $events = $events->whereIn('parties.id', function($q) use ($array){
            $q->select('party_id')
                ->from('lu_location_bases')
                ->whereIn('mflf_area_id', $array);
        });

        //filtering with personnels
        if (Input::get('personnels')!=="")
        {
            $personnels = Input::get('personnels');
            $events = $events->whereIn('parties.id', function($q) use ($personnels){
                $q->select('party_id')
                    ->from('lu_manager_assign')
                    ->whereIn('coordinator_assigned', $personnels);
            });
        }

        $events = $events
                    ->whereNotIn('parties.status', array('postpone', 'terminated', 'cancelled1', 'cancelled2'))
                    ->get();

        foreach($events as $event)
        {
            /*add a event color by criteria*/
            $case_doitung = array(
                array(1,2,3),
                array(1,2),
                array(2,3),
                array(1,3),
                array(1),
                array(2),
                array(3)
            );

            $case_nan = array(
                array(4)
            );

            $case_all = array(
                array(1,2,3,4),
                array(1,2,4),
                array(2,3,4),
                array(1,3,4),
                array(1,4),
                array(2,4),
                array(3,4)
            );

            $case_bkk = array(
                array(5)
            );

            $base_array = $event->getLocationBaseArrays();//get to compare base

            $event['color'] = '#2c272b';//default if cannot find any case

            /*case doitung, add primary style*/
            foreach($case_doitung as $doitung)
            {
                if (count($base_array)==count($doitung))
                {
                    if (count(array_diff($base_array,$doitung))==0)
                    {
                        $event['color'] = '#64352d';
                    }
                }
            }
            /*case nan, add success style*/
            foreach($case_nan as $nan)
            {
                if (count($base_array)==count($nan))
                {
                    if (count(array_diff($base_array,$nan))==0)
                    {
                        $event['color'] = '#58774e';
                    }
                }
            }
            /*case doitun+nan, add warning style*/
            foreach($case_all as $all)
            {
                if (count($base_array)==count($all))
                {
                    if (count(array_diff($base_array,$all))==0)
                    {
                        $event['color'] = '#da4980';
                    }
                }
            }
            /*case bkk, add info style*/
            foreach($case_bkk as $bkk)
            {
                if (count($base_array)==count($bkk))
                {
                    if (count(array_diff($base_array,$bkk))==0)
                    {
                        $event['color'] = '#289399';
                    }
                }
            }

            /*addition*/
            $event['project_coordinator'] = $event->assignedCoordinator(true);
        }

        return Response::json($events);
    }

    //return json lu customer event
    function getCalendarEvents()
    {
        //บวกวันที่ไปอีก 1 เดือนหัวท้าย
        $start_date = date_add(date_create(Input::get('start')),date_interval_create_from_date_string("-1 months"));
        $end_date = date_add(date_create(Input::get('end')),date_interval_create_from_date_string("+1 months"));

        $view = Input::get('view');

        $events = $this->party->getAllData();

        if ($view=='status')
        {
            $events = $events->select
            (
                'parties.id AS id',
                DB::raw('CASE WHEN (parties.customer_code!="") THEN CONCAT(parties.customer_code, " ", parties.name, " (", parties.people_quantity, ")") ELSE CONCAT(parties.name, " (", parties.people_quantity, ")") END AS title'),
                'parties.start_date AS start',
                DB::raw('DATE_ADD(parties.end_date, INTERVAL 1 DAY) AS end'),
                DB::raw('(
                    CASE parties.status
                    WHEN "reviewing" THEN "#2aaad1"
                    WHEN "reviewed" THEN "#2aaad1"
                    WHEN "pending" THEN "#2aaad1"
                    WHEN "terminated" THEN "#c12e2a"
                    WHEN "cancelled1" THEN "#c12e2a"
                    WHEN "cancelled2" THEN "#c12e2a"
                    ELSE "#245581"
                    END
                    )AS color')
            );
        }
        else
        {
            $events = $events->select
            (
                'parties.id AS id',
                DB::raw('CASE WHEN (parties.customer_code!="") THEN CONCAT(parties.customer_code, " ", parties.name, " (", parties.people_quantity, ")") ELSE CONCAT(parties.name, " (", parties.people_quantity, ")") END AS title'),
                'parties.start_date AS start',
                DB::raw('DATE_ADD(parties.end_date, INTERVAL 1 DAY) AS end')
            );
        }

        $events = $events->where('parties.start_date', '>=', date_format($start_date,"Y-m-d"))
                    ->where('parties.end_date', '<=', date_format($end_date,"Y-m-d"));

        $events = $events->whereIn('parties.status',array('reviewing','pending','reviewed','approved','preparing','ongoing','finished','finishing','terminated','cancelled1','cancelled2'));

        $events = $events->get();

        /*Re-Assign color when use location view*/
        if ($view=='location')
        {
            foreach($events as $event)
            {
                /*add a event color by criteria*/
                $case_doitung = array(
                    array(1,2,3),
                    array(1,2),
                    array(2,3),
                    array(1,3),
                    array(1),
                    array(2),
                    array(3)
                );

                $case_nan = array(
                    array(4)
                );

                $case_all = array(
                    array(1,2,3,4),
                    array(1,2,4),
                    array(2,3,4),
                    array(1,3,4),
                    array(1,4),
                    array(2,4),
                    array(3,4)
                );

                $case_bkk = array(
                    array(5)
                );

                $base_array = $event->getLocationBaseArrays();//get to compare base

                $event['color'] = '#2c272b';//default if cannot find any case : gray theme

                /*case doitung, add golden brown style*/
                foreach($case_doitung as $doitung)
                {
                    if (count($base_array)==count($doitung))
                    {
                        if (count(array_diff($base_array,$doitung))==0)
                        {
                            $event['color'] = '#64352d';
                        }
                    }
                }
                /*case nan, add green style*/
                foreach($case_nan as $nan)
                {
                    if (count($base_array)==count($nan))
                    {
                        if (count(array_diff($base_array,$nan))==0)
                        {
                            $event['color'] = '#58774e';
                        }
                    }
                }
                /*case doitun+nan, add pink style*/
                foreach($case_all as $all)
                {
                    if (count($base_array)==count($all))
                    {
                        if (count(array_diff($base_array,$all))==0)
                        {
                            $event['color'] = '#da4980';
                        }
                    }
                }
                /*case bkk, add info style*/
                foreach($case_bkk as $bkk)
                {
                    if (count($base_array)==count($bkk))
                    {
                        if (count(array_diff($base_array,$bkk))==0)
                        {
                            $event['color'] = '#289399';
                        }
                    }
                }
            }
        }

        $events = $events->toJson();

        return $events;
    }

    //return json list data
    public function getData()
    {
        //return passed manager process if manager or reviewer see all but project co see only your work
        //$parties = $this->party->getManagerPassed()
        $parties = $this->party
                    ->select('parties.id', 'parties.customer_code', 'parties.name', 'parties.party_type_id as type', 'parties.start_date', 'parties.end_date', 'parties.created_at', 'parties.country', 'parties.people_quantity as qty', 'parties.request_person_name', 'parties.request_person_tel', 'parties.request_person_email', 'parties.objective_detail', 'parties.request_code', 'parties.status', 'parties.interested', 'parties.expected', 'parties.joined', 'parties.paid_method', 'parties.related_budget_code')
                    ->orderBy('parties.created_at', 'DESC');

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

            ->edit_column('created_at', function($row){
                return ScheduleController::dateRangeStr($row->created_at, $row->created_at, true, false, 'th', true);
            })

            ->add_column('manager_name', function($row){

                $assigned = LuManagerAssign::where('party_id', '=', $row->id)->first();

                return ($assigned) ? User::find($assigned->created_by)->getShortName() : 'ยังไม่ระบุ';
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

            ->add_column('status', function($row){

                if ($row->status=='reviewing')
                {
                    $revision = $row->numberOfReviewing();
                }
                else if($row->status=='editing')
                {
                    $revision = $row->numberOfEditing();
                }
                else
                {
                    $revision = false;
                }

                $status = $row->statusThai($row->status, true, $revision);

                return $status;
            })

            ->remove_column('id', 'end_date')

            ->make(true);
    }

    //return json all list data
    public function getAllData()
    {
        $array = array();
        //return all data for reviewer manager vip
        $parties = $this->party->getAllData()
            ->select('parties.id', 'parties.customer_code AS customer_code',
                DB::raw('CONCAT(parties.name, "(", parties.people_quantity, ")") AS party_name'),
                'parties.start_date', 'parties.end_date', 'parties.status', 'parties.is_not_lu_manage');

               //filter dealing step
        if (Input::get('is_dealing')==='true')
        {
            array_push($array, 'reviewing', 'reviewed', 'approved');
        }
        //filter dealt or is customer step
        if (Input::get('is_dealt')==='true')
        {
            array_push($array, 'preparing', 'ongoing');
        }
        //filter finished
        if (Input::get('is_finish')==='true')
        {
            array_push($array, 'finishing', 'finished');
        }
        //filter postpone group
        if (Input::get('is_postpone')==='true')
        {
            array_push($array, 'postpone');
        }
        //filter all cancelled step
        if (Input::get('is_cancelled')==='true')
        {
            array_push($array, 'terminated', 'cancelled1', 'cancelled2');
        }

        $parties = $parties->whereIn('parties.status', $array);

        //filtering with personnels
        if (Input::get('personnels')!=="")
        {
            $personnels = Input::get('personnels');
            $parties = $parties->whereIn('parties.id', function($q) use ($personnels){
                $q->select('party_id')
                    ->from('lu_manager_assign')
                    ->whereIn('coordinator_assigned', $personnels);
            });
        }

        $parties = $parties->orderBy('parties.start_date', 'DESC');

        return Datatables::of($parties)

            ->edit_column('customer_code', function($row){
                return ($row->customer_code) ? $row->customer_code : 'ยังไม่ระบุ';
            })

            ->edit_column('start_date', function($row){
                return ScheduleController::dateRangeStr($row->start_date, $row->end_date, true, false, 'th');
            })

            ->edit_column('coordinator_name', function($row){

                return $row->assignedCoordinator(true);
            })
			
			->add_column('staffs', function($row){

                return $row->assignedOverallStaffs(true);
            })

            /*->edit_column('status', function($row){

                //get latest status
                return $row->latestStatus('th');
            })*/

            ->add_column('more_detail', function($row){
                return '<a class="btn btn-sm btn-default" href="' . URL::to('party/'.$row->id.'/view') . '" role="button"><i class="fa fa-hand-o-up"></i> คลิก</a>';
            })

            ->remove_column('id', 'end_date', 'is_not_lu_manage')

            ->make(true);
    }

    //return json list past party (history of party)
    public function getHistories()
    {
        //return passed manager process if manager or reviewer see all but project co see only your work
        $parties = $this->party->getAllHistory()
            ->select('parties.id', 'parties.customer_code', 'parties.name', 'parties.party_type_id as type', 'parties.people_quantity as qty', 'parties.start_date', 'parties.end_date');

        return Datatables::of($parties)

            ->edit_column('type', function($row){
                $type = DB::table('party_type')->where('id', '=', $row->type)->pluck('name');

                return ($type) ? $type : 'ไม่ได้ระบุ';
            })

            ->edit_column('start_date', function($row){
                return ScheduleController::dateRangeStr($row->start_date, $row->end_date, true, false, 'th');
            })

            ->add_column('actions', function($row){

                $html = '';

                if (!Auth::user()->hasRole('contributor'))
                {
                    $html .= '<a href="'. URL::to('party/'.$row->id.'/view') .'" target="_blank" class="btn btn-info btn-xs" ><span class="fa fa-eye"></span> Full View</a> ';
                }

                $html .= '<a onclick="openEdit(' . $row->id . ');" href="javascript:;" class="btn btn-default btn-xs" ><span class="fa fa-pencil-square-o"></span> แก้ไข</a>
                <a onclick="return openDelete(' . $row->id . ');" href="javascript:;" class="btn btn-xs btn-danger"><span class="fa fa-trash-o"></span> ลบ</a>';

                return $html;
            })

            ->remove_column('id', 'end_date')

            ->make();
    }

    /*get party transaction*/
    public function getTransaction()
    {
        $transactions = array();
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
            //get data status from party_statuses
            $party = Party::find(Input::get('party_id'));
            $transactions = $party->statuses()->orderBy('created_at', 'DESC')->get();

            $i = 0;
            foreach($transactions as $transaction)
            {
                if ($transaction->status=='reviewing')
                {
                    $revision = ($transaction->revision)+1;
                }
                else if($transaction->status=='editing')
                {
                    $revision = $transaction->revision;
                }
                else
                {
                    $revision = false;
                }

                $transactions[$i]['name'] = $party->statusThai($transaction->status, true, $revision);

                //check who was created a transaction.
                if ($transaction->status=='pending' || $transaction->status=='reviewing')
                {
                    $strWhoTransaction = $party->request_person_name.'<br>'.$party->request_person_email.'<br>'.$party->request_person_tel;
                }
                else
                {
                    if ($transaction->created_by==1)
                    {
                        $strWhoTransaction = "Systems Admin";
                    }
                    else
                    {
                        $strWhoTransaction = User::find($transaction->created_by)->getFullName();
                    }
                }

                $transactions[$i]['by'] = $strWhoTransaction;

                if ($transaction->status=='cancelled1' || $transaction->status=='cancelled2')
                {
                    $transactions[$i]['reason'] = 'เหตุผลที่ปฎิเสธ '.$transaction->note;
                }
                elseif ($transaction->status=='other')
                {
                    $transactions[$i]['reason'] = $transaction->other_email;
                }
                elseif ($transaction->status=='approved')
                {
                    $transactions[$i]['reason'] = 'ประสานงานโดย '.Personnel::find($transaction->coordinator)->fullName();
                }
                elseif ($transaction->status=='finished' && $transaction->contributor!=0)
                {
                    $transactions[$i]['reason'] = 'กรอกข้อมูลโดย '.Personnel::find($transaction->contributor)->fullName();
                }
                elseif ($transaction->status=='reviewing' && $transaction->revision==0)
                {
                    $transactions[$i]['reason'] = 'กำหนดการดูงาน '.ScheduleController::dateRangeStr($transaction->start_date, $transaction->end_date, true, false, 'th', true);
                }
                elseif ($transaction->status=='re-schedule')
                {
                    $transactions[$i]['reason'] = 'เหตุผลที่เลื่อน '.$transaction->note.' โดยเลื่อนกำหนดการเป็น '.ScheduleController::dateRangeStr($transaction->start_date, $transaction->end_date, true, false, 'th', true);
                }
                else
                {
                    $transactions[$i]['reason'] = ($transaction->note) ? $transaction->note : '';
                }
                $transactions[$i]['day'] = ScheduleController::dateRangeStr($transaction->created_at, $transaction->created_at, true, false, 'th', true);

                $i++;
            }

            $transactions = $transactions->toJson();
        }
        return $transactions;
    }

    /*get party sharepoint data*/
    public function getSharepoint()
    {
        $sharepoints = array();
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
            //get data status from party_statuses
            $party = Party::find(Input::get('party_id'));
            $sharepoints = $party->sharepoints()->orderBy('created_at', 'DESC')->get();
            $sharepoints = $sharepoints->toJson();
        }
        return $sharepoints;
    }
	
	/*get party overall works*/
    public function getStaffs()
    {
        $staffs = array();
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
            //get data status from party_statuses
            $party = Party::find(Input::get('party_id'));
            $staffs = $party->overallStaffs()
					->with('personnel')
					->with(['works' => function ($query) {
						$query->leftJoin('work_types', 'lu_overall_staff_works.work_type_id', '=', 'work_types.id')
								->orderBy('work_types.priority', 'ASC');
					}])
					->orderBy('created_at', 'DESC')
					->get();
            $staffs = $staffs->toJson();
        }
        return $staffs;
    }

    /*create or update sharepoint*/
    public function postCreateOrUpdateSharepoint()
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
            'type'   => 'required',
            'url'   => 'required|url',
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $sharepoint = (Input::get( 'id' )==0) ? new PartySharepoint : PartySharepoint::find(Input::get( 'id' ));
            $sharepoint->party_id = Input::get( 'party_id' );
            $sharepoint->title = Input::get( 'title' );
            $sharepoint->type = Input::get( 'type' );
            $sharepoint->url = Input::get( 'url' );
            if (Input::get( 'id' )==0)
            {
                $sharepoint->created_by =  Auth::user()->id;
                $sharepoint->updated_by =  Auth::user()->id;
            }
            else
            {
                $sharepoint->updated_by =  Auth::user()->id;
                $sharepoint->updated_at = date('Y-m-d H:i:s');
            }

            if($sharepoint->save())
            {
                $response = array(
                    'status' => 'success',
                    'msg' => 'บันทึก Archive Link สำเร็จแล้ว',
                    'party_id' => Input::get( 'party_id' )
                );

                return Response::json( $response );
            }
            $response = array(
                'status' => 'error',
                'msg' => 'ไม่สามารถบันทึก Archive Link ได้'
            );

            return Response::json( $response );
        }
        $response = array(
            'status' => 'error',
            'msg' => $validator->messages()
        );

        return Response::json( $response );
    }
	
	/*create or update overall staff*/
    public function postCreateOrUpdateStaff()
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
            'personnel_id'   => 'required',
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $staff = (Input::get( 'id' )==0) ? new LuOverallStaff : LuOverallStaff::find(Input::get( 'id' ));
            $staff->party_id = Input::get( 'party_id' );
            $staff->personnel_id = Input::get( 'personnel_id' );
			
            if (Input::get( 'id' )==0)
            {
                $staff->created_by =  Auth::user()->id;
                $staff->updated_by =  Auth::user()->id;
            }
            else
            {
                $staff->updated_by =  Auth::user()->id;
                $staff->updated_at = date('Y-m-d H:i:s');
            }
			
			//save personnel
			$staff->save();
			
			/*save array works*/
			$input_works = Input::get('works');
			$works = array();
			
			/*clear before save*/
			LuOverallStaffWork::where('overall_staff_id', '=', $staff->id)->delete();
			
			foreach($input_works as $input_work)
			{
				$work = new LuOverallStaffWork;
				$work->overall_staff_id = $staff->id;
				$work->work_type_id = $input_work;
				$work->created_by =  Auth::user()->id;
                $work->updated_by =  Auth::user()->id;
			
				array_push($works, $work);
			}

			//check save state
            if($staff->works()->saveMany($works))
            {
                $response = array(
                    'status' => 'success',
                    'msg' => 'บันทึกบุคลากรและภาระงานสำเร็จแล้ว',
                    'party_id' => Input::get( 'party_id' )
                );

                return Response::json( $response );
            }
            $response = array(
                'status' => 'error',
                'msg' => 'ไม่สามารถบันทึกบุคลากรและภาระงานได้'
            );

            return Response::json( $response );
        }
        $response = array(
            'status' => 'error',
            'msg' => $validator->messages()
        );

        return Response::json( $response );
    }

    /*delete sharepoint*/
    public function postDeleteSharepoint()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            'id' => 'required|integer',
            'party_id' => 'required|integer'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $sharepoint = PartySharepoint::find(Input::get( 'id' ));

            if($sharepoint->delete())
            {
                $response = array(
                    'status' => 'success',
                    'msg' => 'ลบ Archive Link สำเร็จแล้ว',
                    'party_id' => Input::get( 'party_id' )
                );

                return Response::json( $response );
            }
            $response = array(
                'status' => 'error',
                'msg' => 'ไม่สามารถลบ Archive Link ได้'
            );

            return Response::json( $response );
        }
        $response = array(
            'status' => 'error',
            'msg' => $validator->messages()
        );

        return Response::json( $response );
    }
	
	/*delete overall work*/
    public function postDeleteStaff()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            'id' => 'required|integer',
            'party_id' => 'required|integer'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $staff = LuOverallStaff::find(Input::get( 'id' ));

            if($staff->delete() && $staff->works()->delete())
            {
                $response = array(
                    'status' => 'success',
                    'msg' => 'ลบบุคลากรและภาระงานสำเร็จแล้ว',
                    'party_id' => Input::get( 'party_id' )
                );

                return Response::json( $response );
            }
            $response = array(
                'status' => 'error',
                'msg' => 'ไม่สามารถลบ Archive Link ได้'
            );

            return Response::json( $response );
        }
        $response = array(
            'status' => 'error',
            'msg' => $validator->messages()
        );

        return Response::json( $response );
    }

    /*Static Function Update status*/
    public static function updateStatus($party, $status, $note = NULL, $params = false)
    {
        if ($status!='re-schedule')
        {
            $party->status = $status;
        }

        $party_statuses = new PartyStatuses;
        $party_statuses->party_id = $party->id;
        $party_statuses->status = $status;
        $party_statuses->note = $note;
        $party_statuses->created_by = Auth::user()->id;
        $party_statuses->updated_by = Auth::user()->id;

        //also add extra parameter
        if ($params)
        {
            foreach(array_keys($params) as $key)
            {
                $party_statuses[$key] = $params[$key];
            }
        }

        //save and return
        if($party->save() && $party_statuses->save())
        {
            return true;
        }
        return false;
    }

    /*Function Return project co list*/
    function projectCoordinators()
    {
        return Personnel::leftJoin('personnel_users AS pu', 'personnels.id', '=', 'pu.personnel_id')
            ->leftJoin('users AS u', 'pu.user_id', '=', 'u.id')
            ->leftJoin('assigned_roles AS ar', 'u.id', '=', 'ar.user_id')
            ->whereIn('ar.role_id', array(2))
            ->get();
    }

    /*Function return document in of party*/
    static public function documents($party, $folder = "request")
    {
        $files = PartyUploadFiles::where('party_id', '=', $party->id)->whereFolder($folder)->get();

        return $files;
    }

    /*Function return folder of party*/
    static public function folders()
    {
        $folders = array(
            'request' => 'จดหมายคำร้อง',
            'schedule' => 'โปรแกรมดูงาน',
            'travel01' => 'ศทบ.01',
            'quotation' => 'ใบเสนอราคา ',
            'action_plan' => 'Action Plan',
            'assess' => 'แบบประเมิน',
            'other' => 'อื่นๆ',
            'report' => 'รายงานสรุป'
        );

        return $folders;
    }

    /*Function to save nation*/
    function setNations($local, $party, $countries)
    {
        if ($local=='th')
        {
            $nation = new PartyNations;
            $nation->party_id = $party->id;
            $nation->country = 'th';
            $nation->save();
        }
        else
        {
            $nations = array();

            if (count($countries)>0)
            {
                foreach($countries as $country)
                {
                    $nation = new PartyNations;
                    $nation->party_id = $party->id;
                    $nation->country = $country;

                    array_push($nations, $nation);
                }
                $party->countries()->saveMany($nations);
            }
            else
            {
                //if not selected set thailand
                $nation = new PartyNations;
                $nation->party_id = $party->id;
                $nation->country = 'th';
                $nation->save();
            }
        }
    }

    /*Truncate String*/
    function strTruncate($phrase, $max_words) {
        return (strlen($phrase) > $max_words) ? substr($phrase,0,($max_words-3)).'...' : $phrase;
    }

    /*Return file icon class*/
    static function strFileTypeClass($type)
    {
        $str = "fa fa-file";

        if ($type=='xls' || $type=='xlsx')
        {
            $str = "fa fa-file-excel-o";
        }

        if ($type=='doc' || $type=='docx')
        {
            $str = "fa fa-file-word-o";
        }

        if ($type=='ppt' || $type=='pptx')
        {
            $str = "fa fa-file-powerpoint-o";
        }

        if ($type=='pdf')
        {
            $str = "fa fa-file-pdf-o";
        }

        if ($type=='zip' || $type=='rar')
        {
            $str = "fa fa-file-archive-o";
        }

        if ($type=='png' || $type=='jpg' || $type=='jpeg' || $type=='gif' || $type=='bmp')
        {
            $str = "fa fa-file-image-o";
        }

        return $str;
    }

    /*Return join text*/
    static function strJoin($joined)
    {
        $str = "ไม่แน่ใจ";

        if ($joined=='never')
        {
            $str = "ครั้งแรก";
        }

        if ($joined=='ever')
        {
            $str = "เคยมาเข้าร่วมแล้ว";
        }

        return $str;
    }

    /*Return Paid Method Text*/
    static function strPaidMethod($method, $related_code = NULL)
    {
        $paid_method = FinancialController::model($method, $related_code);

        return $paid_method;
    }

}