<?php
/*
 * User: keke
 * Date: 2018/3/20
 * Time: 9:38
 *——————————————————佛祖保佑 ——————————————————
 *                   _ooOoo_
 *                  o8888888o
 *                  88" . "88
 *                  (| -_- |)
 *                  O\  =  /O
 *               ____/`---'\____
 *             .'  \|     |//  `.
 *            /  \|||  :  |||//  \
 *           /  _||||| -:- |||||-  \
 *           |   | \\  -  /// |   |
 *           | \_|  ''\---/''  |   |
 *           \  .-\__  `-`  ___/-. /
 *         ___`. .'  /--.--\  `. . __
 *      ."" '<  `.___\_<|>_/___.'  >'"".
 *     | | :  ` - `.;`\ _ /`;.`/ - ` : | |
 *     \  \ `-.   \_ __\ /__ _/   .-` /  /
 *======`-.____`-.___\_____/___.-`____.-'======
 *                   `=---='
 *——————————————————代码永无BUG —————————————————
 */

namespace huawei\push;

use Illuminate\Support\Facades\Redis;

class Push
{
    private $data = [];

    //获取token
    public function GetHuaweiToken()
    {
        //引入文件
        $ht = new Http(config('config.HUAWEI_PUSH_CLIENT_SECRET'), config('config.HUAWEI_PUSH_CLIENT_ID'));
        return $ht->GetToken();
    }

    //
    public function setMsg($type, $body)
    {
//        取值含义和说明：
//
//        1 透传异步消息
//
//        3 系统通知栏异步消息
        $this->data['hps']['msg'] = [
            'type' => (int)$type,
            'body' => $body
        ];

        return $this;
    }

    public function setAction($type, $param)
    {
//        1 自定义行为：行为由参数intent定义
//
//        2 打开URL：URL地址由参数url定义
//
//        3 打开APP：默认值，打开App的首页
//
//        注意：富媒体消息开放API不支持

        if ($type == '1') {
            $this->data['hps']['action'] = [
                'type' => (int)$type,
                'param' => [
                    'intent' => $param
                ]
            ];
        } elseif ($type == '2') {
            $this->data['hps']['action'] = [
                'type' => (int)$type,
                'param' => [
                    'url' => $param
                ]
            ];
        } else {
            $this->data['hps']['action'] = [
                'type' => (int)$type,
                'param' => [
                    'appPkgName' => $param
                ]
            ];
        }

        return $this;
    }

    //设置扩展信息
    public function setExt($Trump, $ext)
    {
        if (empty($ext)) {
            $this->data['hps']['ext'] = [
                'biTag' => $Trump
            ];
        } else {
            $this->data['hps']['ext'] = [
                'biTag' => $Trump,
                'customize' => [$ext]
            ];
        }

        return $this;
    }

    //进行推送请求
    public function send_huawei_push($device_token)
    {
//        echo '<pre />';
//        var_dump($this->data);
//        echo '<pre />';
//        die;
        $device_token_list = json_encode([
            $device_token
        ]);

//        $payload = json_encode([
//            'hps' => [
//                'msg' => [
//                    'type' => (int)1,
//                    'body' => [
//                        'content' => '123',
//                        'title' => '456'
//                    ],
//                ],
//                'action' => [
//                    'type' => (int)1,
//                    'param' =>
//                        'intent":"#Intent;compo=com.rvr/.Activity;S.W=U;end'
//                ]
//            ]
//        ]);
//        $payload = '{"hps":{"msg":{"type":3,"body":{"content":"123"}}}}';
//        dd($payload);
        //token值
//        $huawei_token = Redis::get('huawei_push_token');
        $huawei_token = 'CFrCeOg23DEcWf94Un9yJFBdLqJo8e+gnAl9iOlsfg9tvH4cWUZglbPiIvpmzfjliLDOueyBQAC0nb7aX+g6XA==';
        $payload = json_encode($this->data);
        echo $payload;
        echo '<br />';
        //token需要urlencode编码
        if ($huawei_token) {
            self::huawei_curl(urlencode($huawei_token), $device_token_list, $payload);
        } else {
//            $huawei_token = $this->GetHuaweiToken();
            self::huawei_curl(urlencode($this->GetHuaweiToken()), $device_token_list, $payload);
        }
    }

    //curl请求
    public function huawei_curl($token, $device_token_list, $payload)
    {
        //其中nsp_ctx为url-encoding编码，解码后为： nsp_ctx={"ver":"1", "appId":"10923253325"}
//        ver：用来解决大版本升级的兼容问题;
//        appId：用户在联盟申请的APPID;
        $nsp_ctx = json_encode([
            'ver' => '1',
//            'appId' => config('config.HUAWEI_PUSH_CLIENT_ID')
            'appId' => '100228903'
        ]);
//        echo 'https://api.push.hicloud.com/pushsend.do?nsp_ctx=' . urlencode($nsp_ctx);
//        echo '<br />';
//        echo 'access_token=' . $token . '&nsp_svc=openpush.message.api.send&nsp_ts=' . time() . '&device_token_list=' . $device_token_list . '&payload=' . $payload;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.push.hicloud.com/pushsend.do?nsp_ctx=' . urlencode($nsp_ctx),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => 'access_token=' . $token . '&nsp_svc=openpush.message.api.send&nsp_ts=' . time() . '&device_token_list=' . $device_token_list . '&payload=' . $payload,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }
    }
}