<?php

namespace app\admin\controller;

use app\admin\library\BaseController;
use app\admin\model\SchoolNewsModel;

class SchoolNews extends BaseController
{
    public $searchFields = ['school_id'];
    public $SchoolNewsModel = [];
    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->SchoolNewsModel =  new SchoolNewsModel()  ;
    }

    public function getData()
    {
        try{
            $condition = $this->getPageRows();
            $condition['where'] = $this->filterSearchFields($this->searchFields);
            $condition['order'] = ['id'=>'desc'];
            $data['lists'] =  $this->SchoolNewsModel->withSearch(['diySelect'],$condition)->select();
            $data['total'] =  $this->SchoolNewsModel->withSearch(['diyCount'],$condition)->count();
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
        $data =   $this->SchoolNewsModel->where('id',$post)->find();
        $this->ret['code'] = 200;
        $this->ret['data']= $data;
        $this->ret['msg'] = 'success';
        $this->outPutJson();
    }

    // 修改
    public function  edit()
    {
        $mustFields = ['id','title'];
        $extFields = ['school_id','desc','add_time','click_num','status','content','thumb'];

        try {
            $param = $this->receiveParam($mustFields, $extFields);
            $rec = SchoolNewsModel::get(['id' => $param['id']]);
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
        $mustFields = ['title'];
        $extFields = ['school_id','desc','add_time','click_num','status','content','thumb'];

        try {
            $param = $this->receiveParam($mustFields, $extFields);
            $param['add_time'] = date('Y-m-d H:i:s');
            if ($this->SchoolNewsModel->save($param)) {
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

    //删除文章
    public  function delete()
    {
        try {
            $post = $this->request->post('id');
            $this->SchoolNewsModel->destroy($post);
            $this->ret['code'] = 200;
            $this->ret['msg'] = 'success';
        } catch (\Exception $ex) {
            $this->ret['code'] = 500;
            $this->ret['msg'] = $ex->getMessage();
        }
        $this->outPutJson();
    }
}