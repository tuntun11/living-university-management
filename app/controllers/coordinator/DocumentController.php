<?php

use PhpOffice\PhpWord\Autoloader;
use PhpOffice\PhpWord\Settings;

class DocumentController extends CoordinatorController
{

    protected $party;
    protected $location;
    //for schedule
    protected $lu_schedule;
    protected $lu_schedule_task;
    protected $lu_schedule_task_location;
    protected $lu_document_exports;

    public function __construct(Party $party, Location $location, LuSchedule $lu_schedule, LuScheduleTask $lu_schedule_task, LuScheduleTaskLocation $lu_schedule_task_location, LuDocumentExports $lu_document_exports)
    {
        parent::__construct();

        $this->party = $party;
        $this->location = $location;
        $this->lu_schedule = $lu_schedule;
        $this->lu_schedule_task = $lu_schedule_task;
        $this->lu_schedule_task_location = $lu_schedule_task_location;
        $this->lu_document_exports = $lu_document_exports;
    }

    /*Export Action Plan Draft*/
    public function getActionPlanDocument($party)
    {
        $fileName = $party->customer_code . '_' . date('Y_m_d_h_i');
        //create folder to storage file
        $destinationPath = public_path().'/svms/action_plan/'.$party->customer_code;
        $filePath = $destinationPath.'/'.$fileName;

        if(!file_exists($destinationPath))
        {
            //1. create directory name by customer code
            mkdir($destinationPath, 0777);
        }

        $excel = Excel::create($fileName, function($excel) use ($party) {
			
			// Chain the setters
			$excel->setCreator(Auth::user()->getSignName(0))
					->setCompany('MFLF');

            //check have b plan or not
            $count_b_plan = ScheduleController::haveAnotherPlan($party->id);

            $plans = ($count_b_plan > 0) ? array('A', 'B') : array('A');

            //arrays send to create action plan
            $actions = array();

            foreach($plans as $plan)
            {
                //set array for keep plan
                $actions = array();

                //query date to loop
                $days = ScheduleController::dateRangeArray($party->start_date, $party->end_date);

                foreach($days as $day)
                {
                    $actions[$day] = array();

                    //foreach loop task whatever plan a or b query plan a before
                    $schedule_tasks = LuScheduleTask::leftJoin('lu_schedules AS s', 's.id', '=', 'lu_schedule_tasks.lu_schedule_id')
                        ->where('lu_schedule_tasks.is_plan_b', '=', 0)
                        ->whereNotIn('lu_schedule_tasks.type', array('S'))
                        ->where('lu_schedule_tasks.start', '>=', $day.' 00:00:00')
                        ->where('lu_schedule_tasks.end', '<=', $day.' 23:00:00')
                        ->where('s.party_id', '=', $party->id)
                        ->select('s.party_id AS party_id', 'lu_schedule_tasks.*')
                        ->orderBy('lu_schedule_tasks.start', 'ASC')
                        ->get();

                    foreach($schedule_tasks as $schedule)
                    {
                        if ($plan=='A')
                        {
                            $tasks = array(
                                'time' => date("H:i", strtotime($schedule['start'])).' น.',
                                'detail' => ($schedule['title_th']=="") ? $schedule['title_en'] : $schedule['title_th'],
                                'is_plan_b' => 0
                            );
                        }
                        else
                        {
                            //if plan b find plan b if not have use a plan instantly
                            $schedule_b_task = LuScheduleTask::where('master_task_id', '=', $schedule->id)
                                ->where('is_plan_b', '=', 1)
                                ->first();

                            if ($schedule_b_task)
                            {
                                $tasks = array(
                                    'time' => date("H:i", strtotime($schedule_b_task['start'])).' น.',
                                    'detail' => ($schedule_b_task['title_th']=="") ? $schedule_b_task['title_en'] : $schedule_b_task['title_th'],
                                    'is_plan_b' => 1
                                );
                            }
                            else
                            {
                                /*This is main plan*/
                                $tasks = array(
                                    'time' => date("H:i", strtotime($schedule['start'])).' น.',
                                    'detail' => ($schedule['title_th']=="") ? $schedule['title_en'] : $schedule['title_th'],
                                    'is_plan_b' => 0
                                );
                            }
                        }

                        array_push($actions[$day], $tasks);
                    }
                }
                //echo dd($actions);
                /*set sheet loop by plan*/
                $excel->sheet('Action Plan แผน '.$plan, function($sheet) use ($party, $plan, $actions) {

                    //styling sheet default
                    $sheet->setStyle(array(
                        'font' => array(
                            'name' => 'Browallia New',
                            'size' => 14
                        )
                    ));

                    //set freeze at table header
                    $sheet->setFreeze('A5');

                    //using blade to generate
                    $sheet->loadView('reports.parties.excel.action_plan')
                        ->with('party', $party)
                        ->with('plan', $plan)
                        ->with('actions', $actions);
                });
            }
        })
        //->export('xls')
        ->store('xls', $destinationPath);

        //also keep export log
        self::updateExportLog('action_plan', $filePath, $party->id);

        return Response::download($filePath . '.xls');
    }

    /*Export PDF ศทบ */
    public function getTravelDocument($party)
    {
        //use tcpdf lib for export
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

        $pdf->AddPage();
        //image
        $pdf->Image(asset('assets/img/travel01_template.jpg'), '', '', 0, 0, 'JPG', '', '', true, 300, 'C', false, false, 0, true, false, true);
        $pdf->SetFont('freeserif', 'B', 9);
         //party name
         $pdf->MultiCell(0, 5, $party->name, 0, 'L', false, 1, 35, 44);
         //party quantity
         $pdf->MultiCell(0, 5, $party->people_quantity, 0, 'L', false, 1, 155, 44);
        //party start and end
         $pdf->MultiCell(0, 5, ScheduleController::dateRangeStr($party->start_date, $party->start_date, true, true), 0, 'L', false, 1, 36, 57.2);
         $pdf->MultiCell(0, 5, ScheduleController::dateRangeStr($party->end_date, $party->end_date, true, true), 0, 'L', false, 1, 110, 57.2);

        //Coordinator
        $coordinators = $party->coordinators()->get();
        $c=1;
        foreach($coordinators as $coordinator)
        {
            //ใส่ได้ไม่เกิน 1 คน
            if ($c==1)
            {
                $coordinator_name = $coordinator->name;
                $coordinator_phone = $coordinator->mobile;
                $coordinator_mail = $coordinator->email;
            }
        }
        $pdf->MultiCell(0, 5, $coordinator_name, 0, 'L', false, 1, 51, 98);
        $pdf->MultiCell(0, 5, $coordinator_phone, 0, 'L', false, 1, 105, 98);
        $pdf->MultiCell(0, 5, $coordinator_mail, 0, 'L', false, 1, 148, 102);

        //Project Coordinator
         $pdf->MultiCell(0, 5, $party->assignedCoordinator(), 0, 'L', false, 1, 42, 148.9);

        $pdf->lastPage();

        //set file name
        $filename = $party->customer_code.'_'.date('Y_m_d_h_i').'.pdf';

        //create folder to storage file
        $destinationPath = public_path().'/svms/travel01/'.$party->customer_code;
        $filePath = $destinationPath .'/'. $filename;

        if(!file_exists($destinationPath))
        {
            //1. create directory name by customer code
            mkdir($destinationPath, 0777);
        }

        $pdf->output($filePath, 'FI');

        //also keep export log
        self::updateExportLog('travel01', $filePath, $party->id);

        return Response::download($filename);
    }

    /*Export กำหนดการ โดย PHPWord จะมีการบันทึกเป็นไฟล์ทุกครั้ง*/
    public static function getSchedule($party)
    {
        /*Check and Count Plan B if have create 2 version in one document*/
        $count_b_plan = ScheduleController::haveAnotherPlan($party->id);

        $plans = ($count_b_plan > 0) ? array('A', 'B') : array('A');

        /*Check complete of english language if not 100% cannot export*/
        $count_null_english = LuScheduleTask::leftJoin('lu_schedules as s', 's.id', '=', 'lu_schedule_tasks.lu_schedule_id')
            ->where('s.party_id', '=', $party->id)
            ->whereNotIn('lu_schedule_tasks.type', array('S'))
            ->where('lu_schedule_tasks.title_en', '=', '')
            ->count();

        $langs = ($count_null_english > 0) ? array('th') : array('th', 'en');

        if($fileName = self::createSchedule($party, $plans, $langs))
        {
            /*Set file name to redirect and save path*/
            $filePath = public_path().'\svms\schedule\/'.$party->customer_code.'\/'.$fileName;
            /*Save Documented transaction to database*/
            if (self::updateExportLog('schedule', $filePath, $party->id))
            {
                $downloadPath = asset('svms/schedule/' . $party->customer_code . '/'.$fileName);
                //return download path
                return $downloadPath;
            }

            return false;
        }

        return false;
    }

    static function createSchedule($party, $plans = array(), $langs = array('th'))
    {
        /*Call Construct Method*/
        Autoloader::register();
        Settings::loadConfig();

        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        foreach($langs as $lang)
        {
            foreach($plans as $plan)
            {
                $section = $phpWord->addSection();

                //current plan
                $is_plan_b = ($plan==='A') ? 0 : 1; 

                //set Title Font Style
                $fontTitleBold = array('name' => 'BrowalliaUPC', 'size' => 18, 'bold' => true);
                $fontTitleNormal = array('name' => 'BrowalliaUPC', 'size' => 18, 'bold' => false);

                //add footer font style
                $fontVersion = array('name' => 'BrowalliaUPC', 'size' => 12, 'bold' => false, 'color' => '#CCCCCC');

                //set Content Font Style
                $fontContentItalic = array('name' => 'BrowalliaUPC', 'size' => 16, 'italic' => true);
                $fontContentNormal = array('name' => 'BrowalliaUPC', 'size' => 16, 'bold' => false);
                $fontContentBold = array('name' => 'BrowalliaUPC', 'size' => 16, 'bold' => true);
                $fontContentBoldUnderline = array('name' => 'BrowalliaUPC', 'size' => 16, 'bold' => true, 'underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE);

                $phpWord->addTitleStyle(1, $fontTitleBold, array('align' => 'center'));
                $phpWord->addTitleStyle(2, $fontTitleNormal, array('align' => 'center'));
                $phpWord->addTitleStyle(3, $fontVersion, array('align' => 'center'));

                //Get Latest Revision
                $revision = self::nextRevision('schedule', $party->id);

                //change thai or english
                if ($lang=='th')
                {
                    $strTitle1 = '(ร่าง)โปรแกรม แผน ';
                    $strTitle2 = 'วันที่ '.ScheduleController::dateRangeStr($party->start_date, $party->end_date, true);
                    $strVersion = '(ครั้งที่ '.$revision.' ออกเอกสารเมื่อ '.ScheduleController::dateRangeStr(date('Y-m-d'), date('Y-m-d'), true).')';
                }
                else
                {
                    $strTitle1 = '(Draft) Schedule for Study Visit ';
                    $strTitle2 = ScheduleController::dateRangeStr($party->start_date, $party->end_date, false, false, 'en');
                    $strVersion = '(version '.$revision.' created at '.ScheduleController::dateRangeStr(date('Y-m-d'), date('Y-m-d'), false, false, 'en').')';
                }

                $section->addTitle(htmlspecialchars($strTitle1.$plan), 2);
                $section->addTitle(htmlspecialchars($party->name), 1);
                $section->addTitle(htmlspecialchars($strTitle2), 2);
                $section->addTitle(htmlspecialchars($strVersion), 3);

                $section->addTextBreak(1);

                //set Table and Cell Style
                $styleTable = array('width' => 10000, 'border' => null, 'cellMargin' => 80, 'unit' => 'pct');
                $styleCell = array('valign' => 'top');

                $phpWord->addTableStyle('Border Less', $styleTable);
                $table = $section->addTable('Border Less');
                /*Create Only A Plan*/
                $days = ScheduleController::dateRangeArray($party->start_date, $party->end_date);

                foreach($days as $day)
                {
                    $strDayLabel = ($lang=='th') ? 'วัน'.ScheduleController::dateRangeStr($day, $day, true, true) : ScheduleController::dateRangeStr($day, $day, false, true, 'en');

                    $table->addRow();
                    $cellColSpan = array('gridSpan' => 2, 'valign' => 'center');
                    $table->addCell(10000, $cellColSpan)->addText(htmlspecialchars($strDayLabel), $fontContentBoldUnderline);

                    //foreach loop task whatever plan a or b query plan a before
                    $schedule_tasks = LuScheduleTask::leftJoin('lu_schedules AS s', 's.id', '=', 'lu_schedule_tasks.lu_schedule_id')
                                    ->where('lu_schedule_tasks.is_plan_b', '=', $is_plan_b)
                                    ->whereNotIn('lu_schedule_tasks.type', array('S'))
                                    ->where('lu_schedule_tasks.start', '>=', $day.' 00:00:00')
                                    ->where('lu_schedule_tasks.end', '<=', $day.' 23:00:00')
                                    ->where('s.party_id', '=', $party->id)
                                    ->select('s.party_id AS party_id', 'lu_schedule_tasks.*')
                                    ->orderBy('lu_schedule_tasks.start', 'ASC')
                                    ->get();

                    foreach($schedule_tasks as $schedule)
                    {
                        //if plan is A query normal
                        $strScheduleTime = ($lang=='th') ? 'เวลา '.date("H:i", strtotime($schedule['start'])).' - '.date("H:i", strtotime($schedule['end'])).' น.' : date("H:i", strtotime($schedule['start'])).' - '.date("H:i", strtotime($schedule['end']));

                        $table->addRow();
                        $table->addCell(3000, $styleCell)->addText(htmlspecialchars($strScheduleTime), $fontContentNormal);
                        $textTitle = $table->addCell(7000, $styleCell)->addTextRun();
                        $textTitle->addText(htmlspecialchars(($lang=='th') ? $schedule['title_th'] : $schedule['title_en']), $fontContentNormal);
                        $note_title = ($lang=='th') ? $schedule['note_th'] : $schedule['note_en'];
                        if ($note_title!="")
                        {
                            $textTitle->addTextBreak(1);
                            $textTitle->addText(htmlspecialchars('"' . $note_title . '"'), $fontContentItalic);
                        } 
                    }
                }
            }
        }

        // Saving the document as OOXML file...
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');

        $destinationPath = public_path().'/svms/schedule/'.$party->customer_code;
        $fileName = $party->customer_code.'_v'.$revision.".docx"; /*timestamp*/
        $pathToFile = $destinationPath.'/'.$fileName;

        if(!file_exists($destinationPath))
        {
            //1. create directory name by customer code
            mkdir($destinationPath, 0777);
        }
		
        //2. save file in to directory
        $objWriter->save($pathToFile);
		//also update revision
		self::updateRevision($revision, 'schedule', $party->id);
		
        //3. check file exit
        if(file_exists($pathToFile))
        {
            return $fileName;
        }
        return false;
    }

    /*Export Quotation Only โดย PHPWord จะมีการบันทึกเป็นไฟล์ทุกครั้ง; Currently not used*/
    public static function getQuotation($party, $items, $en = 0)
    {
        /*Check and Count Plan B if have create 2 version in one document*/
        $count_b_plan = ScheduleController::haveAnotherPlan($party->id);
        $plans = ($count_b_plan > 0) ? array('A', 'B') : array('A');
        $langs = ($en == 0) ? array('th') : array('th', 'en');

        if($fileName = self::createQuotation($party, $items, $plans, $langs))
        {
            /*Set file name to redirect and save path*/
            $filePath = public_path().'\svms\quotation\/'.$party->customer_code.'\/'.$fileName;
            /*Save Documented transaction to database*/
            if (self::updateExportLog('quotation', $filePath, $party->id))
            {
                $downloadPath = asset('svms/quotation/' . $party->customer_code . '/'.$fileName);
                //return download path
                return $downloadPath;
            }
            //return case cannot update log
            return false;
        }
        //return case cannot export
        return false;
    }

    /*create quotation and also create item price list word doc*/
    static function createQuotation($party, $items, $revision, $plans = array(), $langs = array('th'))
    {
        /*Call Construct Method*/
        Autoloader::register();
        Settings::loadConfig();

        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        foreach($langs as $lang)
        {
            foreach($plans as $plan)
            {
				//section setting
				$sectionSettings = array(
					'marginTop' => 600,
					'marginBottom' => 600,
					'marginLeft' => 800,
					'marginRight' => 800
				);
				
                $section = $phpWord->createSection($sectionSettings);

                //set Title Font Style
                $fontTitleBold = array('name' => 'BrowalliaUPC', 'size' => 18, 'bold' => true);
                $fontTitleNormal = array('name' => 'BrowalliaUPC', 'size' => 18, 'bold' => false);

                //add footer font style
                $fontVersion = array('name' => 'BrowalliaUPC', 'size' => 12, 'bold' => false, 'color' => '#CCCCCC');

                //set Content Font Style
				$standardFontSize = 14;
				
                $fontContentItalic = array('name' => 'BrowalliaUPC', 'size' => $standardFontSize, 'italic' => true);
                $fontContentNormal = array('name' => 'BrowalliaUPC', 'size' => $standardFontSize, 'bold' => false);
                $fontContentUnderline = array('name' => 'BrowalliaUPC', 'size' => $standardFontSize, 'underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE);
                $fontContentBold = array('name' => 'BrowalliaUPC', 'size' => $standardFontSize, 'bold' => true);
                $fontContentBoldUnderline = array('name' => 'BrowalliaUPC', 'size' => $standardFontSize, 'bold' => true, 'underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE);

                $phpWord->addTitleStyle(1, $fontTitleBold, array('align' => 'center'));
                $phpWord->addTitleStyle(2, $fontTitleNormal, array('align' => 'center'));
                $phpWord->addTitleStyle(3, $fontVersion, array('align' => 'center'));

                //add logo header
                $source = asset('assets/img/logo-header.jpg');
                $section->addImage($source, array('align' => 'center', 'width' => 500));

                //change thai or english
                if ($lang=='th')
                {
                    $strTitle1 = 'ใบเสนอราคาการศึกษาดูงาน แผน '.$plan;
                    $strTitle2 = 'วันที่ '.ScheduleController::dateRangeStr($party->start_date, $party->end_date, true);
                    //$strVersion = '(ครั้งที่ '.$revision.' ออกเอกสารเมื่อ '.ScheduleController::dateRangeStr(date('Y-m-d'), date('Y-m-d'), true).')';
                }
                else
                {
                    $strTitle1 = 'Quotation (Plan '.$plan.')';
                    $strTitle2 = ScheduleController::dateRangeStr($party->start_date, $party->end_date, false, false, 'en');
                    //$strVersion = '(version '.$revision.' created at '.ScheduleController::dateRangeStr(date('Y-m-d'), date('Y-m-d'), false, false, 'en').')';
                }

                $section->addTitle(htmlspecialchars($strTitle1), 2);
                $section->addTitle(htmlspecialchars($party->name), 1);
                $section->addTitle(htmlspecialchars($strTitle2), 2);
                //$section->addTitle(htmlspecialchars($strVersion), 3);

                //$section->addTextBreak(1);

                //query saved budgeting sale value
                $quotation = LuBudget::where('party_id', '=', $party->id)->first();
                //set Table and Cell Style
                //$styleTable = array('width' => 10000, 'border' => null, 'cellMargin' => 30, 'unit' => 'pct');
                //$styleCell = array('valign' => 'top');
				$styleTable = array('width' => 10000, 'border' => null, 'cellMargin' => 30, 'unit' => 'pct');
                $styleCell = array('valign' => 'center');
				$textAlignRight = array('align' => 'right');
				
				// This is used to remove "padding" below text-lines
				$noSpace = array('spaceAfter' => 0);

                $phpWord->addTableStyle('Border Less', $styleTable);
                $table = $section->addTable('Border Less');
				
				$cellColSpan = array('gridSpan' => 2, 'valign' => 'center');
				
                $table->addRow();
				
				$strDefaultAccountCode = '912';//insert only quotation
                $strCustomerCode = ($lang=='th') ? 'รหัสลูกค้า  :  ' . $strDefaultAccountCode . '' . $party->customer_code : 'Customer Code : ' . $party->customer_code;
                //set customer code
                $table->addCell(10000, $cellColSpan)->addText(htmlspecialchars($strCustomerCode), $fontContentBold, $textAlignRight);
                //set table header
                $styleCellHeader = array('valign' => 'center', 'bgColor' => '#00e5e5');
                
                $table->addRow();
                $table->addCell(8000, $styleCellHeader)->addText(htmlspecialchars(($lang=='th') ? 'รายการ' : 'Expense List'), $fontContentBold, array('valign' => 'center'));
                $table->addCell(2000, $styleCellHeader)->addText(htmlspecialchars(($lang=='th') ? 'บาท' : 'Baht'), $fontContentBold, array('valign' => 'center', 'align' => 'right'));
                $table->addRow();
                $table->addCell(10000, $cellColSpan)->addText(htmlspecialchars(($lang=='th') ? 'ค่าใช้จ่ายในการศึกษาดูงานจำนวน  ' . $party->people_quantity .'  ท่าน' : 'Study visit programme at Doi Tung Development Project for ' . $party->people_quantity .' pax'), $fontContentBoldUnderline, $noSpace);
				
				//query saved budgeting sale value
				$quotation = LuBudget::where('party_id', '=', $party->id)->first();
                //set param to show
				$saleGrandTotal = ($plan=='A') ? $quotation->grand_total_a : $quotation->grand_total_b;
				
				 //get absorb
				$total_absorb = 0;
				$absorbs = LuBudgetAbsorb::where('party_id', '=', $party->id)->whereIsPlanB(($plan=='A') ? 0 : 1)->get();
                
                //generate text to display เอาข้อมูลจากแท็บแยกประเภทดึงมาใช้งาน
				
				//กิจกรรม
				$strActivityChargeThText = "กิจกรรมศึกษาดูงานการพัฒนาขั้นต้นน้ำ-กลางน้ำ-ปลายน้ำ และองค์การบริหารส่วนตำบลแม่ฟ้าหลวง";
				$strActivityChargeEnText = "Study Visit, Learning Activities and Facilities fee";
				
                //ห้องพัก
				//ภาษา Thai
				$strAccommodationChargeThText = "ที่พัก";
				if (isset($items['accommodations']) && count($items['accommodations'])>0)
				{
					$a = 1;
					$accommodations = $party->returnAccommodationQuantities($plan);
					foreach($accommodations as $accommodation)
					{
						if (count($accommodations)>1 && count($accommodations)==$a)
						{
							$strAccommodationChargeThText .= 'และ';
						}
						$strAccommodationName = Location::find($accommodation['task'])->name;
						$strAccommodationChargeThText .= $strAccommodationName.' '.$accommodation['days'].' คืน ';

						$a++;
					}
					$strAccommodationChargeThText .= ' (รวมอาหารเช้า)';
				}
				//ภาษา English
				$strAccommodationChargeEnText = "Accommodation";
					
                //อาหาร
                $strMealChargeThText = "อาหาร";
				if (count($party->returnMealQuantities($plan))>0)
				{
					$strMealChargeThText .= ' (';
					$m = 1;
					$allMeal = 0;
					foreach($party->returnMealQuantities($plan) as $meal)
					{
						if (count($party->returnMealQuantities($plan))>1 && count($party->returnMealQuantities($plan))==$m)
						{
							$strMealChargeThText .= 'และ';
						}
						$strMealChargeThText .= Food::strMeal($meal['meal']).' '.$meal['quantity'].' มื้อ ';

						$m++;
						$allMeal += $meal['quantity'];
					}
					$strMealChargeThText .= ' รวมทั้งหมด '.$allMeal.' มื้อ)';
				}
				$strMealChargeEnText = "Food and Beverage";

                //รถยนต์
				$strTransportChargeThText = "การเดินทางตลอดระยะเวลาในการศึกษาดูงาน";
				/* ปิดไปก่อนเนื่องจากอาจไม่ต้อง
				if (isset($items['cars']) && count($items['cars'])>0)
				{
					$car_facilitators = array_keys($items['cars'][$plan]);
					//ลูปผู้ให้บริการรถยนต์
					foreach($car_facilitators as $car_facilitator)
					{
						$c = 1;
						$strTransportChargeThText .= '('.$car_facilitator.':';
						//ลูปประเภทรถยนต์
						foreach($items['cars'][$plan][$car_facilitator] as $car)
						{
							if (count($items['cars'][$plan][$car_facilitator])>1 && count($items['cars'][$plan][$car_facilitator])==$c)
							{
								$strTransportChargeThText .= 'และ';
							}
							$strTransportChargeThText .= $car['expense_name'].' '.$car['quantity'].' '.$car['expense_unit'].' จำนวน '.$car['day'].' วัน ';
							$c++;
						}
						$strTransportChargeThText .= ')';
					}
				}
				*/
				$strTransportChargeEnText = "Transportation to all study visit sites";

				//Print Out Text
				//พิมพ์ค่ากิจกรรม
				$table->addRow();
				$table->addCell(8000, $styleCell)->addText(htmlspecialchars(($lang=='th') ? $strActivityChargeThText : $strActivityChargeEnText), $fontContentNormal, $noSpace);
				$table->addCell(2000, $styleCell)->addText(htmlspecialchars(''), $fontContentNormal, $noSpace);
                //พิมพ์วิทยากร โชว์เฉพาะภาษาไทย
                if ($lang=='th')
                {
					if(isset($items['personnels']) && count($items['personnels'])>0)
					{
						$table->addRow();
						$table->addCell(8000, $styleCell)->addText(htmlspecialchars('ค่าวิทยากรและเอกสารประกอบการดูงาน'), $fontContentNormal, $noSpace);
						$table->addCell(2000, $styleCell)->addText(htmlspecialchars(''), $fontContentNormal, $noSpace);
					}
                }
				//พิมพ์ค่าที่พัก
				if (isset($items['accommodations']) && count($items['accommodations'])>0)
				{
					$table->addRow();
					$table->addCell(8000, $styleCell)->addText(htmlspecialchars(($lang=='th') ? $strAccommodationChargeThText : $strAccommodationChargeEnText), $fontContentNormal, $noSpace);
					$table->addCell(2000, $styleCell)->addText(htmlspecialchars(''), $fontContentNormal, $noSpace);
				}
				//พิมพ์ค่าอาหาร
				if (count($party->returnMealQuantities($plan))>0)
				{
					$table->addRow();
					$table->addCell(8000, $styleCell)->addText(htmlspecialchars(($lang=='th') ? $strMealChargeThText : $strMealChargeEnText), $fontContentNormal, $noSpace);
					$table->addCell(2000, $styleCell)->addText(htmlspecialchars(''), $fontContentNormal, $noSpace);
				}
				//พิมพ์ค่ารถ
				if (isset($items['cars']) && count($items['cars'])>0)
				{
					$table->addRow();
					$table->addCell(8000, $styleCell)->addText(htmlspecialchars(($lang=='th') ? $strTransportChargeThText : $strTransportChargeEnText), $fontContentNormal, $noSpace);
					$table->addCell(2000, $styleCell)->addText(htmlspecialchars(''), $fontContentNormal, $noSpace);
				}
				
				//Discount Row
                if ($quotation->discount>0)
                {
                    $discount = round($saleGrandTotal*($quotation->discount/100));
                    $saleGrandTotal = $saleGrandTotal-$discount;

					//พิมพ์ส่วนลด
                    $table->addRow();
                    $table->addCell(8000, $styleCell)->addText(htmlspecialchars(($lang=='th') ? 'ส่วนลด '.(int)$quotation->discount.'%' : 'Discount '.(int)$quotation->discount.'%'), $fontContentNormal, $noSpace);
                    $table->addCell(2000, $styleCell)->addText(htmlspecialchars(''), $fontContentNormal, $noSpace);
                }
				
				//Absorb Row
				if (count($absorbs)>0)
				{
					foreach($absorbs as $absorb)
					{
						$total_absorb += $absorb->total;
					}

					$actualGrandTotal = $saleGrandTotal+$total_absorb;
					
					//พิมพ์ Absorb
                    $table->addRow();
                    $table->addCell(8000, $styleCell)->addText(htmlspecialchars(($lang=='th') ? 'ราคารวม ' : 'Total'), $fontContentNormal);
                    $table->addCell(2000, $styleCell)->addText(htmlspecialchars(number_format ( $actualGrandTotal, 0, ".", "," )), $fontContentNormal, $textAlignRight);
					
					$table->addRow();
                    $table->addCell(8000, $styleCell)->addText(htmlspecialchars(($lang=='th') ? 'มูลนิธิแม่ฟ้าหลวงฯ สนับสนุนค่าใช้จ่ายเป็นจำนวน  ' : 'Sponsored by MFLF '), $fontContentNormal);
                    $table->addCell(2000, $styleCell)->addText(htmlspecialchars('-'.number_format ( $total_absorb, 0, ".", "," )), $fontContentNormal, $textAlignRight);
				}

                $table->addRow();
                $table->addCell(8000, $styleCell)->addText(htmlspecialchars(($lang=='th') ? 'ราคารวมทั้งสิ้น' : 'Total'), $fontContentBold);
                $table->addCell(2000, $styleCell)->addText(htmlspecialchars(number_format ( $saleGrandTotal, 0, ".", "," )), $fontContentBold, $textAlignRight);

                //add text number
                $table->addRow();
                $table->addCell(10000, array('gridSpan' => 2, 'valign' => 'center', 'bgColor' => '#000000'))->addText(htmlspecialchars(($lang=='th') ? 'จำนวนเงินทั้งสิ้น '.DocumentController::num2wordsThai($saleGrandTotal) : 'Grand Total '.DocumentController::convert_number_to_words($saleGrandTotal)), array('name' => 'BrowalliaUPC', 'size' => 16, 'bold' => true, 'color' => '#FFFFFF'), array('valign' => 'center'));
				
				//add payment instruction
				$strAlertPaid = ($lang=='th') ? 'ราคานี้กรุณาชำระภายใน 7 วันหลังที่ได้รับบริการ' : 'Payment due within 7 days after receiving the service.';
                $table->addRow();
				$table->addCell(10000, $cellColSpan)->addText(htmlspecialchars($strAlertPaid), array('name' => 'BrowalliaUPC', 'size' => 12, 'bold' => false, 'color' => '#666666'), $textAlignRight);
				
                //footer
                $paragraphStyle = array('align' => 'center');
                $phpWord->addParagraphStyle('pStyle', $paragraphStyle);
				
				$paragraphNoSpaceStyle = array('align' => 'center');
                $phpWord->addParagraphStyle('pStyleNoSpace', $paragraphNoSpaceStyle);

                $footer = $section->addTable('Border Less');
                $footer->addRow();
                $strAcceptText = ($lang=='th') ? 'ข้าพเจ้ายอมรับเงื่อนไขในการชำระเงิน' : 'I agree to the terms and conditions.';
                $footer->addCell(5500, array('valign' => 'center', 'align' => 'center'))->addText(htmlspecialchars($strAcceptText), $fontContentNormal, 'pStyle');
                $footer->addCell(4500, array('valign' => 'center'))->addText(htmlspecialchars(($lang=='th') ? '' : 'Quotation prepared by'), $fontContentNormal, 'pStyle');
                //signature
                $footer->addRow();
                $footer->addCell(5500, array('valign' => 'center'))->addText(htmlspecialchars(''), $fontContentNormal, 'pStyle');
                $footer->addCell(4500, array('valign' => 'center'))->addText(htmlspecialchars(''), $fontContentNormal, 'pStyle');
                $footer->addRow();
                $footer->addCell(5500, array('valign' => 'center'))->addText(htmlspecialchars('(                                    )'), $fontContentNormal, 'pStyle');
                $footer->addCell(4500, array('valign' => 'center'))->addText(htmlspecialchars(''), $fontContentNormal, 'pStyle');
                $footer->addRow();
                $strAcceptPerson = ($lang=='th') ? 'ผู้มีอำนาจลงนาม' : 'Signature of Authorized Person';
                $footer->addCell(5500, array('valign' => 'center'))->addText(htmlspecialchars($strAcceptPerson), $fontContentNormal, 'pStyleNoSpace');
                $strCoorPerson = ($lang=='th') ? Auth::user()->getSignName(0) : Auth::user()->getSignName(1);
                $footer->addCell(4500, array('valign' => 'center'))->addText(htmlspecialchars("(".$strCoorPerson.")"), $fontContentNormal, 'pStyleNoSpace');

                $footer->addRow();
                $strAcceptPosition = ($lang=='th') ? 'ตำแหน่ง _________________________' : 'Position __________________________';
                $footer->addCell(5500, $styleCell)->addText(htmlspecialchars($strAcceptPosition), $fontContentNormal, 'pStyleNoSpace');
                $strCoorDepart = ($lang=='th') ? Auth::user()->getDepartment(0) : Auth::user()->getDepartment(1);
                $footer->addCell(4500, array('valign' => 'center'))->addText(htmlspecialchars($strCoorDepart), $fontContentNormal, 'pStyleNoSpace');

                $footer->addRow();
                $strAcceptOffice = ($lang=='th') ? 'หน่วยงาน _________________________' : 'Office __________________________';
                $footer->addCell(5500, $styleCell)->addText(htmlspecialchars($strAcceptOffice), $fontContentNormal, 'pStyleNoSpace');
                $strCoorOffice = ($lang=='th') ? 'มูลนิธิแม่ฟ้าหลวง ในพระบรมราชูปถัมภ์' : 'Maefahluang Foundation Under Royal Patronage';
                $footer->addCell(4500, array('valign' => 'center'))->addText(htmlspecialchars($strCoorOffice), $fontContentNormal, 'pStyleNoSpace');

                $footer->addRow();
                $strAcceptDate = ($lang=='th') ? 'วันที่ ____________________________' : 'Date ____________________________';
                $footer->addCell(5500, $styleCell)->addText(htmlspecialchars($strAcceptDate), $fontContentNormal, 'pStyleNoSpace');
                $strCoorDate = ($lang=='th') ? ScheduleController::dateRangeStr(date('Y-m-d'), date('Y-m-d'), true) : ScheduleController::dateRangeStr(date('Y-m-d'), date('Y-m-d'), false, false, 'en');
                $footer->addCell(4500, array('valign' => 'center'))->addText(htmlspecialchars($strCoorDate), $fontContentNormal, 'pStyleNoSpace');

                $footer->addRow();
				$strContact = '';
                $strContact .= ($lang=='th') ? 'เมื่อลงนามแล้วขอความกรุณาส่งโทรสารกลับมาที่ 02-253-6999' : 'Please sign and fax to 02-253-6999';
				$strContact .= ($lang=='th') ? ' หรือ ' . Auth::user()->email : 'or email : '.Auth::user()->email;
                $footer->addCell(10000, array('gridSpan' => 2, 'valign' => 'center'))->addText(htmlspecialchars($strContact), $fontContentNormal);
				
            }
        }

        // Saving the document as OOXML file...
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');

        $destinationPath = public_path().'/svms/quotation/'.$party->customer_code;
        $fileName = $party->customer_code.'_quotation_v'.$revision.".docx"; /*customize filename*/
        $pathToFile = $destinationPath.'/'.$fileName;

        if(!file_exists($destinationPath))
        {
            //1. create directory name by customer code
            mkdir($destinationPath, 0777);
        }
        //2. save file in to directory
        $objWriter->save($pathToFile);
        //3. check file exit
        if(file_exists($pathToFile))
        {
            return $fileName;
        }
        return false;
    }

    //excel
    /*Export Quotation และ Price List โดย Laravel Excel จะมีการบันทึกเป็นไฟล์ทุกครั้ง*/
    public static function getQuotationAndPriceList($party, $items, $en = 0)
    {
        /*Check and Count Plan B if have create 2 version in one document*/
        $count_b_plan = ScheduleController::haveAnotherPlan($party->id);
        $plans = ($count_b_plan > 0) ? array('A', 'B') : array('A');
        $langs = ($en == 0) ? array('th') : array('th', 'en');
		//Update and get Revision
        $revision = self::nextRevision('quotation', $party->id);

		//First Create Quotation if success also create Pricing List  
        if($fileQuotationName = self::createQuotation($party, $items, $revision, $plans, $langs))
        {
			//Second Create Pricing List
			$filePriceName = self::createPriceList($party, $items, $revision, $plans);
            /*Set file name to redirect and save path*/
            $fileQuotationPath = public_path().'\svms\quotation\/'.$party->customer_code.'\/'.$fileQuotationName;
			$filePricePath = public_path().'\svms\quotation\/'.$party->customer_code.'\/'.$filePriceName;
			/*Also update revision*/
			self::updateRevision($revision, 'quotation', $party->id);
            /*Save Documented transaction to database*/
            if (self::updateExportLog('quotation', $fileQuotationPath, $party->id))
            {
				//set Array Download Path
				$downloadPath = array();
                $downloadPath['quotation'] = asset('svms/quotation/' . $party->customer_code . '/'.$fileQuotationName);
				$downloadPath['price'] = asset('svms/quotation/' . $party->customer_code . '/'.$filePriceName);
                //return download path
                return $downloadPath;
            }
            //return case cannot update log
            return false;
        }
        //return case cannot export
        return false;
    }

	//สร้าง pricing list เป็นไฟล์ Excel 
    static function createPriceList($party, $items, $revision, $plans = array(), $langs = array('th'))
    {
        //create folder to storage file
        $destinationPath = public_path().'/svms/quotation/'.$party->customer_code;
        //$filePath = $destinationPath.'/'.$fileName;

        if(!file_exists($destinationPath))
        {
            //create directory name by customer code
            mkdir($destinationPath, 0777);
        }

        //Set file name before create object
        $fileName = $party->customer_code . '_price_v' . $revision;

        //create excel pricing list file
        $excel = Excel::create($fileName, function($excel) use ($party, $items, $plans, $langs, $revision) {
            //ตั้งค่าแสดง sheet
            //$excel->setActiveSheetIndex(0);
			
			//ตั้งค่าผู้สร้างไฟล์
			$excel->setCreator(Auth::user()->getSignName(0))
					->setCompany('MFLF');
			
            //เงื่อนไขการสร้าง ใบเสนอราคามีสองภาษาไทยและอังกฤษ ส่วนราคามีภาษาไทยภาษาเดียว
            foreach($langs as $lang)
            {
                foreach ($plans as $plan)
                {
                    /*ราคาภาษาไทยเท่านั้น*/
                    if ($lang=='th')
                    {
                        $excel->sheet('ราคา แผน '.$plan, function($sheet) use ($party, $plan, $items, $revision) {

                            //styling sheet default
                            $sheet->setStyle(array(
                                'font' => array(
                                    'name' => 'Browallia New',
                                    'size' => 14
                                )
                            ));

                            //set freeze at table header
                            $sheet->setFreeze('A5');

                            //set number format range
                            /*$highestRow = 200;//set dummy
                            $sheet->setColumnFormat([
                                'B1:B' . $highestRow => '0',
                                'C1:C' . $highestRow => '0',
                                'D1:D' . $highestRow => '0',
                                'E1:E' . $highestRow => '0',
                                'F1:F' . $highestRow => '0',
                                'G1:G' . $highestRow => '0',
                                'H1:H' . $highestRow => '0',
                                'I1:I' . $highestRow => '0',
                            ]);*/

                            //using blade to generate
                            $sheet->loadView('reports.parties.excel.price_list')
                                ->with('party', $party)
                                ->with('plan', $plan)
                                ->with('items', $items)
                                ->with('revision', $revision);
                        });
                    }
                }
            }
        })
            //->export('xls')
            ->store('xls', $destinationPath);
        //log is keep when create quotation
		if ($excel)
		{
			//return file name to allow download
			return $fileName . '.xls';
		}
		else
		{
			return false;
		}
    }

    //Currently Not Used;
    //pricing list also create when quotation exported เก็บรวมกับ Quotation เลข version จะผูกกับใบเสนอราคาเสมอ;
   /* static function createQuotationAndPriceList($party, $items, $plans = array(), $langs = array('th'))
    {
        //create folder to storage file
        $destinationPath = public_path().'/svms/quotation/'.$party->customer_code;
        //$filePath = $destinationPath.'/'.$fileName;

        if(!file_exists($destinationPath))
        {
            //create directory name by customer code
            mkdir($destinationPath, 0777);
        }
        $revision = 0;
        //Set file name before create object
        $fileName = $party->customer_code . '_v' . $revision;
        $fileName = $party->customer_code . '_v';

        //create excel pricing list file
        $excel = Excel::create($fileName, function($excel) use ($party, $items, $plans, $langs, $revision) {
            //ตั้งค่าแสดง sheet
            //$excel->setActiveSheetIndex(0);
			
			//ตั้งค่าผู้สร้างไฟล์
			$excel->setCreator(Auth::user()->getSignName(0))
					->setCompany('MFLF');
			
            //เงื่อนไขการสร้าง ใบเสนอราคามีสองภาษาไทยและอังกฤษ ส่วนราคามีภาษาไทยภาษาเดียว
            foreach($langs as $lang)
            {
                foreach ($plans as $plan)
                {
                    //ราคาภาษาไทยเท่านั้น
                    if ($lang=='th')
                    {
                        $excel->sheet('ราคา แผน '.$plan, function($sheet) use ($party, $plan, $items, $revision) {

                            //styling sheet default
                            $sheet->setStyle(array(
                                'font' => array(
                                    'name' => 'Browallia New',
                                    'size' => 14
                                )
                            ));

                            //set freeze at table header
                            $sheet->setFreeze('A5');

                            //using blade to generate
                            $sheet->loadView('reports.parties.excel.price_list')
                                ->with('party', $party)
                                ->with('plan', $plan)
                                ->with('items', $items)
                                ->with('revision', $revision);
                        });
                    }
					
					//ใบเสนอราคาทำได้สองภาษา ไทยและอังกฤษ
                    $excel->sheet('ใบเสนอราคา '.$lang.' แผน '.$plan, function($sheet) use ($party, $items, $plan, $lang, $revision) 
					{
						//margin 0.5 inches
						$margin = 0.5; //set start margin
						$pageMargins = $sheet->getPageMargins();
						
						$pageMargins->setLeft($margin);
						$pageMargins->setRight($margin);
						
                        //styling sheet default
                        $sheet->setStyle(array(
                            'font' => array(
                                'name' => 'Browallia New',
                                'size' => 14
                            )
                        ));

                        $sheet->setpaperSize(1);

                        //using blade to generate
                        $sheet->loadView('reports.parties.excel.quotation')
                            ->with('party', $party)
                            ->with('plan', $plan)
                            ->with('lang', $lang)
                            ->with('items', $items)
                            ->with('revision', $revision);
                    });
                }
            }
        })
            //->export('xls')
            ->store('xls', $destinationPath);
        //log is keep when create quotation

        //return file name to allow download
        return $fileName . '.xls';
    }*/

	//function get next revision number
    static function nextRevision($type, $party_id)
    {
        $newRevision = 0;

        switch($type){
            case 'schedule' :
                //find latest revision
                $schedule = LuSchedule::where('party_id', '=', $party_id)->first();
                //get the number of revision
                $revision = (int)$schedule->revision;
                $newRevision = $revision+1;
            break;
            case 'quotation' :
                //find latest revision
                $quotation = LuBudget::where('party_id', '=', $party_id)->first();
                //get the number of revision
                $revision = (int)$quotation->revision;
                $newRevision = $revision+1;
            break;
            case 'form01' :
                //do nothing
            break;
            case 'action_plan' :
                //do nothing
            break;
        }

        return $newRevision;
    }
	
    //function update document revision number
    static function updateRevision($revision, $type, $party_id)
    {
        switch($type){
            case 'schedule' :
                //find latest revision
                $schedule = LuSchedule::where('party_id', '=', $party_id)->first();
                //find schedule find and save new revision
                $lu_schedule = LuSchedule::find($schedule->id);
                $lu_schedule->revision = $revision;
                $lu_schedule->save();
            break;
            case 'quotation' :
                //find latest revision
                $quotation = LuBudget::where('party_id', '=', $party_id)->first();
                //find schedule find and save new revision
                $lu_budget = LuBudget::find($quotation->id);
                $lu_budget->revision = $revision;
                $lu_budget->save();
                //also update revision in detail because recovery data policy
                LuBudgetDetail::where('party_id', '=', $party_id)
                    ->update(['revision' => $revision]);
            break;
            case 'form01' :
                //do nothing
            break;
            case 'action_plan' :
                //do nothing
            break;
        }

        return $revision;
    }

    //function update export log save many to log table
    static function updateExportLog($type, $path, $party_id)
    {
        //save log with schedule type
        $lu_document_exports = new LuDocumentExports;
        $lu_document_exports->party_id = $party_id;
        $lu_document_exports->type = $type;
        $lu_document_exports->path = $path;
        $lu_document_exports->created_by = Auth::user()->id;
        $lu_document_exports->updated_by = Auth::user()->id;

        return ($lu_document_exports->save()) ? true : false;
    }

    //helper function
    //thai version
    static function num2wordsThai($num)
    {
        $num=str_replace(",","",$num);
        $num_decimal=explode(".",$num);
        $num=$num_decimal[0];
        $returnNumWord="";
        $lenNumber=strlen($num);
        $lenNumber2=$lenNumber-1;
        $kaGroup=array("","สิบ","ร้อย","พัน","หมื่น","แสน","ล้าน","สิบ","ร้อย","พัน","หมื่น","แสน","ล้าน");
        $kaDigit=array("","หนึ่ง","สอง","สาม","สี่","ห้า","หก","เจ็ด","แปด","เก้า");
        $kaDigitDecimal=array("ศูนย์","หนึ่ง","สอง","สาม","สี่","ห้า","หก","เจ็ด","แปด","เก้า");
        $ii=0;
        for($i=$lenNumber2;$i>=0;$i--){
            $kaNumWord[$i]=substr($num,$ii,1);
            $ii++;
        }
        $ii=0;
        for($i=$lenNumber2;$i>=0;$i--){
            if(($kaNumWord[$i]==2 && $i==1) || ($kaNumWord[$i]==2 && $i==7)){
                $kaDigit[$kaNumWord[$i]]="ยี่";
            }else{
                if($kaNumWord[$i]==2){
                    $kaDigit[$kaNumWord[$i]]="สอง";
                }
                if(($kaNumWord[$i]==1 && $i<=2 && $i==0) || ($kaNumWord[$i]==1 && $lenNumber>6 && $i==6)){
                    if($kaNumWord[$i+1]==0){
                        $kaDigit[$kaNumWord[$i]]="หนึ่ง";
                    }else{
                        $kaDigit[$kaNumWord[$i]]="เอ็ด";
                    }
                }elseif(($kaNumWord[$i]==1 && $i<=2 && $i==1) || ($kaNumWord[$i]==1 && $lenNumber>6 && $i==7)){
                    $kaDigit[$kaNumWord[$i]]="";
                }else{
                    if($kaNumWord[$i]==1){
                        $kaDigit[$kaNumWord[$i]]="หนึ่ง";
                    }
                }
            }
            if($kaNumWord[$i]==0){
                if($i!=6){
                    $kaGroup[$i]="";
                }
            }
            $kaNumWord[$i]=substr($num,$ii,1);
            $ii++;
            $returnNumWord.=$kaDigit[$kaNumWord[$i]].$kaGroup[$i];
        }
        if(isset($num_decimal[1]))
		{
			$decimalWording = "";
			
            for($i=0;$i<strlen($num_decimal[1]);$i++)
			{
				$decimalWording.=$kaDigitDecimal[substr($num_decimal[1],$i,1)];
            }
			
			if ($decimalWording=='ศูนย์ศูนย์')
			{
				$returnNumWord.="บาทถ้วน";
			}
			else
			{
				$returnNumWord.="บาท";
				$returnNumWord.=$decimalWording;
				$returnNumWord.="สตางค์";
			}
        }
		
        return $returnNumWord;
    }

    //english version
    static function convert_number_to_words($number) {

        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = array(
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'fourty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . self::convert_number_to_words(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . self::convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = self::convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= self::convert_number_to_words($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
			
			$decimalWording = implode(' ', $words);
			
			if ($decimalWording=='zero zero')
			{
				$string .= " baht only";
			}
			else
			{
				$string .= " baht point ".$decimalWording;
			}
			
        }

        return $string;
    }

}