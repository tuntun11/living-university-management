<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class PersonnelLectureLanguages extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'personnel_lecture_languages';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function personnel()
    {
        return $this->belongsTo('Personnel');
    }

}