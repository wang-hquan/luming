<?php


namespace app\api\controller;


use think\Controller;
use think\Db;

class SearchNews extends Controller
{
    public function getNews()
    {
        $name =  $this->request->param('keyword');
        $data  =   Db::table('news')
            ->where('type',1)
            ->where('title','like','%'.$name.'%')
            ->select();
        return apiReturn('200', '操作成功', $data);
    }

    public function get_school_news()
    {
        $name =  $this->request->param('keyword');
        $data  =   Db::table('school_news')
            ->where('title','like','%'.$name.'%')
            ->select();
        return apiReturn('200', '操作成功', $data);
    }
    
    public function getDataById() {
        $data =  Db::table('about_us')->where('id',1)->find();
        return apiReturn('200', '操作成功', $data);
    }
}