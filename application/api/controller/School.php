<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/16
 * Time: 17:14
 */

namespace app\api\controller;

use think\Db;
use think\facade\Request;

class School extends \app\api\controller\Api
{
    protected $noNeedLoginPC = ['*'];
    protected $noNeedLoginAPP = ['*'];

    protected function initialize()
    {
        parent::initialize();
        $this->tokenGetId();
    }

    /**
     * 拼接sql
     * @param $data
     * @param $name
     * @return int|string
     */
    private function jointSql($data, $name)
    {
        $str = '';
        if (!empty($data)) {
            foreach ($data as $v) {
                $str .= "or {$name}={$v} ";
            }
            $str = trim(substr($str, 2));
        } else {
            $str = 1;
        }
        return $str;
    }

    /**
     * 获取学校分类
     * @return false|string
     */
    public function getSchoolSort()
    {
        $schoolTip = Db::table('school_tip')->where('state',1)->select();
        $nature = Db::table('school_nature')->where('state',1)->select();
        $schoolType = Db::table('school_type')->where('state',1)->select();
        $city = Db::table('city')->where('state',1)->select();


        return apiReturn('200', '返回成功', [
            'schoolTip' => $schoolTip,
            'nature' => $nature,
            'schoolType' => $schoolType,
            'city' => $city
        ]);
    }

    /**
     * 匹配学校列表
     * @return false|string
     */
    public function getSchoolList(\app\api\model\School $school)
    {
        $this->tokenGetId();
        $strMap = $this->filterSearchFields(['degree','keyword']);
        if (empty($strMap)){
            $strMap = 1;
        }

        if (!isset($strMap['keyword'])){
            $keyMap = 1;
        }else{
            $keyMap = [['name','like','%'.$strMap['keyword'].'%']];
        }
        unset($strMap['keyword']);

        $schoolTipId = (array)Request::param('schoolTipId');
        $fields = $this->filterParameters('arr',['schoolTypeId','cityId']);
        $arrMap = [];
        foreach ($fields as $k=>$v){
            switch ($k){
                case 'schoolTypeId':
                    $arrMap[] = ['school_type_id','in',$v];
                    break;
                case 'cityId':
                    $arrMap[] = ['city_id','in',$v];
                    break;
            }
        }

        $tipMap = '';
        if (!empty($schoolTipId)){
            foreach ($schoolTipId as $k=>$v){
                $tipMap = "FIND_IN_SET(".$v.",school_tip_id) or";
            }
            $tipMap = substr($tipMap,0 ,-2);
        }else{
            $tipMap = 1;
        }


        $pageNum = (int)Request::param('pageNum', 1);
        $pageCount = (int)Request::param('pageCount', 20);

        $res = $school->where($strMap)->where($keyMap)->where($tipMap)->where($arrMap)->page($pageNum, $pageCount)->where('status',1)->select();
        $count = $school->where($strMap)->where($keyMap)->where($tipMap)->where('status',1)->where($arrMap)->count();

        foreach ($res as $k => $v) {
            $v['nature'] = Db::table('school_nature')->where('id',$v['nature'])->value('name');
        }

        if (empty($this->userId)){
            return apiReturn('200', '返回成功', ['list' => $res,'count'=>$count]);
        }

        $data = Db::table('user_attention')
            ->where([['user_attention.user_id', '=', $this->userId], ['user_attention.status', '=', 1], ['user_attention.type', '=', 0]])
            ->select();


        foreach ($res as $k=>$v){
            foreach ($data as $key=>$val){
                if ($v['school_id'] == $val['in_val']){
                    $res[$k]['is_attention'] = 1;
                    unset($data[$key]);
                    break;
                }else{
                    $res[$k]['is_attention'] = 0;
                }
            }
        }
        return apiReturn('200', '返回成功', ['list' => $res,'count'=>$count]);
    }

    /**
     * 学校首页
     * @return false|string
     */
    public function schoolIndex(\app\api\model\School $school)
    {
        $id = Request::param('id','');
        if (empty($id)){
            return apiReturn('500', '请传入学校id');
        }
        $data = $school->where('status',1)->where('school.school_id', $id)->find();

        if (!empty($data)) {
        	$data['ranking'] = Db::table('school_ranking')->where('school_id',$id)->find();
            Db::name('school')
                ->where('status',1)
                ->where('school.school_id', $id)
                ->setInc('school_hot');
            $data['is_attention']  = Db::table('user_attention')
                ->where('user_id',$this->userId)
                ->where('type',0)
                ->where('in_val',$id)->value('status');
        }
        $data['nature'] = Db::table('school_nature')->where('id',$data['nature'])->value('name');
        return apiReturn('200', '获取成功', json_decode($data,true));
    }

    /**
     * 获取热门学校
     * @return false|string
     */
    public function getHotSchool(\app\api\model\School $school){
        $num = Request::param('num',5);
        $school_id = $this->request->param('id');
        $city_id =   Db::table('school')->where('school_id',$school_id)->value('city_id');

        $data = $school->where('status',1)
            ->where('city_id',$city_id)
            ->where('parent_id',0)
            ->order('school_hot','desc')->limit(0,$num)->select();
        return apiReturn('200', '获取成功',['list'=>json_decode($data,true)]);
    }

    /**
     * 院系专业
     * @return false|string
     */
    public function getSchoolSpecialty(){
        $id = Request::param('id','');
        if (empty($id)){
            return apiReturn('500', '请传入学校id');
        }

        $school_specialty = Db::table('school_specialty')->where('school_id',$id)->select();
        $school_academy = Db::table('school_academy')->where('status',1)->where('school_id', $id)->select();
        foreach ($school_academy as $k=> &$v){
            foreach ($school_specialty as $key => $val){
                if ($v['id'] == $val['academy_id']){
                    $v['specialty'][] = $val;
                    unset($school_specialty[$key]);
                }
            }
        }
        return apiReturn('200', '获取成功',['list'=>$school_academy]);
    }



    /**
     * 特色专业
     * @return false|string
     */
   public function getReferences(){
        $id = Request::param('id','');
        if (empty($id)){
            return apiReturn('500', '请传入学校id');
        }
        $feature = (string)Request::param('feature','country_feature');

        $data = Db::table('school_specialty')
            ->where('school_id', $id)
            ->where('school_specialty.'.$feature,1)
            ->select();

        return apiReturn('200', '获取成功',['list'=>$data]);
    }

    /**
     * 通过id获取专业父级id
     * @return false|string
     */
    public function getReferencesGrand(){
        $id = Request::param('id','');
        if (empty($id)){
            return apiReturn('500', '请传入专业id');
        }

        $data = Db::table('specialty')
            ->where('id', $id)
            ->select();

        return apiReturn('200', '获取成功',$data);
    }

    /**
     * 获取学校新闻列表
     * @return false|string
     */
    public function getSchoolNewsList(){
        $id = Request::param('id','');
        $pageNum = (int)Request::param('pageNum', 1);
        $pageCount = (int)Request::param('pageCount', 20);
        if (empty($id)){
            return apiReturn('500', '请传入学校id');
        }
        $data = Db::table('school_news')
            ->where('status',1)
            ->where('school_id', $id)
            ->page($pageNum, $pageCount)
            ->select();
        return apiReturn('200', '获取成功',['list'=>$data]);
    }

    /**
     * 通过新闻id获取学校新闻
     * @return false|string
     */
    public function getSchoolNewsByid(){
        $schoolId = (int)Request::param('schoolId','');
        $newsId = (int)Request::param('newsId','');
        if (empty($schoolId) || empty($newsId)){
            return apiReturn('500', '请传入id');
        }
        $data = Db::table('school_news')
            ->where('status',1)
            ->where('school_id', $schoolId)
            ->where('id',$newsId)
            ->find();
        return apiReturn('200', '获取成功',$data);
    }

    /**
     * 获取往年学校新闻
     * @return false|string
     */
    public function getFormerYearsNews(){
        $schoolId = (int)Request::param('schoolId','');
        $newsId = (int)Request::param('newsId','');
        $num = (int)Request::param('num',2);
        if (empty($schoolId) || empty($newsId)){
            return apiReturn('500', '请传入id');
        }

        $r = Db::table('school_news')
            ->where('status',1)
            ->where('school_id', $schoolId)
            ->where('id',$newsId)
            ->find();

        $data = Db::table('school_news')
            ->where('status',1)
            ->where('add_time', '<' ,$r['add_time'])
            ->order('add_time','desc')
            ->limit(0,$num)
            ->select();
        return apiReturn('200', '获取成功',['list'=>$data]);
    }
}