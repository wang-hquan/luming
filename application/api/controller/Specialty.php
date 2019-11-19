<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/16
 * Time: 11:07
 */

namespace app\api\controller;

use think\facade\Request;
use think\Db;

class Specialty extends \app\api\controller\Api
{
    protected $noNeedLoginPC = ['*'];
    protected $noNeedLoginAPP = ['*'];

    protected function initialize()
    {
        parent::initialize();
        $this->tokenGetId();
    }


    /**
     * 获取专业分类
     * @param \app\api\model\Specialty $specialty
     * @return false|string
     */
    public function getSpecialty(\app\api\model\Specialty $specialty)
    {
        $degree = (int)Request::param('degree');
        $res = $specialty::where(['degree' => $degree])
            ->where('parent_id',0)
            ->where('status',1)->select();
        foreach ($res as $k => &$v) {
            $v['child_num'] = Db::table('specialty')->where('parent_id',$v['specialty_code'])
                ->where('status',1)
                ->count();
        }
        return apiReturn('200', '返回成功', $res->toArray());
    }

    public function getSpecialtyChild()
    {
        $specialty_code = $this->request->param('specialty_code');
        $res =    Db::table('specialty')->where('parent_id',$specialty_code)
            ->where('status',1)->select();
        foreach ($res as $k => &$v) {
            $v['childrens'] =  Db::table('specialty')->where('parent_id',$v['specialty_code'])
                ->where('status',1)->select();
        }
        return apiReturn('200', '返回成功', $res);
    }

    /**
     * 获取专业信息
     * @param \app\api\model\Specialty $specialty
     * @return false|string
     */
    public function getSpecialtyInfo(\app\api\model\Specialty $specialty)
    {
        $id = Request::param('id','');
        if (empty($id)) {
            return apiReturn('500', '请传入专业ID');
        }
        $res = $specialty::where(['parent_id' => $id])->where('status',1)->select()->toArray();
        $data = Db::table('user_attention')
            ->where([['user_attention.user_id', '=', $this->userId], ['user_attention.status', '=', 1], ['user_attention.type', '=', 0]])
            ->select();

        foreach ($data as $key=>$val){
            foreach ($res as $k=>$v){
                if ($v['id'] == $val['in_val']){
                    $res[$k]['is_attention'] = 1;
                    unset($data[$key]);
                    break;
                }else{
                    $res[$k]['is_attention'] = 0;
                }
            }
            unset($data[$key]);
        }
        return apiReturn('200', '返回成功', $res);
    }

    /**
     * 根据专业查询开设的学校
     * @param \app\api\model\Specialty $specialty
     * @return false|string
     */
    public function getopenUniversitiesList()
    {
        $id = Request::param('id');
        if (empty($id)) {
            return apiReturn('500', '请传入专业ID');
        }
        $specialty = \app\api\model\Specialty::get(['specialty_code'=>$id]);
        if (empty($specialty)){
            return apiReturn('200', '返回成功');
        }
        $res = $specialty->roles()->where('status',1)->select();
        $data = Db::table('user_attention')
            ->where([['user_attention.user_id', '=', $this->userId], ['user_attention.status', '=', 1], ['user_attention.type', '=', 0]])
            ->select();


        foreach ($res as $k=>$v){
            foreach ($data as $key=>$val){
                if ($v['id'] == $val['in_val']){
                    $res[$k]['is_attention'] = 1;
                    unset($data[$key]);
                    break;
                }else{
                    $res[$k]['is_attention'] = 0;
                }
            }
        }

        return apiReturn('200', '返回成功', json_decode($res));
    }

    /**
     * 根据关键字查找专业
     * @param \app\api\model\Specialty $specialty
     * @return false|string
     */
    public function seachSpecialtyName(\app\api\model\Specialty $specialty)
    {
        $keyword = (string)Request::param('keyword', '');
        if (empty($keyword)) {
            return apiReturn('500', '请传入关键字');
        }
        $data = $specialty->where('status',1)
            ->where('name', 'like', '%' . $keyword . '%')
            ->where('specialty_code', '<>', 0)
            ->where('type',1)
            ->select();
        return apiReturn('200', '返回成功', ['list' => $data]);
    }

    /**
     * 获取热门专业
     * @param \app\api\model\Specialty $specialty
     * @return false|string
     */
    public function getHotSpecialty(\app\api\model\Specialty $specialty)
    {
        $num = (int)Request::param('num', 4);
        $data = $specialty->where('status',1)->where('specialty_code', '<>', 0)
            ->order('specialty_hot', 'desc')
            ->limit(0, $num)->select();
        return apiReturn('200', '返回成功', ['list' => $data]);
    }

    /**
     * 就业职业方向
     * @return false|string
     */
    public function employmentCareer()
    {
        $specialtyId = (int)Request::param('id', '');
        if (empty($specialtyId)) {
            return apiReturn('500', '请传入专业id');
        }
        $data = Db::table('specialty_job')->where('status',1)->where('specialty_id', $specialtyId)->select();
        return apiReturn('200', '返回成功', ['list' => $data]);
    }

    /**
     * 就业行业分布
     * @return false|string
     */
    public function employmentDistribution()
    {
        $specialtyId = (int)Request::param('id', '');
        if (empty($specialtyId)) {
            return apiReturn('500', '请传入专业id');
        }
        $data = Db::table('career')->where('status',1)->where('specialty_id', $specialtyId)->select();
        return apiReturn('200', '返回成功', ['list' => $data]);
    }

    /**
     * 就业地区分布
     * @return false|string
     */
    public function employmentCity()
    {
        $specialtyId = (int)Request::param('id', '');
        if (empty($specialtyId)) {
            return apiReturn('500', '请传入专业id');
        }
        $data = Db::table('career_city')->where('status',1)->where('specialty_id', $specialtyId)->select();
        return apiReturn('200', '返回成功', ['list' => $data]);
    }

    /**
     * 专业概览
     * @return false|string
     */
    public function getSpecialtyOverview()
    {
        $specialtyId = Request::param('specialty_code', '');
        if (empty($specialtyId)) {
            return apiReturn('500', '请传入专业id');
        }
        $data = Db::table('specialty_overview')->where('specialty_id', $specialtyId)
            ->join('specialty','specialty.specialty_code=specialty_overview.specialty_id')->find();
        $res = Db::table('user_attention')->where('user_id',$this->userId)
            ->where('type',1)
            ->where('in_val',$specialtyId)
            ->where('status',1)
            ->find();
        if($res) {
            $data['is_attention'] = 1;
        }else{
            $data['is_attention'] = 0;
        }

        if (!empty($data)){
            Db::name('specialty')
                ->where('status',1)
                ->where('specialty_code', $specialtyId)
                ->setInc('specialty_hot');
        }
        return apiReturn('200', '返回成功', $data);
    }
    
    
    //根据关键字查找专业
    
    public function searchSpecialty()
    {
        $keyword = (string)Request::param('keyword', '');
        if ($keyword == '') {
            return apiReturn('500', '搜索词不能为空');
        }
        $data['list'] =   Db::table('specialty')
            ->where('name', 'like', '%' . $keyword . '%')
            ->field('specialty_code,name')
            ->where('type',1)
            ->select();
        return apiReturn('200', '搜索成功', $data);
    }
    
    
}