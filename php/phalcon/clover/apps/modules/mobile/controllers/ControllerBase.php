<?php
namespace Psgod\Mobile\Controllers;
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

    protected function check_form_token(){
        if ($this->request->isPost()) {
            if ($this->security->checkToken()) {
                ;
            } else {
                ajax_return(0, '重复操作！');die();
            }
        }
    }

    protected function back(){
        $referer = $this->request->getHttpReferer();
        $this->response->redirect($referer);
    }
}
