<?php

class AdminCarsController extends \AdminController {

    protected $car_facilitator;

    protected $cars;

    protected $car_rates;

    public function __construct(CarFacilitator $car_facilitator, Cars $cars, CarRates $car_rates)
    {
        parent::__construct();
        $this->car_facilitator = $car_facilitator;
        $this->cars = $cars;
        $this->car_rates = $car_rates;
    }

    public function getIndex()
    {
        $facilitators = DB::table('car_facilitators')->select('id', 'name')->get();
        // return
        return View::make('svms/admin/transport/cars', array('facilitators' => $facilitators));
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
            'car_facilitator_id'   => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            // Start transaction!
            DB::beginTransaction();

            try {
                $this->cars->name = Input::get('name');
                $this->cars->unit = Input::get('unit');
                $this->cars->car_facilitator_id = Input::get('car_facilitator_id');
                $this->cars->created_by = Auth::user()->id;
                $this->cars->updated_by = Auth::user()->id;

                $rates = array();
                //add rates
                for($i=0;$i<count(Input::get('rate_name'));$i++)
                {
                    //if name = "" not add
                    if(Input::get('rate_name.'.$i)!="")
                    {
                        $rate = new CarRates;
                        $rate->car_id =  $this->cars->id;
                        $rate->name = Input::get('rate_name.'.$i);
                        $rate->cost_price = Input::get('rate_cost.'.$i);
                        $rate->sale_price = Input::get('rate_sale.'.$i);
                        $rate->created_by = Auth::user()->id;
                        $rate->updated_by = Auth::user()->id;

                        array_push($rates, $rate);
                    }
                }

                if ($this->cars->save() && $this->cars->rates()->saveMany($rates))
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
                else
                {
                    throw new Exception("Error สร้างรายการไม่สำเร็จ");
                }
            }
            catch(\Exception $e) {
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
            'old_id'  => 'required',
            'name'   => 'required',
            'unit'   => 'required',
            'car_facilitator_id'   => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            // Start transaction!
            DB::beginTransaction();

            try {
                $cars = Cars::find(input::get('old_id'));

                $cars->name = Input::get('name');
                $cars->unit = Input::get('unit');
                $cars->car_facilitator_id = Input::get('car_facilitator_id');
                $cars->updated_by = Auth::user()->id;

                $rates = array();
                //add rates
                for($i=0;$i<count(Input::get('rate_name'));$i++)
                {
                    //if name = "" not add
                    if(Input::get('rate_name.'.$i)!="")
                    {
                        $rate = (Input::get('rate_id.'.$i)==0) ? new CarRates : CarRates::find(Input::get('rate_id.'.$i));
                        $rate->car_id =  $cars->id;
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

                if ($cars->save() && $cars->rates()->saveMany($rates))
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
                else
                {
                    throw new Exception("Error แก้ไขรายการไม่สำเร็จ");
                }
            }
            catch(\Exception $e) {
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
            $cars = Cars::find(input::get('id'));

            if ($cars->delete() && $cars->rates()->delete())
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
                'error' => $cars->error()
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
            $rate = CarRates::find(input::get('id'));

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

        $data = Cars::find(Input::get( 'id' ));
        $data['rates'] = $data->rates;

        $response = array(
            'data' => $data
        );

        return Response::json( $response );
    }

    public function getData()
    {
        $cars = Cars::leftjoin('car_facilitators', 'cars.car_facilitator_id', '=', 'car_facilitators.id')
            ->select(array('cars.id as car_id', 'car_facilitators.name as fac_name', 'cars.name', 'cars.created_at'));

        if (Input::get('car_facilitator_id')!="")
        {
            $cars = $cars->where('cars.car_facilitator_id', '=', Input::get('car_facilitator_id'));
        }

        return Datatables::of($cars)

            ->add_column('actions', '<a onclick="openEdit({{ $car_id }});" href="javascript:;" class="btn btn-default btn-xs" ><span class="fa fa-pencil-square-o"></span> แก้ไข/ราคา</a>
            <a onclick="return openDelete({{ $car_id }});" href="javascript:;" class="btn btn-xs btn-danger"><span class="fa fa-trash-o"></span> ลบ</a>
        ')

            ->remove_column('car_id')

            ->make();
    }

}