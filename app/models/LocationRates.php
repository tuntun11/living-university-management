<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class LocationRates extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'location_rates';
    protected $fillable = ['name'];
    protected $guarded = ['id'];

    public function location()
    {
        return $this->belongsTo('Location', 'location_id');
    }

}