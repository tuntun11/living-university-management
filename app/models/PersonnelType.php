<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class PersonnelType extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'personnel_types';
    protected $fillable = ['name'];
    protected $guarded = ['id'];

    public function typeAssigned()
    {
        return $this->hasOne('PersonnelAssignedType');
    }

    public function rates()
    {
        return $this->hasMany('PersonnelTypeRates');
    }

}