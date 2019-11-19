<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/25
 * Time: 10:13
 */

namespace app\api\model;
use think\Db;

class SchoolScore extends Base
{
//    public function getTypeAttr($value){
//        $r = Db::table('division')->where('status',1)->where('code',$value)->find();
//        return ['id'=>$r['id'],'name'=>$r['name']];
//    }
    public function getSpecificCodeAttr($value){
        $r = Db::table('especially')->where('status',1)->where('code',$value)->field('name')->find();
        return $r['name'];
    }
//    public function getSchoolIdAttr($value,$data){
//        $r = Db::table('school')->where('id',$data['school_id'])->find();
//        return ['id'=>$r['id'],'name'=>$r['name']];
//    }
//
    public function getClassAttr($value){
        $r = Db::table('school_batch')->where('id',$value)->field('name')->find();
        return $r['name'];
    }
}