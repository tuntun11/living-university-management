<?php

class AdminLocationController extends AdminController {

    protected $location, $mflf_location;

    public function __construct(Location $location, MflfLocation $mflf_location)
    {
        parent::__construct();
        $this->location = $location;
        $this->mflf_location = $mflf_location;
    }

    public function getIndex()
    {
        //mflf area
        $areas = MflfArea::all();
        //send province locations
        $provinces = Province::select(array('PROVINCE_ID', 'PROVINCE_NAME'))->orderBy('PROVINCE_NAME')->get();
        // return view
        return View::make('svms/admin/location', compact('areas', 'provinces'));
    }

    //get area as json
    public function getArea()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        $area = MflfArea::find(Input::get('id'));

        $response = array(
            'data' => $area
        );

        return Response::json( $response );
    }

    //get Amphur by province
    public function getAmphur()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        $amphurs = Amphur::select(array('AMPHUR_ID AS id', 'AMPHUR_NAME AS text'))->where('PROVINCE_ID', '=', Input::get( 'province_id' ))->get();

        $response = array(
            'data' => $amphurs
        );

        return Response::json( $response );
    }

    //get District by amphur
    public function getDistrict()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        $districts = District::select(array('DISTRICT_ID AS id', 'DISTRICT_NAME AS text'))->where('AMPHUR_ID', '=', Input::get( 'amphur_id' ))->get();

        $response = array(
            'data' => $districts
        );

        return Response::json( $response );
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
            'province'   => 'required',
            'city'   => 'required',
            'district'   => 'required',
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            // Start transaction!
            DB::beginTransaction();

            try
            {
                $this->location->name = Input::get('name');
                $this->location->province = Input::get('province');
                $this->location->city = Input::get('city');
                $this->location->district = Input::get('district');
                $this->location->geo = Input::get('gmap');
                $this->location->is_accommodation = Input::get('is_accommodation');
                $this->location->is_restaurant = Input::get('is_restaurant');
                $this->location->is_conference = Input::get('is_conference');
                $this->location->created_by = Auth::user()->id;
                $this->location->updated_by = Auth::user()->id;

                if ($this->location->save())
                {
                    //if have area selected create mflf_location too
                    if (Input::get('area')!="")
                    {
                        $this->mflf_location->location_id = $this->location->id;
                        $this->mflf_location->mflf_area_id = Input::get('area');
                        $this->mflf_location->created_by = Auth::user()->id;
                        $this->mflf_location->updated_by = Auth::user()->id;
                        $this->mflf_location->save();
                    }

                    //Request Commit and send response
                    DB::commit();

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
                    'error' => $this->location->error()
                );

                return Response::json( $response );
            }
            catch(\Exception $e)
            {
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
            'id' => 'required',
            'name'   => 'required',
            'province'   => 'required',
            'city'   => 'required',
            'district'   => 'required',
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            // Start transaction!
            DB::beginTransaction();

            try
            {
                $location = Location::find(input::get('id'));

                $location->name = Input::get('name');
                $location->province = Input::get('province');
                $location->city = Input::get('city');
                $location->district = Input::get('district');
                $location->geo = Input::get('gmap');
                $location->is_accommodation = Input::get('is_accommodation');
                $location->is_restaurant = Input::get('is_restaurant');
                $location->is_conference = Input::get('is_conference');
                $location->updated_by = Auth::user()->id;

                if ($location->save())
                {
                    $mflf_location = MflfLocation::where('location_id', '=', $location->id)->first();
                    if ($mflf_location)
                    {
                        //หากแก้ไขว่าไม่ได้อยู่ในแม่ฟ้าหลวง ลบออก
                        if (Input::get('area')=="")
                        {
                            $del_mflf_location = MflfLocation::find($mflf_location->id);
                            $del_mflf_location->delete();
                        }
                        else
                        {
                            //edit ปกติ
                            $edit_mflf_location = MflfLocation::find($mflf_location->id);
                            $edit_mflf_location->mflf_area_id = Input::get('area');
                            $edit_mflf_location->updated_by = Auth::user()->id;
                            $edit_mflf_location->updated_at = date('Y-m-d H:i:s');
                            $edit_mflf_location->save();
                        }

                    }
                    else
                    {
                        //หากยังไม่เคยระบุว่าเป็นของแม่ฟ้าหลวง หากเลือกว่าเป็นให้เซฟใหม่
                        if (Input::get('area')!="")
                        {
                            $this->mflf_location->location_id = $location->id;
                            $this->mflf_location->mflf_area_id = Input::get('area');
                            $this->mflf_location->created_by = Auth::user()->id;
                            $this->mflf_location->updated_by = Auth::user()->id;
                            $this->mflf_location->save();
                        }
                        //หากยังไม่เคยระบุว่าเป็นของแม่ฟ้าหลวง หากไม่ได้เลือกข้ามไป
                    }

                    //Request Commit and send response
                    DB::commit();

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
                    'error' => $location->error()
                );

                return Response::json( $response );
            }
            catch(\Exception $e)
            {
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
            $location = Location::find(input::get('id'));
            //also delete relation too
            $mflf_location = DB::table('mflf_locations')->where('location_id', '=', $location->id)->delete();

            if ($location->delete() && $mflf_location)
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

    public function getById()
    {
        //check if its our form
        if ( Session::token() !== Input::get( '_token' ) ) {
            return Response::json( array(
                'msg' => 'Unauthorized attempt to create setting'
            ) );
        }

        $location = Location::where('id', '=', input::get('id'))->first();

        $location['area'] = MflfLocation::where('location_id', '=', input::get('id'))->pluck('mflf_area_id');

        $response = array(
            'data' => $location
        );

        return Response::json( $response );
    }

    public function getData()
    {
        $locations = Location::select(array('id', 'name', 'province', 'city', 'district', 'locations.id AS area', 'created_at'));

        return Datatables::of($locations)

            ->edit_column('province', '{{ DB::table(\'provinces\')->where(\'PROVINCE_ID\', \'=\', $province)->pluck(\'PROVINCE_NAME\') }}')

            ->edit_column('city', '{{ DB::table(\'amphurs\')->where(\'AMPHUR_ID\', \'=\', $city)->pluck(\'AMPHUR_NAME\') }}')

            ->edit_column('district', '{{ DB::table(\'districts\')->where(\'DISTRICT_ID\', \'=\', $district)->pluck(\'DISTRICT_NAME\') }}')

            ->edit_column('area', function($row){
                $mflf_area = MflfArea::leftJoin('mflf_locations as l', 'mflf_areas.id', '=', 'l.mflf_area_id')
                    ->where('l.location_id', '=', $row->id)->pluck('name');

                return ($mflf_area) ? $mflf_area : 'ไม่ได้ระบุ';
            })

            ->add_column('actions', '<a onclick="openEdit({{ $id }});" href="javascript:;" class="btn btn-default btn-xs" ><span class="fa fa-pencil-square-o"></span> แก้ไข</a>
                 <a onclick="return openDelete({{ $id }});" href="javascript:;" class="btn btn-xs btn-danger"><span class="fa fa-trash-o"></span> ลบ</a>
            ')

            ->remove_column('id', 'created_at')

            ->make();
    }

}