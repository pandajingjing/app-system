<?php
/**
 * controller_app_phpinfo
 * @author jxu
 * @package system_controller_app
 */
/**
 * controller_app_phpinfo
 *
 * @author jxu
 */
class controller_app_phpinfo extends controller_sys_web{

	function doRequest(){
		$this->addHeader('Content-Type:text/html; charset=utf-8');
		return 'controller_home_404';
		return 'app_phpinfo';
	}
}