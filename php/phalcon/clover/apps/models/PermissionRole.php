<?php

namespace Psgod\Models;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Psgod\Models\ActionLog;


class PermissionRole extends ModelBase
{

    public function getSource()
    {
        return 'permission_roles';
    }

    public static function get_permission($uid, $controller_name, $action_name)
    {
        // $ur = UserRole::findFirst(array("uid = {$uid}"));
        // $p  = Permission::findFirst(array("controller_name = {$controller_name} AND action_name = {$action_name}"));
        // if($ur && $p) {
        //     return self::find()
        // }
    }

    public static function get_permissions_by_role_id($role_id){
        $sql = "SELECT GROUP_CONCAT(`permission_id`) as pids FROM permission_roles WHERE `role_id` = '{$role_id}'";
        $per_role_model = new self();

        // Execute the query
        $res = new Resultset( null, $per_role_model, $per_role_model->getReadConnection()->query($sql) );

        if(empty($res)){
            $res = array();
        }
        else{
            $res = $res -> toArray();
            $res = $res[0]['pids'];
        }
        return $res;
    }

    /**
     * [update_permissions 更新角色的权限]
     * @param  [integer] $role_id      角色id
     * @param  [mixed] $permission_ids  可传单个id，或数组
     * @return [boolean]                返回boolean
     */
    public static function update_permissions( $role_id, $permission_ids ){
        if( empty( $role_id) || !is_numeric($role_id) ){
            return false;
        }

        if( !is_array($permission_ids) ){
            $permission_ids = explode(',', $permission_ids);
        }
        if( empty($permission_ids) ){
            return false;
        }


        $per_role_model = new self();

        $pers = PermissionRole::get_permissions_by_role_id( $role_id );
        $pers = explode(',', $pers);

        $add_pers = array_filter( array_diff( $permission_ids, $pers ) );
        $del_pers = array_filter( array_diff( $pers, $permission_ids ) );

        //add previleges
        foreach( $add_pers as $key => $per_id ){
            $pre = new PermissionRole();
            $pre->role_id= $role_id;
            $pre->permission_id = $per_id;
            $pre->save();
        }

        if(!empty($del_pers)){
            $sql = "DELETE FROM permission_roles WHERE role_id='{$role_id}' AND permission_id IN(".implode(',', $del_pers).")";
            $res = new Resultset( null, $per_role_model, $per_role_model->getReadConnection()->query($sql) );
            if( $res ){
                //ActionLog::log(ActionLog::TYPE_REVOKE_PRIVILEGE, array(), $res);
            }
        }
        return true;
    }
}
