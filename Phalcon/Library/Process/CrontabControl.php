<?php
/**
 * Created by PhpStorm.
 * User: peak.cha
 * Date: 2016-09-06
 * Time: 17:47
 * 控制进程启动
 */

namespace Phalcon\Library\Process;


class CrontabControl extends CrontabBase
{
    protected $tryLimit = 2;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * [run description]
     * @return [type] [description]
     */
    public function run() {
        $this->stopCrontabManager();
        $this->startCrontabManager();
    }

    /**
     * [stopManager description]
     * @return [type] [description]
     */
    public function stopCrontabManager() {
        // 停止主控进程
        $manager = $this->baseUrl . 'crontabmanager';
        $num = System::getProcessNum($manager);
        if ($num > 0) {
            echo '['.date('Y-m-d H:i:s')."] stop...{$manager}...";
            while (true) {
                if (System::stop($manager)) {
                    echo "ok\n";
                    break;
                }
                echo '.';
                sleep(5);
            }
        }
    }

    /**
     * [startManager description]
     * @return [type] [description]
     */
    public function startCrontabManager() {
        $try = 0;
        $manager = $this->baseUrl . 'crontabmanager';
        $log = $this->getLogFullPath(array('app_name'=>'crontabmanager', 'task_action'=>'crontabmanager'));
        echo '['.date('Y-m-d H:i:s')."] start...{$manager} > {$log}...";
        while ($try < $this->tryLimit) {
            $try++;
            $status = System::startByRoot($this->bin, $manager, 1, $log);
            if ($status) {
                echo "ok\n";
                break;
            }
            echo '.';
        }
    }
}