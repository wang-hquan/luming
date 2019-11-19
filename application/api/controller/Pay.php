<?php

namespace app\api\controller;

use think\Controller;
use think\Db;

class Pay extends Controller
{
    public function PC_alipay($subject,$trade_no,$amount,$body)
    {
        header("Content-type:text/html;charset=utf-8");
        if ($subject) {
            include_once  '../extend/alipay/config.php';
            include_once  '../extend/alipay/pagepay/service/AlipayTradeService.php';
            include_once  '../extend/alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';
            //商户订单号，商户网站订单系统中唯一订单号，必填
            $out_trade_no = trim($trade_no);

            //订单名称，必填
            $subject = trim($subject);

            //付款金额，必填
            $total_amount = trim($amount);

            //商品描述，可空
            $body = trim($body);
            //构造参数
            $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
            $payRequestBuilder->setBody($body);
            $payRequestBuilder->setSubject($subject);
            $payRequestBuilder->setTotalAmount($total_amount);
            $payRequestBuilder->setOutTradeNo($out_trade_no);

            $aop = new \AlipayTradeService($config);

            /**
             * pagePay 电脑网站支付请求
             * @param $builder 业务参数，使用buildmodel中的对象生成。
             * @param $return_url 同步跳转地址，公网可以访问
             * @param $notify_url 异步通知地址，公网可以访问
             * @return $response 支付宝返回的信息
             */
            $response = $aop->pagePay($payRequestBuilder, $config['return_url'], $config['notify_url']);
            return $response;
        }
    }

    public function test($type,$subject,$trade_no,$amount,$body) {
        require_once '../extend/alipay_sdk/aop/AopClient.php';
        require_once '../extend/alipay_sdk/aop/request/AlipayTradeWapPayRequest.php';
        require_once '../extend/alipay_sdk/aop/request/AlipayTradePagePayRequest.php';

        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = '2019102868701547';
        $aop->rsaPrivateKey = 'MIIEowIBAAKCAQEApJ7NcwPmJLq5KnrFY8tRF+q0A/E3GcAv8dG04P1WY0ccwtzjxser29kyR+02oxZoAA1xftDaC3UuOE96kDVvs0S8M+siYqcIIltpikRIsbxCTJgb1AwgOlORmtXB5OAsyITlSL8ILY5FR4SxQS+5BlvZtkvnBd4GzoPoiBpHAy1INHbz240RIBtoeP2F10YZIStGIHMozoTNceP5P1KZsqy07lzqcrAL5BcWYnuLjOuER8TSQZh6S3swrjBkdEORKar0ZypCejTotOFJ5mL6U9siMFHgPz2PcOYYteaFlD402Dj45zKggFS381yFc+ZsKqFTkgkkO4y9qAIf3qHeiwIDAQABAoIBAAujMlD0MJveG2L2ZOfmIqh3LhytO8D1Ri5pLclN/4JrL4xTA0M1MGANXbGaAmfLGLUQPjrB1vhCY28Vp0t0i533uPpOoBKrTjbnkXF13FG8Rk+V3TnDVa/p4nw4qklc062lP3RCFPjrethQeBtsnT7m1WA+w2k1xfNpfdHJUcXWmyLgSSq9BI+YccFdsojcu/Z5nnJsTGQC4zrXltAdLlbuQryrDRMDIwjIAvMryJqXxNLEXaF2fpgOMYLn4z1Qqt1L5XdyyKRw90us4OwMcoKI2kCNy6+VeWls/AMZxIVQ3qsdHvPFu07dyZUAkRsA8drL29udn67msILFkti8IfECgYEA9w+GhKZc0JuowkXYgD/cZL7LpovA9yvTpoyIJvL+jaD4AoAdHkGBvUclQVb+0SwyDOnGWS8KgWf6cSgNS/rTofl/t1n62RIXuP6x2pOTkcfb3rrcjhCku03UPxAn9DHwaxiKUIKuZ3tQnsJiJk8sZ9sn4LTHUz/lp0Qley4fqzkCgYEAqpOmAKO+vXjYBjCFx7q03LWcoUqcta4BCdio7B53YsJhceVWTQuKQAeyP2bzQ5zEVCO+dvtzZBwYubd1XQkbfluc98wGWgh852bUEGXohNa89+bXKm6hLCo7KEWOZ+HeLBrdrULT885rA9/MjwTJOFIIMYaW5Mp0VwOL4WYGY+MCgYA9iMVlVpY5cvumX4Ub9EvtX8QTWC2YonbVvZzPrqgOGHCNFuGpvoEU5pp12rge2xYgNz2qytegAYjUFDizmuaJKj5QKX4cALCd5/neSfFwVsoBgKMcUzLkX+8kQd7hqGqMEhGpGdNcfM9Iq3uMtR2HRIN7KSuYJYsKjSOUuZkzqQKBgDA8orQJmjXwh35wmFRb+yty/EEsXVaPR2HmcBadovs9ptrTlO0it0zWIFUHowFawrZciU8SWpZTz7YtKDZQDgNUmvYEL0OkpNW3YX7lorR2GWgtUmmAcXmQ07vWt6eu2uo0Cr4laNTIacDlt+4110VjBUgkHGXEztOQgC0UcfgrAoGBAJN7BVP3dChlWu1LMBSsTLKJL02XWQbx5izHbfzxDEHdgX5aflSc2NbGmY5cYGheESi1+O0fnuHLxb3uEiPREcChUDdT13/LyDwhdT8j2rWS7Gj9n3GJ4CxYAq9/JTfju4mc1QN1ScKsNe0ok2I1u1HbWpjo3r59LiLt35WFPXM+';
        $aop->alipayrsaPublicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAiGOMFf12noCbryB3HoK1H5f4g7qJW1gj5XIGe4xrt717WB91PzKGtePLOxrEC8J4c/zSJ7wij79JNGhUsY0kELzmktn0q47UMAojvgsWK405EHfa4r0OHYiiZD/eVDXakNuHT9Ym00nmzi5TefwGxWDHU6VT9X89bdkKfqKLG7Mn5raLg38/A4Pad96jlHGtdFfcICuak38uvjL10PXd069jsNOKUakxdAst0jBW9SiyYGYeOFCFVEgRuaHVWsSznSehWn12Aql2Mgt7q3XnGzH3R2K3TaCumOesrVSkAm0Pl+GFuB2RvSKi5uM4IKQUohXGTeIlOZwV4Iyar/eM5wIDAQAB';
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='utf-8';
        $aop->format='json';
        $param = [
            'subject' =>$subject,
            'out_trade_no' => $trade_no,
            'total_amount' => $amount,
            'product_code' => 'FAST_INSTANT_TRADE_PAY',
        ];
        switch ($type){
            case  1:
                $request = new \AlipayTradePagePayRequest ();
                $param['product_code'] = 'FAST_INSTANT_TRADE_PAY';
                break;
            case 2:
                $request = new \AlipayTradeWapPayRequest ();
                $param['product_code'] = 'QUICK_WAP_WAY';
                break;
            default:
                $request =  new \AlipayTradePagePayRequest ();
        }
        $request->setNotifyUrl('http://s.bkgaoshou.com/index.php/api/pay/notify_url');
        $request->setReturnUrl('http://s.bkgaoshou.com');
        $request->setBizContent(json_encode($param));
        $result = $aop->pageExecute ( $request);
        echo $result;
    }

    public function notify_url()
    {
        $info = $this->request->param();
        file_put_contents('../index.text',$info);
        include_once  '../extend/alipay/config.php';
        include_once  '../extend/alipay/pagepay/service/AlipayTradeService.php';
        $out_trade_no = $info['out_trade_no'];
        $param = [
            'order_status' => 1,
            'pay_time' => date('Y-m-d H:i:s')
        ];
        $order = Db::table('order')->where('order_no',$out_trade_no)->find();
        if ($order['goods_id'] == 2) {
            Db::table('user')->where('id',$order['user_id'])->update(['is_vip'=>1]);
        }
        Db::table('order')->where('order_no',$out_trade_no)->update($param);
        echo "success";	//请不要修改或删除
    }
}