<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/24
 * Time: 14:35
 */

namespace app\api\model;


class Appraisal extends Base
{
    public function getResultAttr($value){
        return json_decode($value,true);
    }
}