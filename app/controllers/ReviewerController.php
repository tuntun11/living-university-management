<?php

class ReviewerController extends BaseController {

    protected $party;

    protected $party_statuses;

    protected $assigned_roles;

    public function __construct(Party $party, PartyStatuses $party_statuses, AssignedRoles $assigned_roles)
    {
        parent::__construct();
        $this->party = $party;
        $this->party_statuses = $party_statuses;
        $this->assigned_roles = $assigned_roles;
    }

    //return index page
    public function getIndex()
    {
        return View::make('svms/reviewer/index');
    }

    //return review a view
    public function getReview($party)
    {
        //Check Review Task Count if not have return zero
        $countReview = ReviewerController::reviewerTask()->count();
        //check status can review state
        $canReview = $party->canReviewAndApproval();
        //Return data of reviewing
        $reviewings = ReviewerController::reviewerTask()->orderBy('request_code', 'ASC')->get();
        $party = $party->fullData();
        $party['nationals'] = $party->getNationArrays(true);

        return View::make('svms/reviewer/review_and_approval', compact('countReview', 'canReview', 'reviewings', 'party'));
    }

    //post transaction of Accept
    public function postReviewerAccept()
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

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            // Start transaction!
            DB::beginTransaction();

            try
            {
                $party = Party::find(Input::get('party_id'));

                //check if party have already review alert ;sleep 2 second
                //sleep(2);
                $party->status = 'reviewed';

                //keep party status log
                $this->party_statuses->party_id = Input::get('party_id');
                $this->party_statuses->status = 'reviewed';
                $this->party_statuses->note = Input::get('note');
                $this->party_statuses->created_by = Auth::user()->id;
                $this->party_statuses->updated_by = Auth::user()->id;

                if ($party->save() && $this->party_statuses->save())
                {
                    /*-Send Email To Manager and Request-*/
                    $party = Party::find(Input::get('party_id'));
                    /*if no party data abort*/
                    if(!$party)
                    {
                        throw new Exception("Error คณะนี้ได้ถูกลบไปแล้ว");
                    }

                    $data = $party->fullData();
                    //get Reviewer Name
                    $data['reviewer_name'] = Auth::user()->getFullName();
                    //comment extends
                    $data['note'] = Input::get('note');
                    //get manager and admin mail
                    $data['mails'] = $this->assigned_roles->getMailsByRole(array('manager'));

                    //ในกรณีที่ reviewer แก้ไขผลการทำงาน เมื่อเลือก ศทบไปแล้วกลับใจส่งให้ฝ่ายพัฒนารับคณะเอง ต้องส่งเมลแจ้งเตือนกลับศทบ.ด้วยว่าไมต้องเตรียมการแล้ว่
                    //1 ค้นหา latest review
                    $latest_reviewed = PartyStatuses::where('party_id', '=', $party->id)
                        ->where('status', '=', 'other')
                        ->orderBy('created_at', 'DESC')
                        ->first();

                    $data['other_mails'] = array();

                    if ($latest_reviewed)
                    {
                        $other_mail_lists = array();
                        $other_mails = explode(',', $latest_reviewed->other_email);

                        if (count($other_mails)>1)
                        {
                            foreach($other_mails as $other_mail)
                            {
                                array_push($other_mail_lists, $other_mail);
                            }
                        }
                        else
                        {
                            array_push($other_mail_lists, $latest_reviewed->other_email);
                        }

                        //also add request person
                        array_push($other_mail_lists, $data->request_person_email);
                        //also add lu_team
                        array_push($other_mail_lists, 'lu_team@doitung.org');
                        //assign mail list
                        $data['other_mails'] = $other_mail_lists;
                    }

                    Mail::send('emails.transaction.reviewer.accept', compact('data'), function($message) use ($data)
                    {
                        $reviewerAcceptMailTitle = 'LU : '.$data->name.'ผ่านการอนุมัติรับคณะแล้ว';

                        if (count($data->other_mails)>0)
                        {
                            $message
                                ->to($data->mails)
                                ->cc($data->other_mails)
                                ->subject($reviewerAcceptMailTitle);
                        }
                        else
                        {
                            $message
                                ->to($data->mails)
                                ->cc($data->request_person_email)
                                ->subject($reviewerAcceptMailTitle);
                        }

                    });

                    if(count(Mail::failures()) > 0)
                    {
                        throw new Exception("ไม่สามารถทำการอนุมัติได้เนื่องจากส่งเมลไม่สำเร็จ");
                    }
                    else
                    {
                        //Request Commit and send response
                        DB::commit();
                        //response success when data save and mail sent
                        $response = array(
                            'status' => 'success',
                            'tasks' => Auth::user()->getCountTasks(),
                            'msg' => 'ทำการตรวจสอบสำเร็จแล้ว'
                        );

                        return Response::json( $response );
                    }
                }

                //response error
                throw new Exception("Error ทำการตรวจสอบไม่สำเร็จ กรุณาแจ้ง Admin");
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

    //post transaction return back for get more info
    public function postReviewerReturn()
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

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            // Start transaction!
            DB::beginTransaction();

            try
            {
                $party = Party::find(Input::get('party_id'));

                //check if party have already review alert ;sleep 2 second
                sleep(2);
                $party->status = 'editing';//Set editing for request more info.

                //set a revision
                $revision_number = ($party->numberOfEditing())+1;
                //keep party status log
                $this->party_statuses->party_id = Input::get('party_id');
                $this->party_statuses->status = 'editing';
                $this->party_statuses->note = Input::get('note');
                //set revision by query latest + 1
                $this->party_statuses->revision = $revision_number;
                $this->party_statuses->created_by = Auth::user()->id;
                $this->party_statuses->updated_by = Auth::user()->id;

                if ($party->save() && $this->party_statuses->save())
                {
                    /*-Send Email back to Requester-*/
                    $party = Party::find(Input::get('party_id'));
                    /*if no party data abort*/
                    if(!$party)
                    {
                        throw new Exception("Error คณะนี้ได้ถูกลบไปแล้ว");
                    }

                    $data = $party->fullData();
                    //get Reviewer Name
                    $data['reviewer_name'] = Auth::user()->getFullName();
                    //comment extends
                    $data['note'] = Input::get('note');
                    //encrypt party id
                    $data['encrypt'] = Crypt::encrypt($party->id);
                    //$data['mails'] = 'lu_team@doitung.org';//cc to lu_team
                    $data['number_next_editing'] = $revision_number;

                    //Get email list from reviewer list
                    $data['mails'] = $this->assigned_roles->getMailsByRole(array('reviewer'));

                    //Get all note paste on history
                    if ($revision_number==1)
                    {
                        $data['histories'] = array();
                    }
                    else
                    {
                        $data['histories'] = $party->editNoteHistories($revision_number);
                    }

                    Mail::send('emails.transaction.reviewer.return', compact('data'), function($message) use ($data)
                    {
                        $message
                            ->to($data->request_person_email)
                            ->cc($data->mails)
                            ->subject('LU : ขอข้อมูลเพิ่มเติมเพื่อประกอบการตัดสินใจ ครั้งที่ '.$data->number_next_editing.' ('.$data->name.')');
                    });

                    if(count(Mail::failures()) > 0)
                    {
                        throw new Exception("ไม่สามารถทำการส่งคืนได้เนื่องจากส่งเมลไม่สำเร็จ");
                    }
                    else
                    {
                        //Request Commit and send response
                        DB::commit();
                        //response success
                        $response = array(
                            'status' => 'success',
                            'tasks' => Auth::user()->getCountTasks(),
                            'msg' => 'ทำการส่งคืนสำเร็จแล้ว'
                        );

                        return Response::json( $response );
                    }
                }

                //response error
                throw new Exception("Error ทำการตรวจสอบไม่สำเร็จ กรุณาแจ้ง Admin");
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

    //post transaction of Cancel or Reject
    public function postReviewerCancel()
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
            // Start transaction!
            DB::beginTransaction();

            try
            {
                $party = Party::find(Input::get('party_id'));
                $party->status = 'cancelled1';

                //check if party have already review alert ;sleep 2 second
                //sleep(2);

                //keep party status log
                $this->party_statuses->party_id = Input::get('party_id');
                $this->party_statuses->status = 'cancelled1';
                $this->party_statuses->note = Input::get('note');
                $this->party_statuses->created_by = Auth::user()->id;
                $this->party_statuses->updated_by = Auth::user()->id;

                if ($party->save() && $this->party_statuses->save())
                {
                    //send mail response to request person
                    $party = Party::find(Input::get('party_id'));
                    /*if no party data abort*/
                    if(!$party)
                    {
                        throw new Exception("Error คณะนี้ได้ถูกลบไปแล้ว");
                    }

                    $data = $party->fullData();

                    //get Reviewer Name
                    $data['reviewer_name'] = Auth::user()->getFullName();
                    $data['cancel_reason'] = Input::get('note');

                    //also cc lu_team
                    $data['cc_mails'] = 'lu_team@doitung.org';

                    Mail::send('emails.transaction.reviewer.cancel', compact('data'), function($message) use ($data)
                    {
                        $message
                            ->to($data->request_person_email)
                            //also cc lu team
                            ->cc($data->cc_mails)
                            ->subject('LU : '.$data->name.'ได้ถูกปฎิเสธการรับคณะ');
                    });

                    if(count(Mail::failures()) > 0)
                    {
                        throw new Exception("ไม่สามารถทำการปฎิเสธคำร้องได้เนื่องจากส่งเมลไม่สำเร็จ");
                    }
                    else
                    {
                        //Request Commit and send response
                        DB::commit();
                        //response success
                        $response = array(
                            'status' => 'success',
                            'tasks' => Auth::user()->getCountTasks(),
                            'msg' => 'ทำการปฎิเสธคำร้องสำเร็จแล้ว'
                        );

                        return Response::json( $response );
                    }
                }

                //response error
                throw new Exception("Error ทำการปฎิเสธคำร้องไม่สำเร็จ กรุณาแจ้ง Admin");
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

    //post transaction of Delete
    public function postReviewerDelete()
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
            // Start transaction!
            DB::beginTransaction();

            try
            {
                $party = Party::find(Input::get('party_id'));
                //Keep data to send mail
                $data = $party->fullData();

                if ($party->delete())
                {
                    //Request Commit and send response
                    DB::commit();

                    $strTextReturn = "ทำการลบ Cache สำเร็จแล้ว";
                    //first check if delete cache or incomplete data not send a mail
                    if (Input::get('is_cache')!=1)
                    {
                        //send mail response to request person when it's not clear cache.
                        //get Reviewer Name
                        $data['reviewer_name'] = Auth::user()->getFullName();
                        $data['delete_reason'] = Input::get('note');

                        //also cc lu_team
                        $data['cc_mails'] = 'lu_team@doitung.org';

                        Mail::send('emails.transaction.reviewer.delete', compact('data'), function($message) use ($data)
                        {
                            $message
                                ->to($data->request_person_email)
                                ->cc($data->cc_mails)
                                ->subject('LU : '.$data->name.'ได้ถูกลบข้อมูลออกจากระบบ');
                        });

                        $strTextReturn = "ทำการลบข้อมูลสำเร็จแล้ว";

                        if(count(Mail::failures()) > 0)
                        {
                            throw new Exception("ไม่สามารถทำการลบคำร้องได้เนื่องจากส่งเมลไม่สำเร็จ");
                        }
                    }

                    //response success
                    $response = array(
                        'status' => 'success',
                        'tasks' => Auth::user()->getCountTasks(),
                        'msg' => $strTextReturn
                    );

                    return Response::json( $response );
                }

                //response error
                throw new Exception("Error ทำการลบคำร้องไม่สำเร็จ กรุณาแจ้ง Admin");
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

    //return reviewer count backlog
    public static function countTask()
    {
        return self::reviewerTask()->count();
    }

    //return data task
    public function getData()
    {
        $parties = self::reviewerTask(Input::get('is_history'), Input::get('is_all'));
        $parties = $parties->orderBy('parties.request_code', 'desc');

        return Datatables::of($parties)

            ->edit_column('name', function($row){

                $strShowNameAndButton = $row->name;

                $strShowNameAndButton .= '<div class="clearfix"></div>';
                $strShowNameAndButton .= '<div class="pull-right">';

                if ($row->status=='pending')
                {
                    //button edit and re-send for dev-admin only
                    if (Auth::user()->is_developer)
                    {
                        $strShowNameAndButton .= '<button id="btnForceRequest'.$row->id.'" class="btn btn-xs btn-default" data-loading-text="กำลังส่งข้อมูล..." party="'.$row->id.'" type="button" onclick="forceSend(\''.Crypt::encrypt($row->id).'\','.$row->id.');">Force Send</button>';
                        $strShowNameAndButton .= ' <a class="btn btn-xs btn-info" href="'.URL::to('party/'.$row->id.'/view').'" role="button"><i class="fa fa-pencil" aria-hidden="true"></i> ดู/แก้ไข</a>';
                    }
                    //check date over 1 night can delete
                    $strShowNameAndButton .= ' <button id="btnDelRequestNotComplete'.$row->id.'" class="btn btn-xs btn-danger" data-loading-text="กำลังลบ..." party="'.$row->id.'" type="button" onclick="deleteCache('.$row->id.');"><i class="fa fa-trash-o" aria-hidden="true"></i> ลบข้อมูล</button>';
                }
                else
                {
                    $strShowNameAndButton .= '<a class="btn btn-xs btn-info" href="'.URL::to('party/'.$row->id.'/view').'" role="button"><i class="fa fa-pencil" aria-hidden="true"></i> ดู/แก้ไข</a>';
                }

                if ($row->status=='reviewing')
                {
                    $strShowNameAndButton .= ' <a class="btn btn-xs btn-default" href="'.URL::to('reviewer/'.$row->id.'/review').'" role="button"><i class="fa fa-thumbs-up" aria-hidden="true"></i> ส่งผลตรวจสอบ</a>';
                }

                $strShowNameAndButton .= '</div>';

                return $strShowNameAndButton;
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
                return ScheduleController::dateRangeStr($row->created_at, $row->created_at, true, false, 'th', true, true);
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

            ->add_column('actioned', function($row){

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

    static function reviewerTask($is_history = 0, $is_all = 0)
    {
        $parties = Party::select('parties.id', 'parties.request_code', 'parties.name', 'parties.party_type_id as type', 'parties.start_date', 'parties.end_date', 'parties.created_at', 'parties.country', 'parties.people_quantity as qty', 'parties.request_person_name', 'parties.request_person_tel', 'parties.request_person_email', 'parties.objective_detail', 'parties.status', 'parties.interested', 'parties.expected', 'parties.joined', 'parties.paid_method', 'parties.related_budget_code');
        if ($is_history==1)
        {
            //this is history task
            $parties = $parties->whereNotIn('parties.status',array('reviewing','editing','pending')) //reviewing, editing and pending = reviewer can review
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
            $parties = $parties->whereIn('status', array('reviewing', 'editing', 'pending'));
        }

        return $parties;
    }

}