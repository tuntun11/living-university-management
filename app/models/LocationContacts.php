<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class LocationContacts extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'location_contacts';
    protected $fillable = ['name'];
    protected $guarded = ['id'];

    public function location()
    {
        return $this->belongsTo('Location', 'location_id');
    }

}