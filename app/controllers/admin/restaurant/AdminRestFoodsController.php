<?php

class AdminRestFoodsController extends \AdminController {

    protected $location;

    protected $food;

    public function __construct(Location $location, Food $food)
    {
        parent::__construct();
        $this->location = $location;
        $this->food = $food;
    }

    public function getIndex()
    {
        $locations = $this->location->getAllLocation()->get();
        // return
        return View::make('svms/admin/restaurant/foods', compact('locations'));
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
            $this->food->name = Input::get('name');
            $this->food->unit = Input::get('unit');
            $this->food->location_id = Input::get('location_id');
            $this->food->cost_price = Input::get('cost_price');
            $this->food->sale_price = Input::get('sale_price');
            $this->food->created_by = Auth::user()->id;
            $this->food->updated_by = Auth::user()->id;

            if ($this->food->save())
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
                'error' => $this->food->error()
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
            $food = Food::find(input::get('id'));

            $food->name = Input::get('name');
            $food->unit = Input::get('unit');
            $food->location_id = Input::get('location_id');
            $food->cost_price = Input::get('cost_price');
            $food->sale_price = Input::get('sale_price');
            $food->updated_by = Auth::user()->id;

            if ($food->save())
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
                'error' => $food->error()
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

        $data = Food::find(Input::get( 'id' ));

        $response = array(
            'data' => $data
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
            $food = Food::find(input::get('id'));

            if ($food->delete())
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
                'error' => $food->error()
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

    public function getData()
    {
        $food = Food::leftjoin('locations AS l', 'foods.location_id', '=', 'l.id')
                        ->select(array('foods.id', 'l.id as location_id', 'foods.name', 'foods.cost_price', 'foods.sale_price', 'foods.created_at'));

        if (Input::get('location_id')!="")
        {
            $food = $food->where('foods.location_id', '=', Input::get('location_id'));
        }

        return Datatables::of($food)

            ->edit_column('location_id', '{{ DB::table(\'locations\')->where(\'id\', \'=\', $location_id)->pluck(\'name\') }}')

            ->add_column('actions', '<a onclick="openEdit({{ $id }});" href="javascript:;" class="btn btn-default btn-xs" ><span class="fa fa-pencil-square-o"></span> แก้ไข</a>
            <a onclick="return openDelete({{ $id }});" href="javascript:;" class="btn btn-xs btn-danger"><span class="fa fa-trash-o"></span> ลบ</a>
        ')

            ->remove_column('id')

            ->make();
    }

}