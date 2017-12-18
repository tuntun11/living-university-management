<?php

class AdminPartyDashboardController extends \AdminController {

    public function getIndex()
    {
        // return living university party dashboard
        return View::make('admin.party.dashboard');
    }


}