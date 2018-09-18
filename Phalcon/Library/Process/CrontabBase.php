<?php
/**
 * Created by PhpStorm.
 * User: peak.cha
 * Date: 2017-05-18
 * Time: 16:17
 */

namespace Phalcon\Library\Process;


class CrontabBase  extends Base
{
    protected $project_root = '/opt/www/';  // /opt/www/
    protected $cli_php_path = 'app/cli.php';

    public function __construct()
    {
        parent::__construct();
        $this->logPath = $this->project_root.'logs/tasks/';
        $this->app_name='';
    }

    //获取任务完整路径
    public function getTaskFullPath(array $task) {
        if(!$task || !$task['app_name'] || !$task['task_action']) return '';
        if($task['app_name']=='tools') {
            return $this->project_root.'/tools/'. str_replace(array('../',';','&','|'),'',$task['task_action']);
        }
        else return $this->project_root.$task['app_name'].'/' .$this->cli_php_path.' '. str_replace(array('../',';','&','|'),'',$task['task_action']);
    }

    /**
     * 获取log文件绝对路径
     * @param string $fileName
     * @return string
     */
    public function getLogFullPath(array $task) {
        if(!$task || !$task['app_name'] || !$task['task_action']) return '';
        $path = $this->logPath.$task['app_name'].'/' .date('Y').'/'.date('m').'/';
        $r = mkdir($path,0777,true);
        if($r) shell_exec("chown www.www -R ".$this->logPath);
        return $path. preg_replace("/[^\w]+/", '_', $task['task_action']) . '_' .date('Ymd').'.log';
    }
}