<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/17
 * Time: 11:23
 */

namespace app\api\controller;

use think\Controller;
use think\facade\Request;
use think\Db;

class Api extends Controller
{
    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLoginPC = [];
    protected $noNeedLoginAPP = [];
    protected $userId = '';

    protected function initialize()
    {
        $this->checkToken();
    }

    /**
     * 通过token获取用户id
     */
    public function tokenGetId()
    {
        $this->token = $this->request->header('Token');
        if (empty($this->token)){
            return apiReturn('500', '请传入token');
        }
        $this->userId = Db::table('user')->where('token', $this->token)->find()['id'];
    }

    /**
     * 检测当前控制器和方法是否匹配传递的数组
     *
     * @param array $arr 需要验证权限的数组
     * @return boolean
     */
    protected function match($arr = [])
    {
        $request = Request::instance();
        $arr = is_array($arr) ? $arr : explode(',', $arr);
        if (!$arr) {
            return false;
        }
        $arr = array_map('strtolower', $arr);
        // 是否存在
        if (in_array(strtolower($request->action()), $arr) || in_array('*', $arr)) {
            return true;
        }

        // 没找到匹配
        return false;
    }

    /**
     * 验证token
     */
    protected function checkToken()
    {
        $deviceType = Request::header('device-type');
        if (empty($deviceType)) {
            echo apiReturn('500', '无法确认身份');
            die();
        }
        $judgeAuthority = '';
        if ($deviceType == 'pc') {
            $judgeAuthority = $this->match($this->noNeedLoginPC);
        } elseif ($deviceType == 'app') {
            $judgeAuthority = $this->match($this->noNeedLoginAPP);
        }
        if (!$judgeAuthority) {
            $token = Request::header('Token');
            if (empty($token)) {
                echo apiReturn('500', '请传入token');
                die();
            }
            $r = Db::table('user')->where('token', $token)->find();
            if (empty($r)) {
            	echo apiReturn('300', '登录失效，请重新登录');
                die();
            }
        }
    }

    /**
     * 数据分组
     * @param $data
     * @param $keyword
     * @return array
     */
    protected function groupBy($data, $keyword)
    {
        $arr = [];
        foreach ($data as $k => $v) {
            if (!isset($arr[$v[$keyword]])) {
                $arr[$v[$keyword]] = $v;
            }
            unset($data[$k]);
        }
        return $arr;
    }


    /**
     * 获取传输过来的数据
     * @param $searchFields
     * @return array
     */
    public function filterSearchFields($searchFields, $type = 'string')
    {
        $data = [];
        foreach ($searchFields as $filed) {
            $v = $this->request->param($filed);
            switch ($type) {
                case 'string':
                    if (trim($v) === '') {
                        continue;
                    }
                    $data[$filed] = (string)$v;
                    break;
                case 'int':
                    if (trim($v) === '') {
                        continue;
                    }
                    $data[$filed] = (int)$v;
                    break;
                case 'array':
                    if (empty($v)) {
                        continue;
                    }
                    $data[$filed] = json_encode((array)$v);
                    break;
            }
        }
        return $data;
    }

    /**
     * 获取密码加密后的字符串
     * @param string $password 密码
     * @param string $salt 密码盐
     * @return string
     */
    public static function getEncryptPassword($password, $salt = '')
    {
        return md5(md5($password) . $salt);
    }

    /**
     * 接口参数过滤
     * @param string $type 过滤类型
     * @param array $notMust 不必要参数
     * @param array $must 必要参数
     * @return array 过滤结果
     */
    public function filterParameters($type = 'str', array $notMust = [], array $must = [])
    {
        $data = [];
        foreach ($notMust as $filed) {
            $v = $this->request->param($filed);
            switch ($type) {
                case 'str':
                    if (trim($v) === '') {
                        continue;
                    }
                    $data[$filed] = (string)$v;
                    break;
                case 'int':
                    if (trim($v) === '') {
                        continue;
                    }
                    $data[$filed] = (int)$v;
                    break;
                case 'arr':
                    if (empty($v)) {
                        continue;
                    }
                    $data[$filed] = $v;
                    break;
            }
        }

        foreach ($must as $filed) {
            $v = $this->request->param($filed);
            switch ($type) {
                case 'str':
                    if (trim($v) === '') {
                        return apiReturn('500', '参数：'.$filed.' 为必传参数！');
                    }
                    $data[$filed] = (string)$v;
                    break;
                case 'int':
                    if (trim($v) === '') {
                        return apiReturn('500', '参数：'.$filed.' 为必传参数！');
                    }
                    $data[$filed] = (int)$v;
                    break;
                case 'arr':
                    if (empty($v)) {
                        return apiReturn('500', '参数：'.$filed.' 为必传参数！');
                    }
                    $data[$filed] = $v;
                    break;
            }
        }
        return $data;
    }
}