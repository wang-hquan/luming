<?php


namespace app\api\controller;


use think\Controller;
use think\Db;

class Order extends Controller
{

    public function getOrder()
    {
        $goods_id   =     $this->request->param('goods_id',1);
        $mobile  =     $this->request->param('mobile',13298315476);
        $user_id = $this->request->param('user_id',1);
        $type = $this->request->param('type',1);
        $goods_data =    Db::table('goods')->where('id',$goods_id)->find();
        $subject = $goods_data['name'];
        $trade_no = time().rand(100000,999999);
        $amount = $goods_data['current_price'];
        $body = '';
        $param = [
            'user_id'=>$user_id,
            'order_no'=>$trade_no,
            'order_status'=>0,
            'goods_id'=>$goods_id,
            'goods_name'=>$subject,
            'amount'=>$amount,
            'add_time'=>date("Y-m-d H:i:s"),
            'phone'=>$mobile,
        ];
        Db::table('order')->insert($param);
        $pay =   new Pay();
        $pay->test($type,$subject,$trade_no,$amount,$body);
    }
}