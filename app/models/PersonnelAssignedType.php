<?php

class PersonnelAssignedType extends Eloquent {

    protected $table = 'personnel_assigned_type';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function personnel()
    {
        return $this->hasOne('Personnel');
    }

    public function type()
    {
        return $this->hasOne('PersonnelType');
    }

}