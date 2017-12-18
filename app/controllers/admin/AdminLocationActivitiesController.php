<?php

class AdminLocationActivitiesController extends \AdminController {

    protected $location;

    protected $location_activities;

    public function __construct(Location $location, LocationActivities $location_activities)
    {
        parent::__construct();
        $this->location = $location;
        $this->location_activities = $location_activities;
    }

    public function getIndex()
    {
        $locations = $this->location->getAllLocation()->get();
        // return
        return View::make('svms/admin/location_activities', compact('locations'));
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
            'title_th'   => 'max:255',
			'title_en'   => 'max:255',
			'note_th'   => 'max:1000',
			'note_en'   => 'max:1000'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $this->location_activities->title_th = Input::get('title_th');
            $this->location_activities->title_en = Input::get('title_en');
			$this->location_activities->note_th = Input::get('note_th');
            $this->location_activities->note_en = Input::get('note_en');
            $this->location_activities->location_id = Input::get('location_id');
            $this->location_activities->created_by = Auth::user()->id;
            $this->location_activities->updated_by = Auth::user()->id;

            if ($this->location_activities->save())
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
                'error' => $this->location_activities->error()
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
            'id'   => 'required',
            'title_th'   => 'max:255',
			'title_en'   => 'max:255',
			'note_th'   => 'max:1000',
			'note_en'   => 'max:1000'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $location_activities = LocationActivities::find(input::get('id'));
		
            $location_activities->title_th = Input::get('title_th');
            $location_activities->title_en = Input::get('title_en');
			$location_activities->note_th = Input::get('note_th');
            $location_activities->note_en = Input::get('note_en');
            $location_activities->location_id = Input::get('location_id');
            $location_activities->updated_by = Auth::user()->id;
			$location_activities->updated_at = date('Y-m-d H:i:s');

            if ($location_activities->save())
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
                'error' => $location_activities->error()
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
            $location_activities = LocationActivities::find(input::get('id'));

            if ($location_activities->delete())
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
                'error' => $location_activities->error()
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

        $data = LocationActivities::find(Input::get( 'id' ));

        $response = array(
            'data' => $data
        );

        return Response::json( $response );
    }

    public function getData()
    {
        $activities = LocationActivities::select(array('location_activities.id AS id', 'location_activities.location_id AS location', 'location_activities.title_th', 'location_activities.note_th', 'location_activities.title_en', 'location_activities.note_en', 'location_activities.id AS actions'));

        return Datatables::of($activities)
		
			->edit_column('location', function($row){
				
				$location = Location::find($row->location);
				
				return ($location) ? $location->name : 'ไม่ระบุ';
			})
			
            ->remove_column('id')

            ->make(true);
    }

}