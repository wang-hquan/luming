<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/23
 * Time: 9:44
 */

namespace app\api\model;
use think\Db;

class Expert extends Base
{
    public function getRegionIdAttr($value){
        $city = Db::table('region')->where('status',1)->where('id',$value)->find();
        return $city['region_name'];
    }
}