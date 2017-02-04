<?php
/**
 * client curl
 * @package system_common_lib_client
 */
/**
 * client curl
 * @author jxu
 * @package system_common_lib_client
 */
class client_curl{

	/**
	 * 客户端连接
	 * @var object
	 */
	private $_oResource = null;

	/**
	 * CURL信息
	 * @var array
	 */
	private $_aInfo = array();

	/**
	 * 服务器返回信息
	 * @var string
	 */
	private $_sContent = '';

	/**
	 * 构造函数
	 * @param string $p_sURL
	 */
	function __construct($p_sURL = ''){
		$this->_oResource = curl_init($p_sURL);
		$this->setOption(CURLOPT_RETURNTRANSFER, true);
		$this->setOption(CURLOPT_CONNECTTIMEOUT, get_config('iConnectionTimeout', 'client'));
		$this->setTimeOut(get_config('iExecuteTimeout', 'client'));
	}

	/**
	 * 析构函数
	 */
	function __destruct(){
		curl_close($this->_oResource);
	}

	/**
	 * 设置选项
	 * @param int $p_iName
	 * @param mix $p_mValue
	 * @return true/false
	 */
	function setOption($p_iName, $p_mValue){
		return curl_setopt($this->_oResource, $p_iName, $p_mValue);
	}

	/**
	 * 设置要访问的URL
	 * @param string $p_sURL
	 * @return true/false
	 */
	function setURL($p_sURL){
		return $this->setOption(CURLOPT_URL, $p_sURL);
	}

	/**
	 * 设置超时时间
	 * @param int $p_iTime
	 * @return true/false
	 */
	function setTimeOut($p_iTime){
		return $this->setOption(CURLOPT_TIMEOUT, $p_iTime);
	}

	/**
	 * 设置何种方式提交数据
	 * @param boolean $p_bPost
	 * @return true/false
	 */
	function setPost($p_bPost = true){
		return $this->setOption(CURLOPT_POST, $p_bPost);
	}

	/**
	 * 设置Post参数
	 * @param array $p_aParams
	 * @return true/false
	 */
	function setPostParams($p_aParams){
		if(is_array($p_aParams)){
			$p_aParams = http_build_query($p_aParams);
		}
		return $this->setOption(CURLOPT_POSTFIELDS, $p_aParams);
	}

	/**
	 * 发送请求
	 * @return true/false
	 */
	function executeURL(){
		$mResult = curl_exec($this->_oResource);
		if(false === $mResult){
			return false;
		}else{
			$this->_aInfo = curl_getinfo($this->_oResource);
			$this->_sContent = $mResult;
			if(200 == $this->_aInfo['http_code']){
				return true;
			}else{
				return false;
			}
		}
	}

	/**
	 * 获取服务端返回信息
	 * @return string
	 */
	function getContent(){
		return $this->_sContent;
	}

	/**
	 * 得到版本信息
	 * @param int $p_iAge
	 * @return array
	 */
	function getVersion($p_iAge = 0){
		return curl_version($p_iAge);
	}

	/**
	 * 得到CURL信息
	 * @return array
	 */
	function getInfo(){
		if(empty($this->_aInfo)){
			$this->_aInfo = curl_getinfo($this->_oResource);
		}
		return $this->_aInfo;
	}

	/**
	 * 得到错误编号
	 * @return int
	 */
	function getErrNo(){
		return curl_errno($this->_oResource);
	}

	/**
	 * 得到错误信息
	 * @return string
	 */
	function getErrMsg(){
		return curl_error($this->_oResource);
	}
}
