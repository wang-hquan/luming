<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/15
 * Time: 17:57
 */

namespace app\api\model;
use think\Model;
use think\Db;

class User extends Model
{
    public function getLanguageAttr($value){
        $r = Db::table('language')->where('status',1)->where('id',$value)->find();
        return $r['id'];
    }
}