<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/16
 * Time: 14:09
 */

namespace app\api\controller;

use think\Db;
use think\facade\Request;
use app\api\controller\Api;

class Classroom extends \app\api\controller\Api
{
    protected $noNeedLoginPC = ['*'];
    protected $noNeedLoginAPP = ['*'];


    /**
     * 获取试题分类
     * @return false|string
     */
    public function getWorSort()
    {
        $subject = Db::table('subject')->where('status',1)->select();
        $province = Db::table('city')->where('state',1)->select();
        $worCount = Db::table('class')->count();
        $year = [];
        $thisYear = date('Y');
        for ($i = 0; $i < 7; $i++) {
            array_push($year, $thisYear - $i);
        }
        return apiReturn('200', '返回成功', [
            'worCount' => $worCount,
            'subject' => $subject,
            'province' => $province,
            'year' => $year,
        ]);
    }

    /**
     * 匹配试题列表
     * @return false|string
     */
    public function getWorList()
    {
        $isTest = (string)Request::param('isTest','');
        $categoryId = (array)Request::param('categoryId');
        $cityCode = (array)Request::param('cityCode');
        $year = (string)Request::param('year','');
        $keyword = (string)Request::param('keyword','');
        $pageNum = (int)Request::param('pageNum',1);
        $pageCount = (int)Request::param('pageCount',20);


        //where查询条件
        $map = [
            'is_test' => $isTest,
            'year' => $year,
        ];

        //为0表示取全部
        if ($isTest == '') {
            unset($map['is_test']);
        }

        $vmap = [];
        if (!empty($categoryId)) {
            array_push($vmap, ['category_id', 'in', $categoryId]);
        }

        if (!empty($cityCode)) {
            array_push($vmap, ['class.city_code', 'in', $cityCode]);
        }

        if ($year == '') {
            unset($map['year']);
        }

        if ($year == -1) {
            unset($map['year']);
            $map[] = ['year', '<', date("Y") - 6];
        }

        if (!empty($keyword)){
            $keyMap = [['title', 'like', '%' . $keyword . '%']];
        }else{
            $keyMap = 1;
        }

        $count = Db::table('class')->where($map)->where($vmap)->where($keyMap)->join('city', 'class.city_code=city.city_code')->join('subject', 'class.category_id=subject.id')->count();
        $data = Db::table('class')->where($map)->where($vmap)->where($keyMap)->page($pageNum, $pageCount)->join('city', 'class.city_code=city.city_code')->join('subject', 'class.category_id=subject.id')->select();
        return apiReturn('200', '返回成功', array_merge(['list' => $data], ['count' => $count]));
    }

    /**
     * 获取热门下载
     */
    public function getDownloadHot()
    {
        $count = Request::param('count');
        if (empty($count)) {
            $count = 10;
        }
        $data = Db::table('class')->order('click_num desc')->limit(0, $count)->join('city', 'class.city_code=city.city_code')->join('subject', 'class.category_id=subject.id')->select();
        return apiReturn('200', '获取成功', $data);
    }

    /**
     * 搜索试题
     * @param string $keyword 关键字
     */
    public function seachWor()
    {
        $keyword = Request::param('keyword');
        if (empty($keyword)) {
            return apiReturn('500', '搜索词不能为空');
        }
        $pageNum = (int)Request::param('pageNum',1);
        $pageCount = (int)Request::param('pageCount',20);

        $count = Db::table('class')->where('title', 'like', '%' . $keyword . '%')->join('city', 'class.city_code=city.city_code')->join('subject', 'class.category_id=subject.id')->count();
        $data = Db::table('class')->where('title', 'like', '%' . $keyword . '%')->page($pageNum, $pageCount)->join('city', 'class.city_code=city.city_code')->join('subject', 'class.category_id=subject.id')->select();
        return apiReturn('200', '搜索成功', array_merge(['list' => $data], ['count' => $count]));
    }

    /**
     * 下载后回调加下载次数
     * @param int $id 关键字
     */
    public function addDownloadCount()
    {
        $id = Request::param('id');
        if (empty($id)) {
            return apiReturn('500', '请传入试题ID');
        }
        $r = Db::table('class')->where('id', $id)->setInc('click_num');
        if ($r) {
            return apiReturn('200', '回调成功');
        } else {
            return apiReturn('500', '回调失败');
        }
    }


    /**
     * 获取视频列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getVideoList()
    {
        $isHighSchool = Request::param('isHighSchool');

        if ($isHighSchool == '') {
            return apiReturn('500', '请传入视频分类');
        }

        $pageNum = (int)Request::param('pageNum',1);
        $pageCount = (int)Request::param('pageCount',20);

        //where查询条件
        $map = [
            'is_high_school' => $isHighSchool
        ];

        $count = Db::table('video')->where($map)->where('status',1)->count();
        $data = Db::table('video')->where($map)->page($pageNum, $pageCount)->where('status',1)->select();
        return apiReturn('200', '获取视频列表成功', array_merge(['list' => $data], ['count' => $count]));
    }

    /**
     * 点击观看后回调加下载次数
     * @param int $id 关键字
     */
    public function addVideoHot()
    {
        $id = Request::param('id');
        if (empty($id)) {
            return apiReturn('500', '请传入视频ID');
        }
        $r = Db::table('video')->where('id', $id)->setInc('click_num');
        if ($r) {
            return apiReturn('200', '回调成功');
        } else {
            return apiReturn('500', '回调失败');
        }
    }
}