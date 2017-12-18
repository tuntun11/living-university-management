<?php

class ExpertController extends BaseController {

    /**
     * Initializer.
     *
     * @return \ExpertController
     */

    protected $expert;

    public function __construct(Personnel $expert)
    {
        parent::__construct();

        $this->expert = $expert;
    }

    //return index page
    public function getIndex()
    {
        // All Department
        $departments = Department::whereNotIn('id', array(1))->get();

        //get table countries
        $countries = DB::table('countries')->select('name AS text', 'alpha_2 AS id')->get();

        //get personnel types
        $personnel_types = PersonnelType::all();

        //area work base
        $areas = MflfArea::all();

        //get expert type
        $expert_types = ExpertType::all();

        //get ethnic
        $ethnics = DB::table('ethnic')->select('name', 'id')->get();

        return View::make('svms/expert/index', compact('departments', 'countries', 'personnel_types', 'areas', 'expert_types', 'ethnics'));
    }

    //retrun view for expert or personnel data
    public function getView($expert)
    {
        $months = array('มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม');
        $educations = Personnel::listEducations();
        $language_levels = Personnel::listLanguageLevels();

        return View::make('svms/expert/persona', compact('expert', 'months', 'educations', 'language_levels'));
    }

    //retrun json ajax personna data
    public function getPersona()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        //query expert personna
        $expert = Personnel::find(input::get('id'));

        //extend data
        $expert['history'] = $expert->history;
        $expert['lectures'] = $expert->lectureSubjects()->get();
        $expert['languages'] = $expert->lectureLanguages()->get();
        $expert['training_sessions'] = $expert->trainingSessions()->get();
        $expert['educations'] = $expert->educations()->get();
        //$expert['classroom_bases'] = $expert->classroomBases()->get();
        $expert['work_experiences'] = $expert->workExperiences()->get();
        $expert['files'] = $expert->files()->get();

        if ($expert)
        {
            $response = array(
                'status' => 'success',
                'data' => $expert
            );
        }
        else
        {
            $response = array(
                'status' => 'error',
                'msg' => $expert
            );
        }

        return Response::json( $response );
    }

    /*Post to edit extend persona data*/
    public function postPersona()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            'persona_id'   => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);
        // Check if the form validates with success
        if ($validator->passes())
        {
            // Start transaction!
            DB::beginTransaction();

            try {
                // Find out expert
                $expert = Personnel::find(Input::get('persona_id'));
                //Save History
                $expert->history = Input::get('history');

                //Add New Data
                if (count(Input::get('main_subject'))>0)
                {
                    $lectures = array();
                    for($i=0;$i<count(Input::get('main_subject'));$i++)
                    {
                        $subject_id = Input::get('main_subject_id.'.$i);

                        $lecture = ($subject_id==0) ? new PersonnelLectureSubjects : PersonnelLectureSubjects::find($subject_id);
                        $lecture->personnel_id = Input::get('persona_id');
                        $lecture->type = 'main';
                        $lecture->subject = Input::get('main_subject.'.$i);
                        $lecture->created_by = Auth::user()->id;
                        $lecture->updated_by = Auth::user()->id;

                        array_push($lectures, $lecture);
                    }
                    //save to transaction database
                    $expert->lectureSubjects()->saveMany($lectures);
                }
                if (count(Input::get('second_subject'))>0)
                {
                    $lectures = array();
                    for($i=0;$i<count(Input::get('second_subject'));$i++)
                    {
                        $subject_id = Input::get('second_subject_id.'.$i);

                        $lecture = ($subject_id==0) ? new PersonnelLectureSubjects : PersonnelLectureSubjects::find($subject_id);
                        $lecture->personnel_id = Input::get('persona_id');
                        $lecture->type = 'second';
                        $lecture->subject = Input::get('second_subject.'.$i);
                        $lecture->created_by = Auth::user()->id;
                        $lecture->updated_by = Auth::user()->id;

                        array_push($lectures, $lecture);
                    }
                    //save to transaction database
                    $expert->lectureSubjects()->saveMany($lectures);
                }
                if (count(Input::get('languages'))>0)
                {
                    $languages = array();

                    for($i=0;$i<count(Input::get('languages'));$i++)
                    {
                        $language_id = Input::get('language_id.'.$i);

                        $lang = ($language_id==0) ? new PersonnelLectureLanguages : PersonnelLectureLanguages::find($language_id);
                        $lang->personnel_id = Input::get('persona_id');
                        $lang->lang = (trim(Input::get('languages.'.$i))=='') ? 'ไม่ระบุ' : Input::get('languages.'.$i);
                        $lang->listen_level = Input::get('listen_levels.'.$i);
                        $lang->speak_level = Input::get('speak_levels.'.$i);
                        $lang->read_level = Input::get('read_levels.'.$i);
                        $lang->write_level = Input::get('write_levels.'.$i);
                        $lang->created_by = Auth::user()->id;
                        $lang->updated_by = Auth::user()->id;

                        array_push($languages, $lang);
                    }
                    //save to transaction database
                    $expert->lectureLanguages()->saveMany($languages);
                }
                if (count(Input::get('training_course'))>0)
                {
                    $courses = array();
                    for($i=0;$i<count(Input::get('training_course'));$i++)
                    {
                        $training_id = Input::get('training_id.'.$i);

                        $course = ($training_id==0) ? new PersonnelTrainingSessions : PersonnelTrainingSessions::find($training_id);
                        $course->personnel_id = Input::get('persona_id');
                        $course->session_name = (trim(Input::get('training_course.'.$i))=='') ? 'ไม่ระบุ' : Input::get('training_course.'.$i);
                        $course->session_detail = Input::get('training_desc.'.$i);
                        $course->session_period = Input::get('training_time.'.$i);
                        $course->organization = Input::get('training_org.'.$i);
                        $course->start_day = Input::get('training_start_day.'.$i);
                        $course->start_month = Input::get('training_start_month.'.$i);
                        $course->start_year = Input::get('training_start_year.'.$i);
                        $course->end_day = Input::get('training_end_day.'.$i);
                        $course->end_month = Input::get('training_end_month.'.$i);
                        $course->end_year = Input::get('training_end_year.'.$i);
                        $course->created_by = Auth::user()->id;
                        $course->updated_by = Auth::user()->id;

                        array_push($courses, $course);
                    }
                    //save to transaction database
                    $expert->trainingSessions()->saveMany($courses);
                }
                if (count(Input::get('education_level'))>0)
                {
                    $educations = array();
                    for($i=0;$i<count(Input::get('education_level'));$i++)
                    {
                        $education_id = Input::get('education_id.'.$i);

                        $education = ($education_id==0) ? new PersonnelEducations : PersonnelEducations::find($education_id);
                        $education->personnel_id = Input::get('persona_id');
                        $education->level = (trim(Input::get('education_level.'.$i))=='') ? '' : Input::get('education_level.'.$i);
                        $education->school_name = Input::get('education_school.'.$i);
                        $education->graduation = Input::get('education_graduation.'.$i);
                        $education->major = Input::get('education_major.'.$i);
                        $education->gpa = Input::get('education_gpa.'.$i);
                        $education->finish_year = Input::get('education_finish_year.'.$i);
                        $education->created_by = Auth::user()->id;
                        $education->updated_by = Auth::user()->id;

                        array_push($educations, $education);
                    }
                    //save to transaction database
                    $expert->educations()->saveMany($educations);
                }
                if (count(Input::get('exp_org'))>0)
                {
                    $experiences = array();
                    for($i=0;$i<count(Input::get('exp_org'));$i++)
                    {
                        $exp_id = Input::get('exp_id.'.$i);

                        $experience = ($exp_id==0) ? new PersonnelWorkExperiences : PersonnelWorkExperiences::find($exp_id);
                        $experience->personnel_id = Input::get('persona_id');
                        $experience->company = (trim(Input::get('exp_org.'.$i))=='') ? '' : Input::get('exp_org.'.$i);
                        $experience->position = Input::get('exp_position.'.$i);
                        $experience->job_description = Input::get('exp_work_desc.'.$i);
                        $experience->start_month = Input::get('exp_start_month.'.$i);
                        $experience->start_year = Input::get('exp_start_year.'.$i);

                        $chkStillWork = Input::get('chkIfPresentWork');
                        if (isset($chkStillWork))
                        {
                            $experience->end_month = '';
                            $experience->end_year = '';
                            $experience->still_working = true;
                        }
                        else
                        {
                            $experience->end_month = Input::get('exp_end_month.'.$i);
                            $experience->end_year = Input::get('exp_end_year.'.$i);
                            $experience->still_working = false;
                        }
                        $experience->created_by = Auth::user()->id;
                        $experience->updated_by = Auth::user()->id;

                        array_push($experiences, $experience);
                    }
                    //save to transaction database
                    $expert->workExperiences()->saveMany($experiences);
                }
                //upload new file
                if (count(Input::get('new_file'))>0)
                {
                    $files = array();
                    for($i=0;$i<count(Input::get('new_file'));$i++)
                    {
                        //check if add new file save and upload if not skip it.
                        if (Input::get('new_file.'.$i)=='1')
                        {
                            if (Input::hasFile('file.'.$i))
                            {
                                $destinationPath = public_path().'/svms/personnel_files/'.$expert->id;
                                $url = asset('svms/personnel_files/'.$expert->id);
                                $fileName = Input::file('file.'.$i)->getClientOriginalName();

                                if(!file_exists($destinationPath))
                                {
                                    //create folder
                                    mkdir($destinationPath, 0777);
                                }

                                //move file to upload
                                if(Input::file('file.'.$i)->move($destinationPath, $fileName))
                                {
                                    $file_id = Input::get('file_id.'.$i);
                                    $file = ($file_id==0) ? new PersonnelFiles : PersonnelFiles::find($file_id);
                                    $file->personnel_id = Input::get('persona_id');
                                    $file->name = $fileName;
                                    $file->url = $url.'/'.$fileName;
                                    $file->description = Input::get('file_desc.'.$i);
                                    $file->created_by = Auth::user()->id;
                                    $file->updated_by = Auth::user()->id;

                                    array_push($files, $file);
                                }
                            }
                        }
                    }
                    //save to transaction database
                    $expert->files()->saveMany($files);
                }

                if($expert->save())
                {
                    //Request Commit and send response
                    DB::commit();

                    /*return success*/
                    $response = array(
                        'status' => 'success',
                        'id' => $expert->id,
                        'msg' => 'ทำการบันทึกข้อมูลวิทยากรสำเร็จ'
                    );

                    return Response::json( $response );
                }
                else
                {
                    throw new Exception("ไม่สามารถบันทึกได้ กรุณาติดต่อ Admin");
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

    /*post Delete Bullet for Expert*/
    public function postDeleteExpertBullet()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            'id'   => 'required',//to delete
            'personnel_id'   => 'required',//personnel_id
            'data' => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);
        // Check if the form validates with success
        if ($validator->passes())
        {
            // Find out expert extension
           switch(Input::get('data')){
               case 'educations' :
                   $expert = PersonnelEducations::find(Input::get('id'));
               break;
               case 'languages' :
                   $expert = PersonnelLectureLanguages::find(Input::get('id'));
               break;
               case 'subjects' :
                   $expert = PersonnelLectureSubjects::find(Input::get('id'));
               break;
               case 'trainings' :
                   $expert = PersonnelTrainingSessions::find(Input::get('id'));
               break;
               case 'experiences' :
                   $expert = PersonnelWorkExperiences::find(Input::get('id'));
               break;
           }

            if ($expert->delete())
            {
                /*return success*/
                $response = array(
                    'status' => 'success',
                    'id' => Input::get('personnel_id'),// Return personnel id to Reload
                    'msg' => 'ทำการข้อมูลสำเร็จแล้ว'
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

    /*post Delete Single File for Expert*/
    public function postDeleteExpertFile()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
            'id'   => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);
        // Check if the form validates with success
        if ($validator->passes())
        {
            // Find out expert
            $expert_file = PersonnelFiles::find(Input::get('id'));
            $personnel_id = $expert_file->personnel_id;
            $file_path = public_path().'/svms/personnel_files/'.$personnel_id.'/'.$expert_file->name;

            if ($expert_file->delete() && unlink($file_path))
            {
                /*return success*/
                $response = array(
                    'status' => 'success',
                    'id' => $personnel_id,
                    'msg' => 'ทำการลบไฟล์วิทยากรสำเร็จ'
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

    /**
     * Store a newly created resource in storage Or Update.
     */
    public function postCreateOrUpdate()
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
            'prefix'   => 'required|max:50',
            'first_name'   => 'required|max:100',
            'last_name'   => 'required|max:100',
            'email'   => 'required|email|max:255'
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
                //Check When new comer
                if (Input::get('id')==0)
                {
                    //Check code dupplicate
                    $foundCode = Personnel::whereCode(Input::get( 'code' ))->first();
                    if ($foundCode)
                    {
                        throw new Exception("รหัสพนักงานนี้มีอยู่แล้วไม่สามารถเพิ่มได้");
                    }
                }
                //Create New Personnel
                $personnel = (Input::get('id')==0) ? new Personnel : Personnel::find(Input::get('id'));

                $personnel->code = Input::get( 'code' );
                $personnel->prefix = Input::get( 'prefix' );
                $personnel->first_name = Input::get( 'first_name' );
                $personnel->last_name = Input::get( 'last_name' );
                $personnel->prefix_en = Input::get( 'prefix_en' );
                $personnel->first_name_en = Input::get( 'first_name_en' );
                $personnel->last_name_en = Input::get( 'last_name_en' );
                $personnel->nick_name = Input::get( 'nick_name' );
                $personnel->email = Input::get( 'email' );
                $personnel->mobile = Input::get( 'mobile' );
                $personnel->nationality = Input::get( 'nationality' );
                $personnel->position = Input::get( 'position' );
                $personnel->local_role = Input::get('local_role');
                $personnel->is_expert = 1;
                $personnel->work_base = Input::get('work_base');
                $personnel->work_level = Input::get('work_level');
                $personnel->is_ethnic = (Input::get('radioIsEthnic')=='yes') ? 1 : 0;
                $personnel->ethnic = (Input::get('radioIsEthnic')=='yes') ? Input::get( 'ethnic' ) : Input::get( 'ethnic' );
                $personnel->birth_date = Input::get( 'birth_date' );
                $personnel->birth_month = Input::get( 'birth_month' );
                $personnel->birth_year = Input::get( 'birth_year' );
                $personnel->age = Input::get( 'age' );
                $personnel->sex = Input::get( 'sex' );
                $personnel->created_by = Auth::user()->id;
                $personnel->updated_by = Auth::user()->id;

                //Check personnel mflf or not
                if (intval(Input::get( 'radioIsMflf' ))==1)
                {
                    //Case is MFLF
                    $personnel->department_id = Input::get( 'department_id' );
                    $personnel->mfl_office = Input::get( 'mfl_office' );
                }
                else
                {
                    //Case is Not
                    $personnel->department_id = 1;
                    $personnel->other_office = Input::get( 'other_office' );
                }
                //status
                if (Input::get( 'status' )=='active')
                {
                    $personnel->status = Input::get( 'status' );
                    $personnel->status_note = "";
                }
                else
                {
                    $personnel->status = Input::get( 'status' );
                    $personnel->status_note = Input::get( 'status_note' );
                }

                //Check save state
                if ($personnel->save())
                {
                    //save personnel status log
                    $personnel_status = new PersonnelStatuses;
                    $personnel_status->status = $personnel->status;
                    $personnel_status->note = $personnel->status_note;
                    $personnel_status->personnel_id = $personnel->id;
                    $personnel_status->created_by = Auth::user()->id;
                    $personnel_status->updated_by = Auth::user()->id;
                    $personnel_status->save();
                    //if select type of rate
                    if (Input::get( 'expert_type' ))
                    {
                        //check if edit delete old
                        if(Input::get('id')!=0)
                        {
                            //delete before add new type
                            DB::table('personnel_assigned_type')->where('personnel_id', '=', $personnel->id)->delete();
                        }
                        //also save type
                        DB::table('personnel_assigned_type')->insert(
                            array('personnel_id' => $personnel->id, 'personnel_type_id' => Input::get( 'expert_type' ))
                        );
                    }
                    //save personnel expert type
                    if (Input::get( 'personnel_expert_types' ))
                    {
                        //delete all before add new
                        $personnel->personExpertTypes()->delete();

                        $types = array();
                        for($a=0;$a<count(Input::get( 'personnel_expert_types' ));$a++)
                        {
                            $type = new PersonnelExpertTypes;
                            $type->personnel_id = $personnel->id;
                            $type->expert_type_id = Input::get( 'personnel_expert_types.'.$a );
                            $type->created_by = Auth::user()->id;
                            $type->updated_by = Auth::user()->id;

                            array_push($types, $type);
                        }
                        $personnel->personExpertTypes()->saveMany($types);
                    }

                    //Request Commit and send response
                    DB::commit();

                    //Upload picture keep in directory as personnel id
                    if (Input::hasFile('personnelImage'))
                    {
                        $destinationPath = public_path().'/svms/personnel_image/';
                        $fileName = $personnel->id.".jpg";

                        if(!file_exists($destinationPath))
                        {
                            //1. create directory name by request code
                            mkdir($destinationPath, 0777);
                        }
                        //2. save file in to directory
                        Input::file('personnelImage')->move($destinationPath, $fileName);
                    }

                    $response = array(
                        'status' => 'success',
                        'msg' => 'ทำการบันทึกสำเร็จแล้ว'
                    );

                    return Response::json( $response );
                }
                else
                {
                    throw new Exception("ไม่สามารถทำการบันทึกได้");
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
    }

    /**
     * Remove the specified user from storage.
     */
    public function postDelete()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        //personnel id
        $rules = array(
            'id' => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        if ($validator->passes())
        {
            $personnel = Personnel::find(input::get('id'));
            $personnel_user_id = PersonnelUser::where('personnel_id', '=', input::get('id'))->pluck('user_id');

            if ($personnel_user_id)
            {
                $user = User::find($personnel_user_id);
                // Check if we are not trying to delete ourselves
                if ($user->id === Confide::user()->id)
                {
                    $response = array(
                        'status' => 'error',
                        'msg' => Lang::get('admin/users/messages.delete.impossible')
                    );

                    return Response::json( $response );
                }

                //delete roles not use
                //AssignedRoles::where('user_id', $user->id)->delete();

                $id = $user->id;
                //delete personnel and user relation
                DB::table('personnel_users')
                    ->where('personnel_id', '=', $personnel->id)
                    ->where('user_id', '=', $user->id)
                    ->delete();

                //delete user
                $user->delete();
            }


            DB::table('personnel_assigned_type')->where('personnel_id', '=', $personnel->id)->delete();

            //delete personnel
            if ($personnel->delete())
            {
                //response success
                $response = array(
                    'status' => 'success',
                    'msg' => Lang::get('admin/users/messages.delete.success')
                );

                return Response::json( $response );
            }
            else
            {
                // There was a problem deleting the user
                $response = array(
                    'status' => 'error',
                    'msg' => Lang::get('admin/users/messages.delete.error'),
                    'error' => $user->error()
                );

                return Response::json( $response );
            }
        }

    }

    public function getById()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        //query personnel
        $personnel = Personnel::find(input::get('id'));
        //get image
        $personnel['image'] = $personnel->image();

        //select person type if expert
        if ($personnel->is_expert==1)
        {
            $person_type = PersonnelAssignedType::where('personnel_id', '=', $personnel->id)->first();

            $personnel['personnel_type_id'] = ($person_type) ? $person_type->personnel_type_id : 0;

            //get expert type
            $types = $personnel->personExpertTypes()->get();
            $expert_types = array();
            foreach($types as $type)
            {
                array_push($expert_types, $type->expert_type_id);
            }
            $personnel['expert_types'] = $expert_types;
           // $personnel['expert_active'] = ($personnel->is_active) ? 1 : 0;
        }
        else
        {
            $personnel['personnel_type_id'] = 0;
            $personnel['expert_types'] = array();
            //$personnel['expert_active'] = ($personnel->is_active) ? 'not' : 0;
        }

        $response = array(
            'data' => $personnel
        );

        return Response::json( $response );
    }

    //return json list data
    public function getData()
    {
        //return Expert List
        $personnels = $this->expert->specialistPersons()
            ->select('personnels.id', 'personnels.prefix', 'personnels.first_name', 'personnels.last_name' , 'personnels.nick_name', 'personnels.position', 'personnels.status', 'personnels.status_note');

        return Datatables::of($personnels)

            ->add_column('image', function($row){
                return $row->image();
            })

            ->add_column('full_name', function($row){
                return $row->fullName();
            })

            ->add_column('nick_name', function($row){
                return $row->nick_name;
            })

            ->add_column('position', function($row){
                return $row->position;
            })

            ->add_column('status_thai', function($row){
                $status = $row->status();

                return $status;
            })

            ->add_column('expert_type', function($row){
                return $row->expertType();
            })

            ->add_column('actions', function($row){
                $html = '';
                $html .= '<a href="'.URL::to('expert/'.$row->id.'/view').'" class="btn btn-info btn-xs" ><i class="fa fa-info" aria-hidden="true"></i> ข้อมูล</a> ';
                $html .= '<a onclick="openEdit('.$row->id.');" href="javascript:;" class="btn btn-default btn-xs" ><span class="fa fa-pencil-square-o"></span> แก้ไข</a> ';
                if(Auth::check() && Auth::user()->hasRole('manager'))
                {
                    $html .= '<a onclick="return openDelete('.$row->id.');" href="javascript:;" class="btn btn-xs btn-danger"><span class="fa fa-trash-o"></span> ลบ</a>';
                }

                return $html;
            })

            ->remove_column('id')

            ->make(true);
    }

}