<?php

class AdminAccomController extends \AdminController {

    protected $location;

    protected $accommodation;

    protected $location_contacts;

    public function __construct(Location $location, Accommodation $accommodation, LocationContacts $location_contacts)
    {
        parent::__construct();
        $this->accommodation = $accommodation;
        $this->location = $location;
        $this->location_contacts = $location_contacts;
    }

    public function getIndex()
    {
        // return
        return View::make('svms/admin/accommodation/index');
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
            $this->accommodation->name = Input::get('name');
            $this->accommodation->address = Input::get('address');
            $this->accommodation->note = Input::get('note');
            $this->accommodation->location_id = Input::get('location_id');
            $this->accommodation->created_by = Auth::user()->id;
            $this->accommodation->updated_by = Auth::user()->id;

            if ($this->accommodation->save())
            {
                //add contact from array
                if (count(Input::get('contacts'))>0)
                {
                    $contacts = array();
                    //Coordinators Data
                    for($i=0;$i<count(Input::get('contacts'));$i++){

                        $contact = new AccomContacts;

                        $contact->name = Input::get('contacts.'.$i.'.name');
                        $contact->tel = Input::get('contacts.'.$i.'.tel');
                        $contact->email = Input::get('contacts.'.$i.'.email');

                        array_push($contacts, $contact);
                    }

                    $accommodation = Accommodation::find($this->accommodation->id);

                    if ($accommodation->contacts()->saveMany($contacts))
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
                        'error' => $this->accommodation->error()
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
            'id' => 'required',
            'name'   => 'required',
            'location_id'   => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $accommodation = Accommodation::find(input::get('id'));

            $accommodation->name = Input::get('name');
            $accommodation->address = Input::get('address');
            $accommodation->note = Input::get('note');
            $accommodation->location_id = Input::get('location_id');
            $accommodation->updated_by = Auth::user()->id;

            if ($accommodation->save())
            {
                //add contact from array
                if (count(Input::get('contacts'))>0)
                {
                    $contacts = array();
                    //Coordinators Data
                    for($i=0;$i<count(Input::get('contacts'));$i++){

                        $contact = (AccomContacts::find(Input::get('contacts.'.$i.'.id'))) ? AccomContacts::find(Input::get('contacts.'.$i.'.id')) : new AccomContacts;

                        $contact->name = Input::get('contacts.'.$i.'.name');
                        $contact->tel = Input::get('contacts.'.$i.'.tel');
                        $contact->email = Input::get('contacts.'.$i.'.email');

                        array_push($contacts, $contact);
                    }

                    $accommodation = Accommodation::find($accommodation->id);

                    if ($accommodation->contacts()->saveMany($contacts))
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
                        'error' => $this->accommodation->error()
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
            $accommodation = Location::find(input::get('id'));
            $accommodation->contacts()->delete();
            $accommodation->rooms()->delete();

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
            $accom_contacts = AccomContacts::find(input::get('id'));

            $id = $accom_contacts->id;

            if ($accom_contacts->delete())
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
                'error' => $accom_contacts->error()
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

        $data = Location::where('id', '=', input::get('id'))->first();

        $contacts = $data->contacts()->get();

        $response = array(
            'data' => $data,
            'contacts' => $contacts
        );

        return Response::json( $response );
    }


    public function getData()
    {
        $accommodations = Location::select(array('id', 'name', 'created_by', 'created_at'))
                            ->whereIsAccommodation(1);

        return Datatables::of($accommodations)

            ->edit_column('created_by', '{{ DB::table(\'users\')->where(\'id\', \'=\', $created_by)->pluck(\'username\') }}')

            ->add_column('actions', '<a onclick="openEdit({{ $id }});" href="javascript:;" class="btn btn-default btn-xs" ><i class="fa fa-mobile"></i> ข้อมูลติดต่อ</a>
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
            $accommodations = Accommodation::select(array('id', 'name', 'created_by', 'created_at'))->where('location_id', '=', Input::get('location_id'));

            return Datatables::of($accommodations)

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