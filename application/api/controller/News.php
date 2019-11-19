<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/16
 * Time: 9:06
 */

namespace app\api\controller;

use think\facade\Request;
use think\Db;

class News extends Api
{
    protected $noNeedLoginPC = ['*'];
    protected $noNeedLoginAPP = ['*'];

    /**
     * 获取新闻列表
     * @param \app\api\model\News $news
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getNewsList(\app\api\model\News $news)
    {
        $pageNum = (int)Request::param('pageNum', 1);
        $pageCount = (int)Request::param('pageCount', 20);
        $type = (int)Request::param('type', 1);
        $count = $news->where('status',1)->where('type',$type)->count();
        $res = $news::order('create_time', 'desc')
            ->where('type',$type)
            ->order('create_time','desc')
            ->where('status',1
            )->page($pageNum, $pageCount)->select();
        return apiReturn('200', "返回成功", array_merge(['list' => $res], ['count' => $count]));
    }

    /**
     * 获取新闻详情
     * @param \app\api\model\News $news
     * @return false|string
     */
    public function getNewsInfo(\app\api\model\News $news)
    {
        $id = Request::param('id');
        if (empty($id)) {
            return apiReturn('500', "请传入id");
        }
        $r = $news::where([['id','=',$id],['status','=',1]])->find();
        return apiReturn('200', "返回成功", json_decode($r,true));
    }


    /**
     * 获取学生喜讯列表
     * @param \app\api\model\News $news
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getStudentNewsList()
    {
        $pageNum = (int)Request::param('pageNum', 1);
        $pageCount = (int)Request::param('pageCount', 20);
        $count = Db::table('news_student')->where('status',1)->count();
        $res = Db::table('news_student')->order('create_time', 'desc')->where('status',1)->page($pageNum, $pageCount)->select();
        return apiReturn('200', "返回成功", array_merge(['list' => $res], ['count' => $count]));
    }

    /**
     * 获取学生喜讯详情
     * @param
     * @return false|string
     */
    public function getStudentNewsInfo()
    {
        $id = Request::param('id');
        if (empty($id)) {
            return apiReturn('500', "请传入id");
        }
        $r = Db::table('news_student')->where([['id','=',$id],['status','=',1]])->find();
        return apiReturn('200', "返回成功", $r);
    }

    /**
     * 获取轮播
     * @return false|string
     */
    public function getBanner(){
        $type = Request::param('type',0);
        $data = Db::table('banner')->where('status',1)->where('type',$type)->order('weight','desc')->select();
        return apiReturn('200', "返回成功", ['list'=>$data]);
    }
    
     public function getNewsBanner()
    {
        $type = Request::param('type',0);
        $data = Db::table('news_banner')->where('status',1)->where('type',$type)->order('weight','desc')->limit(0,3)->select();
        return apiReturn('200', "返回成功", ['list'=>$data]);
    }
}