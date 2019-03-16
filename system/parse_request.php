<?php

/**
 * Created by PhpStorm.
 * User: ayisun
 * Date: 2016/10/3
 * Time: 11:22
 **************************
 * Modified by FJX
 * Modified Date: 2017/7/5
 * Modified Time: 15:21
 */
class parse_request
{

    public function __construct()
    {
        require_once('return_code.php');
        require_once('application/controllers/qcloud/minaauth/Auth.php');
        require_once('application/controllers/qcloud/cosauth/Auth.php');
        require_once('application/controllers/qcloud/workauth/Auth.php');
    }

    /**
     * @param $request_json
     * @return array|int
     * 描述：解析接口名称
     */
    public function parse_json($request_json)
    {
        if ($this->is_json($request_json)) {
            $json_decode = json_decode($request_json, true);
            if (!isset($json_decode['interface']['interfaceName'])) {
                $ret['returnCode'] = return_code::MA_NO_INTERFACE;
                $ret['returnMessage'] = 'NO_INTERFACENAME_PARA';
                $ret['returnData'] = $json_decode;
            } elseif (!isset($json_decode['interface']['para'])) {
                $ret['returnCode'] = return_code::MA_NO_PARA;
                $ret['returnMessage'] = 'NO_PARA';
                $ret['returnData'] = '';
            } else {
                if ($json_decode['interface']['interfaceName'] == 'qcloud.cam.id_skey') {//去调用get_id_skey方法
                    if (isset($json_decode['interface']['para']['code'])&&isset($json_decode['interface']['para']['appid'])) {
                        $code = $json_decode['interface']['para']['code'];
                        $appid = $json_decode['interface']['para']['appid'];
                        $auth = new Auth();
                        $ret = $auth->get_id_skey($appid,$code);
                    } else {
                        $ret['returnCode'] = return_code::MA_PARA_ERR;
                        $ret['returnMessage'] = 'PARA_ERR';
                        $ret['returnData'] = '';
                    }
                } elseif ($json_decode['interface']['interfaceName'] == 'qcloud.cam.userinfo') {//去调用getUserInfo方法
                    if (isset($json_decode['interface']['para']['id']) && isset($json_decode['interface']['para']['skey'])&&isset($json_decode['interface']['para']['encrypt_data'])&&isset($json_decode['interface']['para']['iv'])) {
                        $id = $json_decode['interface']['para']['id'];
                        $skey = $json_decode['interface']['para']['skey'];
                        $encrypt_data = $json_decode['interface']['para']['encrypt_data'];
                        $iv = $json_decode['interface']['para']['iv'];
                        $auth = new Auth();
                        $ret = $auth->getuserinfo($id, $skey, $encrypt_data, $iv);                        
                    } else {
                        $ret['returnCode'] = return_code::MA_PARA_ERR;
                        $ret['returnMessage'] = 'PARA_ERR';
                        $ret['returnData'] = '';
                    }
                } elseif ($json_decode['interface']['interfaceName'] == 'qcloud.cam.auth') {//去调用auth方法
                    if (isset($json_decode['interface']['para']['id']) && isset($json_decode['interface']['para']['skey'])&&isset($json_decode['interface']['para']['appid'])) {
                        $id = $json_decode['interface']['para']['id'];
                        $skey = $json_decode['interface']['para']['skey'];
                        $appid = $json_decode['interface']['para']['appid'];
                        $auth = new Auth();
                        $ret = $auth->auth($appid, $id, $skey);
                    } else {
                        $ret['returnCode'] = return_code::MA_PARA_ERR;
                        $ret['returnMessage'] = 'PARA_ERR';
                        $ret['returnData'] = '';
                    }
                } elseif ($json_decode['interface']['interfaceName'] == 'qcloud.cam.decrypt') {//去调用decrypt方法
                    if (isset($json_decode['interface']['para']['id']) && isset($json_decode['interface']['para']['skey']) && isset($json_decode['interface']['para']['encrypt_data']) && isset($json_decode['interface']['para']['iv'])) {
                        $id = $json_decode['interface']['para']['id'];
                        $skey = $json_decode['interface']['para']['skey'];
                        $encrypt_data = $json_decode['interface']['para']['encrypt_data'];
                        $iv = $json_decode['interface']['para']['iv'];
                        $auth = new Auth();
                        $ret = $auth->decrypt($id, $skey, $encrypt_data, $iv);
                    } else {
                        $ret['returnCode'] = return_code::MA_PARA_ERR;
                        $ret['returnMessage'] = 'PARA_ERR';
                        $ret['returnData'] = '';
                    }
                } elseif ($json_decode['interface']['interfaceName'] == 'qcloud.cam.initdata') {//去调用init_data方法
                    if (isset($json_decode['interface']['para']['appid']) && isset($json_decode['interface']['para']['secret']) && isset($json_decode['interface']['para']['qcloud_appid']) && isset($json_decode['interface']['para']['ip'])
                    && isset($json_decode['interface']['para']['cdb_ip'])&& isset($json_decode['interface']['para']['cdb_port']) && isset($json_decode['interface']['para']['cdb_user_name'])&& isset($json_decode['interface']['para']['cdb_pass_wd']) ) {
                        $appid = $json_decode['interface']['para']['appid'];
                        $secret = $json_decode['interface']['para']['secret'];
                        $qcloud_appid = $json_decode['interface']['para']['qcloud_appid'];
                        $ip = $json_decode['interface']['para']['ip'];
                        $cdb_ip = $json_decode['interface']['para']['cdb_ip'];
                        $cdb_port = $json_decode['interface']['para']['cdb_port'];
                        $cdb_user_name = $json_decode['interface']['para']['cdb_user_name'];
                        $cdb_pass_wd = $json_decode['interface']['para']['cdb_pass_wd'];
                        $auth = new Auth();
                        $ret = $auth->init_data($appid, $secret, $qcloud_appid, $ip, $cdb_ip, $cdb_port, $cdb_user_name, $cdb_pass_wd);
                    } else {
                        $ret['returnCode'] = return_code::MA_PARA_ERR;
                        $ret['returnMessage'] = 'PARA_ERR';
                        $ret['returnData'] = '';
                    }
                } elseif($json_decode['interface']['interfaceName'] == 'qcloud.mimi.access_token'){//获取access_token方法
                    if(isset($json_decode['interface']['para']['appid'])){
                        $appid = $json_decode['interface']['para']['appid'];
                        $force = isset($json_decode['interface']['para']['force']) ? $json_decode['interface']['para']['force'] : false;
                        $auth = new Auth();
                        $ret = $auth->get_access_token($appid, $force);
                    }else {
                        $ret['returnCode'] = return_code::MA_PARA_ERR;
                        $ret['returnMessage'] = 'PARA_ERR';
                        $ret['returnData'] = 'PARA_NEED JiuBuGaoSuNi';
                    }
                } elseif($json_decode['interface']['interfaceName'] == 'qcloud.work.access_token'){//企业微信获取access_token方法
                    if(isset($json_decode['interface']['para']['agentid'])&&isset($json_decode['interface']['para']['corpid'])){
                        $corpid = $json_decode['interface']['para']['corpid'];
                        $agentid = $json_decode['interface']['para']['agentid'];
                        $force = isset($json_decode['interface']['para']['force']) ? $json_decode['interface']['para']['force'] : false;
                        $auth = new WorkAuth();
                        $ret = $auth->get_access_token($corpid, $agentid, $force);
                    }else {
                        $ret['returnCode'] = return_code::WA_PARA_ERR;
                        $ret['returnMessage'] = 'PARA_ERR';
                        $ret['returnData'] = 'PARA_NEED JiuBuGaoSuNi';
                    }
                } elseif($json_decode['interface']['interfaceName'] == 'qcloud.work.ticket'){//企业微信获取企业jsapi_ticket
                    if(isset($json_decode['interface']['para']['agentid'])&&isset($json_decode['interface']['para']['corpid'])){
                        $corpid = $json_decode['interface']['para']['corpid'];
                        $agentid = $json_decode['interface']['para']['agentid'];
                        $force = isset($json_decode['interface']['para']['force']) ? $json_decode['interface']['para']['force'] : false;
                        $auth = new WorkAuth();
                        $ret = $auth->get_ticket($corpid, $agentid, $force);
                    }else {
                        $ret['returnCode'] = return_code::WA_PARA_ERR;
                        $ret['returnMessage'] = 'PARA_ERR';
                        $ret['returnData'] = 'PARA_NEED JiuBuGaoSuNi';
                    }
                } elseif ($json_decode['interface']['interfaceName'] == 'qcloud.work.initagent') {//重新初始化agent信息
                    if (isset($json_decode['interface']['para']['corpid']) && isset($json_decode['interface']['para']['agentid']) && isset($json_decode['interface']['para']['agentsecret']) && isset($json_decode['interface']['para']['remark'])) {
                        $corpid = $json_decode['interface']['para']['corpid'];
                        $agentid = $json_decode['interface']['para']['agentid'];
                        $agentsecret = $json_decode['interface']['para']['agentsecret'];
                        $remark = $json_decode['interface']['para']['remark'];
                        $auth = new WorkAuth();
                        $ret = $auth->init_agent($corpid, $agentid, $agentsecret, $remark);
                    } else {
                        $ret['returnCode'] = return_code::WA_PARA_ERR;
                        $ret['returnMessage'] = 'PARA_ERR';
                        $ret['returnData'] = 'PARA_NEED JiuBuGaoSuNi';
                    }
                } elseif ($json_decode['interface']['interfaceName'] == 'qcloud.work.agent.ticket') {//企业微信获取agent的jsapi_ticket
                    if(isset($json_decode['interface']['para']['agentid'])&&isset($json_decode['interface']['para']['corpid'])){
                        $corpid = $json_decode['interface']['para']['corpid'];
                        $agentid = $json_decode['interface']['para']['agentid'];
                        $force = isset($json_decode['interface']['para']['force']) ? $json_decode['interface']['para']['force'] : false;
                        $auth = new WorkAuth();
                        $ret = $auth->get_agent_ticket($corpid, $agentid, $force);
                    }else {
                        $ret['returnCode'] = return_code::WA_PARA_ERR;
                        $ret['returnMessage'] = 'PARA_ERR';
                        $ret['returnData'] = 'PARA_NEED JiuBuGaoSuNi';
                    }
                }elseif($json_decode['interface']['interfaceName'] == 'qcloud.cos.auth.reusable'){
                    if(isset($json_decode['interface']['para']['appid'])&&isset($json_decode['interface']['para']['sid'])&&isset($json_decode['interface']['para']['skey'])&&isset($json_decode['interface']['para']['bucket'])&&isset($json_decode['interface']['para']['expiration'])){
                        $appid = $json_decode['interface']['para']['appid'];
                        $sid = $json_decode['interface']['para']['sid'];
                        $skey = $json_decode['interface']['para']['skey'];
                        $bucket = $json_decode['interface']['para']['bucket'];
                        $expiration = $json_decode['interface']['para']['expiration'];
                        if(isset($json_decode['interface']['para']['filepath'])){
                            $filepath = $json_decode['interface']['para']['filepath'];
                        }else{
                            $filepath = null;
                        }
                        $auth = new CosAuth($appid,$sid,$skey);
                        $ret = $auth->createReusableSignature($expiration,$bucket,$filepath);
                    }else{
                        $ret['returnCode'] = return_code::MA_PARA_ERR;
                        $ret['returnMessage'] = 'PARA_ERR';
                        $ret['returnData'] = 'PARA_NEED JiuBuGaoSuNi';
                    }
                }elseif($json_decode['interface']['interfaceName'] == 'qcloud.cos.auth.nonreusable'){
                    if(isset($json_decode['interface']['para']['appid'])&&isset($json_decode['interface']['para']['sid'])&&isset($json_decode['interface']['para']['skey'])&&isset($json_decode['interface']['para']['bucket'])){
                        $appid = $json_decode['interface']['para']['appid'];
                        $sid = $json_decode['interface']['para']['sid'];
                        $skey = $json_decode['interface']['para']['skey'];
                        $bucket = $json_decode['interface']['para']['bucket'];
                        if(isset($json_decode['interface']['para']['filepath'])){
                            $filepath = $json_decode['interface']['para']['filepath'];
                        }else{
                            $filepath = null;
                        }                        
                        $auth = new CosAuth($appid,$sid,$skey);
                        $ret = $auth->createNonreusableSignature($bucket,$filepath);
                    }else{
                        $ret['returnCode'] = return_code::MA_PARA_ERR;
                        $ret['returnMessage'] = 'PARA_ERR';
                        $ret['returnData'] = 'PARA_NEED JiuBuGaoSuNi';
                    }
                } else {
                    $ret['returnCode'] = return_code::MA_INTERFACE_ERR;
                    $ret['returnMessage'] = 'INTERFACENAME_PARA_ERR';
                    $ret['returnData'] = '';
                }
            }
        } else {
            $ret['returnCode'] = return_code::MA_REQUEST_ERR;
            $ret['returnMessage'] = 'REQUEST_IS_NOT_JSON';
            $ret['returnData'] = '';
        }
        $ret['version'] = 1;
        $ret['componentName'] = "MA";
        return $ret;
    }

    /**
     * @param $str
     * @return bool
     * 描述：判断字符串是不是合法的json
     */
    private function is_json($str)
    {
        json_decode($str);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
