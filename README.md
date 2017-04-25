# app-system

当前最新版本 V0.11.2

框架核心文件

##文件夹及对应存放内容

文件夹|存放内容
----|----
config|运维相关配置文件
controller|控制器文件
lib|框架功能文件
util|工具功能文件
bin.php|框架入口文件

注意:
- 该仓库的合并需要总监审批

## 关于作者

```php
date_default_timezone_set('Asia/Shanghai');

class me extends 码畜
{

    public $_sNickName = 'pandajingjing';

    public $_sWebSite = 'http://pandajingjing.jxulife.com';

    protected $_iQQ = 18073848;
}
```

## change log
- 20170425 V0.11.2
	- 1.调整本地调用服务的方法
	- 2.增加和调整response的支持格式
	- 3.移除crossdomain控制器
	- 4.增加获取某种方式全部参数的方法
- 20170419 V0.10.1
	- 1.调整response类输出错误函数名称
	- 2.调整rpc基类继承关系
- 20170418 V0.10
	- 1.分离命令行和web模式的入口函数
	- 2.完善ORM
	- 3.修复部分bug
- 20170406 V0.9.1
	- 1.为命令行模式关闭ob_start
	- 2.为命令行模式设置超时时间1800秒
	- 3.修改数据库密码秘钥
	- 4.增加pdostatement构造函数
- 20170224 V0.9
	- 1.调整配置文件格式
	- 2.增加基础controller
	- 3.完善各基类
	- 4.完成各种基础功能和工具类
	- 5.在应用中继续完善
- 20170206 V0.1.1
	- 1.bin文件移动到system文件夹内
	- 2.创建生成外部url的工具类util_url
	- 3.将url中协议(https?://)和域名的配置合并
	- 4.取消php压缩页面的功能
	- 5.修改本地调用service的bug
	- 6.修改路由生成本地url时返回全部url(包括域名)
	- 7.增加$_SERVER['HTTP_HOST']的返回
	- 8.增加框架输出跨域配置,phpinfo,robot的输出
	- 9.完善自定义url的路由操作
- 20170204 V0.1
	- 1.定义仓库内容
	- 2.创建基本目录结构
	- 3.初始化各种文件