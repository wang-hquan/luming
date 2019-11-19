<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/17
 * Time: 11:23
 */

namespace app\api\controller;
use think\facade\Request;
use think\Db;

class Common
{
    public function checkToken(){
        $token = Request::header('Token');
        if (empty($token)){
            return false;
        }
        $r = Db::table('user')->where('token',$token)->find();
        if (empty($r)){
            return false;
        }
        return true;
    }
}