<?php
namespace Psgod\Services;

use Psgod\Models\User as mUser;

class User extends ServiceBase
{
    public static function addUser() {
        pr(mUser::find());
    }
}
