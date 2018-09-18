这是一个cli 任务进程管理类库，可以管理任务进程，可循环间隔启动任务进程，也可按类似crontab命令在指定时间执行。
* 任务可以保存在数据表，或者配置文件。
* 任务的信息必须包含如下字段：
<table>
<tr><th>字段</th><th>类型</th><th>说明</th> </tr>
<tr><td>id</td><td>Int</td><td>任务ID</td> </tr>
<tr><td>app_name</td><td>String</td><td>项目名称</td> </tr>
<tr><td>ip</td><td>String</td><td>服务器IP</td> </tr>
<tr><td>task_action</td><td>String</td><td>任务命令（任务名 方法 [参数]）</td> </tr>
<tr><td>flag</td><td>Int</td><td>是否开启，1开启，0关闭</td> </tr>
<tr><td>interval</td><td>Int</td><td>循环间隔时间（单位秒）</td> </tr>
<tr><td>cron_str</td><td>String</td><td>类似crontab的时间设置，如*/10 * * * *</td> </tr>
<tr><td>expire</td><td>Int</td><td>任务超时时间（超时自动kill进程，0则不会自动停止）</td> </tr>
</table>

* 文件Base.php 中第42、43行需按自己具体情况获取任务列表。建议任务信息存在数据表中，并在后台增加任务管理功能，方便对任务进行创建、编辑、设置、删除操作。
* 文件MultiProcess.php有用于对同一任务启动多进程处理的方法，比较适合异步队列数据较大的情况。使用方法如下：
<pre>
MultiProcess::multiRun('app_name', 'controller','action', function(&$MP) {
    //等待过程中，每1ms要执行的逻辑，比如检测执行进度
}, 10); //此处10表示同时启用10个进程
</pre>
* 默认项目路径为: /opt/www/project_name
* 实际进程启动完整命令为：php /opt/www/project_name/app/cli.php controller action > /opt/www/logs/tasks/project_name/2017/06/06/controller_action.log 2>&1 &
* 执行任务需要添加一个进程管理控制程序，在tasks目录（针对Phalcon框架）下，添加CrontabprocessTask.php文件，代码如下：
<pre>
class CrontabprocessTask extends \Phalcon\CLI\Task
{
    public function mainAction() {
        $process = new \Phalcon\Library\Process\CrontabControl();
        $process->run();
    }

    public function stopAction() {
        $process = new \Phalcon\Library\Process\CrontabControl();
        $process->stopCrontabManager();
        $manager = new \Phalcon\Library\Process\CrontabManager();
        $manager->stop();
    }
}
</pre>
* 然后添加一个进程管理程序，在tasks目录（针对Phalcon框架）下，添加CrontabmanagerTask.php文件，代码如下：
<pre>
class CrontabmanagerTask extends \Phalcon\CLI\Task
{
    public function mainAction() {
        $manager = new \Phalcon\Library\Process\CrontabManager();
        $manager->run();
    }
}
</pre>
* 最后执行： php /opt/www/project_name/app/cli.php Crontabprocess > /opt/www/logs/tasks/project_name/2017/06/06/Crontabprocess.log 2>&1 & 就行了
* 程序每隔2秒重新获取任务列表，然后判断进程状态，并按需要自动开启或停止任务进程。
