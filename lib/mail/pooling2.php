<?php
/**
 * mail pooling
 * @package system_common_lib_mail
 */
/**
 * mail pooling
 * @author jxu
 * @package system_common_lib_mail
 */
class mail_pooling{

	/**
	 * 邮件服务实例
	 * @var object
	 */
	private static $_oInstance = null;

	/**
	 * 数据库连接池
	 * @var array
	 */
	private static $_aConnect = array();

	/**
	 * 构造函数
	 */
	private function __construct(){

	}

	/**
	 * 析构函数
	 */
	function __destruct(){

	}

	/**
	 * 构造函数
	 */
	private function __clone(){

	}

	/**
	 * 获取实例
	 * @return object
	 */
	static function getInstance(){
		if(!(self::$_oInstance instanceof self)){
			self::$_oInstance = new self();
		}
		return self::$_oInstance;
	}

	/**
	 * 获取邮件服务连接
	 * @param string $p_sMailName
	 * @return object
	 */
	static function getConnect($p_sMailName){
		if(!isset(self::$_aConnect[$p_sMailName])){
			self::$_aConnect[$p_sMailName] = self::_loadMail($p_sMailName);
		}
		return self::$_aConnect[$p_sMailName];
	}

	/**
	 * 加载邮件服务连接
	 * @param string $p_sMailName
	 * @return object
	 */
	private static function _loadMail($p_sMailName){
		$aConfig = get_config($p_sMailName, 'mail');
		switch($aConfig['sType']){
			case 'php':
				load_lib('/mail/phpmail');
				$oMail = new mail_phpmail();
				break;
		}
		return $oMail;
	}
}