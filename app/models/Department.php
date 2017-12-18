<?php

class Department extends Eloquent {

    protected $table = 'departments';
    protected $fillable = ['code', 'financial_code', 'name'];
    protected $guarded = ['id'];

    public function personnels()
    {
        return $this->hasMany('Personnel');
    }

    public function teams()
    {
        $teams = Personnel::leftJoin('department_personnels', 'personnels.id', '=', 'department_personnels.personnel_id')
            ->where('department_personnels.department_id', '=', $this->id)
            ->get();

        return $teams;
    }

    public function fullName()
    {
        $monogram = ($this->monogram!=NULL) ? '('.$this->monogram.')' : '';

        return $this->financial_code.' '.$this->name.$monogram;
    }
}