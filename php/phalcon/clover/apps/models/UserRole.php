<?php
namespace Psgod\Models;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class UserRole extends ModelBase
{

    const ROLE_HELP = 1;
    const ROLE_WORK = 2;
    const ROLE_PARTTIME = 3;
    const ROLE_STAFF    = 4;

    public function getSource()
    {
        return 'user_roles';
    }
    
    /**
     * 新添加用户
     * 
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $nickname 昵称
     * @param integer$phone    手机号码
     * @param string $email    邮箱地址
     * @param array  $options  其它。暂不支持
     */
    public static function addNewRelation($uid, $role_id)
    {
        $u = new self();
        $u->uid     = $uid;
        $u->role_id = $role_id;
        $u->ur_created  = time();
        $u->ur_updated  = time();

        return $u->save_and_return($u, 1);
    }

    /**
     * [check_authentication 检测用户权限]
     * @param  [type] $uid        [用户ID]
     * @param  [type] $role_id    [权限ID]
     * @return [type] [description]
     */
    public static function check_authentication($uid, $role_id){
        if (is_array($role_id)){
            $role_str = implode(',', $role_id);
            return self::findFirst(array("uid = {$uid} AND role_id IN ({$role_str})"));
        }
        else if( $role_id ){
            return self::findFirst(array("uid = {$uid} AND role_id = {$role_id}"));
        }
        else{
            return false;
        }
        /* $sql = 'select \'x\' from user_roles ur where ur.uid = ' . $uid . ' and ur.role_id = ' . $rold_id . ';  */
    }

    /**
     * [get_role_users 获取相应类型的所有用户id
     */
    public static function get_role_users($role_id){
        $user_role = new self;
        $sql = "select uid from user_roles where role_id = $role_id";

        return new Resultset(null, $user_role, $user_role->getReadConnection()->query($sql));
        //return self::find("role_id={$role_id}");
    }

    public static function get_users_in($role_ids){
        $user_role = new self;
        $sql = "SELECT * FROM user_roles ".
            "LEFT JOIN users ON users.uid = user_roles.uid ".
            "WHERE user_roles.role_id in (".implode(",", $role_ids).")";

        return new Resultset(null, $user_role, $user_role->getReadConnection()->query($sql));
    }

    public static function get_roles_by_user_id( $uid ){
        $user_role = new self;
        if( empty($uid) ){
            return false;
        }
        $sql = 'SELECT GROUP_CONCAT(`role_id`) as role_ids FROM user_roles WHERE `uid`='.$uid;
        $res = new Resultset(null, $user_role, $user_role->getReadConnection()->query($sql) );

        if(empty($res)){
            $res = array();
        }
        else{
            $res = $res -> toArray();
            $res = $res[0]['role_ids'];
        }
        return $res;
    }

    /**
     * [assign_role 赋予权限]
     * @param  [type] $uid      [用户id]
     * @param  [type] $role_ids [角色id]
     * @return [type]           [description]
     */
    public static function assign_role( $user_id, $role_ids ){
        if( empty($user_id) || !is_numeric($user_id) ){
            return false;
        }

        if( !is_array($role_ids) ){
            $role_ids = explode(',', $role_ids);
        }
        if( empty($role_ids) ){
            return false;
        }

        $user_role_model = new self();
        $roles = explode(',',self::get_roles_by_user_id($user_id) );

        $add_roles = array_filter( array_diff( $role_ids, $roles ) );
        $del_roles = array_filter( array_diff( $roles, $role_ids ) );

        //add previleges
        foreach( $add_roles as $key => $per_id ){
            $pre = new UserRole();
            $pre->uid= $user_id;
            $pre->role_id = $per_id;
            $pre->create_time = time();
            $pre->update_time = time();
            $pre->save();
        }

        if(!empty($del_roles)){
            $sql = "DELETE FROM user_roles WHERE uid='{$user_id}' AND role_id IN(".implode(',', $del_roles).")";
            $res = new Resultset( null, $user_role_model, $user_role_model->getReadConnection()->query($sql) );
        }
        return true;
    }
}
