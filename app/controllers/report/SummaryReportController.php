<?php

class SummaryReportController extends ReportController {

    protected $party;

    public function __construct(Party $party)
    {
        parent::__construct();
        $this->party = $party;
    }

    //return index page
    public function getIndex()
    {
        return '';
    }

	//return total party by area
	public function getPartyByArea()
	{
		$months = array('01'=>'มกราคม', '02'=>'กุมภาพันธ์', '03'=>'มีนาคม', '04'=>'เมษายน', '05'=>'พฤษภาคม', '06'=>'มิถุนายน', '07'=>'กรกฎาคม', '08'=>'สิงหาคม', '09'=>'กันยายน', '10'=>'ตุลาคม', '11'=>'พฤศจิกายน', '12'=>'ธันวาคม');
		$types = PartyType::select('id', 'name')->orderBy('name', 'ASC')->get();
		$service_departments = Department::whereIsLu(1)->get();

		return View::make('summaries/total/party_by_area', compact('months', 'types', 'service_departments'));
	}
	
	//return total party by type
    public function getPartyByType()
    {
		//get values to selector
		$months = array('01'=>'มกราคม', '02'=>'กุมภาพันธ์', '03'=>'มีนาคม', '04'=>'เมษายน', '05'=>'พฤษภาคม', '06'=>'มิถุนายน', '07'=>'กรกฎาคม', '08'=>'สิงหาคม', '09'=>'กันยายน', '10'=>'ตุลาคม', '11'=>'พฤศจิกายน', '12'=>'ธันวาคม');
        $types = PartyType::select('id', 'name')->orderBy('name', 'ASC')->get();
        $service_departments = Department::whereIsLu(1)->get();
		
        return View::make('summaries/total/party_by_type', compact('months', 'types', 'service_departments'));
    }
	
	//return total party by participant
    public function getPartyByParticipant()
    {
        //get values to selector
		$months = array('01'=>'มกราคม', '02'=>'กุมภาพันธ์', '03'=>'มีนาคม', '04'=>'เมษายน', '05'=>'พฤษภาคม', '06'=>'มิถุนายน', '07'=>'กรกฎาคม', '08'=>'สิงหาคม', '09'=>'กันยายน', '10'=>'ตุลาคม', '11'=>'พฤศจิกายน', '12'=>'ธันวาคม');
        $types = PartyType::select('id', 'name')->orderBy('name', 'ASC')->get();
        $service_departments = Department::whereIsLu(1)->get();
		
        return View::make('summaries/total/party_by_participant', compact('months', 'types', 'service_departments'));
    }
	
	//return total party by income
    public function getPartyByIncome()
    {
        //get values to selector
		$months = array('01'=>'มกราคม', '02'=>'กุมภาพันธ์', '03'=>'มีนาคม', '04'=>'เมษายน', '05'=>'พฤษภาคม', '06'=>'มิถุนายน', '07'=>'กรกฎาคม', '08'=>'สิงหาคม', '09'=>'กันยายน', '10'=>'ตุลาคม', '11'=>'พฤศจิกายน', '12'=>'ธันวาคม');
        $types = PartyType::select('id', 'name')->orderBy('name', 'ASC')->get();
        $service_departments = Department::whereIsLu(1)->get();
		
        return View::make('summaries/total/party_by_income', compact('months', 'types', 'service_departments'));
    }
	
	//return total party by type execution
	public function postPartyByType()
    {
		//set array to return values 
		$summaries = array();
		//get input
        $report_type = Input::get('report_type');
		//set default
		$report_month = date('m');
		$report_year = (date('Y'))+543;
		$report_month_end = null;
		$report_year_end = null;
		$country = Input::get('country');
		$party_type = Input::get('party_type');
		$service = Input::get('service');
		
		//setting day month year
		switch($report_type)
		{
			case 'monthly' :
				$report_month = Input::get('report_month_monthly');
				$report_year = Input::get('report_year_monthly');
				$report_month_end = Input::get('report_month_monthly_end');
				$report_year_end = Input::get('report_year_monthly_end');
			break;
			case 'quarter' :
				$report_month = Input::get('report_month_quarter');
				$report_year = Input::get('report_year_quarter');
			break;
			case 'yearly' :
				$report_month = '01';
				$report_year = Input::get('report_year_yearly');
			break;
			case 'budget' :
				$report_month = '10';
				$report_year = Input::get('report_year_budget');
			break;
		}

		//get range to scope
		$range = ReportController::getReportRange($report_type, $report_month, $report_year, $report_month_end, $report_year_end);
		
		//set type column control
		if ($party_type=="")
		{
			//case no select type
			$party_types = PartyType::all();
		}
		else
		{
			//case selected type
			$party_types = PartyType::where('id', '=', $party_type)->get();
		}
		
		//loop fill column control
		$summaries['types'] = array();//keep types
		//$summaries['type_index'] = array();//keep type for indexing
		$summaries['type_totals'] = array();//keep summaries type column

		foreach($party_types as $type)
		{
			//$totally = 0;
			array_push($summaries['types'], $type->name);
			
			//also add total summary column
			$summaries['type_totals'][$type->name] = array();
			
			$column_thai = Party::where('party_type_id', '=', $type->id)
					->where('start_date', '>=', $range['start'])
					->where('start_date', '<=', $range['end']);

			$column_inter = Party::where('party_type_id', '=', $type->id)
				->where('start_date', '>=', $range['start'])
				->where('start_date', '<=', $range['end']);
			
			if ($service!="")
			{
				$column_thai = $column_thai->where(DB::raw('LEFT(customer_code, 2)'), '=', $service);
				$column_inter = $column_inter->where(DB::raw('LEFT(customer_code, 2)'), '=', $service);
			}		
			
			if ($country=="")
			{
				//case no selection
				$column_thai = $column_thai->whereIn('id', function($q) use ($country){
													$q->select('party_id')->from('party_nations')->where('country', '=', 'th');
												})->count();
												
				$column_inter = $column_inter->whereIn('id', function($q) use ($country){
													$q->select('party_id')->from('party_nations')->whereNotIn('country', array('th'));
												})->count();
				
				$summaries['type_totals'][$type->name]['th'] = $column_thai;
												
				$summaries['type_totals'][$type->name]['inter'] = $column_inter;

				//$totally = $column_thai+$column_inter;
			}
			else
			{
				if ($country=="th")
				{
					//case select only thailand 
					$summaries['type_totals'][$type->name]['th'] = $column_thai->whereIn('id', function($q) use ($country){
														$q->select('party_id')->from('party_nations')->where('country', '=', 'th');
													})
													->count();

					//$totally = $summaries['type_totals'][$type->name]['th'];
				}
				else
				{
					//case select only international
					$summaries['type_totals'][$type->name]['inter'] = $column_inter->whereIn('id', function($q) use ($country){
															$q->select('party_id')->from('party_nations')->whereNotIn('country', array('th'));
														})
														->count();

					//$totally = $summaries['type_totals'][$type->name]['inter'];
				}
			}
			//push total for indexing
			//array_push($summaries['type_index'], $totally);
		}
		
		//set total array to keep for rounds
		$summaries['summaries'] = array();
		
		//loop round and insert total
		$rounds = ReportController::getReportRounds($report_type, $report_month, $report_year, $report_month_end, $report_year_end);
		
		//execution total cell
		$i = 0;
		foreach($rounds as $round)
		{
			//set round total default
			$round_total = 0;
			//set array to keep total
			$totals = array();
			
			$totals['month'] = $round['month'];
			$totals['year'] = $round['year'];
			
			foreach($party_types as $type)
			{
				
				//set type array to keep total
				$totals['totals'][$type->name] = array();
				
				//query total summary
				$total_thai = Party::whereRaw(DB::raw('MONTH(start_date) = ? and YEAR(start_date) = ?'), array($round['month'], $round['year']))
						->where('party_type_id', '=', $type->id);
				$total_inter = Party::whereRaw(DB::raw('MONTH(start_date) = ? and YEAR(start_date) = ?'), array($round['month'], $round['year']))
					->where('party_type_id', '=', $type->id);
				
				//select case by filter departments
				if ($service!="")
				{
					$total_thai = $total_thai->where(DB::raw('LEFT(customer_code, 2)'), '=', $service);
					$total_inter = $total_inter->where(DB::raw('LEFT(customer_code, 2)'), '=', $service);
				}		
						
				//report case if select country type or no select
				if ($country=="")
				{
					//case no selection
					$total_thai_qty = $total_thai->whereIn('id', function($q) use ($country){
													$q->select('party_id')->from('party_nations')->where('country', '=', 'th');
												})
												->count();
					
					$total_inter_qty = $total_inter->whereIn('id', function($q) use ($country){
													$q->select('party_id')->from('party_nations')->whereNotIn('country', array('th'));
												})
												->count();
					
					$totals['totals'][$type->name]['th'] = $total_thai_qty;
													
					$totals['totals'][$type->name]['inter'] = $total_inter_qty;

					$round_total += ($totals['totals'][$type->name]['th'])+($totals['totals'][$type->name]['inter']);
				}
				else
				{
					if ($country=="th")
					{
						//case select only thailand 
						$totals['totals'][$type->name]['th'] = $total_thai->whereIn('id', function($q) use ($country){
														$q->select('party_id')->from('party_nations')->where('country', '=', 'th');
													})
													->count();
													
						$round_total += ($totals['totals'][$type->name]['th']);
					}
					else
					{
						//case select only international
						$totals['totals'][$type->name]['inter'] = $total_inter->whereIn('id', function($q) use ($country){
															$q->select('party_id')->from('party_nations')->whereNotIn('country', array('th'));
														})
														->count();	
																
						$round_total += ($totals['totals'][$type->name]['inter']);
					}
				}	

			}
			
			$totals['round_total'] = $round_total;
			
			array_push($summaries['summaries'], $totals);
			
			$i++;
		}

		return $summaries;
	}

	//return total party by area execution
	public function postPartyByArea()
	{
		//set array to return values
		$summaries = array();
		//get input
		$report_type = Input::get('report_type');
		//set default
		$report_month = date('m');
		$report_year = (date('Y'))+543;
		$report_month_end = null;
		$report_year_end = null;
		$country = Input::get('country');
		$party_type = Input::get('party_type');
		$area = Input::get('area');

		//setting day month year
		switch($report_type)
		{
			case 'monthly' :
				$report_month = Input::get('report_month_monthly');
				$report_year = Input::get('report_year_monthly');
				$report_month_end = Input::get('report_month_monthly_end');
				$report_year_end = Input::get('report_year_monthly_end');
				break;
			case 'quarter' :
				$report_month = Input::get('report_month_quarter');
				$report_year = Input::get('report_year_quarter');
				break;
			case 'yearly' :
				$report_month = '01';
				$report_year = Input::get('report_year_yearly');
				break;
			case 'budget' :
				$report_month = '10';
				$report_year = Input::get('report_year_budget');
				break;
		}

		//get range to scope
		$range = ReportController::getReportRange($report_type, $report_month, $report_year, $report_month_end, $report_year_end);

		//loop round and insert total
		$rounds = ReportController::getReportRounds($report_type, $report_month, $report_year, $report_month_end, $report_year_end);

		//execution total cell
		$i = 0;
		foreach($rounds as $round)
		{
			//set round total default
			$round_total = 0;
			//set array to keep total
			$totals = array();

			$totals['month'] = $round['month'];
			$totals['year'] = $round['year'];

			$totals['round_total'] = $round_total;

			array_push($summaries, $totals);

			$i++;
		}

		return $summaries;
	}
	
	//return total party by participant execution
	public function postPartyByParticipant()
    {
		//get input
        $report_type = Input::get('report_type');
		//set default
		$report_month = date('m');
		$report_year = (date('Y'))+543;
		$report_month_end = null;
		$report_year_end = null;
		$country = Input::get('country');
		$party_type = Input::get('party_type');
		$service = Input::get('service');
		
		//execution total
		$summaries = array();
		switch($report_type)
		{
			case 'monthly' :
				$report_month = Input::get('report_month_monthly');
				$report_year = Input::get('report_year_monthly');
				$report_month_end = Input::get('report_month_monthly_end');
				$report_year_end = Input::get('report_year_monthly_end');
			break;
			case 'quarter' :
				$report_month = Input::get('report_month_quarter');
				$report_year = Input::get('report_year_quarter');
			break;
			case 'yearly' :
				$report_month = '01';
				$report_year = Input::get('report_year_yearly');
			break;
			case 'budget' :
				$report_month = '10';
				$report_year = Input::get('report_year_budget');
			break;
		}
		
		//loop round and insert total
		$rounds = ReportController::getReportRounds($report_type, $report_month, $report_year, $report_month_end, $report_year_end);
				
		$i = 0;
		foreach($rounds as $round)
		{
			$total = Party::whereRaw(DB::raw('MONTH(start_date) = ? and YEAR(start_date) = ?'), array($round['month'], $round['year']));
			
			if ($party_type!="")
			{
				$total = $total->where('party_type_id', '=', $party_type);
			}
			
			if ($service!="")
			{
				$total = $total->where(DB::raw('LEFT(customer_code, 2)'), '=', $service);
			}
			
			if ($country!='')
			{
				if ($country=='th')
				{
					$total = $total->whereIn('id', function($q) use ($country){
								$q->select('party_id')->from('party_nations')->where('country', '=', 'th');
							});
				}
				else
				{
					$total = $total->whereIn('id', function($q) use ($country){
								$q->select('party_id')->from('party_nations')->whereNotIn('country', array('th'));
							});
				}
			}
			
			$summaries[$i]['month'] = $round['month'];
			$summaries[$i]['year'] = $round['year'];
			$summaries[$i]['total_people_qty'] = $total->sum('people_quantity'); 
			$i++;
		}
		
		return $summaries;
	}
	
	//return total party by income execution
	public function postPartyByIncome()
    {
        //get input
        $report_type = Input::get('report_type');
		//set default
		$report_month = date('m');
		$report_year = (date('Y'))+543;
		$report_month_end = null;
		$report_year_end = null;
		$country = Input::get('country');
		$party_type = Input::get('party_type');
		$service = Input::get('service');
		
		//execution total
		$summaries = array();
		switch($report_type)
		{
			case 'monthly' :
				$report_month = Input::get('report_month_monthly');
				$report_year = Input::get('report_year_monthly');
				$report_month_end = Input::get('report_month_monthly_end');
				$report_year_end = Input::get('report_year_monthly_end');
			break;
			case 'quarter' :
				$report_month = Input::get('report_month_quarter');
				$report_year = Input::get('report_year_quarter');
			break;
			case 'yearly' :
				$report_month = '01';
				$report_year = Input::get('report_year_yearly');
			break;
			case 'budget' :
				$report_month = '10';
				$report_year = Input::get('report_year_budget');
			break;
		}
		
		//loop round and insert total
		$rounds = ReportController::getReportRounds($report_type, $report_month, $report_year, $report_month_end, $report_year_end);
				
		$i = 0;
		foreach($rounds as $round)
		{
			$total = Party::whereRaw(DB::raw('MONTH(start_date) = ? and YEAR(start_date) = ?'), array($round['month'], $round['year']));
			
			if ($party_type!="")
			{
				$total = $total->where('party_type_id', '=', $party_type);
			}
			
			if ($service!="")
			{
				$total = $total->where(DB::raw('LEFT(customer_code, 2)'), '=', $service);
			}
			
			if ($country!='')
			{
				if ($country=='th')
				{
					$total = $total->whereIn('id', function($q) use ($country){
								$q->select('party_id')->from('party_nations')->where('country', '=', 'th');
							});
				}
				else
				{
					$total = $total->whereIn('id', function($q) use ($country){
								$q->select('party_id')->from('party_nations')->whereNotIn('country', array('th'));
							});
				}
			}
			
			$summaries[$i]['month'] = $round['month'];
			$summaries[$i]['year'] = $round['year'];
			$summaries[$i]['total_party_qty'] = $total->count(); 
			$summaries[$i]['total_people_qty'] = $total->sum('people_quantity'); 
			$summaries[$i]['total_income'] = $total->sum('summary_income'); 
			$i++;
		}
		
		return $summaries;
    }

}