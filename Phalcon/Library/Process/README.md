这是一个cli 任务进程管理类库，可以管理任务进程，可循环间隔启动任务进程，也可按类似crontab命令在指定时间执行。
任务可以保存在数据表，或者配置文件。
任务的信息必须包含如下字段：
<table>
<tr><th>字段</th><th>类型</th><th>说明</th> </tr>
<tr><th>id</th><th>Int</th><th>任务ID</th> </tr>
<tr><th>app_name</th><th>String</th><th>项目名称</th> </tr>
<tr><th>ip</th><th>String</th><th>服务器IP</th> </tr>
<tr><th>task_action</th><th>String</th><th>任务命令（任务名 方法 [参数]）</th> </tr>
<tr><th>flag</th><th>Int</th><th>是否开启，1开启，0关闭</th> </tr>
<tr><th>interval</th><th>Int</th><th>循环间隔时间（单位秒）</th> </tr>
<tr><th>cron_str</th><th>String</th><th>类似crontab的时间设置，如*/10 * * * *</th> </tr>
<tr><th>expire</th><th>Int</th><th>任务超时时间（超时自动kill进程）</th> </tr>
</table>

* 文件Base.php 中第42、43行需按自己具体情况获取任务列表。建议任务信息存在数据表中，并在后台增加任务管理功能，方便进行创建、编辑、设置、删除任务操作。
* 文件MultiProcess.php有用于对同一任务启动多进程处理的方法，比较适合异步队列数据较大的情况。使用方法如下：
<pre>
MultiProcess::multiRun('app_name', 'task_name','task_action', function(&$MP) {
    //等待过程中，每1ms要执行的逻辑，比如检测执行进度
}, 10); //此处10表示同时启用10个进程
</pre>
