<?php

/**
 * Created by PhpStorm.
 * User: ayisun
 * Date: 2016/10/1
 * Time: 15:15
 **************************
 * Modified by FJX
 * Modified Date: 2017/7/7
 * Modified Time: 11:35
 */
class Csessioninfo_Service
{

    public function __construct()
    {
        require_once('system/db/mysql_db.php');
    }



    public function insert_csessioninfo($params)
    {
        //修改sql插入语句，将unionid和appid插入到session表中
        $insert_sql = 'insert into cSessionInfo set uuid = "'.$params['uuid'].'",skey = "' . $params['skey'] . '",create_time = "' . $params['create_time'] . '",last_visit_time = "' . $params['last_visit_time'] . '",open_id = "' . $params['open_id']. '",union_id = "' . $params['union_id'] . '",session_key="' . $params['session_key'] . '",appid = "'. $params['appid'] .'"';
        $mysql_insert = new mysql_db();
        return $mysql_insert->query_db($insert_sql);
    }



    public function update_csessioninfo_time($params)
    {
        $update_sql = 'update cSessionInfo set last_visit_time = "' . $params['last_visit_time'] . '" where uuid = "' . $params['uuid'].'"';
        $mysql_update = new mysql_db();
        return $mysql_update->query_db($update_sql);
    }


    public function update_csessioninfo($params)
    {
        $update_sql = 'update cSessionInfo set session_key= "'.$params['session_key'].'",create_time = "'.$params['create_time'].'" ,last_visit_time = "'.$params['last_visit_time'].'",skey = "' . $params['skey'].'",appid= "'. $params['appid'].'",union_id= "'. $params['union_id'] .'" where uuid = "' . $params['uuid'].'"';
        $mysql_update = new mysql_db();
        return $mysql_update->query_db($update_sql);
    }

    public function update_cuserinfo($params)
    {
        $update_sql = "update cSessionInfo set user_info='".$params['user_info']."', union_id='".$params['unionid'] . "' where open_id = '" . $params['openid']."'";
        $mysql_update = new mysql_db();
        return $mysql_update->query_db($update_sql);
    }

    public function delete_csessioninfo($open_id)
    {
        $delete_sql = 'delete from cSessionInfo where open_id = "' . $open_id . '"';
        $mysql_delete = new mysql_db();
        return $mysql_delete->query_db($delete_sql);
    }


    public function delete_csessioninfo_by_id_skey($params)
    {
        $delete_sql = 'delete from cSessionInfo where uuid = "' . $params['uuid'].'"';
        $mysql_delete = new mysql_db();
        return $mysql_delete->query_db($delete_sql);
    }


    public function select_csessioninfo($params)
    {
        $select_sql = 'select * from cSessionInfo where uuid = "' . $params['uuid'] . '" and skey = "' . $params['skey'] . '"';
        $mysql_select = new mysql_db();
        $result = $mysql_select->select_db($select_sql);
        if ($result !== false && !empty($result)) {
            $arr_result = array();
            while ($row = mysqli_fetch_array($result)) {
                $arr_result['id'] = $row['id'];
                $arr_result['uuid'] = $row['uuid'];
                $arr_result['skey'] = $row['skey'];
                $arr_result['create_time'] = $row['create_time'];
                $arr_result['last_visit_time'] = $row['last_visit_time'];
                $arr_result['open_id'] = $row['open_id'];
                $arr_result['session_key'] = $row['session_key'];
                $arr_result['user_info'] = $row['user_info'];
                $arr_result['union_id'] = $row['union_id'];
                $arr_result['appid'] = $row['appid'];
            }
            return $arr_result;
        } else {
            return false;
        }
    }


    public function get_id_csessioninfo($open_id)
    {
        $select_sql = 'select uuid from cSessionInfo where open_id = "' . $open_id . '"';
        $mysql_select = new mysql_db();
        $result = $mysql_select->select_db($select_sql);
        if ($result !== false && !empty($result)) {
            $id = false;
            while ($row = mysqli_fetch_array($result)) {
                $id = $row['uuid'];
            }
            return $id;
        } else {
            return false;
        }
    }


    public function check_session_for_login($params)
    {
        $select_sql = 'select * from cSessionInfo where open_id = "' . $params['open_id'] . '"';
        $mysql_select = new mysql_db();
        $result = $mysql_select->select_db($select_sql);
        if ($result !== false && !empty($result)) {
            $create_time = false;
            while ($row = mysqli_fetch_array($result)) {
                $create_time = strtotime($row['create_time']);
            }
            if ($create_time == false) {
                return false;
            } else {
                $now_time = time();
                if (($now_time-$create_time)/86400>$params['login_duration']) {
                    //$this->update_csessioninfo($params);
                    return true;
                } else {
                    return true;
                }
            }
        } else {
            return true;
        }
    }



    public function check_session_for_auth($params)
    {
        $result = $this->select_csessioninfo($params);
        if (!empty($result) && $result !== false && count($result) != 0) {
            $now_time = time();
            $create_time = strtotime($result['create_time']);
            $last_visit_time = strtotime($result['last_visit_time']);
            if (($now_time-$create_time)/86400>$params['login_duration']) {
                //$this->delete_csessioninfo_by_id_skey($params);
                return false;
            } elseif (($now_time-$last_visit_time)>$params['session_duration']) {
                return false;
            } else {
                $params['last_visit_time'] = date('Y-m-d H:i:s', $now_time);
                $this->update_csessioninfo_time($params);
                return $result['user_info'];
            }
        } else {
            return false;
        }
    }


    public function change_csessioninfo($params)
    {
        if ($this->check_session_for_login($params)) {
            $uuid = $this->get_id_csessioninfo($params['open_id']);
            if ($uuid != false) {
                $params['uuid'] = $uuid;
                if ($this->update_csessioninfo($params)) {
                    return $uuid;
                } else {
                    return false;
                }
            } else {
                return $this->insert_csessioninfo($params);
            }
        } else {
            return false;
        }
    }
}
