<?php

namespace Instcar\Server\Plugins;

class Push
{
    protected $client = null;
    
    public function __construct()
    {
        $ak = getDI()->get('config')->baidu_push->ak;
        $sk = getDI()->get('config')->baidu_push->sk;
        $this->client = new \BaiduPush($ak, $sk);
    }

    public function getClient()
    {
        return $this->client;
    }

    // 指定消息内容，单个消息为单独字符串。如果有二进制的消息内容，请先做 BASE64 的编码。
    // 当message_type为1 （通知类型），请按以下格式指定消息内容。
    // 通知消息格式及默认值：
    /*
        {
            // android必选，ios可选
            "title" : "hello" ,   
            "description": "hello world" 
            
            // android特有字段，可选
            "notification_builder_id": 0,
            "notification_basic_style": 7,
            "open_type":0,
            "net_support" : 1,
            "user_confirm": 0,
            "url": "http://developer.baidu.com",
            "pkg_content":"",
            "pkg_name" : "com.instcar.android",
            "pkg_version":"0.1",

            // android自定义字段
            "custom_content": {
                "key1":"value1", 
                "key2":"value2"
            },  

            // ios特有字段，可选
            "aps": {
                "alert":"Message From Baidu Push",
                "Sound":"",
                "Badge":0
            },

            // ios的自定义字段
            "key1":"value1", 
            "key2":"value2"
        }
        // 注意：
        // 当description与alert同时存在时，ios推送以alert内容作为通知内容
        // 当custom_content与 ios的自定义字段"key":"value"同时存在时，ios推送的自定义字段内容会将以上两个内容合并，但推送内容整体长度不能大于256B，否则有被截断的风险。
        // 此格式兼容Android和ios原生通知格式的推送。
    */
    public function message()
    {
        
    }

}