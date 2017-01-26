<?php
namespace Psgod\Admin\Controllers;

use Psgod\Models\User,
    Psgod\Models\Usermeta,
    Psgod\Models\Role,
    Psgod\Models\ActionLog,
    Psgod\Models\Permission,
    Psgod\Models\PermissionRole,
    Psgod\Models\UserRole;

class RoleController extends ControllerBase
{

    public function indexAction()
    {

    }

    public function list_rolesAction()
    {

        $role = new Role;
        // 检索条件
        $cond = array();
        $cond['id']             = $this->post("role_id", "int");
        $cond['role_name']           = array(
            $this->post("role_name", "string"),
            'LIKE'
        );
        $cond['display_name']   = array(
            $this->post("role_display_name", "string"),
            'LIKE'
        );
        $cond['create_time']        = $this->post("role_created", "string");
        $cond['update_time']        = $this->post("role_updated", "string");

        // 用于遍历修改数据
        $data  = $this->page($role, $cond);

        foreach($data['data'] as $row){
            $role_id = $row->id;
            $row->create_time = date('Y-m-d H:i:s', $row->create_time);
            $row->update_time = date('Y-m-d H:i:s', $row->update_time);
            $row->oper = "<a href='#set_previlege' data-toggle='modal' data-id='$role_id' class='set_previlege'>权限设置</a> <a href='#edit_role' data-toggle='modal' data-id='$role_id' class='edit'>编辑</a>";
        }
        // 输出json
        return $this->output_table($data);
	}

    public function set_roleAction(){
        $this->noview();

        $role_id = $this->post("role_id", "int");
        $role_name  = $this->post("role_name", "string");
        $role_display_name = $this->post("role_display_name", "string");

        $oldRole = array();
        if( $role_id ){
            $oldRole = Role::findfirst('id='.$role_id);
        }

        if(is_null($role_name) || is_null($role_name)){
		    return ajax_return(0, '请输入角色名称或展示名称');
        }

        $ret = false;
        $newRole = Role::setRole($role_id, $role_name, $role_display_name);
        if( $newRole ){
            $ret = true;
            if( $role_id ){
                ActionLog::log(ActionLog::TYPE_EDIT_ROLE, $oldRole, $newRole);
            }
            else{
                ActionLog::log(ActionLog::TYPE_ADD_ROLE, $oldRole, $newRole);
            }
        }
        return ajax_return(1, 'okay', $ret);
    }

    /**
     * [user_permission 用户权限]
     * @return [type] [description]
     */
    public function user_permission(){
        if ($this->request->isAjax()) {

        }
    }

    /**
     * [permissionAction 权限列表]
     * @return [type] [description]
     * @author [Vanson] <[Y-m-d H:i:s]>
     */
    public function list_permissionsAction(){
        if ($this->request->isAjax()) {
            $this->noview();
            $permission = new Permission;
            // 检索条件
            $cond = array();
            $cond['id']             = $this->post("pid", "int");
            $cond['controller_name'] = array(
                $this->post("controller_name", "string"),
                'LIKE'
            );

            $cond['action_name']     = array(
                $this->post("action_name", "string"),
                'LIKE'
            );

            $cond['display_name']    = array(
                $this->post("display_name", "string"),
                'LIKE'
            );

            // 用于遍历修改数据
            $data = $this->page($permission, $cond);

            foreach($data['data'] as $row){
                $row->create_time = date('Y-m-d H:i:s', $row->create_time);
                $row->update_time = date('Y-m-d H:i:s', $row->update_time);
                $row->oper = "<a href='#edit_permission' data-toggle='modal' data='" . $row->id . "' class='edit'>编辑</a> <a href='#delete_permission' data-toggle='modal' title='" . $row->display_name . "' data='" . $row->id . "' class='delete'>删除</a>";
            }

            // 输出json
            return $this->output_table($data);
        }
    }

    /**
     * [edit_permissionAction 保存权限模块]
     */
    public function save_permissionAction(){
        $this->noview();

        $pid             = $this->post("pid", 'int', null);
        $display_name    = $this->post("display_name", "string");
        $controller_name = $this->post("controller_name", "string");
        $action_name     = $this->post("action_name", "string");

        if(empty($display_name) || empty($controller_name) || empty($action_name)){
            return ajax_return(0, '请输入必要参数');
        }

        if( $pid ){
            $oldPermission = Permission::findFirst('id='.$pid);
        }
        else{
            // 新增模块检测是否已经存在
            if( Permission::check_exists($controller_name, $action_name) ){
                return ajax_return(2, '模块已经存在');
            }
        }

        $updatePermission = Permission::save_permission($pid, $display_name, $controller_name, $action_name);
        if( $updatePermission ){
            if($pid){ //修改
                ActionLog::log(ActionLog::TYPE_EDIT_PERMISSION, $oldPermission, $updatePermission);
            }
            else{ //新增
                ActionLog::log(ActionLog::TYPE_ADD_PERMISSION, NULL, $updatePermission);
            }
        }
        return ajax_return(1, 'okay');
    }

    /**
     * [delete_permissionAction 删除权限]
     * @return [type] [description]
     */
    public function delete_permissionAction(){
        $this->noview();

        $id = $this->post("pid", 'int');
        $old = Permission::findFirst('id='.$id);
        $del_response = Permission::delete_permission($id);
        if( $del_response ){
            ActionLog::log(ActionLog::TYPE_DELETE_PERMISSION, $old, NULL);
            return ajax_return(1, 'okay');
        }
        else{
            return ajax_return(2, '删除失败');
        }
    }

    /**
     * [get_permissions_by_role_id 获取角色对应所拥有的权限]
     * @return  json [返回拥有的权限id，以逗号分隔的字符串]
     */
    public function get_permissions_by_role_idAction(){
        if (!$this->request->isAjax()) {
            return ajax_return(2,'不是ajax请求');
        }

        $this->noview();

        $role_id= $this->post('role_id','int');
        if( !$role_id ){
            return ajax_return(3,'没有角色id');
        }

        $permissions = PermissionRole::get_permissions_by_role_id($role_id);
        if(empty($permissions)){
            $permissions = '';
        }
        return ajax_return( 1, 'ok', $permissions );
    }

    /**
     * [save_privilege 赋予某个角色权限]
     * @return  boolean [是否保存成功]
     */
    public function save_previlegeAction(){
        if( !$this->request->isAjax() ){
            return ajax_return(2,'不是ajax请求');
        }

        $this->noview();

        $role_id = $this->post('role_id','int');
        $permission_ids = $this->post('permission_id','int');

        if( !$role_id ){
            return ajax_return(3,'没有角色id');
        }

        $ret = PermissionRole::update_permissions( $role_id, $permission_ids );
        if( $ret ){
            ActionLog::log(ActionLog::TYPE_GRANT_PRIVILEGE, array(), $ret);
        }
        return ajax_return(1,'ok',$ret);
    }


    /**
     * [get_roles_by_user_idAction 获取用户拥有的角色]
     * @return [string] [角色id，以逗号分隔的字符串]
     */
    public function get_roles_by_user_idAction(){
        if (!$this->request->isAjax()) {
            return ajax_return(2,'不是ajax请求');
        }

        $this->noview();

        $user_id= $this->post('user_id','int');
        if( !$user_id ){
            return ajax_return(3,'没有角色id');
        }

        $permissions = UserRole::get_roles_by_user_id($user_id);
        if(empty($permissions)){
            $permissions = '';
        }
        return ajax_return( 1, 'ok', $permissions );
    }

    /**
     * [assign_roleAction 赋予用户权限]
     * @return [boolean] [是否赋予成功]
     */
    public function assign_roleAction(){
        if( !$this->request->isAjax() ){
            return ajax_return(2,'不是ajax请求');
        }

        $this->noview();

        $user_id = $this->post('user_id','int');
        $role_ids = $this->post('role_id','int');

        if( empty($user_id) ){
            return ajax_return(3,'没有角色id');
        }

        $old = UserRole::get_roles_by_user_id( $user_id );
        $ret = UserRole::assign_role( $user_id, $role_ids );
        $new = UserRole::get_roles_by_user_id( $user_id );
        ActionLog::log(ActionLog::TYPE_PARTTIME_PAID, explode(',',$old), explode(',',$new));

        return ajax_return(1,'ok',$ret);
    }
}
