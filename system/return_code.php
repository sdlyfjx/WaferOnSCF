<?php

class return_code
{
    const MA_OK 								= 0;       //成功返回码
    const MA_MYSQL_ERR                          = 1001;    // Mysql错误等
    const MA_NO_INTERFACE                       = 1002;    // 接口参数不存在
    const MA_PARA_ERR                           = 1003;    //参数错误
    const MA_DECRYPT_ERR                        = 60021;   //解密失败
    const MA_WEIXIN_NET_ERR                     = 1005;    //连接微信服务器失败
    const MA_WEIXIN_CODE_ERR                    = 40029;   //CODE无效
    const MA_CHANGE_SESSION_ERR                 = 1006;    //新增修改SESSION失败
    const MA_WEIXIN_RETURN_ERR                  = 1007;    //微信返回值错误
    const MA_AUTH_ERR                           = 60012;   //鉴权失败
    const MA_UPDATE_LASTVISITTIME_ERR           = 1008;    //更新最近访问时间失败
    const MA_REQUEST_ERR                        = 1009;    //请求包不是json
    const MA_INTERFACE_ERR                      = 1010;    //接口名称错误
    const MA_NO_PARA                            = 1011;    //不存在参数
    const MA_NO_APPID                           = 1012;    //不能获取AppID
    const MA_INIT_APPINFO_ERR                   = 1013;    //初始化AppID失败
    const MA_CHANGE_TOKEN_ERR                   = 1014;    //新增修改ACCESSTOKEN失败
    const MA_INVALID_APPID                      = 40013;   //获取accesstoken时appid无效

    const WA_OK 								= 0;       //成功返回码
    const WA_MYSQL_ERR                          = 1001;    // Mysql错误等
    const WA_NO_INTERFACE                       = 1002;    // 接口参数不存在
    const WA_PARA_ERR                           = 1003;    //参数错误
    const WA_DECRYPT_ERR                        = 60021;   //解密失败
    const WA_WEIXIN_NET_ERR                     = 1005;    //连接微信服务器失败
    const WA_WEIXIN_CODE_ERR                    = 40029;   //CODE无效
    const WA_CHANGE_SESSION_ERR                 = 1006;    //新增修改SESSION失败
    const WA_WEIXIN_RETURN_ERR                  = 1007;    //微信返回值错误
    const WA_AUTH_ERR                           = 60012;   //鉴权失败
    const WA_UPDATE_LASTVISITTIME_ERR           = 1008;    //更新最近访问时间失败
    const WA_REQUEST_ERR                        = 1009;    //请求包不是json
    const WA_INTERFACE_ERR                      = 1010;    //接口名称错误
    const WA_NO_PARA                            = 1011;    //不存在参数
    const WA_NO_CORPRECORD                      = 1012;    //数据库中无CORPID和agentid对应记录
    const WA_INIT_APPINFO_ERR                   = 1013;    //初始化AppID失败
    const WA_CHANGE_TOKEN_ERR                   = 1014;    //新增修改ACCESSTOKEN失败
    const WA_CHANGE_TICKET_ERR                  = 1015;    //新增修改TICKET失败
    const WA_CHANGE_AGENT_TICKET_ERR            = 1016;    //新增修改AGENT_TICKET失败
    const WA_INVALID_APPID                      = 40013;   //获取accesstoken时appid无效
    const WA_INVALID_ACCESSTOKEN                = 40014;   //获取jsapi_ticket时accesstoken无效
}
//end of script
