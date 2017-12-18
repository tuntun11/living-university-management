<?php

class AdminPersonnelController extends AdminController {

    //personal data

    protected $department;

    protected $personnel;

    /**
     * Construct
     */
    public function __construct(Department $department, Personnel $personnel)
    {
        parent::__construct();
        $this->department = $department;
        $this->personnel = $personnel;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getIndex()
    {
        // Title
        $title = 'จัดการข้อมูลบุคลากรและวิทยากร';

        // Grab all the users
        $personnels = $this->personnel;

        // All Department
        $departments = $this->department->all();

        //get table countries
        $countries = DB::table('countries')->select('name AS text', 'alpha_2 AS id')->get();

        $personnel_types = PersonnelType::all();

        // Show the page
        return View::make('svms/admin/personnels/index', compact('personnels', 'countries', 'departments', 'personnel_types', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postCreate()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        // Declare the rules for the form validation
        $rules = array(
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
                //Create New Personnel
                $this->personnel->code = Input::get( 'code' );
                $this->personnel->prefix = Input::get( 'prefix' );
                $this->personnel->first_name = Input::get( 'first_name' );
                $this->personnel->last_name = Input::get( 'last_name' );
                $this->personnel->prefix_en = Input::get( 'prefix_en' );
                $this->personnel->first_name_en = Input::get( 'first_name_en' );
                $this->personnel->last_name_en = Input::get( 'last_name_en' );
				$this->personnel->nick_name = Input::get( 'nick_name' );
                $this->personnel->email = Input::get( 'email' );
                $this->personnel->mobile = Input::get( 'mobile' );
                $this->personnel->nationality = Input::get( 'nationality' );
                $this->personnel->position = Input::get( 'position' );
				$this->personnel->priority = Input::get( 'priority' );
                $this->personnel->is_expert = Input::get( 'radioExpert' );
                //$this->personnel->ethnic = Input::get( 'ethnic' );
				$this->personnel->birth_date = Input::get( 'birth_date' );
				$this->personnel->birth_month = Input::get( 'birth_month' );
				$this->personnel->birth_year = Input::get( 'birth_year' );
                $this->personnel->created_by = Auth::user()->id;
                $this->personnel->updated_by = Auth::user()->id;

                //Check personnel mflf or not
                if (intval(Input::get( 'radioIsMflf' ))==1)
                {
                    //Case is MFLF
                    $this->personnel->department_id = Input::get( 'department_id' );
                    $this->personnel->mfl_office = Input::get( 'mfl_office' );
                    //$this->personnel->is_administrator = Input::get( 'is_administrator' );
                    $this->personnel->is_administrator = 0;
                    $this->personnel->can_view_fullcalendar = Input::get( 'can_view_fullcalendar' );
                }
                else
                {
                    //Case is Not
                    $this->personnel->department_id = 1;
                    $this->personnel->other_office = Input::get( 'other_office' );
                }

                //Check save state
                if ($this->personnel->save())
                {
                    if($this->personnel->is_expert)
                    {
                        //save type
                        DB::table('personnel_assigned_type')->insert(
                            array('personnel_id' => $this->personnel->id, 'personnel_type_id' => Input::get( 'expert_type' ))
                        );
                    }

                    //Request Commit and send response
                    DB::commit();

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
     * Display the specified resource.
     *
     * @param $user
     * @return Response
     */
    public function getShow($user)
    {
        // redirect to the frontend
    }

    /**
     * Update the specified resource in storage.
     *
     * @param User $user
     * @return Response
     */
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
            'id' => 'required',
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

            try {
                //find user
                $personnel = Personnel::find(Input::get( 'old_id' ));

                //Edit data
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
                $personnel->priority = Input::get( 'priority' );
                $personnel->is_expert = Input::get( 'radioExpert' );
                //$personnel->ethnic = Input::get( 'ethnic' );
                $personnel->birth_date = Input::get( 'birth_date' );
                $personnel->birth_month = Input::get( 'birth_month' );
                $personnel->birth_year = Input::get( 'birth_year' );
                $personnel->updated_by = Auth::user()->id;
                $personnel->updated_at = date('Y-m-d H:i:s');

                //Check personnel mflf or not
                if (intval(Input::get( 'radioIsMflf' ))==1)
                {
                    //Case is MFLF
                    $personnel->department_id = Input::get( 'department_id' );
                    $personnel->mfl_office = Input::get( 'mfl_office' );
                    $personnel->other_office = "";
                    $personnel->is_administrator = (Input::get( 'is_administrator' )) ? Input::get( 'is_administrator' ) : 0;
                    $personnel->can_view_fullcalendar = (Input::get( 'can_view_fullcalendar' )) ? Input::get( 'can_view_fullcalendar' ) : 0;
                }
                else
                {
                    //Case is Not
                    $personnel->department_id = 1;
                    $personnel->mfl_office = "";
                    $personnel->other_office = Input::get( 'other_office' );
                    $personnel->is_administrator = 0;
                    $personnel->can_view_fullcalendar = 0;
                }

                if ($personnel->save())
                {
                    if($personnel->is_expert)
                    {
                        //delete before add new type
                        DB::table('personnel_assigned_type')->where('personnel_id', '=', $personnel->id)->delete();
                        //save type
                        DB::table('personnel_assigned_type')->insert(
                            array('personnel_id' => $personnel->id, 'personnel_type_id' => Input::get( 'expert_type' ))
                        );
                    }
                    else
                    {
                        DB::table('personnel_assigned_type')->where('personnel_id', '=', $personnel->id)->delete();
                    }

                    //Request Commit and send response
                    DB::commit();

                    //upload picture keep in directory as personnel id
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
     *
     * @param $user
     * @return Response
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
            $person_type = DB::table('personnel_assigned_type')->where('personnel_id', '=', $personnel->id)->first();

            $personnel['personnel_type_id'] = ($person_type) ? $person_type->personnel_type_id : 0;
        }
        else
        {
            $personnel['personnel_type_id'] = 0;
        }

        $response = array(
            'data' => $personnel
        );

        return Response::json( $response );
    }

    /**
     * Show a list of all the users formatted for Datatables.
     *
     * @return Datatables JSON
     */
    public function getData()
    {
        $personnels = Personnel::select(array('personnels.id AS id', 'personnels.id AS image', DB::raw('CONCAT(personnels.prefix,personnels.first_name," ",personnels.last_name) AS realName'), 'personnels.department_id AS department', 'personnels.position', 'personnels.is_expert'))
                    ->whereNotIn('personnels.id', array(5,6))
                    ->orderBy('personnels.priority', 'ASC');

        return Datatables::of($personnels)

        ->edit_column('image',function($row){
            return $row->image();
        })

        ->edit_column('department',function($row){
            $department = Department::find($row->department);
            $monogram = ($department->monogram==NULL) ? '' : '('.$department->monogram.')';
            return $department->name.$monogram;
        })

        ->edit_column('is_expert',function($row){
            return ($row->is_expert) ? '<span style="color:green;">ใช่</span>' : '<span style="color:red;">ไม่ใช่</span>';
        })

        ->add_column('actions', '<a onclick="openEdit({{ $id }});" href="javascript:;" class="btn btn-default btn-xs" ><span class="fa fa-pencil-square-o"></span> แก้ไข</a>
                                    <a onclick="return openDelete({{ $id }});" href="javascript:;" class="btn btn-xs btn-danger"><span class="fa fa-trash-o"></span> ลบ</a>
        ')

        ->remove_column('id')

        ->make();
    }

}
