<?php

class PersonnelExpertTypes extends Eloquent {

    protected $table = 'personnel_expert_types';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function personnel()
    {
        return $this->belongsTo('Personnel');
    }

    public function type()
    {
        return $this->belongsTo('ExpertType');
    }

}