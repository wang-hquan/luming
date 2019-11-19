<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/10/15
 * Time: 16:21
 */

namespace app\api\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'username|用户名' => 'require',
        'mobile|手机号' => 'require|mobile',
        'code|验证码' => 'require|number|length:4',
        'password|密码' => 'require|length:32'
    ];

    protected $scene = [
        'register'=>['mobile','code','password'],
        'mobile' => ['mobile'],
        'mobileLogin' => ['mobile','password'],
        'usernameLogin' => ['username','password'],
        'resetpwd'=>['mobile','code','password']
    ];
}