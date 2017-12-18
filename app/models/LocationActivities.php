<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class LocationActivities extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'location_activities';
    protected $fillable = ['title_th', 'title_en'];
    protected $guarded = ['id'];

    public function location()
    {
        return $this->belongsTo('Location', 'location_id');
    }

}