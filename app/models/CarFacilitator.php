<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class CarFacilitator extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'car_facilitators';
    protected $fillable = ['name'];
    protected $guarded = ['id'];

    public function contacts()
    {
        return $this->hasMany('CarFacContacts', 'car_facilitator_id');
    }

    public function cars()
    {
        return $this->hasMany('Cars', 'car_facilitator_id');
    }

}