<?php
namespace Psgod\Models;


class ModelBase extends \Phalcon\Mvc\Model
{
    /**
     * 预发布(审核中)
     */
    const STATUS_READY = 3;

    /**
     * 拒绝状态
     */
    const STATUS_REJECT = 2;

    /**
     * 状态正常
     */
    const STATUS_NORMAL = 1;

    /**
     * 状态已删除
     */
    const STATUS_DELETED= 0;

    public $_uid;

    public function initialize()
    {
        $this->_uid = _uid();
    }

    /**
     * 保存对象并且返回这个对象（可访问自增主键）
     * 
     * @param  \Phalcon\Mvc\Model  $obj    对象
     * @param  boolean $exception_on_error 保存失败时是否抛出异常
     * @return $obj | false
     */
    public function save_and_return($obj, $exception_on_error=true)
    {
        if ($obj->save() == false) {
            $str = "Save data error: " . implode(',', $obj->getMessages());
            if ($exception_on_error) {
                echo $str;
                pr($obj);
                //throw new Exception($str, 1);
            } else {
                $this->getDI()->getDebug_log()->error($str);
            }
            return false;
        } else {
            return $obj;
        }
    }

    /**
     * QueryBuilder分页
     * @param  [object]    $builder [QueryBuilder]
     * @param  [integer]   $page    [页码]
     * @param  [integer]   $limit   [单页大小]
     * @return [paginator] $pi      [分页器]
     */
    public static function query_page($builder, $page=1, $limit=10)
    {
        $paginator = new \Phalcon\Paginator\Adapter\QueryBuilder(
            array(
                "builder" => $builder,
                "limit"   => $limit,
                "page"    => $page
            )
        );
        return $paginator->getPaginate();
    }

    /**
     * [query_builder 获取一个QueryBuilder]
     * @return [object] $builder   [QueryBuilder]
     */
    public static function query_builder($alias = '')
    {
        $builder  = new \Phalcon\Mvc\Model\Query\Builder();

        $from = get_called_class();

        if (!empty($alias)){
            $from = array($alias => $from);
        }

        return $builder->from($from);
    }

    /**
     * [result_page 简单分页查询]
     * 复杂分页查询由子类调用query_builder()和query_page()构造
     * @param  [int]       $page  [页码]
     * @param  [int]       $limit [单页大小]
     * @param  [array]     $keys  [只适配＝, 没有LIKE,>=,<=等等]
     * @return [paginator] $pi    [分页器]
     */
    public static function result_page($page=1, $limit=10, $keys=array())
    {
        if(gettype($keys)=='array') {
            $builder = self::query_builder();
            $conditions = 'TRUE';
            foreach ($keys as $k => $v)
                if(isset($v))
                    $conditions .= " AND $k = :$k:";

            $builder->where($conditions, $keys);
            return self::query_page($builder, $page, $limit);
        } else
            return false;
    }

    public static function format($value, $type = 'number'){
        switch ($type){
        case 'number':
            return isset($value)?$value: 0;
        }
        return '';
    }
}
