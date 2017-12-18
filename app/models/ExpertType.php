<?php

class ExpertType extends Eloquent {

    protected $table = 'expert_types';
    protected $fillable = ['name'];
    protected $guarded = ['id'];

    public function person()
    {
        return $this->hasMany('PersonnelExpertTypes');
    }

}