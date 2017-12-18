<?php

class SalesReportController extends ReportController {

    protected $lu_budget_detail;

    public function __construct(LuBudgetDetail $lu_budget_detail)
    {
        parent::__construct();
        $this->lu_budget_detail = $lu_budget_detail;
    }

    //return index page
    public function getIndex()
    {
        return 'ยังไม่มีหน้านี้';
    }

    public function getLatestPrice()
    {
        $prices = BudgetController::allDataFacilities();

        return View::make('reports.sales.latest_price', compact('prices'));
    }

}