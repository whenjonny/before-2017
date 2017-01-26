<?php

namespace Psgod\Models;

class Askmeta extends ModelBase
{

    use \Psgod\Traits\MetaOpt;   // 混入 key-value 操作 trait


    const KEY_TIMING = 'timing';
    
    /**
     *
     * @var integer
     */
    public $id;

    /**
     * 外键ID，即 ask_id
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
        return 'askmeta';
    }

    /**
     * Independent Column Mapping.
     */
    public function columnMap()
    {
        return array(
            'ameta_id'    => 'id', 
            'ask_id'      => 'fid', 
            'ameta_key'   => 'key', 
            'ameta_value' => 'value'
        );
    }

}
