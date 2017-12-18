<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Cars extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'cars';
    protected $fillable = ['name'];
    protected $guarded = ['id'];

    public function facilitator()
    {
        return $this->belongsTo('CarFacilitator', 'car_facilitator_id');
    }

    public function rates()
    {
        return $this->hasMany('CarRates', 'car_id');
    }

}