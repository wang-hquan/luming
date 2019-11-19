<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/17
 * Time: 17:53
 */

namespace app\api\controller;

use app\api\model\CityScore;
use app\api\model\SchoolScore;
use app\api\model\schoolSpecialty;
use app\api\model\SpecialtyScore;
use think\Db;
use think\facade\Request;

class Smartfill extends \app\api\controller\Api
{
    protected $noNeedLoginPC = ['*'];
    protected $noNeedLoginAPP = ['*'];

    //数据分组
    private function array_group_by($data, $year)
    {
        $tree = [];
        foreach ($data as $k => $v) {
            if ($v['year'] == $year) {
                $tree[] = $v;
                unset($data[$k]);
            }
        }
        foreach ($tree as $k => &$v) {
            foreach ($data as $key => $val) {
                if ($v['specialty_id'] == $val['specialty_id']) {
                    $v['children'][$val['year']] = $val;
                    unset($data[$key]);
                }
            }
        }
        return $tree;
    }


    public function searchList()
    {
        $keyword = (string)Request::param('keyword', '');
        if ($keyword == '') {
            return apiReturn('500', '搜索词不能为空');
        }

		$data['list'] =   Db::table('school_score')
            ->group('school_id')
            ->where('name', 'like', '%' . $keyword . '%')
            ->field('school_id,school_name as name')
            ->select();


        // $data['list'] = Db::table('school')->where('status', 1)->where('name', 'like', '%' . $keyword . '%')->select();

        return apiReturn('200', '搜索成功', $data);
    }

    /**
     * 分数线查询 院校分数线
     * @return false|string
     */
    public function searchCutoffScore(SchoolScore $schoolScorecore)
    {
        $id = (string)Request::param('id', '');
        $class = (int)Request::param('class', 1);
        $division = (string)Request::param('division', 1);
        $specialty_code = (int)Request::param('especially_code', 1);
        $year = (string)Request::param('year');
        $param = [
            'school_id' => $id, 'class'=>$class, 'type' => $division,'specific_code'=>$specialty_code,
        ];
        if($year){
            $param['year'] = $year;
        }


        if ($id == '') {
            return apiReturn('500', 'id不能为空');
        }
        // print_r($param);exit;
        $data = Db::table('school_score')->where($param)
            ->order('year','desc')
            ->select();
		// print_r($data);exit;
        return apiReturn('200', '搜索成功', ['list' => $data]);
    }


    /**
     * 获取往年数据
     * @return false|string
     */
    public function searchFormerYearData(SpecialtyScore $specialtyScore)
    {
        $id = (string)Request::param('id', '');
        $batch = (string)Request::param('batch', 1);
        $year = (string)Request::param('year', date('Y'));
        $division = (string)Request::param('division', 1);
        if ($id == '') {
            return apiReturn('500', 'id不能为空');
        }

        $data = $specialtyScore->where(['school_id' => $id, 'year' => $year, 'class' => $batch, 'type' => $division])->select()->toArray();
        return apiReturn('200', '搜索成功', $data);
    }

    /**
     * 获取学校专业详细分数线
     * @param SpecialtyScore $specialtyScore
     * @return false|string
     */
    public function getSpecialtyDetailsInfo(SpecialtyScore $specialtyScore)
    {
        $id = (string)Request::param('id', '');
        $batch = (string)Request::param('batch', 1);
        $specialty = (string)Request::param('specialty', '');
        if ($id == '') {
            return apiReturn('500', 'id不能为空');
        }

        if (empty($specialty)) {
            return apiReturn('500', '专业id不能为空');
        }

        $type = (string)Request::param('isWenli', 1);

        $data = $specialtyScore->where(['school_id' => $id, 'specialty_id' => $specialty, 'class' => $batch, 'type' => $type])->where('year', '<', date('Y'))->select()->toArray();

        return apiReturn('200', '搜索成功', $data);
    }

    /**
     * 获取当前学校的校区
     * @return false|string
     */
    public function getSchoolCampus()
    {
        $id = (string)Request::param('id', '');
        if ($id == '') {
            return apiReturn('500', 'id不能为空');
        }

        $year = [];
        $thisYear = date('Y') - 1;
        for ($i = 0; $i < 7; $i++) {
            array_push($year, $thisYear - $i);
        }
        $campus = Db::table('school')
            ->where('parent_id', $id)
            ->field('id,parent_id,name')
            ->select();

        $data = Db::table('school')
            ->where('id', $id)
            ->field('id,parent_id,name')
            ->select();

        return apiReturn('200', '搜索成功', [
            'campus' => array_merge($data, $campus),
            'year' => $year
        ]);
    }

    /**
     * 位次查询
     * @return false|string
     */
    public function searchPrecedence()
    {
//        $id = (string)Request::param('id','');
//        if ($id == ''){
//            return apiReturn('500', 'id不能为空');
//        }

        $isWenli = (string)Request::param('isWenli', 0);
        $grades = (string)Request::param('grades', '');

        if ($grades == '') {
            return apiReturn('500', '分数不能为空');
        }

        $res = Db::table('score')
            ->where('is_wenli', $isWenli)
            ->where('score', $grades)
            ->select();
            $ranking = Db::table('score')
            ->where('score',$grades)
            ->where('is_wenli',$isWenli)
            ->where('year',date("Y",time()))
            ->value('ranking');
        foreach ($res as $k => &$v) {
            $v['year_score'] = Db::table('score')
                ->where('year',$v['year'])
                ->where('is_wenli',$isWenli)
                ->where('ranking' ,'>=', $ranking)
                ->order('score','desc')
                ->value('score');
        }
        return apiReturn('200', '搜索成功', ['list' => $res]);
    }

    /**
     * 获取批次列表
     * @return false|string
     */
    public function getBatchList()
    {
        $especially = Db::table('especially')
            ->select();

        $batch = Db::table('school_batch')
            ->select();
        $division = Db::table('division')
            ->select();
        foreach ( $batch as $k => $v) {
            $state = strstr($v['name'], '提前');
            if($state) {
                $ahead[] = $v;
            }else {
                $arr[] = $v;
            }
        }
        return apiReturn('200', '搜索成功',
            [
                'batch' => $arr,
                'especially' => $especially,
                'division' => $division,
                'ahead' => $ahead
            ]);
    }
    /**
     * 省控线
     * @param CityScore $cityScore
     * @return false|string
     */
    public function getProvinceScoreLine()
    {
        $city = $this->request->param('city');
        $year = $this->request->param('year');
        if (empty($year)) {
            $yearMap = 1;
        } else {
            $yearMap = [
               [ 'year','=',$year]
            ];
        }
        if (empty($city)) {
            return apiReturn('500', '城市不能为空');
        }
        $res = Db::table('city_score')
            ->where('city_id', $city)
            ->where($yearMap)->
            order(['year' => 'desc', 'class' => 'asc', 'type' => 'asc'])
            ->select();
        $res = groupByKeyword($res, 'year');
        foreach ($res as $k=> $v) {
            $result= [];
            foreach ($v as $key => $val) {
                if($val['class'] == 1) {
                    $result[$val['class']]['batch']= '本科一批';
                    $result[$val['class']]['year']= $val['year'];
                    $result[$val['class']]['batch_id']= $val['class'];
                    if($val['type'] == 1){
                        $result[$val['class']]['wen'] = $val['score'];
                    }
                    if($val['type'] == 2){
                        $result[$val['class']]['li'] = $val['score'];
                    }
                }
                if($val['class'] == 2) {
                    $result[$val['class']]['batch']= '本科二批';
                    $result[$val['class']]['year']= $val['year'];
                    $result[$val['class']]['batch_id']= $val['class'];
                    if($val['type'] == 1){
                        $result[$val['class']]['wen'] = $val['score'];
                    }
                    if($val['type'] == 2){
                        $result[$val['class']]['li'] = $val['score'];
                    }
                }
                if($val['class'] == 3) {
                    $result[$val['class']]['batch']= '高职高专批';
                    $result[$val['class']]['year']= $val['year'];
                    $result[$val['class']]['batch_id']= $val['class'];
                    if($val['type'] == 1){
                        $result[$val['class']]['wen'] = $val['score'];
                    }
                    if($val['type'] == 2){
                        $result[$val['class']]['li'] = $val['score'];
                    }
                }
            }
            $ttt[] = $result;
        }
        return apiReturn('200', '搜索成功', $ttt);
    }

    /**
     * 一键填报
     * @return false|string
     */
    public function estimatedLine($city,$type,$score){
        $cityScore = new CityScore();
        $res = $cityScore->where(['city_id'=>$city,'year'=>date('Y'),'type'=>$type])->order('class','desc')->select()->toArray();
        $lastScore = '';
        for ($i=0;$i<count($res);$i++){
            if ($res[$i]['score'] > $score){
                break;
            }else{
                $lastScore = $res[$i];
            }
        }
        return $lastScore;
    }



    public function aKeyIsAllowed(\app\api\model\SchoolScore $schoolScore)
    {
        $mustFields = ['city','score','type'];
        $strData = $this->filterSearchFields($mustFields);
        $pageNum = (int)Request::param('pageNum', 1);
        $pageCount = (int)Request::param('pageCount', 20);
        foreach ($mustFields as $k=>$v){
            if (empty($strData[$v])){
                return apiReturn('500', $v.'参数未传');
            }
        }

        $estimatedLine = $this->estimatedLine($strData['city'],$strData['type'],$strData['score']);
        $data = $schoolScore
            ->where(['class' => $estimatedLine['class'], 'type' => $estimatedLine['type']])
            ->group('school_id')
            ->where('entry_score','between',[$strData['score']-50,$strData['score']+50])
            ->page($pageNum,$pageCount)->select()->toArray();
        return apiReturn('200', '搜索成功', ['list' => $data]);
    }
}