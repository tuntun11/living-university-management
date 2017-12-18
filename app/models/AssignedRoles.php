<?php

class AssignedRoles extends Eloquent {
    protected $guarded = array();

    public static $rules = array();

    //return mail of role as array
    public function getMailsByRole($roles = array('admin'), $is_mail_cc = 0)
    {
        $mail_lists = array();

        $mails = $this->select('users.email')
            ->leftJoin('roles', 'assigned_roles.role_id', '=', 'roles.id')
            ->leftJoin('users', 'assigned_roles.user_id', '=', 'users.id')
            ->whereIn('roles.name', $roles);

        //หากเป็นเมล CC
        if ($is_mail_cc)
        {
            //ทำการกรองแต่ผู้ที่ต้องการรับเมล CC
            $mails = $mails->where('users.can_receive_cc_mail', '=', 1);
        }

        $mails = $mails->where('users.confirmed', '=', 1)
            ->whereNull('users.deleted_at')
            ->get();

        foreach($mails as $mail)
        {
            array_push($mail_lists, $mail->email);
        }

        return $mail_lists;
    }

}