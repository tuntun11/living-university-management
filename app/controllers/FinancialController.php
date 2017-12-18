<?php

class FinancialController extends BaseController {

    /**
     * Initializer.
     *
     * @return \FinancialController
     */
    public function __construct()
    {
        parent::__construct();
    }

    static public function models()
    {
        //1. รายได้เข้ามหาวิทยาลัยที่มีชีวิตทั้งหมด (912) 100% เข้า 912 = External Revenue
        //2. รายได้หลักเข้าหน่วยงานอื่นๆ เช่น 423 รหัสนำหน้า 423 แต่มีการทำโอนรายได้บางหมวดให้ 912 = External Revenue
        //3. หน่วยงานสนับสนุนค่าใช้จ่ายคณะทั้งหมด เป็นรหัส 9 หลักนอกเหนือจากลูกค้า เป็นคณะที่ lu ทำงานฟรีไม่ได้เงิน แต่อาจมี inter charge กันทีหลัง = Internal Revenue Maybe
        //4. หน่วยงานสนับสนุนค่าใช้จ่ายบางส่วน รายได้จะเข้า 912 แค่บางส่วน บางส่วนก็จะกระจายไปคิดในส่วนรหัสเงินอื่นๆ = External Revenue but distribute to other
        //5. รายได้รอการพิจารณา
        return array('full' => 'รายได้เข้ามหาวิทยาลัยที่มีชีวิตทั้งหมด (912)', 'other' => 'รายได้เข้าหน่วยงานอื่นๆ', 'donate' => 'หน่วยงานสนับสนุนค่าใช้จ่ายคณะทั้งหมด', 'absorb' => 'หน่วยงานสนับสนุนค่าใช้จ่ายบางส่วน 912 และอื่นๆ', 'delay' => 'รอการพิจารณา');
    }

    static public function model($model, $related_code = NULL)
    {
        $model = array_get(self::models(), $model);

        if ($related_code!=NULL)
        {
            $model.= " (".$related_code.")";
        }

        return $model;
    }

}