<?php
/**
 * db pdo
 * @package system_common_lib_db
 */
load_lib('/db/pdostatement');
/**
 * db pdo
 * @author jxu
 * @package system_common_lib_db
 */
class db_pdo extends pdo{

	/**
	 * 返回数据格式
	 * @var int
	 */
	private $_iDefaultFetchMode = PDO::FETCH_ASSOC;

	/**
	 * 构造函数
	 * @param string $p_sDSN
	 * @param string $p_sUserName
	 * @param string $p_sUserPWD
	 * @param array $p_aDriverOption
	 */
	function __construct($p_sDSN, $p_sUserName = '', $p_sUserPWD = '', $p_aDriverOption = array()){
		parent::__construct($p_sDSN, $p_sUserName, $p_sUserPWD, $p_aDriverOption);
		$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array( 
				'db_pdostatement',
				array( 
						$this 
				) 
		));
	}

	/**
	 * 准备执行计划
	 * @param string $p_sSQL
	 * @param array $p_aDriverOption
	 * @return object
	 */
	function prepare($p_sSQL, $p_aDriverOption = array()){
		$oStatement = parent::prepare($p_sSQL, $p_aDriverOption);
		if($oStatement instanceof PDOStatement){
			$oStatement->setFetchMode($this->_iDefaultFetchMode);
		}
		return $oStatement;
	}
}