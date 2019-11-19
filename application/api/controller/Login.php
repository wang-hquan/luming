<?php


namespace app\api\controller;


use think\Controller;
use think\Db;
use think\Exception;
use think\facade\Cache;
use think\facade\Request;

class Login extends Controller
{
    protected  $appId = 'wxea9f7e95445ee822';
    protected  $Secret = '02e4c627e3047d7d76bea25ed518faa8';

    protected   $code = '';
    protected   $access_token = '';
    protected   $open_id = '';
    protected   $unionid = '';

    public function getUrl()
    {
        $url =  $this->request->param('url');
        $url =  urlencode($url);
        $url =  'https://open.weixin.qq.com/connect/qrconnect?appid='.$this->appId.'&redirect_uri='.$url.'&response_type=code&scope=snsapi_login&state=STATE#wechat_redirect';
        return apiReturn('200', '登陆成功', ['url' => $url]);
    }

    public function get_access_token()
    {
        try{
            $url  = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appId.'&secret='.$this->Secret.'&code='.$this->code.'&grant_type=authorization_code';
            $data =    json_decode(file_get_contents($url),true) ;
            if(isset($data['errcode'])){

                throw new Exception(json_encode($data));
            }
            $this->access_token = $data['access_token'];
            $this->open_id = $data['openid'];
        } catch (\Exception $ex) {
            return toJson(500,'error',$ex->getMessage());
        }
    }

    public function getUserInfo()
    {
        try{
            $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$this->access_token.'&openid='.$this->open_id;
            $data =    json_decode(file_get_contents($url),true) ;
            if(isset($data['errcode'])){
                throw new Exception(json_encode($data));
            }
            $this->unionid = $data['unionid'];
            return $data;
        } catch (\Exception $ex) {
            return toJson(500,'error',$ex->getMessage());
        }
    }

    public function login()
    {
        try{
            $this->code = $this->request->param('code');
            if(!$this->code) {
                throw new Exception('请传入code');
            }
            $this->get_access_token();
            $user =   $this->getUserInfo();
            $data =  \app\api\model\User::where('unionid',$this->unionid)->find();
            if($data){
                $data->token = md5(md5($data->account.$data->password));
                $data->save();
                return apiReturn('200', '登陆成功', ['token' => $data->token]);
            }else{
                return apiReturn('201', '新登录微信', ['data' => $user]);
            }
        } catch (\Exception $ex) {
            return toJson(500,'error',$ex->getMessage());
        }
    }

    public function register(\app\api\validate\User $validate, \app\api\model\User $user)
    {
        $phoneNum = Request::post('mobile');
        $phoneCode = Request::post('code');
        $password = Request::post('password');
        $param = $this->request->param();
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
        $r = $user::where('mobile', $phoneNum)->find();
        if (!empty($r)) {
            $r->unionid = $param['unionid'];
            $r->token = md5(md5($r->account.$r->password));
            $r->save();
            return apiReturn('200', '登陆成功', ['token' => $r->token]);
        }else{
            $randStr = $this->randStr();
            $userInfo = [
                'account' => 'luming'.$randStr,
                'token' =>  md5(md5($randStr.$data['password'])),
                'mobile' => $phoneNum,
                'code' => $phoneCode,
                'unionid' =>  $param['unionid'],
                'user_name' =>  $param['nickname'],
                'password' => md5(md5($password) ),
                'is_sex' =>  $param['sex']== 1?1:0 ,
                'head_img' =>  $param['headimgurl'],
            ];
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
    }

    private static function randStr()
    {
        $str = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKJLZXCVBNM';
        return substr(str_shuffle($str), 0, 12);
    }

}