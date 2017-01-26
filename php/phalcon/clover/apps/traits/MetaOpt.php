<?php
namespace Psgod\Traits;

/**
 * 关系型 key-value 操作实现
 */
trait MetaOpt
{
    /**
     * 写 key - value 数据
     * 
     * @param  integer $fid   外键ID
     * @param  string  $key   键名
     * @param  string  $value 值
     * @return false | obj
     */
    public static function writeMeta($fid, $key, $value)
    {
        $meta = self::findFirst("fid='{$fid}' and key='{$key}'");

        $meta = $meta ? $meta : new self();
        $meta->fid  = $fid;
        $meta->key  = $key;
        $meta->value= $value;

        return $meta->save_and_return($meta);
    }

    /**
     * 读 key-value 值
     * 
     * @param  integer $fid 外键ID
     * @param  string $key  键名
     * @return array
     */
    public static function readMeta($fid, $key='')
    {
        if (!empty($key)) { // 有指定键，就只找出这个键的值
            $result = self::findFirst(array(
                'conditions' => "fid='{$fid}' and key='{$key}'",
            ));
            if ($result) {
                return array( 
                    $key => $result->value
                );
            } else {
                return array();
            }
        } else {    // 没指定键就去找这个fid关联的所有值
            $result = array();
            $metas = self::find(array(
                'conditions' => "fid='{$fid}'"
            ));
            if ($metas) {
                foreach ($metas as $m) {
                    $result["{$m->key}"] = $m->value;
                }
            }

            return $result;
        }
    }
}