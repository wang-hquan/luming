<?php


namespace app\admin\controller;


use app\admin\library\BaseController;
use app\admin\model\SchoolScoreModel;

class SchoolScore extends BaseController
{
    public $searchFields = ['school_id','academy_id'];
    public $SchoolScoreModel = [];
    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->SchoolScoreModel =  new SchoolScoreModel()  ;
    }

    public function getData()
    {
        try{
            $condition = $this->getPageRows();
            $condition['where'] = $this->filterSearchFields($this->searchFields);
            $condition['order'] = ['id'=>'desc'];
            $data['lists'] =  $this->SchoolScoreModel->withSearch(['diySelect'],$condition)->select()->toArray();
            $data['total'] =  $this->SchoolScoreModel->withSearch(['diyCount'],$condition)->count();
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
        $data =   $this->SchoolScoreModel->where('id',$post)->find();
        $this->ret['code'] = 200;
        $this->ret['data']= $data;
        $this->ret['msg'] = 'success';
        $this->outPutJson();
    }

    // 修改
    public function  edit()
    {
        $mustFields = ['id','school_id','class','max_score','entry_score','plan_num','put_into_num','max_ranking','min_score'];
        $extFields = ['min_girl','mean_score','matriculate','mean_ranking','english','math','chinese','ranking','year','specific_code','type'];
        try {
            $param = $this->receiveParam($mustFields, $extFields);
            $rec = SchoolScoreModel::get(['id' => $param['id']]);
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
        $mustFields = ['school_id','class','max_score','entry_score','plan_num','put_into_num','max_ranking','min_score'];
        $extFields = ['min_girl','mean_score','matriculate','mean_ranking','english','math','chinese','ranking','year','specific_code','type'];


        try {
            $param = $this->receiveParam($mustFields, $extFields);
            if ($this->SchoolScoreModel->save($param)) {
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
            $this->SchoolScoreModel->destroy($post);
            $this->ret['code'] = 200;
            $this->ret['msg'] = 'success';
        } catch (\Exception $ex) {
            $this->ret['code'] = 500;
            $this->ret['msg'] = $ex->getMessage();
        }
        $this->outPutJson();
    }
}