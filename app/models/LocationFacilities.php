<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class LocationFacilities extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'location_facilities';
    protected $fillable = ['name'];
    protected $guarded = ['id'];

    public function location()
    {
        return $this->belongsTo('Location', 'location_id');
    }

}