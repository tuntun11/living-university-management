<?php

class AdminDepartmentsController extends AdminController {

    protected $department;

    public function __construct(Department $department)
    {
        parent::__construct();
        $this->department = $department;
    }

    public function getIndex()
    {
        // return view
        return View::make('svms/admin/department', array());
    }

    //post create data
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
            'name'   => 'required',
            'code'   => 'max:2',
            'financial_code'   => 'max:3'
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
                $this->department->code = Input::get('code');
                $this->department->financial_code = Input::get('financial_code');
                $this->department->name = Input::get('name');
                $this->department->name_en = Input::get('name_en');
                $this->department->is_lu = Input::get('is_lu');
                $this->department->is_revenue = Input::get('is_revenue');
                $this->department->is_mflf = Input::get('is_mflf');
                $this->department->created_by = Auth::user()->id;
                $this->department->updated_by = Auth::user()->id;

                if ($this->department->save())
                {
                    //Request Commit and send response
                    DB::commit();

                    //response success
                    $response = array(
                        'status' => 'success',
                        'msg' => 'สร้างรายการใหม่สำเร็จแล้ว'
                    );

                    return Response::json( $response );
                }

                //response error
                $response = array(
                    'status' => 'error',
                    'msg' => 'Error สร้างรายการไม่สำเร็จ',
                    'error' => $this->department->error()
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

    //post edits
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
            'name'   => 'required',
            'code'   => 'max:2',
            'financial_code'   => 'max:3'
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
                $department = Department::find(input::get('id'));

                $department->code = Input::get('code');
                $department->financial_code = Input::get('financial_code');
                $department->name = Input::get('name');
                $department->name_en = Input::get('name_en');
                $department->is_lu = Input::get('is_lu');
                $department->is_revenue = Input::get('is_revenue');
                $department->is_mflf = Input::get('is_mflf');
                $department->updated_by = Auth::user()->id;
                $department->updated_at = date('Y-m-d H:i:s');

                if ($department->save())
                {
                    //Request Commit and send response
                    DB::commit();

                    //response success
                    $response = array(
                        'status' => 'success',
                        'msg' => 'ปรับปรุงรายการใหม่สำเร็จแล้ว'
                    );

                    return Response::json( $response );
                }

                //response error
                $response = array(
                    'status' => 'error',
                    'msg' => 'Error ปรับปรุงรายการไม่สำเร็จ',
                    'error' => $department->error()
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

            $department = Department::find(input::get('id'));

            if ($department->delete())
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
                'error' => $department->error()
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

    public function getById()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        $department = Department::find(Input::get( 'id' ));

        $response = array(
            'data' => $department
        );

        return Response::json( $response );
    }

    public function getData()
    {
        $departments = Department::select(array('id', 'code', 'financial_code', 'name', 'name_en', 'is_lu', 'is_revenue'))
		->whereNotIn('id', array(1))
		->orderBy('code', 'DESC');

        return Datatables::of($departments)

            ->edit_column('is_lu', function($row){
                return ($row->is_lu) ? 'Yes' : 'No';
            })

            ->edit_column('is_revenue', function($row){
                return ($row->is_revenue) ? 'Yes' : 'No';
            })

            ->add_column('actions', '<a onclick="openEdit({{ $id }});" href="javascript:;" class="btn btn-default btn-xs" ><span class="fa fa-pencil-square-o"></span> แก้ไข</a>
                 <a onclick="return openDelete({{ $id }});" href="javascript:;" class="btn btn-xs btn-danger"><span class="fa fa-trash-o"></span> ลบ</a>
            ')

            ->remove_column('id')

            ->make();
    }

}