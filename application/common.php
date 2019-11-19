<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
require_once '../extend/alisms/vendor/autoload.php';
/**
 * Api接口调用
 * @param null $data
 * @return false|string
 */
 
use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;

//api接口返回
function apiReturn($code = '', $msg = '',  $data = null)
{
    $result = [
        'code' => $code,
        'msg' => $msg,
        'data' => $data,
    ];
    return json_encode($result,JSON_UNESCAPED_UNICODE);
}


function send_sms($to, $model, $code)
{
    Config::load(); //加载区域结点配置
//    $config = Db::name('sms_config')->select();
    $accessKeyId = 'LTAI4FqP3QiSz7DYiXXYyudA';
    $accessKeySecret = 'xQYC4jsQNzm5ATB3TpMvt1S9bYzeIa';
    $templateParam = $code;
    $templateCode = $model;
    //短信模板ID
//    switch ($model) {
//        case 1:
//            $templateCode = $config[0]['sms_stencil_code']; // 注册登录短信验证码模板
//            break;
//        case 2:
//            $templateCode = $config[1]['sms_stencil_code']; // 重置密码短信验证码模板
//            break;
//    }
    //短信API产品名（短信产品名固定，无需修改）
    $product = "Dysmsapi";
    //短信API产品域名（接口地址固定，无需修改）
    $domain = "dysmsapi.aliyuncs.com";
    //暂时不支持多Region（目前仅支持cn-hangzhou请勿修改）
    $region = "cn-hangzhou";
    // 初始化用户Profile实例
    $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
    // 增加服务结点
    DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
    // 初始化AcsClient用于发起请求
    $acsClient = new DefaultAcsClient($profile);
    // 初始化SendSmsRequest实例用于设置发送短信的参数
    $request = new SendSmsRequest();
    // 必填，设置雉短信接收号码
    $request->setPhoneNumbers($to);
    // 必填，设置签名名称
    $request->setSignName('鹿鸣教育');
    // 必填，设置模板CODE
    $request->setTemplateCode($templateCode);
    // 可选，设置模板参数
    if ($templateParam) {
        $request->setTemplateParam(json_encode($templateParam));
    }
    //发起访问请求
    $acsResponse = $acsClient->getAcsResponse($request);
    //返回请求结果
    $result = json_decode(json_encode($acsResponse), true);
    // 具体返回值参考文档：https://help.aliyun.com/document_detail/55451.html?spm=a2c4g.11186623.6.563.YSe8FK
    return $result;
}


function toJson($code,$msg,$data='') {
    $param = [
        'code' => $code,
        'data' =>$data,
        'msg' =>$msg
    ];
    echo  json_encode($param,JSON_UNESCAPED_UNICODE);
    exit;
}

function  getTree( $data,   $pid = 0  ) {
    $tree = [];
    foreach ($data as $k => $v) {
        if ($v['parent_id'] == $pid) {
            $result = getTree($data, $v['id']);
            if($result != ''){
                $v['childrens'] = $result;
            }
            $tree[] = $v;
            unset($data[$k]);
        }
    }
    return $tree;
}
function groupByKeyword($data, $keyword)
{
    $arr = [];
    foreach ($data as $k => $v) {
        if (!isset($arr[$v[$keyword]])) {
            $arr[$v[$keyword]] = $v;
        }
    }
    $array = [];
    foreach ($arr as $k=>$v){
        foreach ($data as $key=>$val){
            if ($k == $val[$keyword]){
                $array[$k][] = $val;
            }
        }
    }
    return $array;
}

function php_do_Url_GET($url, $params)
{

    $url = "{$url}?" . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    $result = curl_exec($ch);
    curl_close($ch);

    return mb_convert_encoding($result, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
}

/**
 * POST
 */
function php_do_url_POST($url, $params, $paramName)
{
    $ch = curl_init();
	$params =   mb_convert_encoding ($params,'gb2312','utf-8');
    $strs = $paramName . ' =' . $params;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $strs);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

    $result = curl_exec($ch);
    curl_close($ch);
    return mb_convert_encoding($result, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
}
