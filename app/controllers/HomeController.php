<?php

class HomeController extends BaseController {

    protected $user;

    protected $party;

    protected $personnel;

    /**
     * Inject the models.
     * @param Post $post
     * @param User $user
     */
    public function __construct(User $user, Party $party, Personnel $personnel)
    {
        parent::__construct();

        $this->user = $user;
        $this->party = $party;
        $this->personnel = $personnel;
    }

    public function getIndex()
    {
        //check 1 roles if have only roles redirect to dashboard or landing page by user's role
        if (Auth::user()->hasRole('contributor'))
        {
            //do nothing
            return View::make('svms.dashboard');
        }
        else if (Auth::user()->hasRole('admin'))
        {
            $user_all_count = 0;

            return View::make('svms.dashboard', compact('user_all_count'));
        }
        else
        {
            //get personnel can operating
            $personnels = $this->personnel->canCoordinate()->get();

            if (Auth::user()->hasRole('reviewer') || Auth::user()->hasRole('manager') || Auth::user()->hasRole('vip'))
            {
                //for vip reviewer manager
                $parties = $this->party->getAllData();
            }
            else
            {
                //for project co
                if (Auth::user()->canViewFullCalendar())
                {
                    //case can view full
                    $parties = $this->party->getAllData();
                }
                else
                {
                    //case not
                    $parties = $this->party->getManagerPassed();
                    $parties = $parties->select('parties.*', DB::raw('DATE_FORMAT(ma.created_at, "%Y") AS approved_year'), DB::raw('DATE_FORMAT(ma.created_at, "%c") AS approved_month'), DB::raw('DATE_FORMAT(ma.created_at, "%e") AS approved_date'));
                    //query except finished or finishing
                    $parties = $parties->whereNotIn('parties.status', array('cancelled1', 'cancelled2', 'terminated', 'other', 'finished'));
                }
            }

            $parties = $parties->orderBy('parties.start_date', 'ASC')
                ->get();

            return View::make('svms.dashboard', compact('parties', 'personnels'));
        }
    }

}
