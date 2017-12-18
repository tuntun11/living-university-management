<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Personnel extends Eloquent {

    use SoftDeletingTrait;

    protected $table = 'personnels';
    protected $fillable = ['prefix', 'first_name', 'last_name', 'email', 'mobile'];
    protected $guarded = ['id'];

    public function isUser()
    {
        return $this->hasOne('PersonnelUser');
    }

    //Return personnel can coordinate
    public function canCoordinate()
    {
        return $this->leftJoin('personnel_users AS pu', 'personnels.id', '=', 'pu.personnel_id')
            ->leftJoin('users AS u', 'pu.user_id', '=', 'u.id')
            ->leftJoin('assigned_roles AS ar', 'u.id', '=', 'ar.user_id')
            ->whereIn('ar.role_id', array(2))//2 is project coordinator
            ->whereStatus('active')
            ->where('u.is_developer', '=', 0) //also is not systems developer
            ->select('personnels.*');
    }

    public function department()
    {
        return $this->belongsTo('Department');
    }

    //has one assigned expert type
    public function assignedExpert()
    {
        return $this->hasOne('PersonnelAssignedType');
    }

    //related personnel expert type
    public function personExpertTypes()
    {
        return $this->hasMany('PersonnelExpertTypes');
    }

    //related expert type
    /*public function expertTypes()
    {
        return $this->hasManyThrough('ExpertType', 'PersonnelExpertTypes', 'personnel_id', 'expert_type_id');
    }*/

    //return work base
    public function WorkBase()
    {
        return ($this->work_base) ? MflfArea::find($this->work_base)->name : 'ไม่ได้ระบุ';
    }

    //return ethnic name
    public function ethnic()
    {
        return DB::table('ethnic')->whereId($this->ethnic)->pluck('name');
    }

    //return expert type for one personnel
    public function expertType($short = false, $label = true)
    {
        $types = $this->personExpertTypes()->get();
        $text = "";

        foreach($types as $type)
        {
            $type_name = ExpertType::find($type->expert_type_id)->name;

            if ($short)
            {
                $type_name = str_replace("วิทยากร","",$type_name);
            }

            if ($label)
            {
                $text.= "<span class='label label-default'>".$type_name."</span> ";
            }
            else
            {
                $text.= $type_name;
            }
        }

        return $text;
    }

    //return personnel paid type can use morph
    public function expertPaidType()
    {
        $type = PersonnelType::leftJoin('personnel_assigned_type AS pt', 'personnel_types.id', '=', 'pt.personnel_type_id')
                        ->where('pt.personnel_id', '=', $this->id)
                        ->first();

        return $type;
    }

    //personnel picture
    public function image($w = 100, $h = 100)
    {
        //set image real path
        $images = 'svms/personnel_image/'.$this->id.'.jpg';
        $file_path = public_path($images);
        //set image url
        if (File::exists($file_path))
        {
            $pic = asset($images);
        }
        else
        {
            $pic = asset('assets/img/people.png');
        }

        return '<img class="img-round img-responsive" src="'.$pic.'" border="0" width="'.$w.'" height="'.$h.'" />';
    }

    //personnel image path
    public function imagePath()
    {
        //set image real path
        $images = 'svms/personnel_image/'.$this->id.'.jpg';
        $file_path = public_path($images);
        //set image url
        if (File::exists($file_path))
        {
            $pic_path = asset($images);
        }
        else
        {
            $pic_path = asset('assets/img/people.png');
        }

        return $pic_path;
    }
	
	//personnel is administrator 
    public function isAdministrator()
    {
        return $this->whereIsAdministrator(1);
    }
	
	//personnel staff 
    public function staffs()
    {
        return $this->hasMany('LuOverallStaff');
    }

    //return short name
    public function shortName()
    {
        $nickName = ($this->nick_name) ? '('.$this->nick_name.')' : '';

        return $this->first_name.''.$nickName;
    }

    //return full name
    public function fullName($lang = 'th')
    {
        return ($lang=='th') ? $this->prefix.$this->first_name.' '.$this->last_name : $this->prefix_en.$this->first_name_en.' '.$this->last_name_en;
    }
	
	//return full name with code name
    public function fullNameWithCodeName()
    {
		$codeName = ($this->codename) ? '('.$this->codename.')' : ''; 
		
        return $this->prefix.$this->first_name.' '.$this->last_name.' '.$codeName;
    }

    //return full name with nick name
    public function fullNameWithNickName()
    {
        $nickName = ($this->nick_name) ? '('.$this->nick_name.')' : '';

        return $this->prefix.$this->first_name.' '.$this->last_name.' '.$nickName;
    }

    /*for Expert Usage*/
    //latest status
    public function latestStatus()
    {
        return $this->statuses()->orderBy('created_at', 'desc')->first();
    }

    public function status()
    {
        $status = $this->status;
        switch($status)
        {
            case 'leave' :
                $thai_stat = ($this->status_note) ? 'งดเว้นการปฎิบัติงานชั่วคราว เนื่องจาก '.$this->status_note : 'งดเว้นการปฎิบัติงานชั่วคราว';
            break;
            case 'quit' :
                $thai_stat = ($this->status_note) ? 'ออกจากการเป็นวิทยากร เนื่องจาก '.$this->status_note : 'ออกจากการเป็นวิทยากร';
            break;
            default :
                $thai_stat = "ปฎิบัติงานได้";
        }

        return $thai_stat;
    }

    //status history
    public function statuses()
    {
        return $this->hasMany('PersonnelStatuses');
    }

    //return list of name expert or specialist
    public function specialistPersons()
    {
        return $this->whereIsExpert(1);
    }

    //return list of name personnel can right now operating.
    public function canOperating()
    {
        return $this->whereStatus('active');
    }

    //related with educations
    public function educations()
    {
        return $this->hasMany('PersonnelEducations');
    }

    //related with lecture languages
    public function lectureLanguages()
    {
        return $this->hasMany('PersonnelLectureLanguages');
    }

    //related with lecture subjects
    public function lectureSubjects()
    {
        return $this->hasMany('PersonnelLectureSubjects');
    }

    //related with training sessions
    public function trainingSessions()
    {
        return $this->hasMany('PersonnelTrainingSessions');
    }

    //related with classroom bases
    public function classroomBases()
    {
        return $this->hasMany('PersonnelClassroomBases');
    }

    //related with work experiences
    public function workExperiences()
    {
        return $this->hasMany('PersonnelWorkExperiences');
    }

    //related with files
    public function files()
    {
        return $this->hasMany('PersonnelFiles');
    }

    //return array education
    static public function listEducations()
    {
        $educations = array('ประถมศึกษา', 'มัธยมศึกษาตอนต้น', 'มัธยมศึกษาตอนปลาย', 'ปวช.', 'ปวส.', 'อนุปริญญา', 'ปริญญาตรี', 'ปริญญาโท', 'ปริญญาเอก');

        return $educations;
    }

    //return array languages level
    static public function listLanguageLevels()
    {
        $language_levels = array('ไม่ได้', 'พอใช้', 'ดี', 'ดีเยี่ยม');

        return $language_levels;
    }

    //return array status
    static public function listStatuses()
    {
        $statuses = array('active' => 'ปฎิบัติงานได้', 'leave' => 'งดเว้นการปฎิบัติงานชั่วคราว', 'quit' => 'ออกจากการเป็นวิทยากร');

        return $statuses;
    }

}