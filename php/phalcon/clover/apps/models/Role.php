<?php
namespace Psgod\Models;

class Role extends ModelBase
{
    const TYPE_HELP     = 1;
    const TYPE_WORK     = 2;
    const TYPE_PARTTIME = 3;
    const TYPE_STAFF    = 4;
    const TYPE_JUNIOR   = 5;

    public static function setRole($role_id = null, $role_name, $role_display_name)
    {
        $role = new self();
        $role->id  = $role_id; 
        $role->name= $role_name;
        $role->display_name = $role_display_name;
        $role->create_time = time();
        $role->update_time = time();
        
        if ($role_id) {
            $ori_role = self::findFirst("id = $role_id");
            $role->create_time = $ori_role->create_time;
        }
        return $role->save_and_return($role, true);
        // if ($role->save() == false) {
        //     return false;
        // } else {
        //     return $role;
        // }
    }
    public function getSource()
    {
        return 'roles';
    }
}
