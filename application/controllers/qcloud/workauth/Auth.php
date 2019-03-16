<?php

/**
 * Created by SJXX.
 * User: fjx
 * Date: 2018/12/7
 * Time: 15:06
 */
class WorkAuth
{

    public function __construct()
    {
        require_once('application/services/qcloud/workauth/Cappinfo_Service.php');
        require_once('system/return_code.php');
        require_once('system/http_util.php');
    }

    /**
     * 重新初始化agent数据。若已经存在则重新添加
     * @param $corpid 企业微信的CorpID
     * @param $agentid 应用ID，若为通讯录应用，则对应的是公众号的APPID
     * @param $agentsecret 应用的SECRET
     * @param $remark 备注信息
     */
    public function init_agent($corpid, $agentid, $agentsecret, $remark)
    {
        $cappinfo_service = new Cworkappinfo_Service();
        $cappinfo_data = $cappinfo_service->select_cappinfo($corpid, $agentid);
        $params = array(
            "corpid"=>$corpid,
            "agentid"=>$agentid,
            "agentsecret"=>$agentsecret,
            "qcloud_appid"=>'1253686721',
            "ip"=>'10.0.1.15',
            "remark"=>$remark
        );
        if (empty($cappinfo_data)) {//若查询对应appid的记录为空     
            if ($cappinfo_service->insert_cappinfo($params)) {//若执行数据插入成功
                $ret['returnCode'] = return_code::WA_OK;
                $ret['returnMessage'] = 'INIT_APPINFO_SUCCESS';
                $ret['returnData'] = '';
            } else {//数据插入失败
                $ret['returnCode'] = return_code::WA_INIT_APPINFO_ERR;
                $ret['returnMessage'] = 'INIT_APPINFO_FAIL';
                $ret['returnData'] = 'insert_cappinfo Failed >>> '.$corpid.' >>>'.$agentid;
            }
        } elseif ($cappinfo_data != false) {//若select_cappinfo为真，即查询到对应数据 且 对应数据的记录非空
            $cappinfo_data = $cappinfo_service->delete_cappinfo($corpid, $agentid);
            if ($cappinfo_service->insert_cappinfo($params)) {//删除对应记录后重新插入数据
                $ret['returnCode'] = return_code::WA_OK;
                $ret['returnMessage'] = 'INIT_APPINFO_SUCCESS';
                $ret['returnData'] = '';
            } else {
                $ret['returnCode'] = return_code::WA_INIT_APPINFO_ERR;
                $ret['returnMessage'] = 'INIT_APPINFO_FAIL';
                $ret['returnData'] = '';
            }
        } else {
            $ret['returnCode'] = return_code::WA_MYSQL_ERR;
            $ret['returnMessage'] = 'MYSQL_ERR';
            $ret['returnData'] = '';
        }           
        return $ret;
    }

   /**
    *   @param $corpid
    *   @param $agentid
    *   @param $force = false
    *   描述：获取对应appid的access_token
    */
    public function get_access_token($corpid, $agentid, $force = false)
    {
        $cappinfo_service = new Cworkappinfo_Service();
        $cappinfo_data = $cappinfo_service->select_cappinfo($corpid, $agentid);
        if (($cappinfo_data == false) || empty($cappinfo_data)) {
            $ret['returnCode'] = return_code::WA_NO_CORPRECORD;
            $ret['returnMessage'] = 'NO_CORPRECORD_SET_ON_SERVER';
            $ret['returnData'] = '';
        } else {
            $corpid = $cappinfo_data['corpid'];
            $agentid = $cappinfo_data['agentid'];
            $agentsecret = $cappinfo_data['agentsecret'];
            $access_token = $cappinfo_data['access_token'];
            $expires_in = $cappinfo_data['expires_in'];
            $update_time = strtotime($cappinfo_data['update_time']);
            $now_time = time();
            if ($force == true || empty($access_token) || $update_time == false || ($now_time - $update_time) > $expires_in) {
                //access_token空 或 access_token超时，重新获取
                $ret = $this->fetch_access_token($corpid, $agentsecret);
            } elseif (($expires_in - ($now_time - $update_time)) / 60 < 10) {
                //access_token还有10分钟超时，返回现在的但刷新新token
                $arr_result['corpid'] = $corpid;
                $arr_result['access_token'] = $access_token;
                $arr_result["exp"] = $update_time + $expires_in;
                $ret['returnCode'] = return_code::WA_OK;
                $ret['returnMessage'] = 'OLD_TOKEN_SUCCESS_UPDATED';
                $ret['returnData'] = $arr_result;
                // 刷新现在的access_token
                $this->fetch_access_token($corpid, $agentsecret);
            } else {
                //access_token未超时
                $arr_result['corpid'] = $corpid;
                $arr_result['access_token'] = $access_token;
                $arr_result["exp"] = $update_time + $expires_in;
                $ret['returnCode'] = return_code::WA_OK;
                $ret['returnMessage'] = 'OLD_TOKEN_SUCCESS';
                $ret['returnData'] = $arr_result;
            }
        }
        return $ret;
    }

    public function fetch_access_token($corpid, $agentsecret)
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid='.$corpid.'&corpsecret='.$agentsecret;
        $http_util = new http_util();
        $return_message = $http_util->http_get($url); 
        if ($return_message!=false) {
            $json_message = json_decode($return_message, true);
            if (isset($json_message['access_token']) && isset($json_message['expires_in'])) {
                $access_token = $json_message['access_token'];
                $expires_in = $json_message['expires_in'];
                $now_time = time();
                $update_time = date('Y-m-d H:i:s', $now_time);
                $params = array(
                    "corpid" => $corpid,
                    "agentsecret" => $agentsecret,
                    "access_token" => $access_token,
                    "expires_in" => $expires_in,
                    "update_time" => $update_time
                );
                $cappinfo_service = new Cworkappinfo_Service();
                $change_result = $cappinfo_service->update_accesstoken($params);
                if ($change_result === true) {
                    $arr_result['corpid'] = $corpid;
                    $arr_result['access_token'] = $access_token;
                    $arr_result["exp"] = $now_time + $expires_in;
                    $ret['returnCode'] = return_code::WA_OK;
                    $ret['returnMessage'] = 'NEW_TOKEN_SUCCESS';
                    $ret['returnData'] = $arr_result;
                } elseif ($change_result === false) {
                    $ret['returnCode'] = return_code::WA_CHANGE_TOKEN_ERR;
                    $ret['returnMessage'] = 'CHANGE_TOKEN_ERR';
                    $ret['returnData'] = '';
                } else {
                    $arr_result['corpid'] = $corpid;
                    $arr_result['access_token'] = $access_token;
                    $arr_result["exp"] = $now_time + $expires_in;
                    $ret['returnCode'] = return_code::WA_OK;
                    $ret['returnMessage'] = 'UPDATE_TOKEN_SUCCESS';
                    $ret['returnData'] = $arr_result;
                }
            } elseif (isset($json_message['errcode']) && isset($json_message['errmsg'])) {
                $ret['returnCode'] = return_code::WA_INVALID_APPID;
                $ret['returnMessage'] = 'WEIXIN_CODE_ERR';
                $ret['returnData'] = $json_message['errmsg'];
            } else {
                $ret['returnCode'] = return_code::WA_WEIXIN_RETURN_ERR;
                $ret['returnMessage'] = 'WEIXIN_RETURN_ERR';
                $ret['returnData'] = '';
            }
        } else {
            $ret['returnCode'] = return_code::WA_WEIXIN_NET_ERR;
            $ret['returnMessage'] = 'WEIXIN_NET_ERR';
            $ret['returnData'] = '';
        }
        return $ret;
    }

    /**
    *   @param $corpid
    *   @param $agentid
    *   @param $force = false
    *   描述：获取对应appid的jsapi_ticket
    */
    public function get_ticket($corpid, $agentid, $force = false)
    {
        $cappinfo_service = new Cworkappinfo_Service();
        $cappinfo_data = $cappinfo_service->select_cappinfo($corpid, $agentid);
        if (($cappinfo_data == false) || empty($cappinfo_data)) {
            $ret['returnCode'] = return_code::WA_NO_CORPRECORD;
            $ret['returnMessage'] = 'NO_CORPRECORD_SET_ON_SERVER';
            $ret['returnData'] = '';
        } else {
            $corpid = $cappinfo_data['corpid'];
            $agentid = $cappinfo_data['agentid'];
            $agentsecret = $cappinfo_data['agentsecret'];
            $access_token = $cappinfo_data['access_token'];
            $update_time = strtotime($cappinfo_data['update_time']);
            $expires_in = $cappinfo_data['expires_in'];
            $ticket = $cappinfo_data['ticket'];
            $ticket_expires_in = $cappinfo_data['ticket_expires_in'];
            $ticket_update_time = strtotime($cappinfo_data['ticket_update_time']);
            $now_time = time();
            if ($force == true || empty($ticket) || $ticket_update_time == false || ($now_time - $ticket_update_time) > $ticket_expires_in) {
                //ticket空 或 ticket超时，重新获取
                $ret = $this->fetch_ticket($corpid, $agentsecret, $access_token, $update_time, $expires_in);
            } elseif (($ticket_expires_in - ($now_time - $ticket_update_time)) / 60 < 10) {
                //ticket还有10分钟超时，返回现在的但刷新新ticket
                $arr_result['corpid'] = $corpid;
                $arr_result['ticket'] = $ticket;
                $arr_result["exp"] = $ticket_update_time + $ticket_expires_in;
                $ret['returnCode'] = return_code::WA_OK;
                $ret['returnMessage'] = 'OLD_TICKET_SUCCESS_UPDATED';
                $ret['returnData'] = $arr_result;
                // 刷新现在的ticket
                $this->fetch_ticket($corpid, $agentsecret, $access_token, $update_time, $expires_in);
            } else {
                //ticket未超时
                $arr_result['corpid'] = $corpid;
                $arr_result['ticket'] = $ticket;
                $arr_result["exp"] = $ticket_update_time + $ticket_expires_in;
                $ret['returnCode'] = return_code::WA_OK;
                $ret['returnMessage'] = 'OLD_TICKET_SUCCESS';
                $ret['returnData'] = $arr_result;
            }
        }
        return $ret;
    }

    public function fetch_ticket($corpid, $agentsecret, $access_token, $update_time, $expires_in)
    {
        $now_time = time();
        if (empty($access_token) || $update_time == false || ($now_time - $update_time) > $expires_in){
            // 先处理access_token，保证access_token一定程度有效：若没有access_token或者access_token过期，则先获取access_token
            $token = $this->fetch_access_token($corpid, $agentsecret);
            if ($token['returnCode'] == return_code::WA_OK){
                // 获取到了正确的access_token
                $access_token = $token['returnData']['access_token'];
            }else{
                return $token;          
            }
        }
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token='.$access_token;
        $http_util = new http_util();
        $return_message = $http_util->http_get($url); 
        if ($return_message!=false) {
            $json_message = json_decode($return_message, true);
            if (isset($json_message['ticket']) && isset($json_message['expires_in'])) {
                $ticket = $json_message['ticket'];
                $ticket_expires_in = $json_message['expires_in'];
                $ticket_update_time = date('Y-m-d H:i:s', $now_time);
                $params = array(
                    "corpid" => $corpid,
                    "agentsecret" => $agentsecret,
                    "ticket" => $ticket,
                    "ticket_expires_in" => $ticket_expires_in,
                    "ticket_update_time" => $ticket_update_time
                );
                $cappinfo_service = new Cworkappinfo_Service();
                $change_result = $cappinfo_service->update_ticket($params);
                if ($change_result === true) {
                    $arr_result['corpid'] = $corpid;
                    $arr_result['ticket'] = $ticket;
                    $arr_result["exp"] = $now_time + $ticket_expires_in;
                    $ret['returnCode'] = return_code::WA_OK;
                    $ret['returnMessage'] = 'NEW_TICKET_SUCCESS';
                    $ret['returnData'] = $arr_result;
                } elseif ($change_result === false) {
                    $ret['returnCode'] = return_code::WA_CHANGE_TICKET_ERR;
                    $ret['returnMessage'] = 'CHANGE_TICKET_ERR';
                    $ret['returnData'] = '';
                } else {
                    $arr_result['corpid'] = $corpid;
                    $arr_result['ticket'] = $ticket;
                    $arr_result["exp"] = $now_time + $ticket_expires_in;
                    $ret['returnCode'] = return_code::WA_OK;
                    $ret['returnMessage'] = 'UPDATE_TICKET_SUCCESS';
                    $ret['returnData'] = $arr_result;
                }
            } elseif (isset($json_message['errcode']) && isset($json_message['errmsg'])) {
                $ret['returnCode'] = return_code::WA_INVALID_ACCESSTOKEN;
                $ret['returnMessage'] = 'WEIXIN_ACCESSTOKEN_ERR';
                $ret['returnData'] = $json_message['errmsg'];
            } else {
                $ret['returnCode'] = return_code::WA_WEIXIN_RETURN_ERR;
                $ret['returnMessage'] = 'WEIXIN_RETURN_ERR';
                $ret['returnData'] = '';
            }
        } else {
            $ret['returnCode'] = return_code::WA_WEIXIN_NET_ERR;
            $ret['returnMessage'] = 'WEIXIN_NET_ERR';
            $ret['returnData'] = '';
        }
        return $ret;
    }

    
    /**
    *   @param $corpid
    *   @param $agentid
    *   @param $force = false
    *   描述：获取对应appid的jsapi_ticket
    */
    public function get_agent_ticket($corpid, $agentid, $force = false)
    {
        $cappinfo_service = new Cworkappinfo_Service();
        $cappinfo_data = $cappinfo_service->select_cappinfo($corpid, $agentid);
        if (($cappinfo_data == false) || empty($cappinfo_data)) {
            $ret['returnCode'] = return_code::WA_NO_CORPRECORD;
            $ret['returnMessage'] = 'NO_CORPRECORD_SET_ON_SERVER';
            $ret['returnData'] = '';
        } else {
            $corpid = $cappinfo_data['corpid'];
            $agentid = $cappinfo_data['agentid'];
            $agentsecret = $cappinfo_data['agentsecret'];
            $access_token = $cappinfo_data['access_token'];
            $update_time = strtotime($cappinfo_data['update_time']);
            $expires_in = $cappinfo_data['expires_in'];
            $agent_ticket = $cappinfo_data['agent_ticket'];
            $agent_expires_in = $cappinfo_data['ticket_expires_in'];
            $agent_update_time = strtotime($cappinfo_data['agent_update_time']);
            $now_time = time();
            if ($force == true || empty($agent_ticket) || $agent_update_time == false || ($now_time - $agent_update_time) > $agent_expires_in) {
                //ticket空 或 ticket超时，重新获取
                $ret = $this->fetch_agent_ticket($corpid, $agentsecret, $access_token, $update_time, $expires_in);
            } elseif (($agent_expires_in - ($now_time - $agent_update_time)) / 60 < 10) {
                //ticket还有10分钟超时，返回现在的但刷新新ticket
                $arr_result['corpid'] = $corpid;
                $arr_result['agent_ticket'] = $agent_ticket;
                $arr_result["exp"] = $agent_update_time + $agent_expires_in;
                $ret['returnCode'] = return_code::WA_OK;
                $ret['returnMessage'] = 'OLD_AGENT_TICKET_SUCCESS_UPDATED';
                $ret['returnData'] = $arr_result;
                // 刷新现在的ticket
                $ret = $this->fetch_agent_ticket($corpid, $agentsecret, $access_token, $update_time, $expires_in);
            } else {
                //ticket未超时
                $arr_result['corpid'] = $corpid;
                $arr_result['agent_ticket'] = $agent_ticket;
                $arr_result["exp"] = $agent_update_time + $agent_expires_in;
                $ret['returnCode'] = return_code::WA_OK;
                $ret['returnMessage'] = 'OLD_AGENT_TICKET_SUCCESS';
                $ret['returnData'] = $arr_result;
            }
        }
        return $ret;
    }

    public function fetch_agent_ticket($corpid, $agentsecret, $access_token, $update_time, $expires_in)
    {
        $now_time = time();
        if (empty($access_token) || $update_time == false || ($now_time - $update_time) > $expires_in){
            // 先处理access_token，保证access_token一定程度有效：若没有access_token或者access_token过期，则先获取access_token
            $token = $this->fetch_access_token($corpid, $agentsecret);
            if ($token['returnCode'] == return_code::WA_OK){
                // 获取到了正确的access_token
                $access_token = $token['returnData']['access_token'];
            }else{
                return $token;          
            }
        }
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/ticket/get?type=agent_config&access_token='.$access_token;
        $http_util = new http_util();
        $return_message = $http_util->http_get($url); 
        if ($return_message!=false) {
            $json_message = json_decode($return_message, true);
            if (isset($json_message['ticket']) && isset($json_message['expires_in'])) {
                $agent_ticket = $json_message['ticket'];
                $agent_expires_in = $json_message['expires_in'];
                $agent_update_time = date('Y-m-d H:i:s', $now_time);
                $params = array(
                    "corpid" => $corpid,
                    "agentsecret" => $agentsecret,
                    "agent_ticket" => $agent_ticket,
                    "agent_expires_in" => $agent_expires_in,
                    "agent_update_time" => $agent_update_time
                );
                $cappinfo_service = new Cworkappinfo_Service();
                $change_result = $cappinfo_service->update_agent_ticket($params);
                if ($change_result === true) {
                    $arr_result['corpid'] = $corpid;
                    $arr_result['agent_ticket'] = $agent_ticket;
                    $arr_result["exp"] = $now_time + $agent_expires_in;
                    $ret['returnCode'] = return_code::WA_OK;
                    $ret['returnMessage'] = 'NEW_AGENT_TICKET_SUCCESS';
                    $ret['returnData'] = $arr_result;
                } elseif ($change_result === false) {
                    $ret['returnCode'] = return_code::WA_CHANGE_AGENT_TICKET_ERR;
                    $ret['returnMessage'] = 'CHANGE_AGENT_TICKET_ERR';
                    $ret['returnData'] = '';
                } else {
                    $arr_result['corpid'] = $corpid;
                    $arr_result['agent_ticket'] = $agent_ticket;
                    $arr_result["exp"] = $now_time + $agent_expires_in;
                    $ret['returnCode'] = return_code::WA_OK;
                    $ret['returnMessage'] = 'UPDATE_AGENT_TICKET_SUCCESS';
                    $ret['returnData'] = $arr_result;
                }
            } elseif (isset($json_message['errcode']) && isset($json_message['errmsg'])) {
                $ret['returnCode'] = return_code::WA_INVALID_ACCESSTOKEN;
                $ret['returnMessage'] = 'WEIXIN_ACCESSTOKEN_ERR';
                $ret['returnData'] = $json_message['errmsg'];
            } else {
                $ret['returnCode'] = return_code::WA_WEIXIN_RETURN_ERR;
                $ret['returnMessage'] = 'WEIXIN_RETURN_ERR';
                $ret['returnData'] = '';
            }
        } else {
            $ret['returnCode'] = return_code::WA_WEIXIN_NET_ERR;
            $ret['returnMessage'] = 'WEIXIN_NET_ERR';
            $ret['returnData'] = '';
        }
        return $ret;
    }

}
