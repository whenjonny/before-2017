<?php
namespace Psgod\Main\Controllers;
use Psgod\Models\User,
    Psgod\Models\Permission;

class ControllerBase extends \Psgod\PsgodBaseController
{
    // allow action for not login
    public $_allow  = array();
    // token for app
    public $_token  = null;
    // session user
    public $_user   = null;

    public function initialize()
    {
        $this->tag->appendTitle('—求PS大神：重新定义图片');
        $this->assets->addCss('css/common.css')->addCss('css/style.css')   // 通用公共样式
            ->addJs('uploadify/jquery.min.js')->addJs('js/common.js'); // 通用JS
        $this->is_login();
        //if( !is_dev() )
            //$this->check_auth();
    }
    
    public function get($str, $type = null, $default = null)
    {
        return $this->request->getQuery($str, $type, $default);
    }

    public function post($str, $type = null, $default = null)
    {
        return $this->request->getPost($str, $type, $default);
    }

    public function flash($message, $redirect_url='')
    {
        $this->flash->notice($message);

        if (!empty($redirect_url)) {
            return $this->dispatcher->forward($redirect_url);
        } else {
            return $this->dispatcher->forward($this->request->getHTTPReferer());
        }
    }

    /**
     * verify login status
     * @return boolean
     */
    private function is_login()
    {
        $this->_uid     = $this->session->get("uid");
        $this->_token   = session_id();

        if( CHECK_LOGIN == FALSE ){
            return true;
        }

        $this->set("_uid", $this->_uid);

        $action_name = $this->dispatcher->getActionName();

        if (in_array($action_name, $this->_allow)){
            return true;
        } else if($this->_uid && $this->_user = User::findUserByUID($this->_uid)){
            $this->set("username", $this->_user->username==''?'--': $this->_user->username);
            $this->set("message", '');

            $this->set("_nickname", $this->_user->nickname);
            $this->set("_avatar", $this->_user->avatar);
            $this->set("_sex", $this->_user->sex);
            
            return true;
        } else {
            return false;
        }
    }

    protected function check_form_token(){
        if ($this->request->isPost()) {
            if ($this->security->checkToken()) {
                ;
            } else {
                ajax_return(0, '重复操作！');die();
            }
        }
    }

    protected function check_auth(){
        if( CHECK_PERMISSIONS == FALSE ){
            return true;
        }
        
        $uid = $this->_uid;
        if(empty($uid)){
            return false;
        }

        $ctrler_name = $this->dispatcher->getControllerName();
        $action_name = $this->dispatcher->getActionName();

        $per = Permission::check_permission_by_user_id( $this->_uid, $ctrler_name, $action_name );
        



        if(!$per){
            //Getting a response instance
            $response = new \Phalcon\Http\Response();

            //Set status code
            $response->setStatusCode(403, "Forbidden");

            //Set the content of the response
            $content = array();
            $content[] = "Sorry, you don't have the permissionto visit this page.";
            $content[] = "对不起，您没有权限访问这个页面。";
            if( DEV ){
                $content[] = '';
                $content[] = "请在后台给当前用户所在的角色分配访问首页的权限。";
            }
            $response->setContent( implode('<br />', $content ) );

            //Send response to the client
            $response->send();
            exit;
        }    
    }

    protected function back(){
        $referer = $this->request->getHttpReferer();
        $this->response->redirect($referer);
    }
}
