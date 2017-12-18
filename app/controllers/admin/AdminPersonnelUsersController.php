<?php

class AdminPersonnelUsersController extends AdminController {

    //user with personal data

    protected $user;

    protected $role;

    protected $permission;

    /**
     * Inject the models.
     * @param User $user
     * @param Role $role
     * @param Permission $permission
     */
    public function __construct(User $user, Role $role, Permission $permission)
    {
        parent::__construct();
        $this->user = $user;
        $this->role = $role;
        $this->permission = $permission;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getIndex()
    {
        // Title
        $title = Lang::get('admin/users/title.user_management');

        // personnel not yet create user
        $personnels = Personnel::whereNotIn('id', function($q){
            $q->select('personnel_id')->from('personnel_users');
        })
        ->get();

        // Grab all the users
        $users = $this->user;

        // All roles
        $roles = $this->role->all();

        // Get all the available permissions
        $permissions = $this->permission->all();

        // Show the page
        return View::make('svms/admin/users/index', compact('personnels', 'users', 'roles', 'permissions', 'title'));
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
            'personnel_id'   => 'required',
            'username'   => 'required|max:255'
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
                if ($this->createOrEditUser(Input::all()))
                {
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
            'personnel_id'   => 'required',
            'username'   => 'required|max:255'
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
                if ($this->createOrEditUser(Input::all(), false))
                {
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

    }

    public function getById()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        $personnel = Personnel::leftJoin('personnel_users as p', 'personnels.id', '=', 'p.personnel_id')
            ->leftJoin('users as u', 'p.user_id', '=', 'u.id')
            ->where('p.user_id', '=', input::get('id'))
            ->first();

        $user = User::find($personnel->user_id);

        $personnel['user_id'] = $personnel->user_id;
        $personnel['personnel_id'] = $personnel->personnel_id;
        $personnel['personnel_name'] = $personnel->fullName();
        $personnel['roles'] = $user->roles->all();

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
        $users = User::select(array('users.id AS id', 'users.username AS username', 'users.id AS realName', 'users.id AS department', 'users.email AS email'));

        return Datatables::of($users)

        ->edit_column('realName',function($row){

            return $row->getFullName();
        })

        ->edit_column('department',function($row){

            return $row->getDepartment();
        })

        ->add_column('actions', '<a onclick="openEdit({{ $id }});" href="javascript:;" class="btn btn-default btn-xs" ><span class="fa fa-pencil-square-o"></span> แก้ไขข้อมูล/การใช้งาน</a>')

        ->remove_column('id')

        ->make();
    }

    function createOrEditUser($input, $is_new = true)
    {
        //Create object to new or edit
        $user = ($is_new) ? new User : User::find(array_get($input, 'id'));
        //find Personnel Object
        $personnel = Personnel::find(array_get($input, 'personnel_id'));
        //Create new user and gen password
        if ($is_new)
        {
            //if new user set random password
            //else you have employee_id use it
            $new_password = ($personnel->code==NULL || $personnel->code=="") ? $this->randomPassword() : $personnel->code;
            $user->password = $new_password;
            // The password confirmation will be removed from model
            // before saving. This field will be used in Ardent's
            // auto validation.
            $user->password_confirmation = $new_password;

            // Generate a random confirmation code
            $user->confirmation_code = md5(uniqid(mt_rand(), true));
            $user->confirmed = array_get($input, 'confirm');
        }
        // Validate, then create if valid
        $user->username = array_get($input, 'username');
        $user->email = $personnel->email;

        // Save if valid. Password field will be hashed before save
        if ( $user->save() )
        {
            //assign role
            if (array_get($input, 'roles'))
            {
                // Save roles. Handles updating.
                //note save as user with no active
                $user->saveRoles(array_get($input, 'roles'));
            }
            //create mail alert when first ever
            if ($is_new)
            {
                //save relation with personnel
                DB::table('personnel_users')->insert(
                    array(
                        'personnel_id' => $personnel->id,
                        'user_id' => $user->id
                    )
                );
                //****send email to user****
                $user = User::findOrFail($user->id);
                if ($user)
                {
                    $user['personnel_name'] = $personnel->fullName();
                    $user['usepass'] = $new_password;

                    Mail::send('emails.auth.sendpassword', compact('user'), function($message) use ($user)
                    {
                        $message
                            ->to($user->email, $user->username)
                            ->subject('มหาวิทยาลัยที่มีชีวิต : ขอนำส่งข้อมูลผู้ใช้งานระบบ');
                    });
                }
            }

            return true;
        }
        else
        {
            return false;
        }
    }

    function randomPassword() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}
