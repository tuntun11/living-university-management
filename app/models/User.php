<?php

use Zizaco\Confide\ConfideUser;
use Zizaco\Confide\ConfideUserInterface;
use Zizaco\Entrust\HasRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class User extends Eloquent implements ConfideUserInterface {
    use ConfideUser, HasRole, SoftDeletingTrait;

    public function isPersonnel()
    {
        return $this->hasOne('PersonnelUser');
    }

    function getPerson($id)
    {
        $personnel = Personnel::leftJoin('personnel_users as u', 'personnels.id', '=', 'u.personnel_id')
            ->where('u.user_id', '=', $id)
            ->first();

        return ($personnel) ? $personnel : false;
    }

    public function getPersonnel()
    {
        return $this->getPerson($this->id);
    }
	
	public function getOnlyName()
    {
        return $this->getPerson($this->id)->first_name;
    }

    public function getShortName()
    {
        return "คุณ".$this->getPerson($this->id)->first_name;
    }

    public function getFullName()
    {
        return $this->getPerson($this->id)->prefix.$this->getPerson($this->id)->first_name." ".$this->getPerson($this->id)->last_name;
    }

    //return sign name
    public function getSignName($en = 0)
    {
        return ($en) ? $this->getPerson($this->id)->prefix_en.$this->getPerson($this->id)->first_name_en.' '.$this->getPerson($this->id)->last_name_en : $this->getPerson($this->id)->prefix.$this->getPerson($this->id)->first_name.' '.$this->getPerson($this->id)->last_name;
    }

    //return sign office
    public function getDepartment($en = 0)
    {
        return ($en) ? $this->getPerson($this->id)->department->name_en : $this->getPerson($this->id)->department->name;
    }

    //Return who in department except any process เช่นไม่ต้องแนบศทบ.
    public function canFastTrack()
    {
        return $this->getPerson($this->id)->department->can_fast_track;
    }

    public function getUserByUsername( $username )
    {
        return $this->where('username', '=', $username)->first();
    }

    //return to do list for reviewer or manager
    public function getCountTasks()
    {
        $tasks = array();

        if (Auth::user()->hasRole('reviewer'))
        {
            $tasks['reviewer'] = ReviewerController::countTask();
        }

        if (Auth::user()->hasRole('manager'))
        {
            $tasks['manager'] = ManagerController::countTask();
        }

        return $tasks;
    }

    /*Return authorize to view full calendar*/
    //can manage view full calendar (View full calendar in dashboard)
    public function canViewFullCalendar()
    {
        if ($this->getPerson($this->id)->can_view_fullcalendar==1)
        {
            //case can view
            return true;
        }
        else
        {
            //if manager or upper
            if (Auth::user()->hasRole('vip') || Auth::user()->hasRole('reviewer') || Auth::user()->hasRole('manager'))
            {
                //case can view if executive
                return true;
            }
            else
            {
                //case not view or un-authorize
                return false;
            }
        }
    }

    /*Return authorize to manage data (Party Information)*/
    //can manage party data (Manager is All, Project Co is Only assigned)
    public function canManageParty($party = null)
    {
        if(Auth::user()->hasRole('vip') || Auth::user()->hasRole('reviewer') || Auth::user()->hasRole('contributor'))
        {
            /*หากไม่ใช่ manager หรือ project co ไม่สามารถจัดการข้อมูลได้*/
            return false;
        }
        else
        {
            if ($party == null)
            {
                return false;
            }
            else
            {
                /*always true for manager*/
                if (Auth::user()->hasRole('manager'))
                {
                    return true;
                }
                else
                {
                    /*สำหรับ Project Co หากไม่ใช่งานที่ถูก assign ไม่สามารถทำงานได้*/
                    if ($party->assignmentToManage())
                    {
                        return true;
                    }
                    else
                    {
                        return false;
                    }
                }
            }
        }
    }
    //can edit numeral in budget (Manager is True, Project Co is False)
    public function canEditBudget()
    {
        if(Auth::user()->hasRole('manager'))
        {
            /*true only manager*/
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Find the user and check whether they are confirmed
     *
     * @param array $identity an array with identities to check (eg. ['username' => 'test'])
     * @return boolean
     */
    public function isConfirmed($identity) {
        $user = Confide::getUserByEmailOrUsername($identity);
        return ($user && $user->confirmed);
    }

    /**
     * Get the date the user was created.
     *
     * @return string
     */
    public function joined()
    {
        return String::date(Carbon::createFromFormat('Y-n-j G:i:s', $this->created_at));
    }

    /**
     * Save roles inputted from multiselect
     * @param $inputRoles
     */
    public function saveRoles($inputRoles)
    {
        if(! empty($inputRoles)) {
            $this->roles()->sync($inputRoles);
        } else {
            $this->roles()->detach();
        }
    }

    /**
     * Returns user's current role ids only.
     * @return array|bool
     */
    public function currentRoleIds()
    {
        $roles = $this->roles;
        $roleIds = false;
        if( !empty( $roles ) ) {
            $roleIds = array();
            foreach( $roles as $role )
            {
                $roleIds[] = $role->id;
            }
        }
        return $roleIds;
    }

    /**
     * Returns user's role name
     * @return string
     */
    public function roleDisplay()
    {
        $roles = $this->roles->toArray();

        $roleDisplays = "";

        foreach($roles as $role)
        {
            $roleDisplays .= ucfirst($role['name']).',';
        }

        return substr($roleDisplays,0,-1);
    }

    /**
     * Redirect after auth.
     * If ifValid is set to true it will redirect a logged in user.
     * @param $redirect
     * @param bool $ifValid
     * @return mixed
     */
    public static function checkAuthAndRedirect($redirect, $ifValid=false)
    {
        // Get the user information
        $user = Auth::user();
        $redirectTo = false;

        if(empty($user->id) && ! $ifValid) // Not logged in redirect, set session.
        {
            Session::put('loginRedirect', $redirect);
            $redirectTo = Redirect::to('user/login')
                ->with( 'notice', Lang::get('user/user.login_first') );
        }
        elseif(!empty($user->id) && $ifValid) // Valid user, we want to redirect.
        {
            $redirectTo = Redirect::to($redirect);
        }

        return array($user, $redirectTo);
    }

    public function currentUser()
    {
        return Confide::user();
    }

    /**
     * Get the e-mail address where password reminders are sent.
     *
     * @return string
     */
    public function getReminderEmail()
    {
        return $this->email;
    }

}
