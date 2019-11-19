<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/25
 * Time: 10:13
 */

namespace app\api\model;


class Smartfill extends Base
{
    public function getTypeAttr($value){
        $r = Db::table('division')->where('status',1)->where('code',$value)->find();
        return $r['name'];
    }
}