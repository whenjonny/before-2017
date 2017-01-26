<?php

namespace Psgod\Models;

class Platform extends ModelBase
{

    const PF_WEIXIN = 'weixin';
    const PF_QZONE  = 'qzone';

    /**
     * 添加新第三方应用平台
     * 
     * @param integer             $uid     用户ID
     * @param string              $pf_name 平台名称
     * @param string              $openid  用户在平台上的ID
     * @param string|array|object $data    用户信息数据
     */
    public function addNewPF($uid, $pf_name, $openid, $data)
    {
        $pf = new self();
        if (is_array($data) || is_object($data)) $data = json_encode($data);
        $pf->assign(array(
            'uid'    => $uid,
            'name'   => $pf_name,
            'openid' => $openid,
            'data'   => $data,
            'create_time'=> time(),
            'update_time'=> time(),
        ));

        return $pf->save_and_return($pf);
    }

    /**
     * 添加微信帐号
     * 
     * @param integer $uid          用户ID
     * @param array   $wx_data_array用户微信信息
     */
    public static function addWeiXinPF($uid, $wx_data_array)
    {
        $pf = new self();
        $openid = $wx_data_array['openid'];

        return $pf->addNewPF($uid, self::PF_WEIXIN, $openid, json_encode($wx_data_array));
    }

    public function getSource()
    {
        return 'platforms';
    }

}
