<?php
namespace Psgod\Main\Controllers;
use \Psgod\Models\Reply,
    \Psgod\Models\Ask;

class IndexController extends ControllerBase
{

    public function testAction(){
        $upload = \Psgod\Models\Upload::findFirst();
        Reply::addNewReply(15, 'test', 46, $upload);
    }

    public function indexAction()
    {
        $this->tag->prependTitle('首页');
        $type = $this->post('type');
        $page = $this->post('page', 'int', 1);
        $page_size = 4;
        $replies = \Psgod\Models\Reply::find(array(
            'conditions'=> "status=" . Ask::STATUS_NORMAL,
            'order' => 'click_count DESC',
            'limit' => $page_size,
        ));
        $this->set('replies', $replies);
    }

}

