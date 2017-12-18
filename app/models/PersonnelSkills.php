<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class PersonnelSkills extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'personnel_skills';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function personnel()
    {
        return $this->belongsTo('Personnel');
    }

}