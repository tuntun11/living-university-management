<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class PersonnelTypeRates extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'personnel_type_rates';
    protected $fillable = [];

    public function personnelType()
    {
        return $this->belongsTo('PersonnelType', 'personnel_type_id');
    }

}