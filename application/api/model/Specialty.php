<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/16
 * Time: 11:27
 */

namespace app\api\model;
use think\Model;

class Specialty extends Model
{
    public function roles()
    {
        return $this->belongsToMany('School','school_specialty','school_id', 'specialty_id');
    }
    
}