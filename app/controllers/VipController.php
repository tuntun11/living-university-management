<?php

class VipController extends BaseController {

    protected $party;
	
    protected $party_send_administrators;

    public function __construct(Party $party, PartySendAdministrators $party_send_administrators)
    {
        parent::__construct();
        $this->party = $party;
        $this->party_send_administrators = $party_send_administrators;
    }

    //return index page
    public function getIndex()
    {
        return View::make('svms/vip/index');
    }
	
	//return send to administrator page
    public function getSendToAdministrator()
    {
		//Administrator Data
		$administrators = Personnel::whereIsAdministrator(1)->orderBy('priority', 'ASC')->get();
		
        return View::make('svms/vip/send_to_administrator', compact('administrators'));
    }
	
	//post send to administrator transaction
    public function postSendToAdministrator()
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
			//get party data to send
            $party = Party::find(Input::get('party_id'));

            //keep party send to admin log
            $this->party_send_administrators->party_id = Input::get('party_id');
            $this->party_send_administrators->send_body = Input::get('send_body');
            $this->party_send_administrators->created_by = Auth::user()->id;
            $this->party_send_administrators->updated_by = Auth::user()->id;
			
			//loop administrators data to create text to keep
			$send_administrators = Input::get('persons');
			$send_mail = "";
			$send_person = "";
			$send_administrator_list = "";//attend in mail
			$send_administrator_mail = array();//send mails to admin
			
			$a = 1;
			foreach($send_administrators as $send_administrator)
			{
				//find from personnel data
				$administrator = Personnel::find($send_administrator);
				//if found admin keep it. 
				if ($administrator)
				{
					$send_mail .= $administrator->email.'|';
					array_push($send_administrator_mail, $administrator->email);
					
					//if found code name keep it but not found keep fullname.
					if ($administrator->codename)
					{
						$send_person .= $administrator->codename.'|';
						if (count($send_administrators)>1 && $a==count($send_administrators))
						{
							$send_administrator_list .= ' และ'.$administrator->codename.', ';
						}
						else
						{
							$send_administrator_list .= $administrator->codename.', ';
						}
					}
					else
					{
						$send_person .= $administrator->fullName().'|';
						if (count($send_administrators)>1 && $a==count($send_administrators))
						{
							$send_administrator_list .= ' และ'.$administrator->fullName().', ';
						}
						else
						{
							$send_administrator_list .= $administrator->fullName().', ';
						}
					}
					$a++;
				}
			}
			//keep rest of data
			$this->party_send_administrators->send_mail = substr($send_mail, 0, -1);
            $this->party_send_administrators->send_person = substr($send_person, 0, -1);
			
			$send_administrator_list = substr($send_administrator_list, 0, -2);
			
			//Check save transaction success
            if ($this->party_send_administrators->save())
            {
				//if party disappear 
                if(!$party)
                {
                    $response = array(
                        'status' => 'error',
                        'msg' => 'Error คณะนี้ได้ถูกลบไปแล้ว'
                    );

                    return Response::json( $response );
                }

                $data = $party->fullData();
                //get data to report administrators
                $data['sender_name'] = Auth::user()->getOnlyName();
				$data['send_body'] = Input::get('send_body');
				$data['send_administrator_list'] = $send_administrator_list;
				$data['send_status'] = $party->latestStatus('th');
				//if have assigned project co report then
				if ($party->assignedCoordinator()=='ยังไม่ได้ระบุผู้ประสานงาน')
				{
					$data['send_projectco'] = 0;
				}
				else
				{
					$data['send_projectco'] = $party->assignedCoordinator();
				}
                //add input mail to this
                $data['mails'] = $send_administrator_mail;

                Mail::send('emails.transaction.send_administrators', compact('data'), function($message) use ($data)
                { 
                        $message
							//->from(Auth::user()->email, 'Test')
                            ->to($data->mails)
                            ->cc(Auth::user()->email)//cc to sender
                            ->subject('LU : ขอนำเรียนข้อมูลคณะดูงาน "'.$data->name.'" เพื่อโปรดพิจารณา');
                });

                //response success
                $response = array(
                    'status' => 'success',
                    'msg' => 'ทำการแจ้งผู้บริหารสำเร็จแล้ว'
                );

                return Response::json( $response );
            }

            //response error
            $response = array(
                'status' => 'error',
                'msg' => 'Error ทำการแจ้งผู้บริหารไม่สำเร็จ กรุณาติดต่อ Admin',
                'error' => $this->party_send_administrators->error()
            );

            return Response::json( $response );
        }
    }

    //return data task
    public function getSendAdministratorData()
    {
        $parties = self::sendAdministratorTask(Input::get('is_history'), Input::get('is_all'));
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

            ->edit_column('created_at', function($row){
                return ScheduleController::dateRangeStr($row->created_at, $row->created_at, true, false, 'th', true);
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
				
				//create control button
				$party_description = str_replace(array('"', '/'), "", $row->request_code.' '.$row->name.' ('.$row->people_quantity.')');
                $actions = "";

			    if ($row->sendToAdministrators->count()==0)
				{
					$actions = '<a onclick="openSendAdmin('.$row->id.', 1);" title="'.$party_description.'" href="javascript:;" class="btn btn-default btn-xs" ><i class="fa fa-envelope"></i> ส่งข้อมูลคณะแก่ผู้บริหาร</a>';
				}
				else
				{
					$actions = '<a onclick="openSendAdmin('.$row->id.', 0);" title="'.$party_description.'" href="javascript:;" class="btn btn-default btn-xs" ><i class="fa fa-envelope"></i> ส่งข้อมูลอีกครั้ง</a>';
				}
          
                return $actions;
            })

            ->add_column('actioned', function($row){

                //create control button
                $actions = "";

                
                return $actions;
            })

            ->remove_column('id', 'end_date')

            ->make(true);
    }
	
	//return data query
	static function sendAdministratorTask($is_history = 0, $is_all = 0)
    {
        $parties = Party::select('parties.id', 'parties.request_code', 'parties.name', 'parties.party_type_id as type', 'parties.start_date', 'parties.end_date', 'parties.created_at', 'parties.country', 'parties.people_quantity as qty', 'parties.request_person_name', 'parties.request_person_tel', 'parties.request_person_email', 'parties.objective_detail', 'parties.status', 'parties.interested', 'parties.expected', 'parties.joined', 'parties.paid_method' , 'parties.related_budget_code');
        if ($is_history==1)
        {
            //this is history task
            $parties = $parties
						->whereIn('parties.id', function($q) use ($is_all){
						
							//if all select all
							if ($is_all==1)
							{
								 $q->from('party_send_administrators AS psa')
                                    ->select('psa.party_id')
                                    ->orderBy('psa.created_at', 'DESC');
							}		
							else
							{
								//else not all select specific for your account
								$q->from('party_send_administrators AS psa')
                                    ->select('psa.party_id')
									->where('created_by', '=', Auth::user()->id)
                                    ->orderBy('psa.created_at', 'DESC');
							}
				
			            });						
        }
        else
        {
            //this is current task
			$parties = $parties
						->whereNotIn('parties.id', function($q){
				
							$q->from('party_send_administrators AS psa')
                                    ->select('psa.party_id')
                                    ->orderBy('psa.created_at', 'DESC');
				
			            })
						->whereNotIn('parties.status', array('cancelled1', 'cancelled2', 'terminated', 'other'));
        }

        return $parties;
    }

}