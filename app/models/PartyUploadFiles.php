<?php

class PartyUploadFiles extends Eloquent {

    protected $table = 'party_upload_files';
    protected $fillable = [];
    protected $guarded = ['id'];

    public function party()
    {
        return $this->belongsTo('Party');
    }

}