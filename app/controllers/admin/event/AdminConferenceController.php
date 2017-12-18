<?php

class AdminConferenceController extends \AdminController {

    protected $location;

    protected $location_rates;

    public function __construct(Location $location, LocationRates $location_rates)
    {
        parent::__construct();
        $this->location = $location;
        $this->location_rates = $location_rates;
    }

    public function getIndex()
    {
        // return
        return View::make('svms/admin/event/conferences');
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
            'old_id' => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $location = Location::find(Input::get('old_id'));

            $rates = array();
            //add rates
            for($i=0;$i<count(Input::get('rate_name'));$i++)
            {
                //if name = "" not add
                if(Input::get('rate_name.'.$i)!="")
                {
                    $rate = new LocationRates;
                    $rate->location_id =  $location->id;
                    $rate->name = Input::get('rate_name.'.$i);
                    $rate->cost_price = Input::get('rate_cost.'.$i);
                    $rate->sale_price = Input::get('rate_sale.'.$i);
                    $rate->created_by = Auth::user()->id;
                    $rate->updated_by = Auth::user()->id;

                    array_push($rates, $rate);
                }
            }

            if ($location->rates()->saveMany($rates))
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
                'error' => $this->location_rates->error()
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
            'old_id' => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $location = Location::find(Input::get('old_id'));

            if (!$location)
            {
                $response = array(
                    'status' => 'error',
                    'msg' => 'Error สถานที่นี้ได้ถูกลบไปแล้ว'
                );

                return Response::json( $response );
            }

            $rates = array();
            //add rates
            for($i=0;$i<count(Input::get('rate_name'));$i++)
            {
                //if name = "" not add
                if(Input::get('rate_name.'.$i)!="")
                {
                    $rate = (Input::get('rate_id.'.$i)==0) ? new LocationRates : LocationRates::find(Input::get('rate_id.'.$i));
                    $rate->location_id =  $location->id;
                    $rate->name = Input::get('rate_name.'.$i);
                    $rate->cost_price = Input::get('rate_cost.'.$i);
                    $rate->sale_price = Input::get('rate_sale.'.$i);
                    if (Input::get('rate_id.'.$i)==0)
                    {
                        $rate->created_by = Auth::user()->id;
                    }
                    $rate->updated_by = Auth::user()->id;

                    array_push($rates, $rate);
                }
            }

            if ($location->rates()->saveMany($rates))
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
                'error' => $rates->error()
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

    //delete data with contact
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
            $location = Location::find(input::get('id'));

            if ($location->rates()->delete())
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
                'error' => $location->error()
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

    public function postRateDelete()
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
            $rate = LocationRates::find(input::get('id'));

            if ($rate->delete())
            {
                //response success
                $response = array(
                    'status' => 'success',
                    'id' => input::get('id'),
                    'msg' => 'ลบสำเร็จแล้ว'
                );

                return Response::json( $response );
            }
            //response error
            $response = array(
                'status' => 'error',
                'msg' => 'Error ลบไม่สำเร็จ',
                'error' => $rate->error()
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

        $data = Location::find(input::get('id'));
        $data['have_rates'] = $data->rates->count();
        $data['rates'] = $data->rates;

        $response = array(
            'data' => $data
        );

        return Response::json( $response );
    }


    public function getData()
    {
        $conferences = Location::select(array('id', 'name', 'id as rates', 'created_at'))
                        ->where('is_conference', '=', 1);

        return Datatables::of($conferences)

            ->edit_column('rates', function($row){

                $rate_count = LocationRates::select('cost_price')->where('location_id', '=', $row->id)->count();

                return ($rate_count==0) ? 'No' : 'Yes';
            })

            ->add_column('actions', function($row){
                $rate_count = LocationRates::select('cost_price')->where('location_id', '=', $row->id)->count();

                if($rate_count==0)
                {
                    return '<a onclick="openEdit('.$row->id.',0);" href="javascript:;" class="btn btn-primary btn-xs" ><span class="fa fa-plus"></span> เพิ่มราคา</a>';
                }
                else
                {
                    return '<a onclick="openEdit('.$row->id.',1);" href="javascript:;" class="btn btn-default btn-xs" ><span class="fa fa-pencil-square-o"></span> แก้ไขราคา</a>
            <a onclick="return openDelete('.$row->id.');" href="javascript:;" class="btn btn-xs btn-danger"><span class="fa fa-trash-o"></span> ลบราคา</a>';
                }
            })

            ->remove_column('id')

            ->make();
    }

}