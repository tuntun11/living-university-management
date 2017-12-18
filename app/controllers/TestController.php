<?php

class TestController extends BaseController {

    //return index page
    public function getIndex()
    {
        $party = Party::find(1);

        $dateStart = date_create($party->start_date);
        $dateEnd = date_create($party->end_date);
        $diff = date_diff($dateStart,$dateEnd);
        $period = intval($diff->format("%d"));

        return dd($period);

        /*$password = Hash::make('106996');

        return $password;*/
        /*if (Auth::user()->canFastTrack())
        {
            $result = "Yes";
        }
        else
        {
            $result = "No";
        }

        return $result;*/

        //update value in schema
        /*$u = DB::statement("ALTER TABLE party_statuses CHANGE COLUMN status status ENUM('pending','editing','reviewing','reviewed','approved','preparing','ongoing','finished','finishing','re-schedule','postpone','terminated','cancelled1','cancelled2','other') DEFAULT 'pending'");

        if($u)
        {
            return "Success";
        }
        return "Error";*/
    }

    public function getTestUpdateStatuses()
    {
        //update date
        $ts = PartyStatuses::where('status', '=', 'reviewing')->get();

        foreach($ts as $t)
        {
            $p = PartyStatuses::find($t->id);

            $party = Party::find($p->party_id);

            if ($party)
            {
                $p->start_date = $party->start_date;
                $p->end_date = $party->end_date;
                $p->save();
            }
            
        }

        return json_encode($ts);
    }

    public function getTestScriptUpdate()
    {
        //use for update start and end in history
        $histories = PartyStatuses::where('status','=','reviewing')->get();

        $strUpdate = "";
        foreach($histories as $history)
        {
            $update = PartyStatuses::find($history->id);

            if ($update)
            {
                $party = Party::find($update->party_id);

                if ($party)
                {
                    $update->start_date = $party->start_date;
                    $update->end_date = $party->end_date;

                    if ($update->save())
                    {
                        $strUpdate.="Success <br/>";
                    }
                    else
                    {
                        $strUpdate.="Error <br/>";
                    }
                }
                else
                {
                    $strUpdate.="Error <br/>";
                }
            }
            $strUpdate.="Error <br/>";
        }

        return $strUpdate;
    }

    public function getTestConvertDate()
    {
        $t = LuScheduleTask::where('lu_schedule_id', '=', 240)
            ->whereRaw('DATE(start) = "2017-03-28"')
            ->where('type', '=', 'A')
            ->update([
                'start' => DB::raw('CONCAT("2017-02-23 ",TIME(start))'),
                'end' => DB::raw('CONCAT("2017-02-23 ",TIME(end))')
            ]);

        return dd($t);
    }

    public function getTestPeriod()
    {
        $party = Party::find(712);

        $period = count(ScheduleController::dateRangeArray($party->start_date,$party->end_date));

        return $period;
    }

    public function getTestUpdateBudgetDate()
    {
        $budgets = LuBudgetDetail::all();

        foreach ($budgets as $budget)
        {
            //$task = Lu
        }
    }

    public function getTestTask()
    {
        $party = Party::find(712);

        $schedule = $party->schedules()->with('tasks')->get();

        $strReturn = "";

        foreach($schedule['tasks'] as $task)
        {
            $strReturn .= $task->id." ";
        }

        return $strReturn;
    }

    public function getTestActions()
    {

    }

    public function getTestSendReturn()
    {
        $arrays = array(757);
        $urls = "";

        foreach($arrays as $array)
        {
            $enc = Crypt::encrypt($array);
            $urls.=URL::to("party/".$enc."/editing/editByRequest")."<br/>";
        }

        return $urls;
    }

    public function getTestFunction()
    {
        $count = ScheduleController::countScheduleLanguage(277, 'th');

        return $count;
    }

    public function getSuccess()
    {
        $party = Party::find(749);
        $status = $party->statuses()->orderBy('created_at', 'desc')->pluck('note');

        return dd($status);
    }

    public function getParty()
    {
        $party = Party::find(645);

        $notes = $party->editNoteHistories();

        $html = '';

        $num = 1;
        foreach($notes as $note)
        {
            $html .= '<p>';
                $html .= '<h5>ครั้งที่ '.$num.' เมื่อ '.ScheduleController::dateRangeStr($note->created_at, $note->created_at, true).'</h5>';
                $html .= '<div>';
                $html .= ($note->note) ? $note->note : '[ไม่มีข้อมูล]';
                $html .= '</div>';
            $html .= '</p>';

            $html .= '<hr/>';

            $num++;
        }

        return $html;
    }

    //return environment
    public function getDebugEnv()
    {
        $environment = App::environment();

        return $environment;
    }

    //test attach a file
    public function getMail()
    {
        $data = array();

        Mail::send('emails.test', $data, function($message) use ($data)
        {
            $message->to('wuttichai@doitung.org');
        });

        if(count(Mail::failures()) > 0)
        {
            return 'Test Mail Fail';
        }

        return 'Test Mail Pass';
    }

    public function getCheckLostMoney()
    {
        $arrays = array();
        $html = "<h1>คณะที่คาดว่างบประมาณน่าจะหาย</h1>";
        $parties = Party::all();
        $i = 0;
        foreach ($parties as $party)
        {
            if ($party->budgetingPassed())
            {
                //วิธีการคือ เอาจำนวนเงินรวม ใน Lu Budget กับ Lu Budget Detail เทียบกันหากไม่ตรงกันแสดงว่าอาจเกิดจากการที่เงินหาย
                $latest_total_in_detail = LuBudgetDetail::where('party_id', '=', $party->id)->pluck(DB::raw('SUM(sale_price*qty)'));
                $latest_total_in_budget = LuBudget::where('party_id', '=', $party->id)->pluck('grand_total_a');

                if ($latest_total_in_detail <> $latest_total_in_budget)
                {
                    array_push($arrays, $party);
                    $html.=  $party->customer_code.' '.$party->name.'<br/>';
                    $i++;
                }
            }
        }

        $html.= '<br/><br/> จำนวนทั้งสิ้น '.$i.' คณะ';

        return $html;
    }

    public function getCustomerCode()
    {
        //$test_department = 2;//CSE A
        $test_department = 3;//KLC B
        //$test_department = 4;//PR C
        //$test_department = 213;//D
        //$test_department = 165;//E
        //$test_department = 215;//F

        $party = new Party;

        return $party->getCustomerCode($test_department);
    }

    public function getRequestCode()
    {
        $party = new Party;

        $allCode = array();

        for ($i=1; $i<=10 ; $i++)
        {
            $code = 1;

            array_push($allCode, $code);

            $code++;

            sleep(1);// delay execute for 1 second
        }

        return dd($allCode);
    }

    public function getReportUseProduct()
    {
        $parties = Party::where('start_date', '>=', '2016-01-01')
            ->where('end_date', '<=', '2016-12-31')
            ->with(array('expenses' => function($q){
                $q->where('expense_type', '=', 'tools')
                  ->where('expense_id', '=', 13);//กระเป๋า
            }))
            ->get();

        $sumParty = 0;
        $sumPeople = 0;

        foreach($parties as $party)
        {
            if (count($party->expenses)>0)
            {
                echo $party->customer_code." ".$party->name."<br/>";

                $sumParty++;
                $sumPeople += $party->people_quantity;
            }
        }
        echo "<br/><br/>";
        echo "รวมจำนวนคณะ : ".$sumParty." คณะ";
        echo "<br/>";
        echo "รวมจำนวนคนดูงาน : ".$sumPeople." ท่าน";
    }

    public function getRightFolder()
    {
        //this method for create upload data
        $categories = array(
            'request', 'schedule', 'travel01', 'quotation', 'action_plan', 'assess', 'other', 'report'
        );
        $num = 0;
        foreach($categories as $category)
        {
            $path_upload_files = public_path().'/svms/'.$category;

            if (file_exists($path_upload_files))
            {
                $upload_files = File::allFiles($path_upload_files);
                if (count($upload_files)>0)
                {
                    foreach($upload_files as $upload_file)
                    {
                        //set param category
                        $newCategoryFolder = $category;
                        //set param folder
                        $newFolder = $upload_file->getRelativePath();
                        //set param url
                        $newUrl = asset('svms/'.$newCategoryFolder.'/'.$newFolder.'/'.$upload_file->getFileName());

                        //query party upload files to check
                        $party_upload_file = PartyUploadFiles::where('path', '=', $upload_file->getPathName())->first();
                        //check if pathName of folder == path in db then update
                        //else insert new
                        if ($party_upload_file)
                        {
                            //echo 'UPDATE party_upload_files SET name="'.$upload_file->getFileName().'", type="'.$upload_file->getExtension().'", url="'.$newUrl.'", folder="'.$newCategoryFolder.'" WHERE path="'.$upload_file->getPathName().'";';
                            //echo '<br/>';
                        }
                        else
                        {
                            //find party id if insert new
                            $newPartyId = Party::where('request_code', '=', $newFolder)->pluck('id');
                            $newPartyId = ($newPartyId) ? $newPartyId : Party::where('customer_code', '=', $newFolder)->pluck('id');

                            $newDay = date('Y-m-d H:i:s');

                            if ($newPartyId)
                            {
                                echo 'INSERT INTO party_upload_files (party_id, name, url, path, type, folder, created_by, updated_by, created_at, updated_at) VALUES ('.$newPartyId.', "'.$upload_file->getFileName().'", "'.$newUrl.'", "'.$upload_file->getPathName().'", "'.$upload_file->getExtension().'", "'.$newCategoryFolder.'", 1, 1, "'.$newDay.'", "'.$newDay.'");';
                                echo '<br/>';
                            }
                        }
                        $num++;
                    }
                }
            }
        }
        //echo $num;
        //also delete cannot open file in other method
        //end function or method
    }

    public function getRightOpen()
    {
        $party = Party::find(483);

        echo dd(PartyController::documents($party, 'request'));

        /*$party_upload_files = PartyUploadFiles::all();

        foreach($party_upload_files as $file)
        {
            if(!file_exists())
            {

            }
        }*/
    }

    //return user group list 
    public function getUserGroup()
    {
        $personnels = Personnel::all();

        $html = "";
        $html.= "<table border='1'>";
            $html.= "<tr>";
                $html.= "<th>Name</td>";
                $html.= "<th>Email</td>";
                $html.= "<th>เป็นวิทยากร</td>";
                $html.= "<th>มี user เข้าระบบ</td>";
                $html.= "<th>vip user</td>";
                $html.= "<th>reviewer user</td>";
                $html.= "<th>manager user</td>";
                $html.= "<th>project co user</td>";
            $html.= "</tr>";

            foreach($personnels as $personnel)
            {
                $is_expert = ($personnel->is_expert) ? "Yes" : "No";
                $personnel_user = DB::table('personnel_users')->where('personnel_id', '=', $personnel->id)->pluck('user_id');
                $vip = DB::table('assigned_roles')->where('user_id', '=', $personnel_user)->where('role_id', '=', 5)->get();
                $reviewer = DB::table('assigned_roles')->where('user_id', '=', $personnel_user)->where('role_id', '=', 4)->get();
                $manager = DB::table('assigned_roles')->where('user_id', '=', $personnel_user)->where('role_id', '=', 3)->get();
                $projectco = DB::table('assigned_roles')->where('user_id', '=', $personnel_user)->where('role_id', '=', 2)->get();

                $is_user = ($personnel_user) ? "Yes" : "No";
                $is_vip = ($vip) ? "Yes" : "No";
                $is_reviewer = ($reviewer) ? "Yes" : "No";
                $is_manager = ($manager) ? "Yes" : "No";
                $is_projectco = ($projectco) ? "Yes" : "No";

                $html.= "<tr>";
                $html.= "<td>".$personnel->fullName()."</td>";
                $html.= "<td>".$personnel->email."</td>";
                $html.= "<td>".$is_expert."</td>";
                $html.= "<td>".$is_user."</td>";
                $html.= "<td>".$is_vip."</td>";
                $html.= "<td>".$is_reviewer."</td>";
                $html.= "<td>".$is_manager."</td>";
                $html.= "<td>".$is_projectco."</td>";
                $html.= "</tr>";
            }

        $html.= "</table>";

        return $html;
    }

    /*public function getMoveScheduleBudget($copy, $paste)
    {
        $copy_party = Party::find($copy);
        $paste_party = Party::find($paste);

        if ($copy_party && $paste_party)
        {
            //Check Party to have exist schedule return cannot move manual delete before
            $party_to_copy_schedule = LuSchedule::where('party_id', '=', $paste)->get();
            if ($party_to_copy_schedule)
            {
                return "ไม่สามารถ Copy ได้เพราะคณะที่จะวางมีอันเก่าอยู่แล้ว กรุณาลบก่อน";
            }
            //------Check copy schedule if have doing-----------
            $copy_schedule_id = LuSchedule::where('party_id', '=', $copy)->pluck('id');

            if ($copy_schedule_id)
            {
                //ดึงค่าอันที่ลอกมาลงอันที่วาง
                $copy_schedule = LuSchedule::find($copy_schedule_id);
                //create schedule before
                $paste_schedule = new LuSchedule;
                $paste_schedule->party_id = $to;
                $paste_schedule->revision = 0;
                $paste_schedule->created_by = copy_schedule->created_by;
                $paste_schedule->updated_by = copy_schedule->updated_by;
                $paste_schedule->created_at = copy_schedule->created_at;
                $paste_schedule->updated_at = copy_schedule->updated_at;

                if($paste_schedule->save())
                {
                    //run task copy to paste
                    $copy_tasks = LuScheduleTask::where('lu_schedule_id', '=', $copy_schedule_id)->get();
                
                    if ($copy_tasks)
                    {
                        foreach($copy_tasks as $copy_task)
                        {
                            $paste_task = new LuScheduleTask;
                            $paste_task->lu_schedule_id = $copy_task->lu_schedule_id;
                            $paste_task->start = $copy_task->start;
                            $paste_task->start = $copy_task->start;
                            $paste_task->save();
                        }
                    }
                }
                else
                {
                    return "ไม่สามารถ Copy กำหนดการได้";
                }
            }
            else
            {
                return "ไม่มีกำหนดการให้ copy";
            }
        }

        return "ไม่มีคณะนี้ทั้งสองคณะ";
    }*/

}