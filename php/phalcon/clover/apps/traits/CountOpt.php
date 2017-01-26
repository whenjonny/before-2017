<?php
namespace Psgod\Traits;

trait CountOpt
{
    public static function count_add($id, $count_name, $value=1) {
        $class = get_called_class();
        $obj = $class::findFirst($id);
        $count = $count_name.'_count';
        if(property_exists($obj, $count)) {
            if($obj->$count)
                $obj->$count += $value;
            else
                $obj->$count = $value;
            return $obj->save_and_return($obj);
        } else
            return false;
    }

    public static function count_reduce($id, $count_name, $value=1) {
        $class = get_called_class();
        $obj = $class::findFirst($id);
        $count = $count_name.'_count';
        if(property_exists($obj, $count)) {
            if($obj->$count)
                $obj->$count -= $value;
            // else
            //     $obj->$count = 0;
            return $obj->save_and_return($obj);
        } else
            return false;
    }
}