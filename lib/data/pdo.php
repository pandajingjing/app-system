<?php

/**
 * lib_data_pdo
 *
 * 扩展PDO
 *
 * @package lib_data
 */

/**
 * lib_data_pdo
 *
 * 扩展PDO
 */
class lib_data_pdo extends pdo
{

    /**
     * 默认返回关联数组格式
     *
     * @var int
     */
    private $_iDefaultFetchMode = PDO::FETCH_ASSOC;

    /**
     * 构造函数
     *
     * @param string $p_sDSN            
     * @param string $p_sUserName            
     * @param string $p_sUserPWD            
     * @param array $p_aDriverOption            
     * @return void
     */
    function __construct($p_sDSN, $p_sUserName = '', $p_sUserPWD = '', $p_aDriverOption = [])
    {
        parent::__construct($p_sDSN, $p_sUserName, $p_sUserPWD, $p_aDriverOption);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, [
            'lib_data_pdostatement',
            [
                $this
            ]
        ]);
    }

    /**
     * 准备执行计划
     *
     * @param string $p_sSQL            
     * @param array $p_aDriverOption            
     * @return object
     */
    function prepare($p_sSQL, $p_aDriverOption = [])
    {
        $oStatement = parent::prepare($p_sSQL, $p_aDriverOption);
        if ($oStatement instanceof PDOStatement) {
            $oStatement->setFetchMode($this->_iDefaultFetchMode);
        }
        return $oStatement;
    }
}