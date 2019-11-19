<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/21
 * Time: 10:40
 */

namespace app\api\controller;

use app\api\model\Appraisal;
use app\api\model\Expert;
use think\Db;

class UserInfo extends Api
{
    protected $noNeedLoginPC = [];
    protected $noNeedLoginAPP = [];

    protected function initialize()
    {
        parent::initialize();
        $this->tokenGetId();
    }

    /**
     * 获取用户基础信息
     * @return false|string
     */
    public function getBaseInfo()
    {
        $data = Db::table('user')->where('id', $this->userId)->field('id,user_name,examination,score,head_img')->find();
        $data['attention_school_count'] = Db::table('user_attention')->where([['user_id', '=', $data['id']], ['status', '=', 1], ['type', '=', 0]])->count();
        $data['attention_specialty'] = Db::table('user_attention')->where([['user_id', '=', $data['id']], ['status', '=', 1], ['type', '=', 1]])->count();
        return apiReturn('200', '返回成功', $data);
    }

    /**
     * 获取用户关注
     * @return false|string
     */
    public function getAttentionList()
    {
        $type = $this->request->param('type', 0);
        $pageNum = (int)$this->request->param('pageNum', 1);
        $pageCount = (int)$this->request->param('pageCount', 20);

        if ($type == 0) {
            $data['list'] = Db::table('user_attention')
                ->where([['user_attention.user_id', '=', $this->userId], ['user_attention.status', '=', 1], ['user_attention.type', '=', $type]])
                ->join('school', 'user_attention.in_val=school.school_id')
                ->join('school_type', 'school.school_type_id=school_type.id')
                ->join('city', 'school.city_id=city.city_code')
                ->field('school.id,school_id,school.name school_name,school_type.name school_type,school.logo,school.school_belong,school.nature,city.city,city.city_code,school.school_hot,school.school_tip_id')
                ->page($pageNum, $pageCount)
                ->select();
            foreach ($data['list'] as $k => $v) {
                $school_tip_id = [];
                $res   = explode(',',$data['list'][$k]['school_tip_id']);
                foreach ($res  as $key =>&$val) {
                    if($val == '' || $val == 0){
                        continue;
                    }
                    $school_tip_id[] = Db::table('school_tip')->where('id',$val)->find();
                }
                $data['list'][$k]['school_tip_id'] =  $school_tip_id;
            }
            $data['count'] = Db::table('user_attention')
                ->where([['user_attention.user_id', '=', $this->userId], ['user_attention.status', '=', 1], ['user_attention.type', '=', $type]])
                ->count();
        } elseif ($type == 1) {
            $data['list'] = Db::table('user_attention')
                ->where([['user_attention.user_id', '=', $this->userId], ['user_attention.status', '=', 1], ['user_attention.type', '=', $type]])
                ->join('specialty', 'user_attention.in_val=specialty.specialty_code')
                ->page($pageNum, $pageCount)
                ->select();
            $data['count'] = Db::table('user_attention')
                ->where([['user_attention.user_id', '=', $this->userId], ['user_attention.status', '=', 1], ['user_attention.type', '=', $type]])
                ->count();
            foreach ($data['list'] as $k => &$v) {
                $test =  Db::table('specialty')->where('status', 1)->where('specialty_code', $v['parent_id'])->find();
                $data['list'][$k]['parent'] = $test;
                $data['list'][$k]['grandParent'] = Db::table('specialty')->where('status', 1)->where('specialty_code', $v['parent']['parent_id'])->find();
            }
        } else {
            return apiReturn('500', '参数错误');
        }
        return apiReturn('200', '返回成功', $data);
    }

    /**
     * 获取个人详细信息
     * @return false|string
     */
    public function getUserInfo(\app\api\model\User $user)
    {

        $data = $user
            ->where('user.id', $this->userId)
            ->where('user.status', 1)
            ->field('status,create_time,updata_time,code,is_update,openid,token', true)
            ->find()->toArray();
        $searchFields = ['usual_score', 'lick_city', 'lick_school', 'nolick_school', 'nolick_city', 'lick_specialty', 'nolick_specialty'];
        foreach ($searchFields as $k => $v) {
            if (!empty($data[$v])) {
                $data[$v] = json_decode($data[$v], true);
            } else {
                $data[$v] = [];
            }
        }

        $city = Db::table('city')->where('state', 1)->select();
        $language = Db::table('language')->where('status', 1)->select();
        $school = Db::table('school')->where('status', 1)->where('parent_id', 0)->select();
        $specialty = Db::table('specialty')->where('status', 1)->where('specialty_code', '<>', 0)->select();
        return apiReturn('200', '返回成功', ['info' => $data, 'city' => $city, 'language' => $language, 'school' => $school, 'specialty' => $specialty]);
    }

    /**
     * 更新个人详情信息
     */
    public function updateUserInfo()
    {
        $strData = $this->filterSearchFields(['head_img', 'province', 'ciyt', 'district', 'school', 'grade', 'class', 'user_name', 'wx_name','score','illness']);
        $intData = $this->filterSearchFields(['is_wenli','is_sex', 'minority', 'is_hezuo', 'is_gaoshoufei', 'is_gangaotai', 'is_fushi', 'language', 'language_score'], 'int');
        $arrData = $this->filterSearchFields(['usual_score', 'lick_city', 'lick_school', 'nolick_school', 'nolick_city', 'lick_specialty', 'nolick_specialty'], 'array');

        $data = array_merge($strData, $intData, $arrData);
//        foreach ($data as $k => $v) {
//            if (in_array($k, $isUpdate)) {
//                unset($data[$k]);
//            }
//        }
       
        $r = Db::name('user')
            ->where('id', $this->userId)
            ->data($data)
            ->update();
     
        return apiReturn('200', '更新成功',true);
     
    }

    /**
     * 修改密码
     */
    public function changePwd()
    {
        $oldpwd = $this->request->post('oldpwd');
        $newpwd = $this->request->post('newpwd');
        if (empty($newpwd)) {
            return apiReturn('500', '修改密码不能为空');
        }
        $userInfo = Db::name('user')
            ->where('id', $this->userId)
            ->find();

        if ($userInfo['password'] != UserInfo::getEncryptPassword($oldpwd)) {
            return apiReturn('500', '旧密码错误');
        }
        $r = Db::name('user')
            ->where('id', $this->userId)
            ->data(['password' => UserInfo::getEncryptPassword($newpwd)])
            ->update();

        if ($r) {
            return apiReturn('200', '修改成功');
        } else {
            return apiReturn('500', '修改失败');
        }
    }

    /**
     * 关注与取消关注
     * @return false|string
     */
    public function addAttention()
    {
        $type = (string)$this->request->post('type', '');
        $id = (string)$this->request->post('id', '');
        if ($type == '') {
            return apiReturn('500', '请传入关注类型');
        }

        if (empty($id)) {
            return apiReturn('500', '请传入关注ID');
        }

        $res = Db::table('user_attention')
            ->where('user_id', $this->userId)
            ->where([['type', '=', $type], ['in_val', '=', $id]])
            ->find();
        if (!empty($res)) {
            $r = Db::name('user_attention')
                ->where('user_id', $this->userId)
                ->where([['type', '=', $type], ['in_val', '=', $id]])
                ->data(['status' => ($res['status'] + 1) % 2])
                ->update();
        } else {
            $r = Db::name('user_attention')->insert(['user_id' => $this->userId, 'type' => $type, 'in_val' => $id]);
        }

        if ($r) {
            return apiReturn('200', $res['status'] ? '取消成功' : '关注成功');
        } else {
            return apiReturn('500', '操作失败');
        }
    }

    /**
     * 通过省获取区域列表
     * @return false|string
     */
    public function getRegionList()
    {
        $cityCode = (string)$this->request->param('city_code', '');

        if (empty($cityCode)) {
            return apiReturn('500', '请传入省级code');
        }
        $res = Db::table('region')->where('status', 1)->where('city_code', $cityCode)->select();
        return apiReturn('200', '操作成功', ['list' => $res]);
    }


    /**
     * 获取区域专家信息列表
     * @return false|string
     */
    public function getMakeAppointmentList()
    {
        $cityCode = (string)$this->request->param('city_code', '');
        $region = (array)$this->request->param('region');

        $pageNum = (int)$this->request->param('pageNum', 1);
        $pageCount = (int)$this->request->param('pageCount', 20);
        if (empty($cityCode)) {
            return apiReturn('500', '请传入省级code');
        }
        if (!empty($region)) {
            $map = [['region_id', 'in', $region]];
        } else {
            $map = 1;
        }
        $expert = new Expert();
        $res = $expert->where('status', 1)->where($map)->page($pageNum, $pageCount)->select();
        $count = $expert->where('status', 1)->where($map)->count();
        return apiReturn('200', '操作成功', ['list' => $res, 'count' => $count]);
    }

    public function getExpertDesc()
    {
        $id = $this->request->param('id');
        $data =   Db::table('expert')->where('id',$id)->find();
        return apiReturn('200', '操作成功', $data);
    }

    /**
     * 获取个人预约专家列表
     */
    public function getAppointmentList()
    {
        $res = Db::table('appointment')
            ->where('user_id', $this->userId)
            ->where('appointment.status', 1)
            ->where('expert.status', 1)
            ->join('expert', 'appointment.expert_id = expert.id')
            ->select();
        return apiReturn('200', '操作成功', ['list' => $res]);
    }

    /**
     * 预约
     * @return false|string
     */
    public function appointment()
    {
        $id = $this->request->param('id', '');
        $phoneNum = $this->request->param('phoneNum', '');
        if (empty($id) || empty($phoneNum)) {
            return apiReturn('500', '传入参数过少');
        }

        $res = Db::table('appointment')
            ->where('user_id', $this->userId)
            ->where('appointment.status', 1)
            ->select();
        $arr = [];
        foreach ($res as $v) {
            $arr[] = $v['expert_id'];
        }
        if (in_array($id, $arr)) {
            return apiReturn('500', '该专家你已预约');
        }

        $r = Db::name('appointment')->insert(['user_id' => $this->userId, 'expert_id' => $id, 'mobile' => $phoneNum]);

        if ($r) {
            return apiReturn('200', '操作成功');
        } else {
            return apiReturn('500', '操作失败');
        }
    }

    /**
     * 取消预约
     * @return false|string
     */
    public function callOfappointment()
    {
        $id = $this->request->param('id', '');
        if (empty($id)) {
            return apiReturn('500', '请传入专家ID');
        }

        $res = Db::table('appointment')
            ->where('user_id', $this->userId)
            ->where('appointment.status', 1)
            ->select();
        $arr = [];
        foreach ($res as $v) {
            $arr[] = $v['expert_id'];
        }
        if (!in_array($id, $arr)) {
            return apiReturn('500', '该专家你还未预约');
        }

        $r = Db::name('appointment')
            ->where('user_id', $this->userId)
            ->where([['expert_id', '=', $id]])
            ->where('status', 1)
            ->data(['status' => 0])
            ->update();

        if ($r) {
            return apiReturn('200', '操作成功');
        } else {
            return apiReturn('500', '操作失败');
        }
    }

    /**
     * get curl
     */
    public function getAppraisal()
    {
        $params = $this->filterSearchFields(['url', 'hr_email', 'checkcode', 'id']);
        if (!isset($params['url'])) {
            return apiReturn('500', '没有请求地址');
        }
        $url = $params['url'];
        unset($params['url']);
        return apiReturn('200', '操作成功', json_decode(php_do_Url_GET($url, $params), true));
    }

    /**
     * post curl
     */
    public function postAppraisal()
    {
        $AnswerData = $this->request->param('AnswerData');
        $ParamName = $this->request->param('ParamName');
        $url = $this->request->param('url');
        $answer_url = $this->request->param('answer_url');
        $name = $this->request->param('name');

        if (empty($AnswerData) || empty($url) || empty($ParamName)) {
            return apiReturn('500', '请求参数过少');
        }
        $res = php_do_url_POST($url, $AnswerData, $ParamName);
        $url111 = '/ceping/'.$this->userId.json_decode($res, true)['ReportId'].'.html';

        if (isset(json_decode($res, true)['ReportId'])) {
            $r = Db::table('appraisal')
                ->data(['user_id' => $this->userId,
                    'result' => json_decode($res, true)['ReportId'],
                    'answer_url' => $url111,
                    'create_time' => date('Y-m-d H:i:s'),
                    'name' => $name,
                ])
                ->insert();
            if (!$r) {
                return apiReturn('500', '测评信息储存失败');
            }
        }

        $url =$answer_url.'?id='.  json_decode($res, true)['ReportId'];
        $data = iconv("gb2312", "utf-8//IGNORE",file_get_contents($url));
        $i=strpos($data,'<iframe');
        $j=strpos($data,'</iframe>',$i);
        $script = '<script>
    function printdiv(printpage){
        window.print()
    }
    window.onload=function(){
　　　　　　var bt=document.getElementById("bt");
　　　　　　var div_print=document.getElementById("div_print");
　　　　　　bt.onclick=function(){
　　　　　　　　printdiv(div_print);
　　　　　　}
　　　　}
</script>';
        $css = '<style>
    body{
        background-image: url("http://www.luming.com/img/bg.png");
    }
</style>';
        $div = '<input name="print" type="button" id="bt" value="点击打印" />';
        $top = '<img src="http://www.luming.com/img/top.png" alt="">';
        $contents=substr($data,0,$i).$script.$css.$div.$top. substr($data,$j+9);
        $contents = str_replace("href=\"/","href=\"http://www.apesk.com/",$contents);
        $contents = str_replace("gb2312","utf-8",$contents);
        $contents =  str_replace("src=\"/","src=\"http://www.apesk.com/",$contents);
        file_put_contents(APP_PATH.$url111,$contents);
        return apiReturn('200', '操作成功', 'http://'.$_SERVER['HTTP_HOST'].$url111);
    }

    /**
     * 获取测评结果列表
     * @param Appraisal $appraisal
     * @return false|string
     */

    public function AppraisalList(Appraisal $appraisal)
    {
        $pageNum = (int)$this->request->param('pageNum', 1);
        $pageCount = (int)$this->request->param('pageCount', 20);
        $res = $appraisal
            ->where(['user_id' => $this->userId, 'status' => 1])
            ->page($pageNum,$pageCount)
            ->select();
        $count = $appraisal
            ->where(['user_id' => $this->userId, 'status' => 1])
            ->count();
        foreach ($res as $k => &$v) {
            $v['answer_url'] = 'http://'.$_SERVER['HTTP_HOST'].$v['answer_url'];
        }
        return apiReturn('200', '操作成功', ['list' => $res,'count'=>$count]);
    }

    public function AppraisalDesc()
    {
        $id = $this->request->param('id');
        $res = Db::table('appraisal')

            ->where('id',$id)
            ->value('answer_url');
        return apiReturn('200', '操作成功', 'http://'.$_SERVER['HTTP_HOST'].$res);
    }

  public function GetSchoolList()
    {
        $pageNum = (int)$this->request->param('pageNum', 1);
        $pageCount = (int)$this->request->param('pageCount', 20);
        $score = $this->request->param('score');
        $city_id = $this->request->param('city_id');
        $type = $this->request->param('type');
        $class = $this->request->param('class');
        if($city_id) {
            $param['city_id'] = $city_id;
        }
        if(!isset($param)) {
            $param = [];
        }
        if ($class == 2) {
            $start= $score + 5;
            $end= $score -40;
        }else{
            $start= $score + 5;
            $end= $score - 25;
        }
        $data['lists'] = Db::table('school_score')
            ->where('entry_score','<',$start )
            ->where('entry_score','>',$end )
            ->where('year',date('Y',time()))
            ->where('class',$class)
            ->where('type',$type)
            ->order('entry_score','desc')
            ->order('school_name','desc')
            ->alias('ss')
            ->join('school_shao s','ss.name = s.name')
            ->field('ss.ranking,ss.min_score,ss.entry_score,ss.school_id,ss.type,s.name,s.school_name,s.nature,s.city_name,s.city_id,s.degree,s.lqgz,ss.year')
            ->where($param)
            ->page($pageNum,$pageCount)
            ->select();
        $data['count'] = Db::table('school_score')
            ->where('entry_score','<',$start )
            ->where('entry_score','>',$end )
            ->where('year',date('Y',time()))
            ->where('class',$class)
            ->where('type',$type)
            ->alias('ss')
            ->join('school_shao s','ss.name = s.name')
            ->where($param)
            ->count();
        foreach ($data['lists'] as $k => &$v) {
            $v['dengji'] = ceil((($score+ 5 - $v['entry_score'])) / 5 );
            preg_match_all('/\(.*?\)/i', $v['school_name'],$res);
            if ($res[0]){
                $ttt= [];
                foreach ( $res[0] as $key => &$val) {
                   $str =  str_replace(["(",")"],"",$val);
                   $ttt[] = $str;
                }
            }else{
                $ttt = [];
            }
            $v['tip'] = $ttt;
            $v['child']  =  Db::table('school_score')
                ->where('class',$class)
                ->where('type',$type)
                ->where('school_name',$v['school_name'])
                ->order('year','desc')
                ->limit(0,3)
//                ->field('entry_score,matriculate,min_score,ranking,year')
                ->field('plan_num,min_score,ranking,mean_score,mean_ranking,year')
                ->select();
        }

        //获取录取线分数
        $data['enroll_score'] = Db::table('city_score')
            ->where('type',$type)
            ->where('class',$class)
            ->order('year','desc')
            ->field('year,score')
            ->select();

        // 等同于往年分数
        $ranking = Db::table('score')
            ->where('score',$score)
            ->where('is_wenli',$type)
            ->where('year',date("Y",time()))
            ->value('ranking');

        $sql = 'SELECT `score`,`ranking`,`year` FROM ( SELECT * FROM score  where `ranking` >= '.$ranking.'  and `is_wenli` = '.$type.'  ORDER BY score DESC ) `tttt` GROUP BY  year  ORDER BY year DESC';
        $data['equal_score'] =    Db::query($sql);
        return apiReturn('200', '操作成功', $data);
    }

   public function getSpecialty()
    {
        $type = $this->request->param('type');
        $class = $this->request->param('class');
        $score = $this->request->param('score');
        $school_id  =  $this->request->param('school_id');
        $data['lists'] =   Db::table('specialty_score')->where('school_id',$school_id)
            ->where('type',$type)->where('class',$class)
            ->where('year',date('Y',time())-1)
            ->order('entry_score','asc')
            ->field('school_id,entry_score,specialty_code,matriculate_num,plan_num,study_year,pay_num,specialty_name,min_score,ranking')
            ->select();
        foreach ($data['lists'] as $k => &$v) {
            $v['child']  =  Db::table('specialty_score')
                ->where('class',$class)
                ->where('type',$type)
                ->where('school_id',$v['school_id'])
                ->where('specialty_name',$v['specialty_name'])
                ->order('year','desc')
                ->field('plan_num,min_score,ranking,mean_score,mean_ranking,year')
                ->limit(0,3)
                ->select();
        }

        //获取录取线分数
        $data['enroll_score'] = Db::table('city_score')
            ->where('type',$type)
            ->where('class',$class)
            ->order('year','desc')
            ->field('year,score')
            ->select();

		$score = $score?$score:200;
	
        // 等同于往年分数
        $ranking = Db::table('score')
            ->where('score',$score)
            ->where('is_wenli',$type)
            ->where('year',date("Y",time()))
            ->value('ranking');

        $sql = 'SELECT `score`,`ranking`,`year` FROM ( SELECT * FROM score  where `ranking` >= '.$ranking.'   and `is_wenli` = '.$type.'  ORDER BY ranking ASC ) `tttt` GROUP BY  year  ORDER BY year DESC';
        $data['equal_score'] =    Db::query($sql);
        return apiReturn('200', '操作成功', $data);
    }

    public function getReport() {
        $data =   $this->request->param();
        if ($data['class'] != 2 ) {
            $i = 0;
            foreach ($data['table'] as $k => $v) {
                if($v['dengji'] == 1){
                    $i += 0;
                }else if ($v['dengji'] == 2){
                    $i += 10;
                }else if ($v['dengji'] == 3){
                    $i += 30;
                }else if ($v['dengji'] == 4){
                    $i += 40;
                }else if ($v['dengji'] == 5){
                    $i += 60;
                }else if ($v['dengji'] == 6){
                    $i += 70;
                }else if ($v['dengji'] == 2){
                    $i += 10;
                }
            }
            if($i != 0) {
                $num = $i/290 *100;
                $num = $num> 99 ? 99 : $num;
                $num = $num< 5? 5: $num;
            }else {
                $num = 5;
            }
        }
        else{
            foreach ($data['table'] as $k => $v) {
                $i = 0;
                if($v['dengji'] == 1){
                    $i += 0;
                }else if ($v['dengji'] == 2){
                    $i += 10;
                }else if ($v['dengji'] == 3){
                    $i += 20;
                }else if ($v['dengji'] == 4){
                    $i += 30;
                }else if ($v['dengji'] == 5){
                    $i += 40;
                }else if ($v['dengji'] == 6){
                    $i += 50;
                }else if ($v['dengji'] == 7){
                    $i += 60;
                }else if ($v['dengji'] == 8){
                    $i += 70;
                }else if ($v['dengji'] == 9){
                    $i += 80;
                }
            }
            if($i != 0) {
                $num = $i/290 *100;
                $num = $num> 99 ? 99 : $num;
                $num = $num< 5? 5: $num;
            }else {
                $num = 5;
            }
        }
        $param = [
          'user_id' => $this->userId,
          'is_num' => $num,
          'desc' => json_encode($this->request->param(),JSON_UNESCAPED_UNICODE) ,
          'create_time' => date('Y-m-d H:i:s',time()),
          'class' => $data['class'],
          'score' => $data['score'],
          'is_wenli' => $data['is_wenli'],
        ];
        $num =  Db::table('user_report')->insertGetId($param);
        return apiReturn('200', '操作成功', $num);
    }

    public function getReportList() {
        $pageNum = (int)$this->request->param('pageNum', 1);
        $pageCount = (int)$this->request->param('pageCount', 20);
        $data = Db::table('user_report')
            ->where('user_id',$this->userId)
            ->page($pageNum,$pageCount)
            ->select();
        $count = Db::table('user_report')
            ->where('user_id',$this->userId)
            ->count();
        return apiReturn('200', '操作成功', ['list' => $data,'count'=>$count]);
    }

    public function getReportDesc()
    {
        $id = $this->request->param('id');
        $data = Db::table('user_report')->where('user_id',$this->userId)->where('id',$id)->find();
        return apiReturn('200', '操作成功',$data);
    }

   public function report()
    {
        $class = $this->request->param('class');
        $type = $this->request->param('type');
        $score = $this->request->param('score');
        $score =  $score+5 ;
        if($class == 2) {
            $num = 9;
        }else{
            $num = 6;
        }
        for ($i =0 ;$i<$num;$i++) {
            $score = $score -5;
            $school_id_array = Db::table('school_score')
                ->alias('ss')
                ->where('ss.class',$class)
                ->where('ss.type',$type)
                ->where('ss.entry_score','<',$score+5 )
                ->where('ss.entry_score','>',$score)
                ->where('ss.year',date('Y',time()))
                ->join('school_shao shao','shao.name = ss.name')
                ->field('shao.school_name,ss.school_id,shao.city_name,ss.entry_score')
                ->select();
            if(!$school_id_array) {
                return apiReturn('500', '未查询到学校', $num);
            }
            $random_keys=array_rand($school_id_array,1);
            $schoolData = $school_id_array[$random_keys];
            $specialty =   Db::table('specialty_score')
                ->where('school_id',$schoolData['school_id'])
                ->where('class',$class)
                ->where('type',$type)
                ->where('year',2018)
                ->field('ranking,min_score,specialty_name,entry_score')
                ->group('specialty_name')
                ->order('entry_score','asc')
                ->limit(0,3)
                ->select();
            $schoolData['specialty'] = $specialty;
            $table[] = $schoolData;
        }
        $param = [
            'class' => 1,
            'table' => $table,
            'score' =>$score,
            'is_wenli' =>$type,
            'weci' =>$score,
        ];
        $test = [
            'user_id'=>$this->userId,
            'is_num'=>0,
            'desc'=>json_encode($param,JSON_UNESCAPED_UNICODE),
            'create_time'=>date('Y-m-d H:i:s'),
            'class'=>$class,
            'score'=>$score,
            'is_wenli'=>$type,
        ];
        $num =   Db::table('user_report')->insertGetId($test);
        return apiReturn('200', '操作成功', $num);
    }
    
    public function getDataById() {
        $data =  Db::table('about_us')->where('id',1)->find();
        return apiReturn('200', '操作成功', $data);
    }
    
      public function getYearScore()
    {
        $score = $this->request->param('score');
        $type = $this->request->param('type');
        $ranking = Db::table('score')
            ->where('score',$score)
            ->where('is_wenli',$type)
            ->where('year',date("Y",time()))
            ->value('ranking');
        $sql = 'SELECT `score`,`ranking`,`year` FROM ( SELECT * FROM  score  where `ranking` >= '.$ranking.'  and `is_wenli` = '.$type.' ORDER BY ranking ASC ) `tttt` GROUP BY  year  ORDER BY year DESC';
        $data = Db::query($sql);
        return apiReturn('200', '操作成功', $data);
    }

	public function getSchoolData()
    {
        $school_id = (int)$this->request->param('school_id');
        $score = $this->request->param('score');
        $type = $this->request->param('type');
        $class = $this->request->param('class');
        $data['lists'] = Db::table('school_score')
            ->where('school_id',$school_id)
            ->group('school_id')
            ->where('class',$class)
            ->where('type',$type)
            ->order('entry_score','desc')
            ->order('school_name','desc')
            ->alias('ss')
            ->join('school_shao s','ss.name = s.name')
            ->field('ss.ranking,ss.min_score,ss.entry_score,ss.school_id,ss.type,s.name,s.school_name,s.nature,s.city_name,s.city_id,s.degree,s.lqgz,ss.year')
            ->select();
        foreach ($data['lists'] as $k => &$v) {
            $v['dengji'] = ceil((($score+ 5 - $v['entry_score'])) / 5 );
            preg_match_all('/\(.*?\)/i', $v['school_name'],$res);
            if ($res[0]){
                $ttt= [];
                foreach ( $res[0] as $key => &$val) {
                    $str =  str_replace(["(",")"],"",$val);
                    $ttt[] = $str;
                }
            }else{
                $ttt = [];
            }
            $v['tip'] = $ttt;
            $v['child']  =  Db::table('school_score')
                ->where('class',$class)
                ->where('type',$type)
                ->where('school_name',$v['school_name'])
                ->order('year','desc')
                ->limit(0,3)
                ->field('plan_num,min_score,ranking,mean_score,mean_ranking,year')
                ->select();
        }

        //获取录取线分数
        $data['enroll_score'] = Db::table('city_score')
            ->where('type',$type)
            ->where('class',$class)
            ->order('year','desc')
            ->field('year,score')
            ->select();

        // 等同于往年分数
        $ranking = Db::table('score')
            ->where('score',$score)
            ->where('is_wenli',$type)
            ->where('year',date("Y",time()))
            ->value('ranking');

        $sql = 'SELECT `score`,`ranking`,`year` FROM ( SELECT * FROM score  where `ranking` >= '.$ranking.'  and `is_wenli` = '.$type.'  ORDER BY score DESC ) `tttt` GROUP BY  year  ORDER BY year DESC';
        $data['equal_score'] =    Db::query($sql);
        return apiReturn('200', '操作成功', $data);
    }
    
    
        //专业优先
   //专业优先
    public function getSpecialtyList()
    {
        $pageNum = (int)$this->request->param('pageNum', 1);
        $pageCount = (int)$this->request->param('pageCount', 20);
        $score = $this->request->param('score');
        $type = $this->request->param('type');
        $class = $this->request->param('class');
        $specialty_code = $this->request->param('specialty_code');
        $school_id = Db::table('specialty_score')
            ->where('specialty_code','in',$specialty_code)
            ->where('type',$type)
            ->where('class',$class)
            ->group('school_id')
            ->order('entry_score','desc')
            ->field('id,school_id,entry_score')
            ->column('school_id');
        if ($class == 2) {
            $start= $score + 5;
            $end= $score -40;
        }else{
            $start= $score + 5;
            $end= $score - 25;
        }
        $data['lists'] = Db::table('school_score')
            ->where('entry_score','<',$start )
            ->where('entry_score','>',$end )
            ->where('year',date('Y',time()))
            ->where('class',$class)
            ->where('school_id','in',$school_id)
            ->where('type',$type)
            ->order('entry_score','desc')
            ->order('school_name','desc')
            ->alias('ss')
            ->join('school_shao s','ss.name = s.name')
            ->field('ss.ranking,ss.min_score,ss.entry_score,ss.school_id,ss.type,s.name,s.school_name,s.nature,s.city_name,s.city_id,s.degree,s.lqgz,ss.year')
            ->page($pageNum,$pageCount)
            ->select();

        $data['count'] = Db::table('school_score')
            ->where('entry_score','<',$start )
            ->where('entry_score','>',$end )
            ->where('year',date('Y',time()))
            ->where('class',$class)
            ->where('type',$type)
            ->where('school_id','in',$school_id)
            ->alias('ss')
            ->join('school_shao s','ss.name = s.name')
            ->count();
        foreach ($data['lists'] as $k => &$v) {
            $v['dengji'] = ceil((($score+ 5 - $v['entry_score'])) / 5 );
            preg_match_all('/\(.*?\)/i', $v['school_name'],$res);
            if ($res[0]){
                $ttt= [];
                foreach ( $res[0] as $key => &$val) {
                    $str =  str_replace(["(",")"],"",$val);
                    $ttt[] = $str;
                }
            }else{
                $ttt = [];
            }
            $v['tip'] = $ttt;
            $v['child']  =  Db::table('school_score')
                ->where('class',$class)
                ->where('type',$type)
                ->where('school_name',$v['school_name'])
                ->order('year','desc')
                ->limit(0,3)
//                ->field('entry_score,matriculate,min_score,ranking,year')
                ->field('plan_num,min_score,ranking,mean_score,mean_ranking,year')
                ->select();
        }

        //获取录取线分数
        $data['enroll_score'] = Db::table('city_score')
            ->where('type',$type)
            ->where('class',$class)
            ->order('year','desc')
            ->field('year,score')
            ->select();

        // 等同于往年分数
        $ranking = Db::table('score')
            ->where('score',$score)
            ->where('is_wenli',$type)
            ->where('year',date("Y",time()))
            ->value('ranking');

        $sql = 'SELECT `score`,`ranking`,`year` FROM ( SELECT * FROM score  where `ranking` >= '.$ranking.'  and `is_wenli` = '.$type.'  ORDER BY score DESC ) `tttt` GROUP BY  year  ORDER BY year DESC';
        $data['equal_score'] =    Db::query($sql);
        return apiReturn('200', '操作成功', $data);
    }

	// 专业 优先。 获取专业
    public function getSpecialtyData()
    {
        $type = $this->request->param('type');
        $class = $this->request->param('class');
        $score = $this->request->param('score');
        $school_id  =  $this->request->param('school_id');
        $specialty_code = $this->request->param('specialty_code');
        $map[] = 'specialty_code';
        foreach ($specialty_code as $k =>$v) {
               $str =   substr($v,0,4);
            $map[] = ['like',$str.'%'];
        }
        $map[] = 'or';
        $where[] = $map;
        $data['lists'] =   Db::table('specialty_score')
            ->where($where)
            ->where('school_id',$school_id)
            ->where('type',$type)->where('class',$class)
            ->where('year',date('Y',time())-1)
            ->order('entry_score','asc')
            ->field('school_id,entry_score,specialty_code,matriculate_num,plan_num,study_year,pay_num,specialty_name,min_score,ranking')
            ->select();
        foreach ($data['lists'] as $k => &$v) {
            $v['child']  =  Db::table('specialty_score')
                ->where('class',$class)
                ->where('type',$type)
                ->where('school_id',$v['school_id'])
                ->where('specialty_name',$v['specialty_name'])
                ->order('year','desc')
                ->field('plan_num,min_score,ranking,mean_score,mean_ranking,year')
                ->limit(0,3)
                ->select();
        }
        //获取录取线分数
        $data['enroll_score'] = Db::table('city_score')
            ->where('type',$type)
            ->where('class',$class)
            ->order('year','desc')
            ->field('year,score')
            ->select();
        $score = $score ? $score:200;
        // 等同于往年分数
        $ranking = Db::table('score')
            ->where('score',$score)
            ->where('is_wenli',$type)
            ->where('year',date("Y",time()))
            ->value('ranking');

        $sql = 'SELECT `score`,`ranking`,`year` FROM ( SELECT * FROM score  where `ranking` >= '.$ranking.'   and `is_wenli` = '.$type.'  ORDER BY ranking ASC ) `tttt` GROUP BY  year  ORDER BY year DESC';
        $data['equal_score'] =    Db::query($sql);
        return apiReturn('200', '操作成功', $data);
    }
    
    
    
    
    
    
    
    
    
    
}