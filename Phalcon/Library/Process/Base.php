<?php
/**
 * Created by PhpStorm.
 * User: peak.cha
 * Date: 2016-09-06
 * Time: 17:50
 */

namespace Phalcon\Library\Process;


class Base
{
    protected $bin = 'php';
    protected $reboot = false;
    protected $ip = '';
    protected $app_name = '';
    protected $date = '';
    protected $baseUrl = '../app/cli.php ';
    protected $logPath = '../app/logs/';
    protected $minInterval = 5;
    protected $taskList= array();
    protected $tasksCacheKey= 'ids_of_running_task';
    protected $tasksLastRunTimeCacheKey= 'last_runtime_of_tasks';

    public function __construct() {
        $this->baseUrl = APP_PATH . '/app/cli.php ';
        $this->logPath = APP_PATH . '/app/logs/';
        $this->ip = System::getServerIp();
        $this->app_name = basename(APP_PATH);
        $this->date = date("Ymd");
        $config = $this->getDi()->getShared('config')->toArray();
        if(!empty($config['task'])) {
            foreach ($config['task'] as $k=>$v) {
                $this->$k = $v;
            }
        }
    }

    protected function refreshTasks() {
        //获取任务列表(字段：id, app_name, ip, task_action, flag, interval, cron_str, expire)，获取方式按具体情况需更改
        $r = \Phalcon\Library\Api::call('auth','api/tasks/list',array('show_all'=>1,'app_name'=>$this->app_name));
        $this->taskList = empty($r['data']['rows'])? array() : $r['data']['rows'] ;
    }

    public function sleep($cd) {
        if ($this->reboot && $this->date != date("Ymd")) {
            exit("reboot\n");
        }
        sleep($cd);
    }

    /**
     * 获取log文件绝对路径
     * @param string $fileName
     * @return string
     */
    public function getLogFile($fileName='') {
        if(empty($fileName)) return '';
        $path = $this->logPath .date('Y').'/'.date('m').'/';
        $r = mkdir($path,0777,true);
        if($r) shell_exec("chown www.www -R ".$this->logPath);
        return $path. str_replace(array('/',' '), '_', $fileName) . '_' .date('Ymd').'.log';
    }


    //获取默认DI实例
    public static function getDi() {
        return \Phalcon\Di::getDefault();
    }

    protected function getRedis() {
        return $this->getDi()->getShared('redis');
    }


}