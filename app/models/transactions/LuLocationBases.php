<?php

class LuLocationBases extends Eloquent {

    protected $table = 'lu_location_bases';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function party()
    {
        return $this->belongsTo('Party', 'party_id');
    }

    public function area()
    {
        return $this->belongsTo('MflfArea', 'mflf_area_id');
    }

}