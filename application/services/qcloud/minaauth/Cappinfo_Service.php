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
class Cappinfo_Service
{

    public function __construct()
    {
        require_once('system/db/mysql_db.php');
    }

    /**
     * @param $appid
     * @param $secret
     * @param int $login_duration
     * @param int $session_duration
     * @return bool
     */
    public function insert_cappinfo($params)
    {
        $insert_sql = 'insert into cAppinfo SET appid = "' . $params['appid'] . '",secret = "' . $params['secret'] . '",qcloud_appid = "'.$params['qcloud_appid'].'",ip="'.$params['ip'].'"';
        $mysql_insert = new mysql_db();
        return $mysql_insert->query_db($insert_sql);
    }

    /**
     * @param $appid
     * @param $secret
     * @param $login_duration
     * @param $session_duration
     * @return bool
     */
    public function update_cappinfo($params)
    {
        $update_sql = 'update cAppinfo set login_duration = ' . $params['login_duration'] . ',session_duration=' . $params['session_duration'] . ',$secret = "' . $params['secret'] . '" where appid = "' . $params['appid'] . '"';
        $mysql_update = new mysql_db();
        return $mysql_update->query_db($update_sql);
    }

    /**
     * @param $appid
     * @return bool
     */
    public function delete_cappinfo($appid)
    {
        $delete_sql = 'delete from cAppinfo where appid = "'.$appid.'"';
        $mysql_delete = new mysql_db();
        return $mysql_delete->query_db($delete_sql);
    }


    /**
     * Modified
     * 修改sql语句，通过传入的appid查找对应的Auth记录
     * @param $appid
     * @return array|bool
     */
    public function select_cappinfo($appid)
    {
        $select_sql = 'select * from cAppinfo where appid = "'.$appid.'"';
        $mysql_select = new mysql_db();
        $result = $mysql_select->select_db($select_sql);
        if ($result !== false && !empty($result)) {
            $arr_result = array();
            while ($row = mysqli_fetch_array($result)) {
                $arr_result['appid'] = $row['appid'];
                $arr_result['secret'] = $row['secret'];
                $arr_result['login_duration'] = $row['login_duration'];
                $arr_result['session_duration'] = $row['session_duration'];
                $arr_result['qcloud_appid'] = $row['qcloud_appid'];
                $arr_result['ip'] = $row['ip'];
                $arr_result['access_token'] = $row['access_token'];
                $arr_result['expires_in'] = $row['expires_in'];
                $arr_result['update_time'] = $row['update_time'];
            }
            return $arr_result;
        } else {
            return false;
        }
    }

    /**
     * @param $appid
     * @param $access_token
     * @param $expires_in
     * @param $update_time
     * @return bool
     */
    public function update_accesstoken($params)
    {
        $update_sql = 'update cAppinfo set access_token = "' . $params['access_token'] . '",expires_in=' . $params['expires_in'] . ',update_time = "' . $params['update_time'] . '" where appid = "' . $params['appid'] . '"';
        $mysql_update = new mysql_db();
        return $mysql_update->query_db($update_sql);
    }

}