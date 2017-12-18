<?php

class AdminCarFacController extends \AdminController {

    protected $location;

    protected $car_facilitator;

    protected $car_fac_contacts;

    public function __construct(Location $location, CarFacilitator $car_facilitator, CarFacContacts $car_fac_contacts)
    {
        parent::__construct();
        $this->car_facilitator = $car_facilitator;
        $this->location = $location;
        $this->car_fac_contacts = $car_fac_contacts;
    }

    public function getIndex()
    {
        $locations = $this->location->getAllLocation()->get();
        // return
        return View::make('svms/admin/transport/index', compact('locations'));
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
            $this->car_facilitator->name = Input::get('name');
            $this->car_facilitator->address = Input::get('address');
            $this->car_facilitator->note = Input::get('note');
            $this->car_facilitator->is_mflf = Input::get('is_mflf');
            $this->car_facilitator->created_by = Auth::user()->id;
            $this->car_facilitator->updated_by = Auth::user()->id;

            if ($this->car_facilitator->save())
            {
                //add contact from array
                if (count(Input::get('contacts'))>0)
                {
                    $contacts = array();
                    //Coordinators Data
                    for($i=0;$i<count(Input::get('contacts'));$i++){

                        $contact = new CarFacContacts;

                        $contact->name = Input::get('contacts.'.$i.'.name');
                        $contact->tel = Input::get('contacts.'.$i.'.tel');
                        $contact->email = Input::get('contacts.'.$i.'.email');

                        array_push($contacts, $contact);
                    }

                    $car_facilitator = CarFacilitator::find($this->car_facilitator->id);

                    if ($car_facilitator->contacts()->saveMany($contacts))
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
                        'error' => $this->car_facilitator->error()
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
                'error' => $this->car_facilitator->error()
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
            'name'   => 'required'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $car_facilitator = CarFacilitator::find(input::get('id'));

            $car_facilitator->name = Input::get('name');
            $car_facilitator->address = Input::get('address');
            $car_facilitator->note = Input::get('note');
            $car_facilitator->is_mflf = Input::get('is_mflf');
            $car_facilitator->updated_by = Auth::user()->id;

            if ($car_facilitator->save())
            {
                //add contact from array
                if (count(Input::get('contacts'))>0)
                {
                    $contacts = array();
                    //Coordinators Data
                    for($i=0;$i<count(Input::get('contacts'));$i++){

                        $contact = (CarFacContacts::find(Input::get('contacts.'.$i.'.id'))) ? CarFacContacts::find(Input::get('contacts.'.$i.'.id')) : new CarFacContacts;

                        $contact->name = Input::get('contacts.'.$i.'.name');
                        $contact->tel = Input::get('contacts.'.$i.'.tel');
                        $contact->email = Input::get('contacts.'.$i.'.email');

                        array_push($contacts, $contact);
                    }

                    $car_facilitator = CarFacilitator::find($car_facilitator->id);

                    if ($car_facilitator->contacts()->saveMany($contacts))
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
                        'error' => $car_facilitator->error()
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
                'error' => $car_facilitator->error()
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
            $car_facilitator = CarFacilitator::find(input::get('id'));
            $car_facilitator->contacts()->delete();
            $car_facilitator->cars()->delete();

            if ($car_facilitator->delete())
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
                'error' => $car_facilitator->error()
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
            $car_fac_contacts = CarFacContacts::find(input::get('id'));

            $id = $car_fac_contacts->id;

            if ($car_fac_contacts->delete())
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
                'error' => $car_fac_contacts->error()
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

        $data = CarFacilitator::where('id', '=', input::get('id'))->first();

        $contacts = $data->contacts()->get();

        $response = array(
            'data' => $data,
            'contacts' => $contacts
        );

        return Response::json( $response );
    }


    public function getData()
    {
        $car_facilitators = CarFacilitator::select(array('id', 'name', 'address', 'note', 'is_mflf', 'created_at', 'car_facilitator_id'));

        return Datatables::of($car_facilitators)

            ->edit_column('is_mflf', function($row){
                return ($row->is_mflf==1) ? 'ใช่' : 'ไม่ใช่';
            })

            ->add_column('actions', '<a onclick="openEdit({{ $id }});" href="javascript:;" class="btn btn-default btn-xs" ><span class="fa fa-pencil-square-o"></span> แก้ไข</a>
            <a onclick="return openDelete({{ $id }});" href="javascript:;" class="btn btn-xs btn-danger"><span class="fa fa-trash-o"></span> ลบ</a>
        ')

            ->remove_column('id')

            ->make();
    }

}