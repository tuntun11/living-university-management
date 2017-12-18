<?php

class AdminExpertController extends \AdminController {

    protected $personnel_type;

    protected $personnel_type_rates;

    public function __construct(PersonnelType $personnel_type, PersonnelTypeRates $personnel_type_rates)
    {
        parent::__construct();
        $this->personnel_type = $personnel_type;
        $this->personnel_type_rates = $personnel_type_rates;
    }

    public function getIndex()
    {
        return View::make('svms/admin/expert');
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
            'unit'   => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            // Start transaction!
            DB::beginTransaction();

            try {
                $this->personnel_type->name = Input::get('name');
                $this->personnel_type->unit = Input::get('unit');
                $this->personnel_type->priority = Input::get('priority');
                $this->personnel_type->created_by = Auth::user()->id;
                $this->personnel_type->updated_by = Auth::user()->id;

                $rates = array();
                //add rates
                for($i=0;$i<count(Input::get('rate_name'));$i++)
                {
                    //if name = "" not add
                    if(Input::get('rate_name.'.$i)!="")
                    {
                        $rate = new PersonnelTypeRates;
                        $rate->personnel_type_id =  $this->personnel_type->id;
                        $rate->name = Input::get('rate_name.'.$i);
                        $rate->cost_price = Input::get('rate_cost.'.$i);
                        $rate->sale_price = Input::get('rate_sale.'.$i);
                        $rate->created_by = Auth::user()->id;
                        $rate->updated_by = Auth::user()->id;

                        array_push($rates, $rate);
                    }
                }

                if ($this->personnel_type->save() && $this->personnel_type->rates()->saveMany($rates))
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
            'unit'   => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            // Start transaction!
            DB::beginTransaction();

            try {
                $personnel_type = PersonnelType::find(input::get('old_id'));

                $personnel_type->name = Input::get('name');
                $personnel_type->unit = Input::get('unit');
                $personnel_type->priority = Input::get('priority');
                $personnel_type->updated_by = Auth::user()->id;

                $rates = array();
                //add rates
                for($i=0;$i<count(Input::get('rate_name'));$i++)
                {
                    //if name = "" not add
                    if(Input::get('rate_name.'.$i)!="")
                    {
                        $rate = (Input::get('rate_id.'.$i)==0) ? new PersonnelTypeRates : PersonnelTypeRates::find(Input::get('rate_id.'.$i));
                        $rate->personnel_type_id =  $personnel_type->id;
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

                if ($personnel_type->save() && $personnel_type->rates()->saveMany($rates))
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
            $personnel_type = PersonnelType::find(input::get('id'));

            if ($personnel_type->delete() && $personnel_type->rates()->delete())
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
                'error' => $personnel_type->error()
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
            $rate = PersonnelTypeRates::find(input::get('id'));

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

        $data = PersonnelType::find(Input::get( 'id' ));
        $data['rates'] = $data->rates;

        $response = array(
            'data' => $data
        );

        return Response::json( $response );
    }

    public function getData()
    {
        $types = PersonnelType::select(array('id', 'name', 'unit', 'priority', 'created_at'));

        return Datatables::of($types)

            ->add_column('actions', '<a onclick="openEdit({{ $id }});" href="javascript:;" class="btn btn-default btn-xs" ><span class="fa fa-pencil-square-o"></span> แก้ไข/ราคา</a>
            <a onclick="return openDelete({{ $id }});" href="javascript:;" class="btn btn-xs btn-danger"><span class="fa fa-trash-o"></span> ลบ</a>
        ')

            ->remove_column('id')

            ->make();
    }

}