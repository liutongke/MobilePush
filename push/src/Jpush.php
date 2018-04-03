<?php

/*
 * User: keke
 * Date: 2018/1/18
 * Time: 17:15
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

class Jpush
{
    // 推送的接口
    private $url = "https://api.jpush.cn/v3/push";

    private $data = array();

    // 若实例化的时候传入相应的值，则按新的相应值进行配置推送
    public function __construct($app_key = null, $master_secret = null, $url = null)
    {
        if ($app_key) $this->app_key = $app_key;
        if ($master_secret) $this->master_secret = $master_secret;
        if ($url) $this->url = $url;
    }

    //封装安卓和iOS的消息体结构
    //设置推送平台
    public function setPlatform($platform)
    {
        // 目标用户终端手机的平台类型android,ios
        $this->data['platform'] = $platform;

        return $this;
    }

    //发送给全部
    public function addAllAudience()
    {
        // 目标用户
        $this->data['audience'] = 'all';

        return $this;
    }

    //别名
    public function addAlias($alias)
    {
        // 目标用户
        $this->data['audience'] = [
            'alias' => [
                $alias
            ]
        ];

        return $this;
    }

    //iOS消息体
    public function iosNotification($msg)
    {
        $this->data['notification'] = [
            "ios" => $msg
        ];

        return $this;
    }

    //安卓消息体
    public function androidNotification($msg)
    {
        $this->data['notification'] = [
            "android" => $msg
        ];

        return $this;
    }

    //发送给全部
    public function allNotification($msg)
    {
        $this->data['notification'] = $msg;

        return $this;
    }

    //发送消息
    public function send()
    {
        $base64 = base64_encode("$this->app_key:$this->master_secret");

        $header = array("Authorization:Basic $base64", "Content-Type:application/json");

        // 附加选项
        $this->data['options'] = [
            "sendno" => time(),
            // 保存离线时间的秒数默认为一天
            "time_to_live" => '86400',
            // 指定 APNS 通知发送环境：false开发环境，true生产环境
            "apns_production" => true,
        ];
//        echo '<pre />';
//        var_dump($this->data);
//        echo '<pre />';
        $param = json_encode($this->data);
//        var_dump($param);
//        die;
        $res = $this->push_curl($param, $header);

        if ($res) {         // 得到返回值--成功已否后面判断
            return self::send_res($res);
        } else {            // 未得到返回值--返回失败
            return false;
        }
    }

    // 推送的Curl方法
    private function push_curl($param = "", $header = "")
    {
        if (empty($param)) {
            return false;
        }
        $postUrl = $this->url;
        $curlPost = $param;
//        var_dump($curlPost);
        // 初始化curl
        $ch = curl_init();
        // 抓取指定网页
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        // 设置header
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // 要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post提交方式
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        // 增加 HTTP Header（头）里的字段
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // 运行curl
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    // 供外部调用的推送方法 目标用户是否全部(别名之类的) 标题 主要内容体 角标 区分安卓和iOS
    public function send_res($result)
    {
        if ($result) {
            $res_arr = json_decode($result, true);
            // 如果返回了error则证明失败
            if (isset($res_arr['error'])) {
                // 错误信息
                echo $res_arr['error']['message'];
                // 错误码
                $error_code = $res_arr['error']['code'];
                switch ($error_code) {
                    case 200:
                        $message = '发送成功！';
                        break;
                    case 1000:
                        $message = '失败(系统内部错误)';
                        break;
                    case 1001:
                        $message = '失败(只支持 HTTP Post 方法，不支持 Get 方法)';
                        break;
                    case 1002:
                        $message = '失败(缺少了必须的参数)';
                        break;
                    case 1003:
                        $message = '失败(参数值不合法)';
                        break;
                    case 1004:
                        $message = '失败(验证失败)';
                        break;
                    case 1005:
                        $message = '失败(消息体太大)';
                        break;
                    case 1008:
                        $message = '失败(appkey参数非法)';
                        break;
                    case 1020:
                        $message = '失败(只支持 HTTPS 请求)';
                        break;
                    case 1030:
                        $message = '失败(内部服务超时)';
                        break;
                    default:
                        $message = '失败(返回其他状态，目前不清楚额，请联系开发人员！)';
                        break;
                }
            } else {
                $message = [
                    'msg' => "发送成功！",
                    'code' => '200'
                ];
            }
        } else {
            //接口调用失败或无响应
            $message = '接口调用失败或无响应';
        }

        return $message;
    }
}