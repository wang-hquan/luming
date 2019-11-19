<?php


namespace app\admin\controller;


use app\admin\library\BaseController;
use app\admin\model\CityModel;
use app\admin\model\RegionModel;
use app\admin\model\SchoolAcademyModel;
use app\admin\model\SchoolBatchModel;
use app\admin\model\SchoolModel;
use app\admin\model\SchoolNatureModel;
use app\admin\model\SchoolTipModel;
use app\admin\model\SchoolTypeModel;
use app\admin\model\SpecialtyModel;
use app\admin\model\SubjectModel;
use think\Db;

class Common extends BaseController
{
    public function getCity(){
        try{
            $city =     new CityModel();
            $data = $city->where('state',1)->select();
            $this->ret['code']  = 200;
            $this->ret['data'] = $data;
        } catch (\Exception $ex) {
            $this->ret['code'] = 500;
            $this->ret['msg'] = $ex->getMessage();
        }
        $this->outPutJson();
    }

    public function getSchool()
    {
        try{
            $res =  new SchoolModel();
            $data = $res->where('status',1)->select();
            $this->ret['code']  = 200;
            $this->ret['data'] = $data;
        } catch (\Exception $ex) {
            $this->ret['code'] = 500;
            $this->ret['msg'] = $ex->getMessage();
        }
        $this->outPutJson();
    }

    public function getSchoolTip()
    {
        try{
            $res =     new SchoolTipModel();
            $data = $res->where('state',1)->select();
            $this->ret['code']  = 200;
            $this->ret['data'] = $data;
        } catch (\Exception $ex) {
            $this->ret['code'] = 500;
            $this->ret['msg'] = $ex->getMessage();
        }
        $this->outPutJson();
    }

    public function getSchoolType()
    {
        try{
            $res =     new SchoolTypeModel();
            $data = $res->where('state',1)->select();
            $this->ret['code']  = 200;
            $this->ret['data'] = $data;
        } catch (\Exception $ex) {
            $this->ret['code'] = 500;
            $this->ret['msg'] = $ex->getMessage();
        }
        $this->outPutJson();
    }

    public function getSchoolNature()
    {
        try{
            $res =     new SchoolNatureModel();
            $data = $res->where('state',1)->select();
            $this->ret['code']  = 200;
            $this->ret['data'] = $data;
        } catch (\Exception $ex) {
            $this->ret['code'] = 500;
            $this->ret['msg'] = $ex->getMessage();
        }
        $this->outPutJson();
    }

    public function getSpecialty()
    {
        try{
            $res =  new SpecialtyModel();
            $data = $res->where('status',1)
                ->where('type',1)
                ->select();
            $this->ret['code']  = 200;
            $this->ret['data'] = $data;
        } catch (\Exception $ex) {
            $this->ret['code'] = 500;
            $this->ret['msg'] = $ex->getMessage();
        }
        $this->outPutJson();
    }

    public function getAcademy()
    {
        try{
            $res =  new SchoolAcademyModel();
            $condition['where'] = $this->filterSearchFields(['school_id']);
            $data =  $res->withSearch(['diySelect'],$condition)->select();
            $this->ret['code']  = 200;
            $this->ret['data'] = $data;
        } catch (\Exception $ex) {
            $this->ret['code'] = 500;
            $this->ret['msg'] = $ex->getMessage();
        }
        $this->outPutJson();
    }

    public function getSubject()
    {
        try{
            $res =  new SubjectModel();
            $data = $res->where('status',1)->select();
            $this->ret['code']  = 200;
            $this->ret['data'] = $data;
        } catch (\Exception $ex) {
            $this->ret['code'] = 500;
            $this->ret['msg'] = $ex->getMessage();
        }
        $this->outPutJson();
    }

    public function getBatch()
    {
        try{
            $res =  new SchoolBatchModel();
            $data = $res->select();
            $this->ret['code']  = 200;
            $this->ret['data'] = $data;
        } catch (\Exception $ex) {
            $this->ret['code'] = 500;
            $this->ret['msg'] = $ex->getMessage();
        }
        $this->outPutJson();
    }

    public function getRegion()
    {
        try{
            $res =  new RegionModel();
            $data = $res->where('status',1)->select();
            $this->ret['code']  = 200;
            $this->ret['data'] = $data;
        } catch (\Exception $ex) {
            $this->ret['code'] = 500;
            $this->ret['msg'] = $ex->getMessage();
        }
        $this->outPutJson();
    }
}