<?php
/**
 * Created by PhpStorm.
 * User: ayisun
 * Date: 2016/10/12
 * Time: 16:15
 */

class init_db {

    public function __construct()
    {
        require_once "mysql_db.php";
        require_once "system/load_config.php";
    }

    public function init_db_config($params){
        if(isset($params['cdb_ip']) && isset($params['cdb_port']) && isset($params['cdb_user_name']) && isset($params['cdb_pass_wd'])){
            $host = $params['cdb_ip'];
            $port = $params['cdb_port'];
            $user_name = $params['cdb_user_name'];
            $pass_wd = $params['cdb_pass_wd'];
            $data_base = "cAuth";
            $data = "[db]\nhost = $host\nport = $port\nuser_name = $user_name\npass_wd = $pass_wd\ndata_base= $data_base";
            $data_path = "system/db/db.ini";
            $handle = fopen($data_path,'w');
            flock($handle, LOCK_EX);
            if (fwrite($handle,$data) === false){
                flock($handle, LOCK_UN);
                fclose($handle);
                return false;
            }else{
                flock($handle, LOCK_UN);
                fclose($handle);
                return true;
            }
        }else{
            return false;
        }
    }

    public function init_db_table(){
        $sql = file_get_contents("system/db/db.sql");
        if($sql === false){
            return false;
        }
        $_arr = explode(';', $sql);
        $mysql_db = new mysql_db();
        if($mysql_db->init_db("DROP DATABASE IF EXISTS `cAuth`")){
            if($mysql_db->init_db("CREATE DATABASE `cAuth`")){
                foreach ($_arr as $_value) {
                    if(!empty($_value)){
                        if(!$mysql_db->query_db($_value.';'))
                            return false;
                    }
                }
            }
            else{
                return false;
            }
        }else{
            return false;
        }
        return true;
    }

    //为客户创建非root权限用户
    public function create_user_for_db(){
        $pass_wd = $this->random_str(10);//随机生成十位的密码
        $mysql_db = new mysql_db();
        $sql = "grant select, insert, update, delete on cAuth.* to session_user@'%' identified by '".$pass_wd."'";
        if($mysql_db->init_db($sql)){
            return $pass_wd;
        }else{
            return false;
        }
    }

    //生成随机密码
    private function random_str($length)
    {
        //生成一个包含 大写英文字母, 小写英文字母, 数字 的数组
        $arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        $str = '';
        $arr_len = count($arr);
        for ($i = 0; $i < $length; $i++)
        {
            $rand = mt_rand(0, $arr_len-1);
            $str.=$arr[$rand];
        }
        return $str;
    }
}