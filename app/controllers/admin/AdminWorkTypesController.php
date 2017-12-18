<?php

class AdminWorkTypesController extends AdminController {

    protected $work_type;

    public function __construct(WorkType $work_type)
    {
        parent::__construct();
        $this->work_type = $work_type;
    }

    public function getIndex()
    {
        // return view
        return View::make('svms/admin/work_type', array());
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
            'name'   => 'required'
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
                $this->work_type->name = Input::get('name');
				$this->work_type->description = Input::get('description');
				$this->work_type->priority = Input::get('priority');
                $this->work_type->created_by = Auth::user()->id;
                $this->work_type->updated_by = Auth::user()->id;

                if ($this->work_type->save())
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
                    'error' => $this->work_type->error()
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
            'name'   => 'required'
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
                $work_type = WorkType::find(input::get('id'));

                $work_type->name = Input::get('name');
				$work_type->description = Input::get('description');
				$work_type->priority = Input::get('priority');
                $work_type->updated_by = Auth::user()->id;
                $work_type->updated_at = date('Y-m-d H:i:s');

                if ($work_type->save())
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
                    'error' => $work_type->error()
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

            $work_type = WorkType::find(input::get('id'));

            if ($work_type->delete())
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
                'error' => $work_type->error()
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

        $work_type = WorkType::find(Input::get( 'id' ));

        $response = array(
            'data' => $work_type
        );

        return Response::json( $response );
    }

    public function getData()
    {
        $work_types = WorkType::select(array('id', 'name', 'description', 'priority'));

        return Datatables::of($work_types)

            ->add_column('actions', '<a onclick="openEdit({{ $id }});" href="javascript:;" class="btn btn-default btn-xs" ><span class="fa fa-pencil-square-o"></span> แก้ไข</a>
                 <a onclick="return openDelete({{ $id }});" href="javascript:;" class="btn btn-xs btn-danger"><span class="fa fa-trash-o"></span> ลบ</a>
            ')

            ->remove_column('id')

            ->make();
    }

}