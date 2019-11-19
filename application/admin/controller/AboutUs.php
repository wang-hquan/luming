<?php


namespace app\admin\controller;


use app\admin\library\BaseController;
use think\Db;

class AboutUs extends BaseController
{
    public function getDataById() {
        $data =  Db::table('about_us')->where('id',1)->find();
        $this->ret['code'] = 200;
        $this->ret['data'] = $data;
        $this->outPutJson();
    }

    public function add()
    {
        $mustFields = ['name','mobile','time','warming','vip_banner'];
        $extFields = [];

        try {
            $param = $this->receiveParam($mustFields, $extFields);
             Db::table('about_us')->where('id',1)->update($param);
            $this->ret['code'] = 200;
            $this->ret['msg'] = '修改成功';
        } catch (\Exception $ex) {
            $this->ret['code'] = 500;
            $this->ret['msg'] = $ex->getMessage();
        }
        $this->outPutJson();
    }
}