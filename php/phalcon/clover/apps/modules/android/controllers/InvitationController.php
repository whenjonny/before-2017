<?php
namespace Psgod\Android\Controllers;

//use Psgod\Controllers\MasterController;
use Psgod\Models\ActionLog;
use Psgod\Models\Invitation;

class InvitationController extends ControllerBase{
    public function inviteAction( ){
        $this->noview();

        $ask_id = $this->get('ask_id','int');
        $invite_uid = $this->get('invite_uid', 'int');
        if( empty( $ask_id) || empty( $invite_uid) ){
            return ajax_return(1,'not enough parameters.', false);
        }
        $save = Invitation::updateInvitation( $ask_id, $invite_uid, Invitation::STATUS_READY );

        if( $save ){
            if( $save instanceof Invitation ){
                ActionLog::log(ActionLog::TYPE_INVITE_FOR_ASK, array(), $save);
            }
            return ajax_return(1,'okay', true);
        }
        else{
            return ajax_return(1,'error',false);
        }
    }
}

