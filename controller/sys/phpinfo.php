<?php
/**
 * controller_sys_phpinfo
 * @author jxu
 * @package system_controller_sys
 */
/**
 * controller_sys_phpinfo
 *
 * @author jxu
 */
class controller_sys_phpinfo extends lib_controller_web{

	function doRequest(){
		$this->addHeader('Content-Type:text/html; charset=utf-8');
		return 'controller_home_404';
		return 'app_phpinfo';
	}
}