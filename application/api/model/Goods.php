<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/22
 * Time: 17:28
 */

namespace app\api\model;
use think\Db;
use think\Model;
class Goods extends Model
{
    public function getMembersBuyAttr($value,$data)
    {
        $data = Db::table('order')->where('goods_id',$data['id'])->join('user','user.id=order.user_id')->field('head_img')->order('pay_time','desc')->limit(0,10)->select();
        return $data;
    }
}