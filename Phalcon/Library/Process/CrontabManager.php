<?php
/**
 * Created by PhpStorm.
 * User: peak.cha
 * Date: 2017-05-18
 * Time: 15:20
 * 任务
 */

namespace Phalcon\Library\Process;


class CrontabManager extends CrontabBase
{

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * [run description]
     * @return [type] [description]
     */
    public function run() {
        if (System::exists($this->baseUrl . 'crontabmanager')) {
            echo "exists...\n";
            exit();
        }
        while(true) {
            try {
                $this->refreshTasks();
                $this->check();
            } catch(Exception $e) {
                echo $e->getMessage() . "\n";
            }
            $this->sleep(2);
        }
    }

    /**
     *
     */
    public function stop() {
        try {
            $this->refreshTasks();
            $this->stopWorkers();
        } catch(Exception $e) {
            echo $e->getMessage() . "\n";
            exit();
        }
    }

    /**
     * [stopWorker description]
     * @return [type] [description]
     */
    public function stopWorker($task) {
        $worker = $this->getTaskFullPath($task);
        if ($worker && System::exists($worker)) {
            echo '['.date('Y-m-d H:i:s')."] stop...{$worker}...";
            if (!System::stop($worker)) {
                throw new Exception("stop {$worker} error...");
            }
            echo "ok\n";
        }
        $this->getRedis()->hDel($this->tasksCacheKey,$task['id']);
    }
    /**
     * [stopWorkers description]
     * @return [type] [description]
     */
    public function stopWorkers() {
        if ($this->taskList) {
            foreach ($this->taskList as $item) {
                $worker = $this->getTaskFullPath($item);
                if($worker && System::exists($worker)) {
                    echo '['.date('Y-m-d H:i:s')."] stop...{$worker}...";
                    if (!System::stop($worker)) {
                        throw new Exception("stop {$worker} error...");
                    }
                    echo "ok\n";
                }
                $this->getRedis()->hDel($this->tasksCacheKey,$item['id']);
            }
        }
    }

    /**
     * [check description]
     * @return [type] [description]
     */
    public function check() {
        if ($this->taskList) {
            foreach ($this->taskList as $key => $item) {
                if($item['app_name']=='laravelS') continue;
                if(in_array($item['ip'],$this->ip)) {
                    $worker = $this->getTaskFullPath($item);
                    if(!$worker) {
                        $this->getRedis()->hDel($this->tasksCacheKey,$item['id']); //任务不存在，删除状态缓存
                        continue;
                    }
                    $num = 1;
                    $current = System::getProcessNum($worker);
                    $num -= $current;

                    $runTime = time(); //当前时间
                    $cron_str = trim($item['cron_str']);
                    $lastDateTime = intval($this->getRedis()->hget($this->tasksLastRunTimeCacheKey,$item['id'])); //上次执行时间

                    if (!$item['flag']) { //任务未开启
                        if ($num <= 0) $this->stopWorker($item); //进程仍然存在则停止进程
                        else if($this->getRedis()->hget($this->tasksCacheKey,$item['id'])) $this->getRedis()->hDel($this->tasksCacheKey,$item['id']); //删除状态缓存
                        unset($this->taskList[$key]);
                        continue;
                    }
                    else if ($num <= 0) { //任务开启进程仍然存在
                        if($lastDateTime>0 && $item['expire']>=10 && $item['expire']<=172800 && $runTime-$lastDateTime>$item['expire']) { //进程执行超时则停止进程
                            $this->stopWorker($item);
                        }
                        else continue;
                    }
                    if($this->getRedis()->hget($this->tasksCacheKey,$item['id'])) $this->getRedis()->hDel($this->tasksCacheKey,$item['id']); //删除状态缓存

                    $interval = intval($item['interval']);
                    $interval = $interval<=0 ? $this->minInterval : $interval;

                    if($cron_str) { //按定时设置执行
                        $cronTime =\Phalcon\Library\Process\Crontab::parse($cron_str);
                        if($runTime!=$cronTime) continue;
                        $runTime = strtotime(substr(date('Y-m-d H:i:s',$runTime),0,-2).'00');
                        if($lastDateTime==$runTime) continue;
                    }
                    else if($interval<=0 || $runTime<$lastDateTime+$interval) { //按循环间隔执行
                        continue;
                    }

                    $log = $this->getLogFullPath($item);
                    //echo '[' . date('Y-m-d H:i:s') . "] start...{$worker} > {$log}...";
                    if (System::start($item['app_name']=='tools'?'':$this->bin, $worker, $num, $log, '')) {
                        //echo "ok\n";
                        $this->getRedis()->hSet($this->tasksCacheKey,$item['id'],$item['id']);
                        $this->getRedis()->hSet($this->tasksLastRunTimeCacheKey,$item['id'],$runTime);
                    } else {
                        echo '[' . date('Y-m-d H:i:s') . "] start...{$worker} > {$log}...";
                        echo "fail\n";
                    }
                }
            }
        }
    }

}