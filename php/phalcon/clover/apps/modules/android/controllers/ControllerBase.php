<?php
namespace Psgod\Android\Controllers;

use Psgod\Models\User;

class ControllerBase extends \Psgod\PsgodBaseController
{
    // allow action for not login
    public $_allow  = array();
    // token for app
    public $_token  = null;
    // session user
    public $_user   = null;

    public $_log    = null;

    public function initialize()
    {
        header("Access-Control-Allow-Origin: *");

        if( !$this->is_login() ){
            ajax_return(2 ,'登录失效', array('msg'=>'登录失效!'));
            exit;
        }
    }
    
    public function get ($str, $type = null, $default = null) 
    {
        return $this->request->getQuery($str, $type, $default);
    }

    public function post ($str, $type = null, $default = null)
    {
        return $this->request->getPost($str, $type, $default);
    }
    
    /**
     * verify login status
     * @return boolean
     */
    private function is_login()
    {
        $this->_uid     = $this->session->get("uid");
        $this->_token   = session_id();

        if(is_dev() && !$this->_uid){
            $this->_uid = 1;
            $this->session->set("uid", 1);
        }

        $action_name = $this->dispatcher->getActionName();
        if (in_array($action_name, $this->_allow)){
            return true;
        } 
        else if($this->_uid && $this->_user = User::findUserByUID($this->_uid)){
            return true;
        } 
        else {
            return false;
        }
    }

    public function check_token($token='')
    {
        if($token === '')
            if($this->cookies->has('token'))
                $token = $this->cookies->get('token')->getValue();
        // phalcon的session机制是只有第一次使用的时候才会调用这个
        @session_start();
        if($token === session_id())
                return true;    
        return false;
    }

    protected function check_form_token(){
        if ($this->request->isPost()) {
            if ($this->security->checkToken()) {
                ;
            } else {
                ajax_return(0, '重复操作！');
            }
        }
    }

}
