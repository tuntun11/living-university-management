<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class PersonnelLectureSubjects extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'personnel_lecture_subjects';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function personnel()
    {
        return $this->belongsTo('Personnel');
    }

}