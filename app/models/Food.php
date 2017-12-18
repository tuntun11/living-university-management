<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Food extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'foods';
    protected $fillable = ['name'];
    protected $guarded = ['id'];

    public function location()
    {
        return $this->belongsTo('Location', 'location_id');
    }

    public static function strMeal($meal, $en = false)
    {
        $strMeal = "";
        switch($meal){
            case 'breakfast' :
                $strMeal = 'มื้อเช้า';
            break;
            case 'lunch' :
                $strMeal = 'มื้อกลางวัน';
            break;
            case 'dinner' :
                $strMeal = 'มื้อเย็น';
            break;
            case 'break_morning' :
                $strMeal = 'เบรกเช้า';
            break;
            case 'break_afternoon' :
                $strMeal = 'เบรกบ่าย';
            break;
            case 'night' :
                $strMeal = 'มื้อดึก';
            break;
        }

        return $strMeal;
    }

}