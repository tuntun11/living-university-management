<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class PersonnelWorkExperiences extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'personnel_work_experiences';
    protected $fillable = ['company', 'position', 'job_description'];
    protected $guarded = ['id'];

    public function personnel()
    {
        return $this->belongsTo('Personnel');
    }

}