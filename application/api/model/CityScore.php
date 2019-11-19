<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/25
 * Time: 14:26
 */

namespace app\api\model;
use think\Db;

class CityScore extends Base
{
    public function getTypeAttr($value){
        $r = Db::table('division')->where('status',1)->where('code',$value)->find();
        return ['id'=>$r['id'],'name'=>$r['name']];
    }

    public function getClassAttr($value){
        $r = Db::table('school_batch')->where('id',$value)->find();
        return ['id'=>$r['id'],'name'=>$r['name']];
    }
}