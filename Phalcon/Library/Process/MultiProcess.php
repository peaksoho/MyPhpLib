<?php
/**
 * Created by PhpStorm.
 * User: peak.cha
 * Date: 2016-10-12
 * Time: 9:56
 */

namespace Phalcon\Library\Process;

class MultiProcess extends CrontabBase
{
    protected $taskList=array();
    private $cacheKeyPre = 'MultiProcess_';
    //private $results = array();

    private function getCacheKey($task) {
        return $this->cacheKeyPre.preg_replace("/\W/", "_",$task);
    }

    public function runTask($app_name,$task_action,$is_debug=0) {  //,$returnKey=''
        $task = array('app_name'=>$app_name,'task_action'=>$task_action);
        $worker = $this->getTaskFullPath($task);
        //$cacheKey = $this->getCacheKey($worker);

        $num = 1;
        $current = System::getProcessNum($worker);
        $num -= $current;
        if ($num > 0) {
            $log = !$is_debug ? '/dev/null' : $this->getLogFullPath($task);
            if (!System::start($this->bin, $worker, $num,$log, '')) {
                throw new \Exception("Task [{$worker}] failed!");
            }
            array_push($this->taskList, array(
                //'cacheKey' => $cacheKey,
                //'returnKey'=>$returnKey?$returnKey:$cacheKey,
                //'task'=>$task,
                'worker'=>$worker,
            ));
        }
    }

    /**
     * @param int $timeout 每个进程超时时间（秒）
     * @param callable|null $fn
     * @return mixed
     * @throws \Exception
     */
    public function wait($timeout=10, callable $fn=null) {
        $start   =  microtime(TRUE);
        while(1) {
            $allDone = true;  //是否都执行完
            $taskNum = count($this->taskList);
            foreach ($this->taskList as $k=>$v) {
                $current = (int)System::getProcessNum($v['worker']);
                if(empty($current)) unset($this->taskList[$k]); //执行完成
                else { //子进程超时自动退出
                    $allDone = false;
                    $end = microtime(TRUE);
                    if($timeout>0 && $end-$start>$timeout) {
                        if (!System::stop($v['worker'])) echo "\nStop [ {$v['worker']} ] error...\n";
                        else echo "\nWorker[ {$v['worker']} ] timeout! Stopped!\n";
                    }
                }
            }
            if(empty($this->taskList)) $allDone = true;
            if(is_callable($fn)) $fn($this);
            if($allDone===true) break;
            $end = microtime(TRUE);
            if($timeout>0 && ($end-$start) > $timeout * $taskNum) throw new \Exception("Timeout!");
            usleep(1000);
        }
        $end = microtime(TRUE);
        return $end-$start;
    }

    public function stop() {
        usleep(100000);
        if(!empty($this->taskList)) {
            foreach ($this->taskList as $k=>$v) {
                if (!System::stop($v['worker'])) {
                    echo "\nStop [ {$v['worker']} ] error...\n";
                }
            }
        }
    }


    /**
     * 自动开启多进程并发处理
     * @param $prjName  //项目
     * @param $controller  //脚本控制器
     * @param $action  //脚本方法
     * @param callable|null $fn 回调函数
     * @param int $processNum 开启并发进程数
     * @param int $timeout 每个进程超时时间（秒）
     * @param int $is_debug 是否调试模式（记录子进程日志）
     * @return bool
     */
    static public function multiRun($prjName, $controller, $action, callable $fn=null, $processNum = 1, $timeout = 0, $isDebug=0) {
        if(empty($prjName) || empty($controller) || empty($action)) return false;
        $MP = new \Phalcon\Library\Process\MultiProcess();
        for ($i=0;$i<$processNum;$i++) {
            $MP->runTask($prjName,$controller.' '.$action.' '.$i, $isDebug);
        }
        $MP->wait($timeout,$fn);
        return true;
    }

}