<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Location extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'locations';
    protected $fillable = ['name', 'country', 'province', 'city', 'district', 'geo'];
    protected $guarded = ['id'];

    //classroom base for personnel
    public function personnelClassrooms()
    {
        return $this->hasMany('PersonnelClassroomBases');
    }

    //facility of location
    public function facilities()
    {
        return $this->hasMany('LocationFacilities');
    }

    //rates of location or conference
    public function rates()
    {
        return $this->hasMany('LocationRates');
    }
	
	//activities or event
    public function activities()
    {
        return $this->hasMany('LocationActivities');
    }

    //many schedule task location
    public function scheduleTasks()
    {
        return $this->hasMany('LuScheduleTaskLocation');
    }

    //is accommodation
    public function isAccommodation()
    {
        return $this->whereIsAccommodation(1);
    }

    //is conference
    public function isConference()
    {
        return $this->whereIsConference(1);
    }

    //is restaurant
    public function isRestaurant()
    {
        return $this->whereisRestaurant(1);
    }

    //related with accommodation
    public function accommodations()
    {
        return $this->hasMany('Accommodation');
    }

    //related with conference
    public function conferences()
    {
        return $this->hasMany('Conference');
    }

    //related with restaurents
    public function restaurants()
    {
        return $this->hasMany('Restaurant');
    }

    //related with contacts
    public function contacts()
    {
        return $this->hasMany('LocationContacts');
    }

    public function getAllLocation()
    {
        /*$array_locations = array();
        $locations = $this;

        foreach($locations as $location)
        {
            $array_location = array();
            $array_location['id'] = $location->id;
            $array_location['name'] = $location->name;
            $array_location['province_name'] = ($location->province==NULL);
            $array_location['amphur_name'] = ($location->city==NULL);
            $array_location['district_name'] = ($location->district==NULL);

            array_push($array_locations, $array_location);
        }

        return $array_locations;*/
        return $this->leftjoin('provinces', 'provinces.PROVINCE_ID', '=', 'locations.province')
                ->leftjoin('amphurs', 'amphurs.AMPHUR_ID', '=', 'locations.city')
                ->leftjoin('districts', 'districts.DISTRICT_ID', '=', 'locations.district')
                ->select(array('locations.id', 'locations.name', 'provinces.PROVINCE_NAME as province_name', 'amphurs.AMPHUR_NAME as amphur_name', 'districts.DISTRICT_NAME as district_name'));
    }

    public function getLocationInfo()
    {
        return $this->name.', ต.'.$this->district($this->district).' อ.'.$this->city($this->city).' จ.'.$this->province($this->province);
    }

    function province($province_id)
    {
        return DB::table('provinces')->where('PROVINCE_ID', '=', $province_id)->pluck('PROVINCE_NAME');
    }

    function city($amphur_id)
    {
        return DB::table('amphurs')->where('AMPHUR_ID', '=', $amphur_id)->pluck('AMPHUR_NAME');
    }

    function district($district_id)
    {
        return DB::table('districts')->where('DISTRICT_ID', '=', $district_id)->pluck('DISTRICT_NAME');
    }

}