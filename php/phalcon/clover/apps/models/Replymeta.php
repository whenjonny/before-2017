<?php

namespace Psgod\Models;

class Replymeta extends ModelBase
{

    use \Psgod\Traits\MetaOpt;   // 混入 key-value 操作 trait

    const KEY_TIMING = 'timing';
    /**
     *
     * @var integer
     */
    public $id;

    /**
     * 外键ID，即 reply_id
     * 
     * @var integer
     */
    public $fid;

    /**
     *
     * @var string
     */
    public $key;

    /**
     *
     * @var string
     */
    public $value;

    public function getSource()
    {
        return 'replymeta';
    }

    /**
     * Independent Column Mapping.
     */
    public function columnMap()
    {
        return array(
            'rmeta_id'    => 'id', 
            'reply_id'    => 'fid', 
            'rmeta_key'   => 'key', 
            'rmeta_value' => 'value'
        );
    }

}
