<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Accommodation extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'accommodations';
    protected $fillable = ['name'];
    protected $guarded = ['id'];

    public function location()
    {
        return $this->belongsTo('Location', 'location_id');
    }

}