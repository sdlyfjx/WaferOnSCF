<?php

/**
 * Created by PhpStorm.
 * User: ayisun
 * Date: 2016/10/1
 * Time: 15:06
 **************************
 * Modified by FJX
 * Modified Date: 2017/7/5
 * Modified Time: 15:21
 */
class Auth
{

    public function __construct()
    {
        require_once('application/services/qcloud/minaauth/Cappinfo_Service.php');
        require_once('application/services/qcloud/minaauth/Csessioninfo_Service.php');
        require_once('system/wx_decrypt_data/old/decrypt_data.php');
        require_once('system/wx_decrypt_data/new/wxBizDataCrypt.php');
        require_once('system/return_code.php');
        require_once('system/http_util.php');
        require_once('system/db/init_db.php');
    }

    /**
     * @param $code
     * @param $appid
     * @param $secret
     * @return array|int
     * 描述：登录校验，返回id,skey和appid
     */
    public function get_id_skey($appid, $code)
    {
        $cappinfo_service = new Cappinfo_Service();
        $cappinfo_data = $cappinfo_service->select_cappinfo($appid);
        if (empty($cappinfo_data) || ($cappinfo_data == false)) {
            $ret['returnCode'] = return_code::MA_NO_APPID;
            $ret['returnMessage'] = 'NO_APPID_SET_ON_SERVER';
            $ret['returnData'] = '';
        } else {
            $appid = $cappinfo_data['appid'];
            $secret = $cappinfo_data['secret'];
            $ip = $cappinfo_data['ip'];
            $qcloud_appid = $cappinfo_data['qcloud_appid'];
            $login_duration = $cappinfo_data['login_duration'];
            $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $appid . '&secret=' . $secret . '&js_code=' . $code . '&grant_type=authorization_code';
            $http_util = new http_util();
            $return_message = $http_util->http_get($url);
            if ($return_message!=false) {
                $json_message = json_decode($return_message, true);
                if (isset($json_message['openid']) && isset($json_message['session_key'])) {
                    $uuid = md5((time()-mt_rand(1, 10000)) . mt_rand(1, 1000000));//生成UUID
                    $skey = md5(time() . mt_rand(1, 1000000));//生成skey
                    $create_time = date('Y-m-d H:i:s', time());
                    $last_visit_time = date('Y-m-d H:i:s', time());
                    $openid = $json_message['openid'];
                    $session_key = $json_message['session_key'];
                    $unionid = "";
                    if(isset($json_message['unionid'])) $unionid = $json_message['unionid'];
                    $params = array(
                        "uuid" => $uuid,
                        "skey" => $skey,
                        "create_time" => $create_time,
                        "last_visit_time" => $last_visit_time,
                        "open_id" => $openid,
                        "session_key" => $session_key,
                        "login_duration" => $login_duration,
                        "appid" => $appid,
                        "union_id" => $unionid
                    );
                    $csessioninfo_service = new Csessioninfo_Service();
                    $change_result = $csessioninfo_service->change_csessioninfo($params);
                    if ($change_result === true) {
                        $id = $csessioninfo_service->get_id_csessioninfo($openid);
                        $arr_result['id'] = $id;
                        $arr_result['skey'] = $skey;
                        $arr_result['appid'] = $appid;//加入返回appid
                        $arr_result['unionid'] = $unionid;
                        $ret['returnCode'] = return_code::MA_OK;
                        $ret['returnMessage'] = 'NEW_SESSION_SUCCESS';
                        $ret['returnData'] = $arr_result;
                    } elseif ($change_result === false) {
                        $ret['returnCode'] = return_code::MA_CHANGE_SESSION_ERR;
                        $ret['returnMessage'] = 'CHANGE_SESSION_ERR';
                        $ret['returnData'] = '';
                    } else {
                        $arr_result['id'] = $change_result;
                        $arr_result['skey'] = $skey;
                        $arr_result['appid'] = $appid;//加入返回appid
                        $arr_result['unionid'] = $unionid;
                        $ret['returnCode'] = return_code::MA_OK;
                        $ret['returnMessage'] = 'UPDATE_SESSION_SUCCESS';
                        $ret['returnData'] = $arr_result;
                    }
                } elseif (isset($json_message['errcode']) && isset($json_message['errmsg'])) {
                    $ret['returnCode'] = return_code::MA_WEIXIN_CODE_ERR;
                    $ret['returnMessage'] = 'WEIXIN_CODE_ERR';
                    $ret['returnData'] = '';
                } else {
                    $ret['returnCode'] = return_code::MA_WEIXIN_RETURN_ERR;
                    $ret['returnMessage'] = 'WEIXIN_RETURN_ERR';
                    $ret['returnData'] = '';
                }
            } else {
                $ret['returnCode'] = return_code::MA_WEIXIN_NET_ERR;
                $ret['returnMessage'] = 'WEIXIN_NET_ERR';
                $ret['returnData'] = '';
            }

        }
        return $ret;
    }

    /**
     * @param $id
     * @param $skey
     * @param $encrypt_data
     * @param $iv
     * @return array|int
     * 描述：登录校验，返回id,skey,userinfo和appid
     */
    public function getuserinfo($id, $skey, $encrypt_data, $iv)
    {

        $csessioninfo_service = new Csessioninfo_Service();
        $params = array(
            "uuid" => $id,
            "skey" => $skey
        );
        $result = $csessioninfo_service->select_csessioninfo($params);
        if ($result !== false && count($result) != 0 && isset($result['session_key']) && isset($result['appid'])) {
            $session_key = $result['session_key'];
            $appid = $result['appid'];
            $openid = $result['open_id'];
            $pc = new WXBizDataCrypt($appid, $session_key);
            $errCode = $pc->decryptData($encrypt_data, $iv, $user_info);
            $unionid = json_decode($user_info, true);
            if (isset($unionid['unionId'])) {
                $unionid = $unionid['unionId'];
            } else {
                $unionid = "";
            };
            $user_info = base64_encode($user_info);
            if ($user_info === false || $errCode !== 0) {
                $ret['returnCode'] = return_code::MA_DECRYPT_ERR;
                $ret['returnMessage'] = 'USERINFO_DECRYPT_FAIL';
                $ret['returnData'] = '';
            } else {
                $params = array(
                    "openid" => $openid,
                    "user_info" => $user_info,
                    "unionid" => $unionid,
                );
                $csessioninfo_service = new Csessioninfo_Service();
                $change_result = $csessioninfo_service->update_cuserinfo($params);
                if ($change_result === true) {
                    $id = $csessioninfo_service->get_id_csessioninfo($openid);
                    $arr_result['id'] = $id;
                    $arr_result['skey'] = $skey;
                    $arr_result['appid'] = $appid;//加入返回appid
                    $arr_result['user_info'] = json_decode(base64_decode($user_info));
                    $ret['returnCode'] = return_code::MA_OK;
                    $ret['returnMessage'] = 'UPDATE_USERINFO_SUCCESS';
                    $ret['returnData'] = $arr_result;
                } elseif ($change_result === false) {
                    $ret['returnCode'] = return_code::MA_CHANGE_SESSION_ERR;
                    $ret['returnMessage'] = 'UPDATE_USERINFO_FAIL';
                    $ret['returnData'] = '';
                } else {
                    $arr_result['id'] = $change_result;
                    $arr_result['skey'] = $skey;
                    $arr_result['appid'] = $appid;//加入返回appid
                    $arr_result['user_info'] = json_decode(base64_decode($user_info));
                    $ret['returnCode'] = return_code::MA_OK;
                    $ret['returnMessage'] = 'UPDATE_SESSION_SUCCESS';
                    $ret['returnData'] = $arr_result;
                }
            }
        } else {
            $ret['returnCode'] = return_code::MA_DECRYPT_ERR;
            $ret['returnMessage'] = 'GET_SESSION_KEY_OR_APPID_FAIL';
            $ret['returnData'] = '';
        }
        return $ret;
    }

    /**
     * @param $id
     * @param $skey
     * @return bool
     * 描述：登录态验证
     */
    public function auth($appid, $id, $skey)
    {
        //根据Id和skey 在cSessionInfo中进行鉴权，返回鉴权失败和密钥过期
        $cappinfo_service = new Cappinfo_Service();
        $cappinfo_data = $cappinfo_service->select_cappinfo($appid);
        if (empty($cappinfo_data) || ($cappinfo_data == false)) {
            $ret['returnCode'] = return_code::MA_NO_APPID;
            $ret['returnMessage'] = 'NO_APPID';
            $ret['returnData'] = '';
        } else {
            $login_duration = $cappinfo_data['login_duration'];
            $session_duration = $cappinfo_data['session_duration'];
            $ip = $cappinfo_data['ip'];
            $qcloud_appid = $cappinfo_data['qcloud_appid'];

            $params = array(
            "uuid" => $id,
            "skey" => $skey,
            "login_duration" => $login_duration,
            "session_duration" => $session_duration
            );

            $csessioninfo_service = new Csessioninfo_Service();
            $auth_result = $csessioninfo_service->check_session_for_auth($params);
            if ($auth_result!==false) {
                $arr_result['user_info'] = json_decode(base64_decode($auth_result));
                $ret['returnCode'] = return_code::MA_OK;
                $ret['returnMessage'] = 'AUTH_SUCCESS';
                $ret['returnData'] = $arr_result;
            } else {
                $ret['returnCode'] = return_code::MA_AUTH_ERR;
                $ret['returnMessage'] = 'AUTH_FAIL';
                $ret['returnData'] = '';
            }

        }
        return $ret;
    }

    /**
     * @param $id
     * @param $skey
     * @param $encrypt_data
     * @return bool|string
     * 描述：解密数据
     */
    public function decrypt($id, $skey, $encrypt_data, $iv)
    {
        //1、根据id和skey获取session_key。
        //2、session_key获取成功则正常解密,可能解密失败。
        //3、获取不成功则解密失败。
        $csessioninfo_service = new Csessioninfo_Service();
        $params = array(
        "uuid" => $id,
        "skey" => $skey
        );
        $result = $csessioninfo_service->select_csessioninfo($params);
        if ($result !== false && count($result) != 0 && isset($result['session_key']) && isset($result['appid'])) {
            $session_key = $result['session_key'];
            $appid = $result['appid'];
            $pc = new WXBizDataCrypt($appid, $session_key);
            $errCode = $pc->decryptData($encrypt_data, $iv, $data);
            $data = json_decode($data);
            if ($data === false || $errCode !== 0) {
                $ret['returnCode'] = return_code::MA_DECRYPT_ERR;
                $ret['returnMessage'] = 'GET_SESSION_KEY_SUCCESS_BUT_DECRYPT_FAIL';
                $ret['returnData'] = 'errorCode='.$errCode;
            } else {
                $ret['returnCode'] = return_code::MA_OK;
                $ret['returnMessage'] = 'DECRYPT_SUCCESS';
                $ret['returnData'] = $data;
            }
        } else {
            $ret['returnCode'] = return_code::MA_DECRYPT_ERR;
            $ret['returnMessage'] = 'GET_SESSION_KEY_FAIL';
            $ret['returnData'] = '';
        }
        return $ret;
    }

    public function init_data($appid, $secret, $qcloud_appid, $ip, $cdb_ip, $cdb_port, $cdb_user_name, $cdb_pass_wd)
    {
        //初始化数据库配置
        $init_db = new init_db();
        $params_db = array(
        "cdb_ip"=>$cdb_ip,
        "cdb_port"=>$cdb_port,
        "cdb_user_name" => $cdb_user_name,
        "cdb_pass_wd" => $cdb_pass_wd
        );
        //若调用数据库配置初始化成功
        if ($init_db->init_db_config($params_db)) {
            //若数据库中已经有表
            if ($init_db->init_db_table()) {
                $cappinfo_service = new Cappinfo_Service();
                $cappinfo_data = $cappinfo_service->select_cappinfo($appid);
                $params = array(
                "appid"=>$appid,
                "secret"=>$secret,
                "qcloud_appid"=>$qcloud_appid,
                "ip"=>$ip
                );

                if (empty($cappinfo_data)) {//若查询对应appid的记录为空
                    if ($cappinfo_service->insert_cappinfo($params)) {//若执行数据插入成功
                        $ret['returnCode'] = return_code::MA_OK;
                        $ret['returnMessage'] = 'INIT_APPINFO_SUCCESS';
                        $ret['returnData'] = '';
                    } else {//数据插入失败
                        $ret['returnCode'] = return_code::MA_INIT_APPINFO_ERR;
                        $ret['returnMessage'] = 'INIT_APPINFO_FAIL';
                        $ret['returnData'] = 'insert_cappinfo Failed >>> '.$appid;
                    }
                } elseif ($cappinfo_data != false) {//若select_cappinfo为真，即查询到对应数据 且 对应数据的记录非空
                        $cappinfo_service->delete_cappinfo($appid);
                    if ($cappinfo_service->insert_cappinfo($params)) {//删除对应记录后重新插入数据
                        $ret['returnCode'] = return_code::MA_OK;
                        $ret['returnMessage'] = 'INIT_APPINFO_SUCCESS';
                        $ret['returnData'] = '';
                    } else {
                        $ret['returnCode'] = return_code::MA_INIT_APPINFO_ERR;
                        $ret['returnMessage'] = 'INIT_APPINFO_FAIL';
                        $ret['returnData'] = '';
                    }
                } else {
                        $ret['returnCode'] = return_code::MA_MYSQL_ERR;
                        $ret['returnMessage'] = 'MYSQL_ERR';
                        $ret['returnData'] = '';
                }
            } else {
                $ret['returnCode'] = return_code::MA_INIT_APPINFO_ERR;
                $ret['returnMessage'] = 'INIT_APPINFO_FAIL';
                $ret['returnData'] = '';
            }
        } else {
            $ret['returnCode'] = return_code::MA_INIT_APPINFO_ERR;
            $ret['returnMessage'] = 'INIT_APPINFO_FAIL';
            $ret['returnData'] = '';
        }
            //为用户生成非root账户，只有增删改查的权限
        if ($ret['returnCode'] === return_code::MA_OK) {
            $user_pass_wd = $init_db->create_user_for_db();
            if ($user_pass_wd !== false) {
                $params_db['cdb_user_name'] = "session_user";
                $params_db['cdb_pass_wd'] = $user_pass_wd;
                if ($init_db->init_db_config($params_db)) {
                    $ret['returnCode'] = return_code::MA_OK;
                    $ret['returnMessage'] = 'INIT_APPINFO_SUCCESS';
                    $ret['returnData'] = '';
                } else {
                    $ret['returnCode'] = return_code::MA_INIT_APPINFO_ERR;
                    $ret['returnMessage'] = 'INIT_CDBINI_FAIL';
                    $ret['returnData'] = '';
                }
            } else {
                $ret['returnCode'] = return_code::MA_INIT_APPINFO_ERR;
                $ret['returnMessage'] = 'INIT_CDBPASSWD_FAIL';
                $ret['returnData'] = '';
            }
        }
            return $ret;
    }

   /**
    *   @param $appid
    *   @param $force = false 强制刷新
    *   描述：获取对应appid的access_token
    */
    public function get_access_token($appid, $force = false)
    {
        $cappinfo_service = new Cappinfo_Service();
        $cappinfo_data = $cappinfo_service->select_cappinfo($appid);
        if (($cappinfo_data == false) || empty($cappinfo_data)) {
            $ret['returnCode'] = return_code::MA_NO_APPID;
            $ret['returnMessage'] = 'NO_APPID_SET_ON_SERVER';
            $ret['returnData'] = '';
        } else {
            $appid = $cappinfo_data['appid'];
            $secret = $cappinfo_data['secret'];
            $access_token = $cappinfo_data['access_token'];
            $expires_in = $cappinfo_data['expires_in'];
            $update_time = strtotime($cappinfo_data['update_time']);
            $now_time = time();
            if ($force == true || empty($access_token) || $update_time == false || ($now_time - $update_time) > $expires_in) {
                //access_token空 或 access_token超时，重新获取
                $ret = $this->fetch_access_token($appid, $secret);
            } elseif (($expires_in - ($now_time - $update_time)) / 60 < 10) {
                //access_token还有10分钟超时，返回现在的但刷新新token
                $arr_result['appid'] = $appid;
                $arr_result['access_token'] = $access_token;
                $arr_result["exp"] = $update_time + $expires_in;
                $ret['returnCode'] = return_code::MA_OK;
                $ret['returnMessage'] = 'OLD_TOKEN_SUCCESS_UPDATED';
                $ret['returnData'] = $arr_result;
                // 刷新现在的access_token
                $this->fetch_access_token($appid, $secret);
            } else {
                //access_token未超时
                $arr_result['appid'] = $appid;
                $arr_result['access_token'] = $access_token;
                $arr_result["exp"] = $update_time + $expires_in;
                $ret['returnCode'] = return_code::MA_OK;
                $ret['returnMessage'] = 'OLD_TOKEN_SUCCESS';
                $ret['returnData'] = $arr_result;
            }
        }
        return $ret;
    }

    public function fetch_access_token($appid, $secret)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
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
                    "appid" => $appid,
                    "secret" => $secret,
                    "access_token" => $access_token,
                    "expires_in" => $expires_in,
                    "update_time" => $update_time
                );
                $cappinfo_service = new Cappinfo_Service();
                $change_result = $cappinfo_service->update_accesstoken($params);
                if ($change_result === true) {
                    $arr_result['appid'] = $appid;
                    $arr_result['access_token'] = $access_token;
                    $arr_result["exp"] = $now_time + $expires_in;
                    $ret['returnCode'] = return_code::MA_OK;
                    $ret['returnMessage'] = 'NEW_TOKEN_SUCCESS';
                    $ret['returnData'] = $arr_result;
                } elseif ($change_result === false) {
                    $ret['returnCode'] = return_code::MA_CHANGE_TOKEN_ERR;
                    $ret['returnMessage'] = 'CHANGE_TOKEN_ERR';
                    $ret['returnData'] = '';
                } else {
                    $arr_result['appid'] = $appid;
                    $arr_result['access_token'] = $access_token;
                    $arr_result["exp"] = $now_time + $expires_in;
                    $ret['returnCode'] = return_code::MA_OK;
                    $ret['returnMessage'] = 'UPDATE_TOKEN_SUCCESS';
                    $ret['returnData'] = $arr_result;
                }
            } elseif (isset($json_message['errcode']) && isset($json_message['errmsg'])) {
                $ret['returnCode'] = return_code::MA_INVALID_APPID;
                $ret['returnMessage'] = 'WEIXIN_CODE_ERR';
                $ret['returnData'] = $json_message['errmsg'];
            } else {
                $ret['returnCode'] = return_code::MA_WEIXIN_RETURN_ERR;
                $ret['returnMessage'] = 'WEIXIN_RETURN_ERR';
                $ret['returnData'] = '';
            }
        } else {
            $ret['returnCode'] = return_code::MA_WEIXIN_NET_ERR;
            $ret['returnMessage'] = 'WEIXIN_NET_ERR';
            $ret['returnData'] = '';
        }
        return $ret;
    }



}
