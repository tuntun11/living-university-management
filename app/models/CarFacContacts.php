<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class CarFacContacts extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'car_facilitator_contacts';
    protected $fillable = ['name', 'email', 'tel'];
    protected $guarded = ['id'];

    public function facilitator()
    {
        return $this->belongsTo('CarFacilitator', 'car_facilitator_id');
    }

}