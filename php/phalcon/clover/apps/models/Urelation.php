<?php

namespace Psgod\Models;

class Urelation extends ModelBase
{

    public function getSource()
    {
        return 'follow';
    }

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->belongsTo('fellow', 'Psgod\Models\User', 'uid', array(
            'alias' => 'the_fellow',
        ));
        $this->belongsTo('fans', 'Psgod\Models\User', 'uid', array(
            'alias' => 'the_fans',
        ));
    }

    public function setUserRelation($fellow, $fans, $status)
    {
        $rela = self::findFirst(array(
            "fellow = '$fellow' AND fans = '$fans'"
        ));
        if($rela)
            $rela->status = $status;
        else {
            $rela = new self();
            $rela->fans = $fans;
            $rela->fellow = $fellow;
        }
        return $rela->save_and_return($rela);
    }

}
