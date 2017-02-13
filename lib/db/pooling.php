<?php

/**
 * db pooling
 * @package system_common_lib_db
 */
/**
 * db pooling
 *
 * @author jxu
 * @package system_common_lib_db
 */
class lib_db_pooling
{

    /**
     * 数据库连接池实例
     *
     * @var object
     */
    private static $_oInstance = null;

    /**
     *
     * @var string
     */
    private $_sDeCrypt = 'ce61649168';

    /**
     * 数据库连接池
     *
     * @var array
     */
    private $_aConnect = [];

    /**
     * 构造函数
     */
    private function __construct()
    {}

    /**
     * 析构函数
     */
    function __destruct()
    {}

    /**
     * 构造函数
     */
    private function __clone()
    {}

    /**
     * 获取实例
     *
     * @return object
     */
    static function getInstance()
    {
        if (! (self::$_oInstance instanceof self)) {
            self::$_oInstance = new self();
        }
        return self::$_oInstance;
    }

    /**
     * 获取数据库连接
     *
     * @param string $p_sDBName            
     * @return object
     */
    function getConnect($p_sDBName)
    {
        if (! isset($this->_aConnect[$p_sDBName])) {
            $this->_aConnect[$p_sDBName] = $this->_loadDB($p_sDBName);
        }
        return $this->_aConnect[$p_sDBName];
    }

    /**
     * 加载数据库连接
     *
     * @param string $p_sDBName            
     * @return object
     */
    private function _loadDB($p_sDBName)
    {
        $aConfig = lib_sys_var::getInstance()->getConfig($p_sDBName, 'database');
        $oDatabase = new lib_db_pdo($aConfig['sDSN'], $aConfig['sUserName'], util_crypt::deCrypt($aConfig['sUserPassword'], $this->_sDeCrypt));
        foreach ($aConfig['aInitSQL'] as $sSQL) {
            $oDatabase->exec($sSQL);
        }
        return $oDatabase;
    }
}