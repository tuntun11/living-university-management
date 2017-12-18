<?php

class AdminRestController extends \AdminController {

    protected $location;

    protected $restaurant;

    protected $restaurant_contacts;

    public function __construct(Location $location, Restaurant $restaurant, RestaurantContacts $restaurant_contacts)
    {
        parent::__construct();
        $this->restaurant = $restaurant;
        $this->location = $location;
        $this->restaurant_contacts = $restaurant_contacts;
    }

    public function getIndex()
    {
        $locations = $this->location->getAllLocation()->get();
        // return
        return View::make('svms/admin/restaurant/index', compact('locations'));
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
            'location_id'   => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $this->restaurant->name = Input::get('name');
            $this->restaurant->address = Input::get('address');
            $this->restaurant->note = Input::get('note');
            $this->restaurant->location_id = Input::get('location_id');
            $this->restaurant->created_by = Auth::user()->id;
            $this->restaurant->updated_by = Auth::user()->id;

            if ($this->restaurant->save())
            {
                //add contact from array
                if (count(Input::get('contacts'))>0)
                {
                    $contacts = array();
                    //Coordinators Data
                    for($i=0;$i<count(Input::get('contacts'));$i++){

                        $contact = new RestaurantContacts;

                        $contact->name = Input::get('contacts.'.$i.'.name');
                        $contact->tel = Input::get('contacts.'.$i.'.tel');
                        $contact->email = Input::get('contacts.'.$i.'.email');

                        array_push($contacts, $contact);
                    }

                    $restaurant = Restaurant::find($this->restaurant->id);

                    if ($restaurant->contacts()->saveMany($contacts))
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
                        'msg' => 'Error ไม่สามารถสร้าง Contact ได้',
                        'error' => $this->restaurant->error()
                    );

                    return Response::json( $response );

                }

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
                'error' => $this->restaurant->error()
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
            'id' => 'required',
            'name'   => 'required',
            'location_id'   => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $restaurant = Restaurant::find(input::get('id'));

            $restaurant->name = Input::get('name');
            $restaurant->address = Input::get('address');
            $restaurant->note = Input::get('note');
            $restaurant->location_id = Input::get('location_id');
            $restaurant->updated_by = Auth::user()->id;

            if ($restaurant->save())
            {
                //add contact from array
                if (count(Input::get('contacts'))>0)
                {
                    $contacts = array();
                    //Coordinators Data
                    for($i=0;$i<count(Input::get('contacts'));$i++){

                        $contact = (RestaurantContacts::find(Input::get('contacts.'.$i.'.id'))) ? RestaurantContacts::find(Input::get('contacts.'.$i.'.id')) : new RestaurantContacts;

                        $contact->name = Input::get('contacts.'.$i.'.name');
                        $contact->tel = Input::get('contacts.'.$i.'.tel');
                        $contact->email = Input::get('contacts.'.$i.'.email');

                        array_push($contacts, $contact);
                    }

                    $restaurant = Restaurant::find($restaurant->id);

                    if ($restaurant->contacts()->saveMany($contacts))
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
                        'msg' => 'Error ไม่สามารถปรับปรุง Contact ได้',
                        'error' => $restaurant->error()
                    );

                    return Response::json( $response );

                }

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
                'error' => $restaurant->error()
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
            $restaurant = Restaurant::find(input::get('id'));
            $restaurant->contacts()->delete();
            $restaurant->foods()->delete();

            if ($restaurant->delete())
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
                'error' => $restaurant->error()
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

    //delete contact only
    public function postContactDelete()
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
            $restaurant_contacts = RestaurantContacts::find(input::get('id'));

            $id = $restaurant_contacts->id;

            if ($restaurant_contacts->delete())
            {
                //response success
                $response = array(
                    'status' => 'success',
                    'msg' => 'ลบสำเร็จแล้ว',
                    'id' => $id
                );

                return Response::json( $response );
            }
            //response error
            $response = array(
                'status' => 'error',
                'msg' => 'Error ลบไม่สำเร็จ',
                'error' => $restaurant_contacts->error()
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

        $data = Restaurant::where('id', '=', input::get('id'))->first();

        $contacts = $data->contacts()->get();

        $response = array(
            'data' => $data,
            'contacts' => $contacts
        );

        return Response::json( $response );
    }


    public function getData()
    {
        $restaurants = Restaurant::select(array('id', 'location_id', 'name', 'created_by', 'created_at'));

        return Datatables::of($restaurants)

            ->edit_column('location_id', '{{ Location::find($location_id)->getLocationInfo() }}')

            ->edit_column('created_by', '{{ DB::table(\'users\')->where(\'id\', \'=\', $created_by)->pluck(\'username\') }}')

            ->add_column('actions', '<a onclick="openEdit({{ $id }});" href="javascript:;" class="btn btn-default btn-xs" ><span class="fa fa-pencil-square-o"></span> แก้ไข</a>
            <a onclick="return openDelete({{ $id }});" href="javascript:;" class="btn btn-xs btn-danger"><span class="fa fa-trash-o"></span> ลบ</a>
        ')

            ->remove_column('id')

            ->make();
    }

    public function getDataByLocation()
    {
        // Declare the rules for the form validation
        $rules = array(
            'location_id' => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $restaurants = Restaurant::select(array('id', 'name', 'created_by', 'created_at'))->where('location_id', '=', Input::get('location_id'));

            return Datatables::of($restaurants)

                ->edit_column('created_by', '{{ DB::table(\'users\')->where(\'id\', \'=\', $created_by)->pluck(\'username\') }}')

                ->add_column('actions', '<a onclick="openEdit({{ $id }});" href="javascript:;" class="btn btn-default btn-xs" ><span class="fa fa-pencil-square-o"></span> แก้ไข</a>
                <a href="{{{ URL::to(\'admin/blogs/\' . $id . \'/delete\' ) }}}" class="btn btn-xs btn-danger"><span class="fa fa-trash-o"></span> ลบ</a>
            ')

                ->remove_column('id')

                ->make();
        }
        return;
    }

}