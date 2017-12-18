<?php

class AdminLocationFacilitiesController extends \AdminController {

    protected $location;

    protected $location_facilities;

    public function __construct(Location $location, LocationFacilities $location_facilities)
    {
        parent::__construct();
        $this->location = $location;
        $this->location_facilities = $location_facilities;
    }

    public function getIndex()
    {
        $locations = $this->location->getAllLocation()->get();
        // return
        return View::make('svms/admin/location_facilities', compact('locations'));
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
            $this->location_facilities->name = Input::get('name');
            $this->location_facilities->unit = Input::get('unit');
            $this->location_facilities->location_id = Input::get('location_id');
            $this->location_facilities->cost_price = Input::get('cost_price');
            $this->location_facilities->sale_price = Input::get('sale_price');
            $this->location_facilities->created_by = Auth::user()->id;
            $this->location_facilities->updated_by = Auth::user()->id;

            if ($this->location_facilities->save())
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
                'error' => $this->location_facilities->error()
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
            $location_facilities = LocationFacilities::find(input::get('id'));

            $location_facilities->name = Input::get('name');
            $location_facilities->unit = Input::get('unit');
            $location_facilities->location_id = Input::get('location_id');
            $location_facilities->cost_price = Input::get('cost_price');
            $location_facilities->sale_price = Input::get('sale_price');
            $location_facilities->updated_by = Auth::user()->id;

            if ($location_facilities->save())
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
                'error' => $location_facilities->error()
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
            $location_facilities = LocationFacilities::find(input::get('id'));

            if ($location_facilities->delete())
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
                'error' => $location_facilities->error()
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

        $data = LocationFacilities::find(Input::get( 'id' ));

        $response = array(
            'data' => $data
        );

        return Response::json( $response );
    }

    public function getData()
    {
        $rooms = LocationFacilities::leftjoin('locations as l', 'location_facilities.location_id', '=', 'l.id')
            ->select(array('location_facilities.id AS room_id', 'l.name as location_name', 'location_facilities.name as accom_name', 'location_facilities.cost_price', 'location_facilities.sale_price', 'location_facilities.created_at'));

        return Datatables::of($rooms)

            ->add_column('actions', '<a onclick="openEdit({{ $room_id }});" href="javascript:;" class="btn btn-default btn-xs" ><span class="fa fa-pencil-square-o"></span> แก้ไข</a>
            <a onclick="return openDelete({{ $room_id }});" href="javascript:;" class="btn btn-xs btn-danger"><span class="fa fa-trash-o"></span> ลบ</a>
        ')

            ->remove_column('room_id')

            ->make();
    }

}