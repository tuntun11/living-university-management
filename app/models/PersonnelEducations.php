<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class PersonnelEducations extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'personnel_educations';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function personnel()
    {
        return $this->belongsTo('Personnel');
    }

}