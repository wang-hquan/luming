<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/25
 * Time: 10:51
 */

namespace app\api\model;
use think\Db;

class SpecialtyScore extends Base
{
    public function getClassAttr($value){
        $r = Db::table('school_batch')->where('id',$value)->find();
        return $r['name'];
    }

//    public function getSpecialtyIdAttr($value){
//        $r = Db::table('specialty')->where('id',$value)->find();
//        return ['id'=>$r['id'],'name'=>$r['name']];
//    }

//    public function getTypeAttr($value){
//        $r = Db::table('division')->where('code',$value)->find();
//        return ['id'=>$r['id'],'name'=>$r['name']];
//    }
}