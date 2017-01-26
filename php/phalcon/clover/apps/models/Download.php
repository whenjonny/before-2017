<?php

namespace Psgod\Models;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class Download extends ModelBase
{

    /**
     * 求助的下载
     */
    const TYPE_ASK = 1;

    /**
     * 回复的下载
     */
    const TYPE_REPLY = 2;

    /**
     * 如果回复过求P 这里置为已完成
     */
    const STATUS_REPLIED = 1;

    /**
     * 初始化状态
     */
    const STATUS_INITIAL = 0;

    /**
     * 坑爹的初始化状态
     */
    const STATUS_DELETED = -1;

    public function getSource()
    {
        return 'downloads';
    }

    public static function addNewDownload($uid, $type, $target_id, $url, $status){
        $download            = new self();
        $download->uid       = $uid;
        $download->type      = $type;
        $download->target_id = $target_id;
        $download->create_time   = time();
        $download->asker_ip  = get_client_ip();
        $download->url       = $url;
        $download->status    = $status;

        return $download->save_and_return($download);
    }

    /**
     * [get_inprogress 进行中(download_status 是初始化的)]
     * @return [type] [description]
     */
    public static function get_inprogress($uid, $last_updated, $page, $limit){
        // A raw SQL statement
        $sql = '(SELECT a.id as id, 1 as type, d.id as download_id, u.uid, u.nickname, u.avatar, u.sex, up.savename, up.ratio, up.scale, a.`desc`, a.reply_count, d.type, d.target_id, d.url, d.create_time, d.update_time
                FROM `downloads` d
                LEFT JOIN asks a ON a.id = d.target_id
                LEFT JOIN users u ON u.uid = a.uid
                LEFT JOIN uploads up ON up.id = a.upload_id
                WHERE d.type = ' . Download::TYPE_ASK . 
                ' AND d.uid = ' . $uid . 
                ' AND d.create_time < ' . $last_updated. 
                ' AND d.status = ' . Download::STATUS_INITIAL . ')
                UNION
                (SELECT r.ask_id as id, 2 as type, d.id as download_id, u.uid, u.nickname, u.avatar, u.sex, up.savename, up.ratio, up.scale, a.`desc`, a.reply_count, d.type, d.target_id, d.url, d.create_time, d.update_time
                FROM `downloads` d
                LEFT JOIN replies r ON r.id = d.target_id
                LEFT JOIN asks a ON r.ask_id = a.id
                LEFT JOIN users u ON u.uid = r.uid
                LEFT JOIN uploads up ON up.id = r.upload_id
                WHERE d.type = ' . Download::TYPE_REPLY . 
                ' AND d.create_time < ' . $last_updated. 
                ' AND d.uid = ' . $uid . 
                ' AND d.status = ' . Download::STATUS_INITIAL . ' )
                ORDER BY create_time DESC limit ' . ($page - 1) * $limit . ',' . $limit .'';
        //order by id desc 

        // Base model
        $download = new Download();

        // Execute the query
        return new Resultset(null, $download, $download->getReadConnection()->query($sql));
    }

    public static function has_downloaded($type, $uid,$target_id) {
        return self::findFirst(array("type = ".$type." AND uid = {$uid} AND target_id = {$target_id} and status = " . Download::STATUS_INITIAL));
    }

    public static function get_download_target($type, $uid) {
        if($type=='ask') {
            $type = Download::TYPE_ASK;
        } else if($type=='reply'){
            $type = Download::TYPE_REPLY;
        } else
            return false;
        $builder = self::query_builder();
        $status  = self::STATUS_INITIAL;
        return $builder->columns('target_id')->where("type = {$type} AND uid = {$uid} AND status = {$status}")->getQuery()->execute()->toArray();
    }

    public static function  get_current_ask($uid, $target_id){
        return self::findFirst("uid=$uid and type=".Download::TYPE_ASK."and target_id=$target_id"); 
    }

    public static function get_progressing($uid, $last_updated, $page=1, $limit=10) {
        $builder = self::query_builder('d');
        $builder->columns('d.id, d.type, d.target_id, d.create_time as download_time')
            ->where('d.uid =  '.$uid.
                ' AND d.status = '.self::STATUS_INITIAL,
                ' AND d.create_time < '.$last_updated 
            )
            ->orderBy('create_time desc');
        return self::query_page($builder, $page, $limit);
    }
}
