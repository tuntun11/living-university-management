<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class CarRates extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'car_rates';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function car()
    {
        return $this->belongsTo('Cars', 'car_id');
    }

}