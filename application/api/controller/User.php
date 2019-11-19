<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/15
 * Time: 15:17
 */

namespace app\api\controller;


use think\Db;
use app\common\controller\Auth;
use think\facade\Request;
use think\facade\Cache;

class User extends \app\api\controller\Api
{
    protected $noNeedLoginPC = ['*'];
    protected $noNeedLoginAPP = ['*'];


    /**
     * 获取全球唯一标识
     * @return string
     */
    private static function uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }



    private static function randStr()
    {
        $str = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKJLZXCVBNM';
        return substr(str_shuffle($str), 0, 12);
    }

    /**
     * 发送验证码
     * @param \app\api\validate\User $validate
     * @return false|string
     */
    public function sendPhoneCode(\app\api\validate\User $validate)
    {
        $phoneNum = Request::param('mobile');
        //判断验证码 防止用户污染
        // if (Cache::get($phoneNum)) {
        //     return apiReturn('500', '1分钟内只能发送一次验证码！');
        // }

        //判断手机号有效性
        if (!$validate->scene('mobile')->check(['mobile' => $phoneNum])) {
            return apiReturn('500', $validate->getError());
        }

        //生成验证码，储存验证码
        $phoneCode = [
                'code' => rand(1000, 9999)
            ];
        Cache::set($phoneNum, $phoneCode['code'], 5 * 60);
        //发送验证码
        $result =    send_sms($phoneNum,'SMS_177248330',$phoneCode);
        if ($result['Message'] == 'OK') {
            return toJson('200', '获取验证码成功');
        }else{
            return toJson('500', '验证码发送失败');
        }
    }

    /**
     * 用户注册
     * @param \app\api\validate\User $validate
     * @return bool|false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function register(\app\api\validate\User $validate, \app\api\model\User $user)
    {

        $phoneNum = Request::post('mobile');
        $phoneCode = Request::post('code');
        $password = Request::post('password');
        if (empty($password)) {
            return apiReturn('500', '密码不能为空');
        }
        $data = [
            'mobile' => $phoneNum,
            'code' => $phoneCode,
            'password' => User::getEncryptPassword($password)
        ];

        if (!$validate->scene('register')->check($data)) {
            return apiReturn('500', $validate->getError());
        }


        if (Cache::get($phoneNum) != $phoneCode) {
            return apiReturn('500', '手机验证码错误');
        }
        // if (1234 != $phoneCode) {
        //     return apiReturn('500', '手机验证码错误');
        // }

        $r = $user::where('mobile', $phoneNum)->find();

        if (!empty($r)) {
            return apiReturn('500', '该手机号已经被注册');
        }

        $randStr = User::randStr();
        $userInfo = [
            'user_name' => 'luming' . $randStr,
            'account' => $randStr,
            'token' =>  md5(md5($randStr.$data['password']))
        ];
        $userInfo = array_merge($data, $userInfo);

        //账号注册时需要开启事务,避免出现垃圾数据
        Db::startTrans();
        try {
            $user->save($userInfo);
            Db::commit();
        } catch (Exception $e) {
            $e->getMessage();
            Db::rollback();
            return false;
        }
        return apiReturn('200', '注册成功',['token'=>$userInfo['token']]);
    }

    /**
     * 登陆
     * @param \app\api\validate\User $validate
     * @return false|string
     */
    public function login(\app\api\validate\User $validate, \app\api\model\User $user)
    {
        $username = Request::post('username');
        $mobile = Request::post('mobile');
        $password = Request::post('password');

        if (!$username && !$mobile) {
            return apiReturn('500', '请输入账号');
        }

        if ($username) {
            if (!$validate->scene('usernameLogin')->check(['username' => $username, 'password' => User::getEncryptPassword($password)])) {
                return apiReturn('500', $validate->getError());
            }
            $r = $user::where('user_name', $username)->find();
            if ($r) {
                if ($r->password != User::getEncryptPassword($password)) {
                    return apiReturn('500', '账号或密码错误');
                } else {
                    return apiReturn('500', '登陆成功', json_encode($r));
                }
            } else {
                return apiReturn('500', '该用户尚未注册');
            }
        }

        if ($mobile) {
//            if (!$validate->scene('mobileLogin')->check(['mobile' => $mobile, 'password' => User::getEncryptPassword($password)])) {
//                return apiReturn('500', $validate->getError());
//            }
            $r = $user::where('mobile', $mobile)->where('password',md5(md5($password)))->find();
            if ($r) {
            	 $r->token = md5(md5($r->account.$r->password));
                    $r->save();
                    return apiReturn('200', '登陆成功', ['token' => $r->token]);
            } else {
                $r = $user::where('account', $mobile)->where('password',md5(md5($password)))->find();
                if ($r) {
                	$r->token = md5(md5($r->account.$r->password));
                    $r->save();
                    return apiReturn('200', '登陆成功', ['token' => $r->token]);

                } else {
                    return apiReturn('500', '账号或密码错误');
                }
            }

        }
    }


    /**
     * 修改密码
     * @param \app\api\validate\User $validate
     * @param \app\api\model\User $user
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function resetpwd(\app\api\validate\User $validate, \app\api\model\User $user)
    {
        $mobile = Request::post('mobile');
        $code = Request::post('code');
        $password = Request::post('password');

        if (empty($password)) {
            return apiReturn('500', '新密码不能为空');
        }

        $data = [
            'mobile' => $mobile,
            'code' => $code,
            'password' => User::getEncryptPassword($password)
        ];

        if (!$validate->scene('resetpwd')->check($data)) {
            return apiReturn('500', $validate->getError());
        }

        $r = $user::where('mobile', $mobile)->find();

        if (empty($r)) {
            return apiReturn('500', '该手机号未被注册');
        }

        if (Cache::get($mobile) != $code) {
            return apiReturn('500', '手机验证码错误');
        }

        $r = $user->save($data, ['mobile' => $mobile]);

        if ($r) {
            return apiReturn('200', '密码修改成功');
        } else {
            return apiReturn('200', '密码修改失败');
        }
    }

}
