<?php

/**
 * Created by PhpStorm.
 * User: ayisun
 * Date: 2016/10/1
 * Time: 15:14
 **************************
 * Modified by FJX
 * Modified Date: 2017/7/5
 * Modified Time: 15:21
 */
class Cworkappinfo_Service
{

    public function __construct()
    {
        require_once('system/db/mysql_db.php');
    }

    /**
     * @param $corpid
     * @param $agentid
     * @param $agentsecret
     * @param int $login_duration
     * @param int $session_duration
     * @return bool
     */
    public function insert_cappinfo($params)
    {
        $insert_sql = 'insert into cAppinfo_work SET corpid = "' . $params['corpid'] .'",agentid = "'.$params['agentid'] . '",agentsecret = "' . $params['agentsecret'] . '",qcloud_appid = "'.$params['qcloud_appid'].'",ip="'.$params['ip'].'", remark="'.$params['remark'].'"';
        $mysql_insert = new mysql_db();
        return $mysql_insert->query_db($insert_sql);
    }

    /**
     * @param $corpid
     * @param $agentid
     * @param $agentsecret
     * @param $login_duration
     * @param $session_duration
     * @return bool
     */
    public function update_cappinfo($params)
    {
        $update_sql = 'update cAppinfo_work set login_duration = ' . $params['login_duration'] . ',session_duration=' . $params['session_duration'] . ',$agentsecret = "' . $params['agentsecret'] . '" where corpid = "' . $params['corpid'] . '" and agentid = "' . $params['agentid'] . '"';
        $mysql_update = new mysql_db();
        return $mysql_update->query_db($update_sql);
    }

    /**
     * @param $corpid
     * @param $agentid
     * @return bool
     */
    public function delete_cappinfo($corpid, $agentid)
    {
        $delete_sql = 'delete from cAppinfo_work where corpid = "'.$corpid.'" and agentid = "' . $agentid . '"';
        $mysql_delete = new mysql_db();
        return $mysql_delete->query_db($delete_sql);
    }


    /**
     * Modified
     * 修改sql语句，通过传入的corpid和agentid查找对应的Auth记录
     * @param $corpid
     * @param $agentid
     * @return array|bool
     */
    public function select_cappinfo($corpid, $agentid)
    {
        $select_sql = 'select * from cAppinfo_work where corpid = "'.$corpid.'" and agentid = "' .$agentid.'"';
        $mysql_select = new mysql_db();
        $result = $mysql_select->select_db($select_sql);
        if ($result !== false && !empty($result)) {
            $arr_result = array();
            while ($row = mysqli_fetch_array($result)) {
                $arr_result['corpid'] = $row['corpid'];
                $arr_result['agentid'] = $row['agentid'];
                $arr_result['agentsecret'] = $row['agentsecret'];
                $arr_result['login_duration'] = $row['login_duration'];
                $arr_result['session_duration'] = $row['session_duration'];
                $arr_result['qcloud_appid'] = $row['qcloud_appid'];
                $arr_result['ip'] = $row['ip'];
                $arr_result['access_token'] = $row['access_token'];
                $arr_result['expires_in'] = $row['expires_in'];
                $arr_result['update_time'] = $row['update_time'];
                $arr_result['remark'] = $row['remark'];
                $arr_result['ticket'] = $row['ticket'];
                $arr_result['ticket_expires_in'] = $row['ticket_expires_in'];
                $arr_result['ticket_update_time'] = $row['ticket_update_time'];
                $arr_result['agent_ticket'] = $row['agent_ticket'];
                $arr_result['agent_update_time'] = $row['agent_update_time'];
            }
            return $arr_result;
        } else {
            return false;
        }
    }

    /**
     * @param $corpid
     * @param $agentsecret
     * @param $access_token
     * @param $expires_in
     * @param $update_time
     * @return bool
     */
    public function update_accesstoken($params)
    {
        $update_sql = 'update cAppinfo_work set access_token = "' . $params['access_token'] . '",expires_in=' . $params['expires_in'] . ',update_time = "' . $params['update_time'] . '" where corpid = "' . $params['corpid'] . '" and agentsecret = "'.$params['agentsecret'] .'"';
        $mysql_update = new mysql_db();
        return $mysql_update->query_db($update_sql);
    }

    /**
     * @param $corpid
     * @param $agentsecret
     * @param $ticket
     * @param $ticket_expires_in
     * @param $ticket_update_time
     * @return bool
     */
    public function update_ticket($params)
    {
        $update_sql = 'update cAppinfo_work set ticket = "' . $params['ticket'] . '",ticket_expires_in=' . $params['ticket_expires_in'] . ',ticket_update_time = "' . $params['ticket_update_time'] . '" where corpid = "' . $params['corpid'] . '" and agentsecret = "'.$params['agentsecret'] .'"';
        $mysql_update = new mysql_db();
        return $mysql_update->query_db($update_sql);
    }
    
    /**
     * @param $corpid
     * @param $agentsecret
     * @param $agent_ticket
     * @param $agent_update_time
     * @return bool
     */
    public function update_agent_ticket($params)
    {
        $update_sql = 'update cAppinfo_work set agent_ticket = "' . $params['agent_ticket'] . '",agent_update_time = "' . $params['agent_update_time'] . '" where corpid = "' . $params['corpid'] . '" and agentsecret = "'.$params['agentsecret'] .'"';
        $mysql_update = new mysql_db();
        return $mysql_update->query_db($update_sql);
    }

}