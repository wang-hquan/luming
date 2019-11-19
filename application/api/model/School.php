<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/24
 * Time: 14:03
 */

namespace app\api\model;

use think\Db;

class School extends Base
{
    public function getSchoolTipIdAttr($value)
    {
        $data = Db::table('school_tip')->where('state', 1)->where('id', 'in', explode(',',$value))->select();
        return $data;
    }


    public function getCityIdAttr($value){
        $data = Db::table('city')->where('state', 1)->where('city_code', $value)->find();
        return $data['city'];
    }

    public function getSchoolTypeIdAttr($value){
        $data = Db::table('school_type')->where('state', 1)->where('id', $value)->find();
        return $data['name'];
    }

    public function roles()
    {
        return $this->belongsToMany('Specialty','school_specialty','specialty_id', 'school_id');
    }
}