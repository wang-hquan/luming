<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/20
 * Time: 11:09
 */

namespace app\api\controller;
use think\Db;
use think\facade\Request;

class Goods extends Api
{
    protected $noNeedLoginPC = ['*'];
    protected $noNeedLoginAPP = ['*'];

    public function createOrderNumber(){
        $str = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        var_dump($str);
    }

    /**
     * 获取服务列表
     * @return false|string
     */
    public function getGoodsList(){
    	$pageNum = (int)$this->request->param('pageNum', 1);
        $pageCount = (int)$this->request->param('pageCount', 20);
    	
        $goods = new \app\api\model\Goods();
        
        $data = $goods::where('status',1)
        ->page($pageNum,$pageCount)
        ->select();
        $count = $goods::where('status',1)
        
        ->count();
        $data = $data->append(['members_buy'])->toArray();
        return apiReturn('200', '获取成功',['list'=>$data,'count'=>$count]);
    }

    /**
     * 根据服务id获取服务详细信息
     * @return false|string
     */
    public function getGoodsInfo(){
        $id = $this->request->param('id');
        if (empty($id)){
            return apiReturn('500', '请传入服务id');
        }
        $data = Db::table('goods')->where('status',1)->where('id',$id)->find();
        return apiReturn('200', '获取成功',$data);
    }
}