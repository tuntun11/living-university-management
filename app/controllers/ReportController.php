<?php

class ReportController extends BaseController {

    /**
     * Initializer.
     *
     * @return \ReportController
     */
    public function __construct()
    {
        parent::__construct();
    }

    //return index page
    public function getIndex()
    {
        return View::make('svms/report/index');
    }
	
	/*static function*/
	//return start or end date to define range
	public static function getReportRange($type, $start_month, $start_year, $end_month = null, $end_year = null)
	{
		$range = array();
		
		$day_end_month = '31';
		if ($end_month=='02')
		{
			$day_end_month = '29';
		}
		if ($end_month=='04' || $end_month=='06' || $end_month=='09' || $end_month=='11')
		{
			$day_end_month = '30';
		}
		
		switch($type)
		{
			case 'monthly' :
			
				/*return month by selection*/
				$range = array(
									'start' => (string)((int)$start_year-543).'-'.$start_month.'-01', 
									'end' => (string)((int)$end_year-543).'-'.$end_month.'-'.$day_end_month		
								);
				
			break;
			case 'quarter' :
			
				/*return 3 month*/
				$range = array(
								'start' => (string)((int)$start_year-543).'-'.$start_month.'-01', 
								'end' => (string)((int)$start_year-543).'-'.str_pad((string)((int)$end_month+3),1,"0",STR_PAD_LEFT).'-'.$day_end_month		
							);
				
			break;
			case 'yearly' :
			
				/*return 12 month*/
				$range = array(
								'start' => (string)((int)$start_year-543).'-01-01', 
								'end' => (string)((int)$start_year-543).'-12-'.$day_end_month		
							);
						
			break;
			case 'budget' :
			
				/*return 12 month start 1 oct before year*/
				$start_year = ($start_year)-543;
				
				$range = array(
							'start' => (string)($start_year-1).'-10-01', 
							'end' => (string)($start_year).'-09-'.$day_end_month		
						);
	
			break;
		}
		
		return $range;
	}
	
	//return round include month and year
	public static function getReportRounds($type, $start_month, $start_year, $end_month = null, $end_year = null)
	{
		$rounds = array();
		
		switch($type)
		{
			case 'monthly' :
			
				/*return month by selection*/
				$strStart = (string)((int)$start_year-543).'-'.$start_month.'-01';
				$start = new DateTime($strStart);
				$interval = new DateInterval('P1M');
				$day_end_month = '31';
				if ($end_month=='02')
				{
					$day_end_month = '28';
				}
				if ($end_month=='04' || $end_month=='06' || $end_month=='09' || $end_month=='11')
				{
					$day_end_month = '30';
				}
				$strEnd = (string)((int)$end_year-543).'-'.$end_month.'-'.$day_end_month;
				$end = new DateTime($strEnd);
				
				$period = new DatePeriod($start, $interval, $end);

				$i = 0;
				foreach ($period as $dt) 
				{
					$mm = $dt->format('m');
					$yy = $dt->format('Y');
					
					$rounds[$i] = array('month' => (string)$mm, 'year' => (string)$yy);
					$i++;
				}
				
			break;
			case 'quarter' :
			
				/*return 3 month*/
				$range = 2;
				
				$i = 0;
				for($m=(int)$start_month;$m<=((int)$start_month)+$range;$m++)
				{
					$rounds[$i] = array('month' => str_pad($m,1,"0",STR_PAD_LEFT), 'year' => (string)(($start_year)-543));
					$i++;
				}
				
			break;
			case 'yearly' :
			
				/*return 12 month*/
				$range = 11;
				
				$i = 0;
				for($m=(int)$start_month;$m<=((int)$start_month)+$range;$m++)
				{
					$rounds[$i] = array('month' => str_pad($m,1,"0",STR_PAD_LEFT), 'year' => (string)(($start_year)-543));
					$i++;
				}
				
			break;
			case 'budget' :
			
				/*return 12 month start 1 oct before year*/
				$start_year = ($start_year)-543;
				
				$rounds[0] = array('month' => '10', 'year' => (string)($start_year-1));
				$rounds[1] = array('month' => '11', 'year' => (string)($start_year-1));
				$rounds[2] = array('month' => '12', 'year' => (string)($start_year-1));
				$rounds[3] = array('month' => '01', 'year' => (string)($start_year));
				$rounds[4] = array('month' => '02', 'year' => (string)($start_year));
				$rounds[5] = array('month' => '03', 'year' => (string)($start_year));
				$rounds[6] = array('month' => '04', 'year' => (string)($start_year));
				$rounds[7] = array('month' => '05', 'year' => (string)($start_year));
				$rounds[8] = array('month' => '06', 'year' => (string)($start_year));
				$rounds[9] = array('month' => '07', 'year' => (string)($start_year));
				$rounds[10] = array('month' => '08', 'year' => (string)($start_year));
				$rounds[11] = array('month' => '09', 'year' => (string)($start_year));
				
			break;
		}
		
		return $rounds;
	}
	
}