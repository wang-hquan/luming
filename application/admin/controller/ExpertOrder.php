<?php


namespace app\admin\controller;

use app\admin\library\BaseController;
use app\admin\model\ExpertOrderModel;

class ExpertOrder extends BaseController
{
    public $searchFields = ['status'];
    public $ExpertOrderModel = [];
    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->ExpertOrderModel =  new ExpertOrderModel()  ;
    }

    public function getData()
    {
        try{
            $condition = $this->getPageRows();
            $condition['where'] = $this->filterSearchFields($this->searchFields);
            $data['lists'] =  $this->ExpertOrderModel->withSearch(['diySelect'],$condition)->select();
            $data['total'] =  $this->ExpertOrderModel->withSearch(['diyCount'],$condition)->count();
            $this->assembleTableData(200, 'success', $data);
        } catch (\Exception $ex) {
            $this->ret['code'] = 500;
            $this->ret['msg'] = $ex->getMessage();
        }
        $this->outPutJson();
    }

    //查询单个详情
    public function getDataById()
    {
        $post = $this->request->post('id');
        $data =   $this->ExpertOrderModel->where('id',$post)->find();
        $this->ret['code'] = 200;
        $this->ret['data']= $data;
        $this->ret['msg'] = 'success';
        $this->outPutJson();
    }

    // 修改
    public function  edit()
    {
        $mustFields = ['id','user_id','expert_id','mobile','status'];
        $extFields = [];

        try {
            $param = $this->receiveParam($mustFields, $extFields);
            $rec = ExpertOrderModel::get(['id' => $param['id']]);
            foreach ( $param as $k => $v ) {
                $rec->$k = $v;
            }
            if ($rec->save()) {
                $this->ret['code'] = 200;
                $this->ret['msg'] = '编辑成功';
            } else {
                $this->ret['code'] = 500;
                $this->ret['msg'] = '编辑失败';
            }

        } catch (\Exception $ex) {
            $this->ret['code'] = 500;
            $this->ret['msg'] = $ex->getMessage();
        }
        $this->outPutJson();
    }

    //添加
    public function add()
    {
        $mustFields = ['user_id','expert_id','mobile','status'];
        $extFields = [];

        try {
            $param = $this->receiveParam($mustFields, $extFields);
            if ($this->ExpertOrderModel->save($param)) {
                $this->ret['code'] = 200;
                $this->ret['msg'] = '添加成功';
            } else {
                $this->ret['code'] = 500;
                $this->ret['msg'] = '添加失败';
            }
        } catch (\Exception $ex) {
            $this->ret['code'] = 500;
            $this->ret['msg'] = $ex->getMessage();
        }
        $this->outPutJson();
    }

    //删除
    public  function delete()
    {
        try {
            $post = $this->request->post('id');
            $this->ExpertOrderModel->destroy($post);
            $this->ret['code'] = 200;
            $this->ret['msg'] = 'success';
        } catch (\Exception $ex) {
            $this->ret['code'] = 500;
            $this->ret['msg'] = $ex->getMessage();
        }
        $this->outPutJson();
    }

}