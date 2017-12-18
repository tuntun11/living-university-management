<?php

class AdminAccomRoomsController extends \AdminController {

    protected $location;

    protected $accommodation;

    public function __construct(Location $location, Accommodation $accommodation)
    {
        parent::__construct();
        $this->location = $location;
        $this->accommodation = $accommodation;
    }

    public function getIndex()
    {
        $locations = $this->location->getAllLocation()->get();
        // return
        return View::make('svms/admin/accommodation/rooms', compact('locations'));
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
            'unit'   => 'required',
            'location_id'   => 'required',
            'cost_price'   => 'required',
            'sale_price'   => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $this->accommodation->name = Input::get('name');
            $this->accommodation->unit = Input::get('unit');
            $this->accommodation->location_id = Input::get('location_id');
            $this->accommodation->cost_price = Input::get('cost_price');
            $this->accommodation->sale_price = Input::get('sale_price');
            $this->accommodation->created_by = Auth::user()->id;
            $this->accommodation->updated_by = Auth::user()->id;

            if ($this->accommodation->save())
            {
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
                'error' => $this->accommodation->error()
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
            'name'   => 'required',
            'unit'   => 'required',
            'location_id'   => 'required',
            'cost_price'   => 'required',
            'sale_price'   => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $accommodation = Accommodation::find(input::get('id'));

            $accommodation->name = Input::get('name');
            $accommodation->unit = Input::get('unit');
            $accommodation->location_id = Input::get('location_id');
            $accommodation->cost_price = Input::get('cost_price');
            $accommodation->sale_price = Input::get('sale_price');
            $accommodation->updated_by = Auth::user()->id;

            if ($accommodation->save())
            {
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
                'error' => $accommodation->error()
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
            $accommodation = Accommodation::find(input::get('id'));

            if ($accommodation->delete())
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
                'error' => $accommodation->error()
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

        $data = Accommodation::find(Input::get( 'id' ));

        $response = array(
            'data' => $data
        );

        return Response::json( $response );
    }

    public function getData()
    {
        $rooms = Accommodation::leftjoin('locations as l', 'accommodations.location_id', '=', 'l.id')
                        ->select(array('accommodations.id AS room_id', 'l.name as location_name', 'accommodations.name as accom_name', 'accommodations.cost_price', 'accommodations.sale_price', 'accommodations.created_at'));

        if (Input::get('location_id')!="")
        {
            $rooms = $rooms->where('accommodations.location_id', '=', Input::get('location_id'));
        }

        return Datatables::of($rooms)

            ->add_column('actions', '<a onclick="openEdit({{ $room_id }});" href="javascript:;" class="btn btn-default btn-xs" ><span class="fa fa-pencil-square-o"></span> แก้ไข</a>
            <a onclick="return openDelete({{ $room_id }});" href="javascript:;" class="btn btn-xs btn-danger"><span class="fa fa-trash-o"></span> ลบ</a>
        ')

            ->remove_column('room_id')

            ->make();
    }

}