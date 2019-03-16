<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
/**
 * Created by PhpStorm.
 * User: ayisun
 * Date: 2016/10/1
 * Time: 16:47
 */
class mysql_db
{

    private $host= null;
    private $port= null;
    private $user_name= null;
    private $pass_wd = null;
    private $data_base = null;

    public function __construct()
    {
        require_once "system/load_config.php";
        $load_config = new load_config();
        $config = $load_config->fc_load_config("system/db/db.ini");
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->user_name = $config['user_name'];
        $this->pass_wd = $config['pass_wd'];
        $this->data_base = $config['data_base'];
    }

    /**
     * @param $sql
     * @return string
     * 描述:安全过滤sql,防止sql注入
     */
    function safe($sql)
    {
        if (get_magic_quotes_gpc()) {
            $sql = stripslashes($sql);
        }
        $sql = mysqli_real_escape_string($sql);
        return $sql;
    }

    /**
     * @param $sql
     * @return bool
     * 描述:执行Mysql增删改操作
     */
    public function query_db($sql)
    {
        $con = mysqli_connect($this->host . ':' . $this->port, $this->user_name, $this->pass_wd);
        if ($con) {
            mysqli_select_db($con,$this->data_base);
            $mysql_result = mysqli_query($con,$sql);
            if ($mysql_result === false) {
                mysqli_close($con);
                return false;
            }
            mysqli_close($con);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $sql
     * @return bool|resource
     * 描述：执行mysql查询操作
     */
    public function select_db($sql)
    {
        $con = mysqli_connect($this->host . ':' . $this->port, $this->user_name, $this->pass_wd);
        if ($con) {
            mysqli_select_db($con,$this->data_base);
            $arr_result = mysqli_query($con,$sql);
            mysqli_close($con);
            if(mysqli_num_rows($arr_result) < 1)
                return false;
            return $arr_result;
        } else {
            return false;
        }
    }

    public function init_db($sql){
        $con = mysqli_connect($this->host . ':' . $this->port, $this->user_name, $this->pass_wd);
        if ($con) {
            $result = mysqli_query($con,"$sql");
            if($result===false){
                return false;
            }
            return true;
        }else{
            return false;
        }
    }
}