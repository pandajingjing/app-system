<?php
class lib_sys_orm{
    
}

/**
 * orm orm
* @package system_common_lib_orm
*/
load_lib('/db/pooling');
load_lib('/cache/pooling');
/**
 * orm orm
 * @author jxu
 * @todo 支持事务
 * @package system_common_lib_orm
 */
abstract class orm_orm{

    /**
     * 所有执行的SQL语句
     * @var array
     */
    protected static $_aSQLs = array();

    /**
     * 数据库操作次数
     * @var int
     */
    protected static $_iQueryCnt = 0;

    /**
     * 缓存操作次数
     * @var int
     */
    protected static $_iCacheCnt = 0;

    /**
     * PHP静态变量缓存
     * @var array
     */
    protected static $_aStaticCache = array();

    /**
     * 有Sequence的表
     * @var array
     */
    protected static $_aSeqTbl = array();

    /**
     * 数据库连接池
     * @var array
     */
    protected static $_aDBPool = array();

    /**
     * 数据库陈述
     * @var object
     */
    protected static $_oDBSTMT = null;

    /**
     * 缓存连接
     * @var object
     */
    protected static $_oCache = null;

    /**
     * 调试对象
     * @var object
     */
    protected static $_oDebug = null;

    /**
     * 是否开启Debug信息
     * @var boolean
     */
    protected static $_bolDebug = false;

    /**
     * 用于保存调试信息
     * @var mix
     */
    protected static $_mixDebugResult = null;

    /**
     * 变量绑定占位符
     * @var string
     */
    protected static $_sBindHolder = ':';

    /**
     * ORM数据
     * @var array
     */
    protected $_aData = array();

    /**
     * ORM名称
     * @var string
     */
    protected $_sClassName = null;

    /**
     * 数据库表结构
     * @var array
     */
    protected $_aTblField = array();

    /**
     * 调用APC的表数据
     * @var array
     */
    protected $_aAPCTbl = array();

    /**
     * 主键字段
     * @var string
     */
    protected $_sPKIDField = null;

    /**
     * 表名称
     * @var string
     */
    protected $_sTblName = null;

    /**
     * Master数据库连接名,在子类中配置
     * @var string
     */
    protected $_sMasterDBName = null;

    /**
     * Slave数据库连接名,在子类中配置
     * @var string
     */
    protected $_sSlaveDBName = null;

    /**
     * 是否开启缓存
     * @var $_bolNeedCache
     */
    protected $_bolNeedCache = true;

    /**
     * 表结构缓存时间
     * @var int
     */
    protected $_iTblFieldCacheTime = 0;

    /**
     * 表结构缓存是否压缩
     * @var int
     */
    protected $_iTblFieldCacheCompress = 2;

    /**
     * 数据缓存时间
     * @var int
     */
    protected $_iDataCacheTime = 86400;

    /**
     * 数据缓存是否压缩
     * @var int
     */
    protected $_iDataCacheCompress = 0;

    /**
     * 排序
     * @var string
     */
    protected $_sOrder = '';

    /**
     * 开始行数
     * @var int
     */
    protected $_iStartRow = 0;

    /**
     * 获取行数
     * @var int
     */
    protected $_iFetchRow = 0;

    /**
     * 过滤条件
     * @var array
     */
    protected $_aFilter = array();

    /**
     * 注册SQL语句
     * @var array
     */
    protected $_aRegSQLs = array(
        'tablename' => array(
            'lp_list_most_cheap_5' => array(
                'sSQL' => 'select iAutoID from iStatus=:iStatus order by iPrice desc',
                'iType' => self::SQL_FETCH_TYPE_LIST
            )
        )
    );

    /**
     * 查询获取数据类型-一列
     * @var int
     */
    const SQL_FETCH_TYPE_COLUMN = 1;

    /**
     * 查询获取数据类型-一行
     * @var int
     */
    const SQL_FETCH_TYPE_ROW = 2;

    /**
     * 查询获取数据类型-多行
     * @var int
     */
    const SQL_FETCH_TYPE_LIST = 3;

    /**
     * 创建实例
     * @param string $p_sTblName
     * @param boolean $p_bStrictMaster
     */
    function __construct($p_sTblName, $p_bStrictMaster = false){
        $this->_sClassName = get_class($this);
        self::$_oDebug = sys_debugger::getInstance();
        self::$_bolDebug = self::$_oDebug->canDebug();
        $this->_sTblName = $p_sTblName;
        $this->_getTblField();
        if($p_bStrictMaster){
            $this->_sSlaveDBName = $this->_sMasterDBName;
        }
    }

    /**
     * 析构实例
     */
    function __destruct(){
        self::$_oDebug->showMsg($this->_sClassName . '[ORM]->Query time: ' . self::$_iQueryCnt . '. Cache time: ' . self::$_iCacheCnt);
    }

    function __set($p_sField, $p_mValue){
        if(isset($this->_aTblField[$p_sField])){
            $o_sOperator = $o_iParam = '';
            if(!self::_isSelfOperate($p_sField, $p_mValue, $o_sOperator, $o_iParam)){
                if('i' == $this->_aTblField[$p_sField]['sType'] or 'f' == $this->_aTblField[$p_sField]['sType']){
                    if(!is_numeric($p_mValue)){
                        if($this->_aTblField[$p_sField]['bNullable'] and '' == $p_mValue){}else{
                            throw new Exception('You set an illegal attribute(' . $p_sField . ') to ORM instance.Needed is number, maybe is ' . gettype($p_mValue) . '(\'' . print_r($p_mValue, true) . '\').');
                            return false;
                        }
                    }
                }
                if(isset($this->_aTblField[$p_sField]['iLength']) and 0 < $this->_aTblField[$p_sField]['iLength']){
                    if('s' == $this->_aTblField[$p_sField]['sType']){
                        $iLength = mb_strlen($p_mValue);
                    }else{
                        $iLength = strlen($p_mValue);
                    }
                    if($iLength > $this->_aTblField[$p_sField]['iLength']){
                        throw new Exception('You set an attribute(' . $p_sField . ') out of DB settings to ORM instance.Max is ' . $this->_aTblField[$p_sField]['iLength'] . ', actually is ' . $iLength . '.');
                        return false;
                    }
                }
            }
            $this->_aData[$p_sField] = $p_mValue;
        }else{
            throw new Exception($this->_sClassName . ': you set an unexpected attribute(' . $p_sField . ') to ORM instance.');
            return false;
        }
    }

    function __get($p_sField){
        if(isset($this->_aTblField[$p_sField])){
            if(isset($this->_aData[$p_sField])){
                return $this->_aData[$p_sField];
            }else{
                return null;
            }
        }else{
            throw new Exception($this->_sClassName . ': you get an unexpected attribute(' . $p_sField . ') from ORM instance.');
            return false;
        }
    }

    function __isset($p_sField){
        return isset($this->_aData[$p_sField]);
    }

    function __unset($p_sField){
        unset($this->_aData[$p_sField]);
    }

    /**
     * 得到所有执行的SQL语句
     * @return array;
     */
    static function getAllSQL(){
        return self::$_aSQLs;
    }

    /**
     * 返回数据库操作次数
     * @return int
     */
    static function getQueryCnt(){
        return self::$_iQueryCnt;
    }

    /**
     * 返回缓存操作次数
     * @return int
     */
    static function getCacheCnt(){
        return self::$_iCacheCnt;
    }

    /**
     * 设置排序
     * @param string $p_sOrder
     */
    function setOrder($p_sOrder){
        $this->_sOrder = $p_sOrder;
    }

    /**
     * 设置开始行数
     * @param int $p_iStart
     */
    function setStart($p_iStart){
        $this->_iStartRow = $p_iStart;
    }

    /**
     * 设置获取行数
     * @param int $p_iRow
     */
    function setRow($p_iRow){
        $this->_iFetchRow = $p_iRow;
    }

    /**
     * 添加过滤器
     * @param string $p_sField
     * @param string $p_sOperator
     * @param value $p_mValue
     */
    function addFilter($p_sField, $p_sOperator, $p_mValue){
        $p_sOperator = trim($p_sOperator);
        if(isset($this->_aTblField[$p_sField])){
            if(in_array($p_sOperator, array(
                '=',
                '!=',
                '<',
                '>',
                '<=',
                '>=',
                'in',
                'like'
            ))){
                $this->_aFilter[] = array(
                    'sField' => $p_sField,
                    'sOperator' => $p_sOperator,
                    'mValue' => $p_mValue
                );
            }else{
                throw new Exception($this->_sClassName . ': you use an unexpected operator(' . $p_sOperator . ') of ORM instance.');
                return false;
            }
        }else{
            throw new Exception($this->_sClassName . ': you add an unexpected filter(' . $p_sField . ') to ORM instance.');
            return false;
        }
    }

    /**
     * 清除过滤器
     * @param string $p_sField
     */
    function delFilter($p_sField){
        foreach ($this->_aFilter as $key => $value) {
            if ($value['sField'] == $p_sField) {
                unset($this->_aFilter[$key]);
                return;
            }
        }
    }

    /**
     * 得到ORM的所有数据
     * @return array
     */
    function getSource(){
        return $this->_aData;
    }

    /**
     * ORM从数组加载数据
     * @param array $p_aData
     */
    function loadSource($p_aData){
        foreach($p_aData as $sField => $mValue){
            if(null !== $mValue){
                $this->$sField = $mValue;
            }
        }
    }

    /**
     * 设置ORM缓存时间
     * @param int $p_iSecond
     */
    function setCacheTime($p_iSecond){
        $this->_iDataCacheTime = $p_iSecond;
    }

    /**
     * 设置ORM缓存内容是否压缩
     * @param int $p_iCompress
     */
    function setCacheCompress($p_iCompress){
        $this->_iDataCacheCompress = $p_iCompress;
    }

    /**
     * 关闭缓存功能
     */
    function disableCache(){
        $this->_bolNeedCache = false;
    }

    /**
     * 修改缓存ORMClassName
     */
    function setORMClassName($p_sClassName = false){
        $this->_sClassName = $p_sClassName;
    }

    /**
     * 根据主键删除ORM单行缓存
     * @param int $p_iPKID
     * @return true/false
     */
    function clearRowCache($p_iPKID){
        return self::_clearCacheData(self::_getCacheRowKey($this->_sClassName, $this->_dispatchTable($this->_sTblName), $this->_sPKIDField, $p_iPKID));
    }

    /**
     * 添加数据
     * @return int/false
     */
    function addData(){
        $aSQLParam = self::_joinAddString($this->_aTblField, $this->_aData);
        $sSQL = 'insert into ' . $this->_dispatchTable($this->_sTblName) . ' (' . $aSQLParam['sFieldStr'] . ')values(' . $aSQLParam['sParamStr'] . ')';
        return self::_insertDBData($sSQL, $aSQLParam['aValue']);
    }

    /**
     * 更新数据
     * @return int
     */
    function updData(){
        $aNewData = $this->getSource();
        $aOldData = $this->getRow();
        foreach($aNewData as $sField => $sValue){
            if($sField != $this->_sPKIDField and $sValue == $aOldData[$sField]){
                unset($aNewData[$sField]);
            }
        }
        if(1 == count($aNewData)){
            return 0;
        }
        $aSQLParam = self::_joinUpdString($this->_aTblField, $aNewData, $this->_sPKIDField);
        $aPKParam = self::_joinPKWhereString($this->_sPKIDField, $aNewData);
        $sSQL = 'update ' . $this->_dispatchTable($this->_sTblName) . ' set ' . $aSQLParam['sFieldStr'] . ' where ' . $aPKParam['sFieldStr'];
        $aSQLParam['aValue'] = array_merge($aSQLParam['aValue'], $aPKParam['aValue']);
        if(count($this->_aFilter) > 0){
            $aWhereParam = self::_joinWhereString($this->_aFilter);
            $sSQL .= ' and ' . $aWhereParam['sFieldStr'];
            $aSQLParam['aValue'] = array_merge($aSQLParam['aValue'], $aWhereParam['aValue']);
        }
        $this->clearRowCache($aPKParam['aValue'][$this->_sPKIDField]);
        return self::_updDBData($sSQL, $aSQLParam['aValue']);
    }

    /**
     * 删除数据
     * @return int
     */
    function delData(){
        $aPKParam = self::_joinPKWhereString($this->_sPKIDField, $this->_aData);
        $sSQL = 'delete from ' . $this->_dispatchTable($this->_sTblName) . ' where ' . $aPKParam['sFieldStr'];
        $this->clearRowCache($aPKParam['aValue'][$this->_sPKIDField]);
        return $this->_updDBData($sSQL, $aPKParam['aValue']);
    }

    /**
     * 获取一行数据
     * @param boolean $p_bStrictFreshCache
     * @return array/null
     */
    function getRow($p_bStrictFreshCache = false){
        $aPKParam = self::_joinPKWhereString($this->_sPKIDField, $this->_aData);
        $sCacheKey = self::_getCacheRowKey($this->_sClassName, $this->_dispatchTable($this->_sTblName), $this->_sPKIDField, $aPKParam['aValue'][$this->_sPKIDField]);
        if($p_bStrictFreshCache or !$this->_bolNeedCache){
            $aData = false;
        }else{
            $aData = $this->_getCacheData($sCacheKey);
        }
        if(false === $aData){
            $sSQL = 'select ' . self::_joinSelectString($this->_aTblField) . ' from ' . $this->_dispatchTable($this->_sTblName) . ' where ' . $aPKParam['sFieldStr'];
            $aData = $this->_getDBData($sSQL, $aPKParam['aValue'], 2);
            if(null === $aData){
                return null;
            }
            $this->_aData = $aData;
            $this->_setCacheData($sCacheKey, $aData);
        }else{
            $this->_aData = $aData;
        }
        return $this->_aData;
    }

    /**
     * 获取多行数据
     * @param boolean $p_bStrictFreshCache
     * @return array
     */
    function getList($p_bStrictFreshCache = false){
        $sSQL = 'select ' . $this->_sPKIDField . ' from ' . $this->_dispatchTable($this->_sTblName);
        $aWhereParam = self::_joinWhereString($this->_aFilter);
        $sSQL .= ' where ' . $aWhereParam['sFieldStr'];
        if('' != $this->_sOrder){
            $sSQL .= ' order by ' . $this->_sOrder;
            $this->_sOrder = '';
        }
        if($this->_iFetchRow > 0){
            if($this->_iStartRow > 0){
                $sSQL .= ' limit ' . $this->_iStartRow . ',' . $this->_iFetchRow;
                $this->_iStartRow = 0;
            }else{
                $sSQL .= ' limit ' . $this->_iFetchRow;
            }
            $this->_iFetchRow = 0;
        }
        $aPKIDs = $this->_getDBData($sSQL, $aWhereParam['aValue'], 3);
        if(empty($aPKIDs)){
            return array();
        }
        $iCnt = count($aPKIDs);
        if(0 < $iCnt){
            return $this->getListByPKIDs($aPKIDs, $p_bStrictFreshCache);
        }else{
            return array();
        }
    }

    /**
     * 得到统计数据
     * @param boolean $p_bStrictFreshCache
     * @return int
     */
    function getCnt($p_bStrictFreshCache = false){
        $sSQL = 'select count(*) as cnt from ' . $this->_dispatchTable($this->_sTblName);
        $aWhereParam = self::_joinWhereString($this->_aFilter);
        $sSQL .= ' where ' . $aWhereParam['sFieldStr'];
        return $this->_getDBData($sSQL, $aWhereParam['aValue'], 1);
    }

    /**
     * 根据PKID获取数据
     * @param mix $p_mIDs
     * @param boolean $p_bStrictFreshCache
     * @return array
     */
    function getListByPKIDs($p_mIDs, $p_bStrictFreshCache = false){
        $aPKIDs = self::_rebuildPKIDs($p_mIDs, $this->_sPKIDField);
        if(empty($aPKIDs)){
            return array();
        }
        $aRS = array();
        $iCntPKIDs = count($aPKIDs);
        if($this->_bolNeedCache and !$p_bStrictFreshCache){
            $aCacheKey = array();
            $aCacheRS = array();
            for($i = 0; $i < $iCntPKIDs; ++$i){
                $aCacheKey[] = self::_getCacheRowKey($this->_sClassName, $this->_dispatchTable($this->_sTblName), $this->_sPKIDField, $aPKIDs[$i]);
            }
            $aCacheRS = $this->_getCacheData($aCacheKey);
            $aCacheMissIDs = array();
            for($i = 0; $i < $iCntPKIDs; ++$i){
                if(isset($aCacheRS[$aCacheKey[$i]])){}else{
                    $aCacheMissIDs[] = $aPKIDs[$i];
                }
            }
            $iCacheMissIDsCnt = count($aCacheMissIDs);
            if(0 < $iCacheMissIDsCnt){
                $aPKIDsPattern = '';
                $aParam = array();
                for($i = 0; $i < $iCacheMissIDsCnt; ++$i){
                    $aPKIDsPattern[] = self::$_sBindHolder . $this->_sPKIDField . '_' . $i;
                    $aParam[$this->_sPKIDField . '_' . $i] = $aCacheMissIDs[$i];
                }
                $sSQL = 'select ' . self::_joinSelectString($this->_aTblField) . ' from ' . $this->_dispatchTable($this->_sTblName) . ' where ' . $this->_sPKIDField . ' in (' . join(' ,', $aPKIDsPattern) . ')';
                $aDBData = $this->_getDBData($sSQL, $aParam, 3);
                $iCntDBData = count($aDBData);
                $aNeedCacheData = array();
                for($i = 0; $i < $iCntDBData; ++$i){
                    $aNeedCacheData[self::_getCacheRowKey($this->_sClassName, $this->_dispatchTable($this->_sTblName), $this->_sPKIDField, $aDBData[$i][$this->_sPKIDField])] = $aDBData[$i];
                }
                $this->_setCacheDataMulti($aNeedCacheData);
            }
            for($i = 0; $i < $iCntPKIDs; ++$i){
                if(!empty($aCacheRS)){
                    foreach($aCacheRS as $aCacheData){
                        if($aPKIDs[$i] == $aCacheData[$this->_sPKIDField]){
                            $aRS[$i] = $aCacheData;
                            continue 2;
                        }
                    }
                }
                for($k = 0; $k < $iCacheMissIDsCnt; ++$k){
                    if($aPKIDs[$i] == @$aDBData[$k][$this->_sPKIDField]){
                        $aRS[$i] = @$aDBData[$k];
                        continue 2;
                    }
                }
            }
        }else{
            $aPKIDsPattern = '';
            $aParam = array();
            for($i = 0; $i < $iCntPKIDs; ++$i){
                $aPKIDsPattern[] = self::$_sBindHolder . $this->_sPKIDField . '_' . $i;
                $aParam[$this->_sPKIDField . '_' . $i] = $aPKIDs[$i];
            }
            $sSQL = 'select ' . self::_joinSelectString($this->_aTblField) . ' from ' . $this->_dispatchTable($this->_sTblName) . ' where ' . $this->_sPKIDField . ' in (' . join(' ,', $aPKIDsPattern) . ')';
            $aDBData = $this->_getDBData($sSQL, $aParam, 3);
            $iCntDBData = count($aDBData);
            for($i = 0; $i < $iCntDBData; ++$i){
                $this->_setCacheData(self::_getCacheRowKey($this->_sClassName, $this->_dispatchTable($this->_sTblName), $this->_sPKIDField, $aDBData[$i][$this->_sPKIDField]), $aDBData[$i]);
            }
            for($i = 0; $i < $iCntPKIDs; ++$i){
                for($k = 0; $k < $iCntDBData; ++$k){
                    if($aPKIDs[$i] == $aDBData[$k][$this->_sPKIDField]){
                        $aRS[$i] = $aDBData[$k];
                        continue 2;
                    }
                }
            }
        }
        return $aRS;
    }

    /**
     * 根据PKID更新数据
     * @param mix $p_mIDs
     * @return int
     */
    function updListByPKIDs($p_mIDs){
        $aPKIDs = self::_rebuildPKIDs($p_mIDs, $this->_sPKIDField);
        if(empty($aPKIDs)){
            return array();
        }
        $iCnt = count($aPKIDs);
        $aSQLParam = self::_joinUpdString($this->_aTblField, $this->_aData, $this->_sPKIDField);
        $aPKIDsPattern = '';
        $aParam = array();
        for($i = 0; $i < $iCnt; ++$i){
            $this->clearRowCache($aPKIDs[$i]);
            $aPKIDsPattern[] = self::$_sBindHolder . $this->_sPKIDField . '_' . $i;
            $aParam[$this->_sPKIDField . '_' . $i] = $aPKIDs[$i];
        }
        $sSQL = 'update ' . $this->_dispatchTable($this->_sTblName) . ' set ' . $aSQLParam['sFieldStr'] . ' where ' . $this->_sPKIDField . ' in (' . join(' ,', $aPKIDsPattern) . ')';
        return $this->_updDBData($sSQL, array_merge($aSQLParam['aValue'], $aParam));
    }

    /**
     * 执行SQL
     * @param string $p_sSQLName
     * @param array $p_aParam
     * @return array/string
     */
    function executeSQL($p_sSQLName, $p_aParam = array()){
        if(isset($this->_aRegSQLs[$this->_sTblName][$p_sSQLName])){
            $aRegSQL = $this->_aRegSQLs[$this->_sTblName][$p_sSQLName];
            $sSQL = 'select ' . $aRegSQL['sField'] . ' from ' . $this->_dispatchTable($this->_sTblName) . ' where ' . $aRegSQL['sWhere'];
            return $this->_getDBData($sSQL, $p_aParam, $aRegSQL['iType']);
        }else{
            throw new Exception('Invalid SQL name.');
            return false;
        }
    }

    /**
     * 执行原生SQL
     * @param string $p_sSQL
     * @param array $p_aParam
     * @return array
     */
    public function querySQL($p_sSQL, $p_aParam = array()){
        return $this->_getDBData($p_sSQL, $p_aParam, self::SQL_FETCH_TYPE_LIST);
    }

    /**
     * 开始一个事务
     *
     * @author wanglong <wanglong@pinganfang.com>
     * @return void
     */
    public function begin(){
        $sDBName = $this->_dispatchDB($this->_sMasterDBName);
        self::_connectDB($sDBName);
        self::$_aDBPool[$sDBName]->beginTransaction();
    }

    /**
     * 提交事务
     *
     * @author wanglong <wanglong@pinganfang.com>
     * @return void
     */
    public function commit(){
        $sDBName = $this->_dispatchDB($this->_sMasterDBName);
        self::_connectDB($sDBName);
        self::$_aDBPool[$sDBName]->commit();
    }

    /**
     * 回滚事务
     *
     * @author wanglong <wanglong@pinganfang.com>
     * @return void
     */
    public function rollback(){
        $sDBName = $this->_dispatchDB($this->_sMasterDBName);
        self::_connectDB($sDBName);
        self::$_aDBPool[$sDBName]->rollback();
    }

    /**
     * 返回数据库表结构
     */
    private function _getTblField(){
        $sTblName = $this->_dispatchTable($this->_sTblName);
        $sTblFieldCacheKey = self::_getTblFieldCacheKey($this->_sClassName, $sTblName);
        $aTblField = $this->_getCacheData($sTblFieldCacheKey);
        if(false === $aTblField){
            $aTblField = self::_getDBData('desc ' . $sTblName, array(), 3);
            self::_setCacheData($sTblFieldCacheKey, $aTblField, 7);
        }
        foreach($aTblField as $aFieldSet){
            $aTmpFieldInfo = self::_field2ORMField($aFieldSet['Type']);
            $this->_aTblField[$aFieldSet['Field']] = array(
                'sType' => $aTmpFieldInfo['sType'],
                'sPDOType' => $aTmpFieldInfo['sPDOType'],
                'iLength' => isset($aTmpFieldInfo['iLength']) ? $aTmpFieldInfo['iLength'] : 0,
                'bNullable' => ('YES' == $aFieldSet['Null'] ? true : false)
            );
            if('PRI' == $aFieldSet['Key']){
                if(null === $this->_sPKIDField){
                    $this->_sPKIDField = $aFieldSet['Field'];
                }else{
                    throw new Exception('Table(' . $sTblName . ') is not accessible for ORM(Too many primary key).');
                    return false;
                }
            }
        }
        if(!isset($this->_sPKIDField[0])){
            throw new Exception('Table(' . $sTblName . ') is not accessible for ORM(No primary key).');
            return false;
        }
    }

    /**
     * 获取缓存连接
     */
    private static function _connectCache(){
        if(null == self::$_oCache){
            self::$_oCache = cache_pooling::getInstance()->getCache('orm');
        }
    }

    /**
     * 获取数据库连接
     * @param string 数据库连接名
     */
    protected static function _connectDB($p_sDBName){
        if(isset(self::$_aDBPool[$p_sDBName])){}else{
            self::$_aDBPool[$p_sDBName] = db_pooling::getInstance()->getConnect($p_sDBName);
        }
    }

    /**
     * 分配DB
     * @param string $p_sDBName
     * @return string
     */
    protected function _dispatchDB($p_sDBName){
        return $p_sDBName;
    }

    /**
     * 分配表
     * @param string $p_sTblName
     * @return string
     */
    protected function _dispatchTable($p_sTblName){
        return $p_sTblName;
    }

    /**
     * 根据Key删除缓存
     * @param string $p_sCacheKey
     * @return true/false
     */
    private function _clearCacheData($p_sCacheKey){
        ++self::$_iCacheCnt;
        $this->_clearStaticCacheData($p_sCacheKey);
        $this->_clearAPCCacheData($p_sCacheKey);
        return $this->_clearMemCacheData($p_sCacheKey);
    }

    /**
     * 根据Key删除静态缓存
     * @param string $p_sCacheKey
     * @return true
     */
    private function _clearStaticCacheData($p_sCacheKey){
        if(isset(self::$_aStaticCache[$p_sCacheKey])){
            unset(self::$_aStaticCache[$p_sCacheKey]);
            self::$_mixDebugResult = true;
        }else{
            self::$_mixDebugResult = false;
        }
        if(self::$_bolDebug){
            self::$_oDebug->showMsg($this->_sClassName . '[StaticCache]->Delete: ' . $p_sCacheKey . '|' . var_export(self::$_mixDebugResult, true));
        }
        return true;
    }

    /**
     * 根据Key删除APC缓存
     * @param string $p_sCacheKey
     */
    private function _clearAPCCacheData($p_sCacheKey){}

    /**
     * 根据Key删除Memcache
     * @param string $p_sCacheKey
     * @return true/false
     */
    private function _clearMemCacheData($p_sCacheKey){
        self::_connectCache();
        for($i = 0; $i < 5; ++$i){
            self::$_mixDebugResult = self::$_oCache->delete($p_sCacheKey);
            if(true === self::$_mixDebugResult){
                break;
            }
        }
        if(self::$_bolDebug){
            self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Delete: ' . $p_sCacheKey . '|' . var_export(self::$_mixDebugResult, true));
        }
        return self::$_mixDebugResult;
    }

    /**
     * 写入缓存数据
     * @param array $p_aCache
     * @param int $p_iDeepLevel 1-static, 2-apc, 4-memcache
     */
    private function _setCacheDataMulti($p_aCache, $p_iDeepLevel = 1){
        ++self::$_iCacheCnt;
        if(0x04 === ($p_iDeepLevel & 0x04)){
            $this->_setStaticCacheDataMulti($p_aCache);
        }
        if(0x02 === ($p_iDeepLevel & 0x02)){
            $this->_setAPCCacheDataMulti($p_aCache);
        }
        if(0x01 === ($p_iDeepLevel & 0x01)){
            $this->_setMemCacheDataMulti($p_aCache);
        }
    }

    /**
     * 写入静态缓存数据
     * @param array $p_aCache
     * @param mix $p_mData
     */
    private function _setStaticCacheDataMulti($p_aCache){
        if(self::$_bolDebug){
            foreach($p_aCache as $sKey => $mValue){
                self::$_aStaticCache[$sKey] = $mValue;
                self::$_oDebug->showMsg($this->_sClassName . '[StaticCache]->Set: ' . $sKey . '|true');
            }
        }else{
            foreach($p_aCache as $sKey => $mValue){
                self::$_aStaticCache[$sKey] = $mValue;
            }
        }
    }

    /**
     * 写入APC缓存数据
     * @param array $p_aCache
     * @param mix $p_mData
     */
    private function _setAPCCacheDataMulti($p_aCache){}

    /**
     * 写入Memcache缓存数据
     * @param array $p_aCache
     * @param mix $p_mData
     */
    private function _setMemCacheDataMulti($p_aCache){
        self::_connectCache();
        if(self::$_bolDebug){
            foreach($p_aCache as $sKey => $mValue){
                $p_aCache[$sKey] = self::_implodeCache($mValue, $this->_iDataCacheTime);
            }
            for($i = 0; $i < 5; ++$i){
                self::$_mixDebugResult = self::$_oCache->setMulti($p_aCache, $this->_iDataCacheTime);
                if(true === self::$_mixDebugResult){
                    break;
                }
            }
            self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Set: Multi Key|' . var_export($p_aCache, true) . '|' . var_export(self::$_mixDebugResult, true));
            if(false !== self::$_mixDebugResult){
                self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Info: Key=>Multi Key Create=>' . date('Y-m-d H:i:s', $p_aCache[$sKey]['iCreateTime']) . ' Expire=>' . (0 == $p_aCache[$sKey]['iLifeTime'] ? 'unlimit' : date('Y-m-d H:i:s', $p_aCache[$sKey]['iCreateTime'] + $p_aCache[$sKey]['iLifeTime'])));
            }
        }else{
            foreach($p_aCache as $sKey => $mValue){
                $p_aCache[$sKey] = self::_implodeCache($mValue, $this->_iDataCacheTime);
            }
            for($i = 0; $i < 5; ++$i){
                self::$_mixDebugResult = self::$_oCache->setMulti($p_aCache, $this->_iDataCacheTime);
                if(true === self::$_mixDebugResult){
                    break;
                }
            }
        }
    }

    /**
     * 写入缓存数据
     * @param string $p_sKey
     * @param mix $p_mData
     * @param int $p_iDeepLevel 1-static, 2-apc, 4-memcache
     */
    private function _setCacheData($p_sCacheKey, $p_mData, $p_iDeepLevel = 1){
        ++self::$_iCacheCnt;
        if(0x04 === ($p_iDeepLevel & 0x04)){
            $this->_setStaticCacheData($p_sCacheKey, $p_mData);
        }
        if(0x02 === ($p_iDeepLevel & 0x02)){
            $this->_setAPCCacheData($p_sCacheKey, $p_mData);
        }
        if(0x01 === ($p_iDeepLevel & 0x01)){
            $this->_setMemCacheData($p_sCacheKey, $p_mData);
        }
    }

    /**
     * 写入静态缓存数据
     * @param string $p_sCacheKey
     * @param mix $p_mData
     */
    private function _setStaticCacheData($p_sCacheKey, $p_mData){
        self::$_aStaticCache[$p_sCacheKey] = $p_mData;
        if(self::$_bolDebug){
            self::$_oDebug->showMsg($this->_sClassName . '[StaticCache]->Set: ' . $p_sCacheKey . '|true');
        }
    }

    /**
     * 写入APC缓存数据
     * @param string $p_sCacheKey
     * @param mix $p_mData
     */
    private function _setAPCCacheData($p_sCacheKey, $p_mData){}

    /**
     * 写入Memcache缓存数据
     * @param string $p_sCacheKey
     * @param mix $p_mData
     * @todo 批量缓存
     */
    private function _setMemCacheData($p_sCacheKey, $p_mData){
        self::_connectCache();
        $aCacheData = self::_implodeCache($p_mData, $this->_iDataCacheTime);
        for($i = 0; $i < 5; ++$i){
            self::$_mixDebugResult = self::$_oCache->set($p_sCacheKey, $aCacheData, $this->_iDataCacheTime);
            if(true === self::$_mixDebugResult){
                break;
            }
        }
        if(self::$_bolDebug){
            self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Set: ' . $p_sCacheKey . '|' . var_export($p_mData, true) . '|' . var_export(self::$_mixDebugResult, true));
            if(false !== self::$_mixDebugResult){
                self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Info: Key=>' . $p_sCacheKey . ' Create=>' . date('Y-m-d H:i:s', $aCacheData['iCreateTime']) . ' Expire=>' . (0 == $aCacheData['iLifeTime'] ? 'unlimit' : date('Y-m-d H:i:s', $aCacheData['iCreateTime'] + $aCacheData['iLifeTime'])));
            }
        }
    }

    /**
     * 获取缓存数据
     * @param mix $p_mCacheKey
     * @return mix
     */
    private function _getCacheData($p_mCacheKey){
        ++self::$_iCacheCnt;
        if(is_array($p_mCacheKey)){
            $iResultType = 1; //数组
        }else{
            $iResultType = 2; //单个
            $p_mCacheKey = array(
                $p_mCacheKey
            );
        }
        $iCnt = count($p_mCacheKey);
        $aMissKey = array();
        $mData = array();
        if(self::$_bolDebug){
            foreach($p_mCacheKey as $sCacheKey){
                if(isset(self::$_aStaticCache[$sCacheKey])){
                    $mData[$sCacheKey] = self::$_aStaticCache[$sCacheKey];
                    self::$_oDebug->showMsg($this->_sClassName . '[StaticCache]->Get: ' . $sCacheKey . '|' . var_export(self::$_aStaticCache[$sCacheKey], true));
                }else{
                    $aMissKey[] = $sCacheKey;
                    self::$_oDebug->showMsg($this->_sClassName . '[StaticCache]->Get: ' . $sCacheKey . '|false');
                }
            }
        }else{
            foreach($p_mCacheKey as $sCacheKey){
                if(isset(self::$_aStaticCache[$sCacheKey])){
                    $mData[$sCacheKey] = self::$_aStaticCache[$sCacheKey];
                }else{
                    $aMissKey[] = $sCacheKey;
                }
            }
        }
        if(empty($aMissKey)){
            if(1 == $iResultType){
                return $mData;
            }else{
                return $mData[$p_mCacheKey[0]];
            }
        }
        $iCnt = count($aMissKey);
        self::_connectCache();
        $aCacheData = self::$_oCache->getMulti($aMissKey);
        if(self::$_bolDebug){
            foreach($aMissKey as $sCacheKey){
                if(isset($aCacheData[$sCacheKey])){
                    $aEachCacheData = $aCacheData[$sCacheKey];
                    $mData[$sCacheKey] = $aEachCacheData['mData'];
                    self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Get: ' . $sCacheKey . '|' . var_export($aEachCacheData['mData'], true));
                    self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Info: Key=>' . $sCacheKey . ' Create=>' . date('Y-m-d H:i:s', $aEachCacheData['iCreateTime']) . ' Expire=>' . (0 == $aEachCacheData['iLifeTime'] ? 'unlimit' : date('Y-m-d H:i:s', $aEachCacheData['iCreateTime'] + $aEachCacheData['iLifeTime'])));
                    $this->_setStaticCacheData($sCacheKey, $aEachCacheData['mData']);
                }else{
                    self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Get: ' . $sCacheKey . '|false');
                }
            }
        }else{
            foreach($aMissKey as $sCacheKey){
                if(isset($aCacheData[$sCacheKey]) and false !== $aCacheData[$sCacheKey]){
                    $aEachCacheData = $aCacheData[$sCacheKey];
                    $mData[$sCacheKey] = $aEachCacheData['mData'];
                    $this->_setStaticCacheData($sCacheKey, $aEachCacheData['mData']);
                }
            }
        }
        if(0 == count($mData)){
            return false;
        }else{
            if(1 == $iResultType){
                return $mData;
            }else{
                return $mData[$p_mCacheKey[0]];
            }
        }
    }

    /**
     * 获取数据库数据
     * @param string $p_sSQL
     * @param array $p_aParam
     * @param int $p_iType
     * @return array/string
     */
    protected function _getDBData($p_sSQL, $p_aParam, $p_iType){
        $sDBName = $this->_dispatchDB($this->_sSlaveDBName);
        self::_connectDB($sDBName);
        self::$_aSQLs[] = $p_sSQL;
        self::$_oDBSTMT = self::$_aDBPool[$sDBName]->prepare($p_sSQL);
        self::_bindData(self::_parseParameter($p_aParam, $this->_aTblField));
        self::$_oDBSTMT->execute();
        ++self::$_iQueryCnt;
        self::$_oDBSTMT->setFetchMode(PDO::FETCH_ASSOC);
        switch($p_iType){
            case self::SQL_FETCH_TYPE_COLUMN:
                $mData = self::$_oDBSTMT->fetchColumn();
                break;
            case self::SQL_FETCH_TYPE_ROW:
                $mData = self::$_oDBSTMT->fetch();
                if(false === $mData){
                    $mData = null;
                }
                break;
            case self::SQL_FETCH_TYPE_LIST:
                $mData = self::$_oDBSTMT->fetchAll();
                break;
        }
        if(self::$_bolDebug){
            self::$_oDebug->showMsg($this->_sClassName . '[' . $sDBName . ']->Execute: ' . $p_sSQL);
            self::$_oDebug->showMsg($this->_sClassName . '[' . $sDBName . ']->Parameter: ' . print_r($p_aParam, true));
            self::$_oDebug->showMsg($this->_sClassName . '[' . $sDBName . ']->Result: ' . var_export($mData, true));
        }
        return $mData;
    }

    /**
     * 更新数据库数据
     * @param string $p_sSQL
     * @param array $p_aParam
     * @return int
     */
    private function _updDBData($p_sSQL, $p_aParam){
        $sDBName = $this->_dispatchDB($this->_sMasterDBName);
        self::_connectDB($sDBName);
        self::$_aSQLs[] = $p_sSQL;
        self::$_oDBSTMT = self::$_aDBPool[$sDBName]->prepare($p_sSQL);
        self::_bindData(self::_parseParameter($p_aParam, $this->_aTblField));
        self::$_mixDebugResult = self::$_oDBSTMT->execute();
        ++self::$_iQueryCnt;
        $iLastAffectedCnt = self::$_oDBSTMT->rowCount();
        if(self::$_bolDebug){
            self::$_oDebug->showMsg($this->_sClassName . '[' . $sDBName . ']->Execute: ' . $p_sSQL);
            self::$_oDebug->showMsg($this->_sClassName . '[' . $sDBName . ']->Parameter: ' . print_r($p_aParam, true));
            self::$_oDebug->showMsg($this->_sClassName . '[' . $sDBName . ']->Result: ' . var_export(self::$_mixDebugResult, true) . '.<br />Affected row count: ' . $iLastAffectedCnt, true);
        }
        return $iLastAffectedCnt;
    }

    /**
     * 插入数据库数据
     * @param string $p_sSQL
     * @param array $p_aParam
     * @return int/false
     */
    private function _insertDBData($p_sSQL, $p_aParam){
        /*echo $p_sSQL;
         print_r($p_aParam);
         $iSeqID=self::_getSeqNextValue();
         $aTmp=array();
         preg_match('/insert into '.$this->_sTblName.'\s*\((.*)\)\s*values\s*\((.*)\)/i',$p_sSQL,$aTmp);
         $p_sSQL='insert into '.$this->_sTblName.'('.$this->_sPKIDField.', '.$aTmp[1].')values(:'.$this->_sPKIDField.', '.$aTmp[2].')';
         $p_aParam[$this->_sPKIDField]=$iSeqID;*/
        $sDBName = $this->_dispatchDB($this->_sMasterDBName);
        self::_connectDB($sDBName);
        self::$_aSQLs[] = $p_sSQL;
        self::$_oDBSTMT = self::$_aDBPool[$sDBName]->prepare($p_sSQL);
        self::_bindData(self::_parseParameter($p_aParam, $this->_aTblField));
        self::$_mixDebugResult = self::$_oDBSTMT->execute();
        ++self::$_iQueryCnt;
        $iLastInsertID = self::$_aDBPool[$sDBName]->lastInsertId();
        if(self::$_bolDebug){
            self::$_oDebug->showMsg($this->_sClassName . '[' . $sDBName . ']->Execute: ' . $p_sSQL);
            self::$_oDebug->showMsg($this->_sClassName . '[' . $sDBName . ']->Parameter: ' . print_r($p_aParam, true));
            self::$_oDebug->showMsg($this->_sClassName . '[' . $sDBName . ']->Result: ' . var_export(self::$_mixDebugResult, true) . '.<br />LastID: ' . $iLastInsertID, true);
        }
        return $iLastInsertID;
    }

    /**
     * 获取主键值
     * @todo getSeqNextValue 以后完成
     * @return int
     */
    private function _getSeqNextValue(){
        $sSeqDBName = 'seq_master';
        self::_connectDB($sSeqDBName);
        if(empty(self::$_aSeqTable)){
            $sSeqTblKey = 'seq_tbl';
            $aSeqTable = $this->_getCacheData($sSeqTblKey);
            if(false === $aSeqTable){
                $sSQL = 'show tables';
                $bGetSeq = false;
                self::$_aSQLs[] = $sSQL;
                self::$_oDBSTMT = self::$_aDBPool[$sSeqDBName]->prepare($sSQL);
                self::$_oDBSTMT->execute();
                ++self::$_iQueryCnt;
                self::$_oDBSTMT->setFetchMode(PDO::FETCH_ASSOC);
                $mData = self::$_oDBSTMT->fetchAll();
                if(self::$_bolDebug){
                    self::$_oDebug->showMsg($this->_sClassName . '[' . $sSeqDBName . ']->Execute: ' . $sSQL);
                    self::$_oDebug->showMsg($this->_sClassName . '[' . $sSeqDBName . ']->Result: ' . var_export($mData, true));
                }
                $aSeqTable = array();
                foreach($mData as $aTblName){
                    $aSeqTable[] = $aTblName['Tables_in_seq_db'];
                    if($this->_sTblName == $aTblName['Tables_in_seq_db']){
                        $bGetSeq = true;
                    }
                }
                $this->_setCacheData($sSeqTblKey, $aSeqTable);
            }
        }else{
            foreach(self::$_aSeqTbl as $sTblName){
                if($sTblName == $this->_sTblName){
                    $bGetSeq = true;
                }
            }
        }
        if($bGetSeq){
            $sSQL = 'insert into ' . $this->_sTblName . '(SEQNAME)values("' . $this->_sTblName . '") on duplicate key update SEQVALUE=LAST_INSERT_ID(SEQVALUE+1)';
            self::$_aSQLs[] = $sSQL;
            self::$_oDBSTMT = self::$_aDBPool[$sSeqDBName]->prepare($sSQL);
            self::$_mixDebugResult = self::$_oDBSTMT->execute();
            ++self::$_iQueryCnt;
            $iLastInsertID = self::$_aDBPool[$sSeqDBName]->lastInsertId();
            if(self::$_bolDebug){
                self::$_oDebug->showMsg($this->_sClassName . '[' . $this->_sMasterDBName . ']->Execute: ' . $sSQL);
                self::$_oDebug->showMsg($this->_sClassName . '[' . $this->_sMasterDBName . ']->Parameter: ' . print_r(array(
                    array(
                        'SEQNAME' => $this->_sTblName
                    )
                ), true));
                self::$_oDebug->showMsg($this->_sClassName . '[' . $this->_sMasterDBName . ']->Result: ' . var_export(self::$_mixDebugResult, true) . '.<br />LastID: ' . $iLastInsertID, true);
            }
            return $iLastInsertID;
        }else{
            return 0;
        }
    }

    /**
     * 分析SQL参数
     * @param array $p_aParam
     * @param array $p_aTblFields
     * @return array
     */
    private static function _parseParameter($p_aParam, $p_aTblFields){
        $aParam = array();
        foreach($p_aParam as $sField => $mValue){
            $aField = array();
            preg_match('/([a-zA-Z0-9]+)(\_\d)?/', $sField, $aField);
            $aParam[] = array(
                'sField' => $sField,
                'mValue' => $mValue,
                'sPDOType' => @$p_aTblFields[$aField[1]]['sPDOType'],
                'iLength' => @$p_aTblFields[$aField[1]]['iLength']
            );
        }
        return $aParam;
    }

    /**
     * 绑定变量
     * @param array $p_aParam
     */
    private static function _bindData($p_aParam){
        foreach($p_aParam as $aParam){
            $mValue = $aParam['mValue'];
            self::$_oDBSTMT->bindParam(':' . $aParam['sField'], $mValue, $aParam['sPDOType'], $aParam['iLength']);
            unset($mValue);
        }
    }

    /**
     * mysql字段信息转换为ORM字段信息
     * @param string $p_sField
     * @return array
     */
    private static function _field2ORMField($p_sField){
        $aTmpFieldInfo = array();
        preg_match('/(int|char|varchar|text|float|timestamp)\(?(\d*)\)?(unsigned)?/', $p_sField, $aTmpFieldInfo);
        $aFieldInfo = array();
        if(empty($aTmpFieldInfo)){
            throw new Exception('Unknow field from Mysql(' . $p_sField . ').');
            return false;
        }else{
            switch($aTmpFieldInfo[1]){
                case 'int':
                    $aFieldInfo['sType'] = 'i';
                    $aFieldInfo['iLength'] = $aTmpFieldInfo[2];
                    $aFieldInfo['sPDOType'] = PDO::PARAM_INT;
                    break;
                case 'char':
                    $aFieldInfo['sType'] = 's';
                    $aFieldInfo['iLength'] = $aTmpFieldInfo[2];
                    $aFieldInfo['sPDOType'] = PDO::PARAM_STR;
                    break;
                case 'varchar':
                    $aFieldInfo['sType'] = 's';
                    $aFieldInfo['iLength'] = $aTmpFieldInfo[2];
                    $aFieldInfo['sPDOType'] = PDO::PARAM_STR;
                    break;
                case 'text':
                    $aFieldInfo['sType'] = 's';
                    $aFieldInfo['iLength'] = 20000;
                    $aFieldInfo['sPDOType'] = PDO::PARAM_LOB;
                    break;
                case 'float':
                    $aFieldInfo['sType'] = 'i';
                    $aFieldInfo['iLength'] = $aTmpFieldInfo[2];
                    $aFieldInfo['sPDOType'] = PDO::PARAM_STR;
                    break;
                case 'timestamp':
                    $aFieldInfo['sType'] = 's';
                    $aFieldInfo['iLength'] = 19;
                    $aFieldInfo['sPDOType'] = PDO::PARAM_STR;
                    break;
                default:
                    throw new Exception('Unknow field from Mysql(' . $p_sField . ').');
                    break;
            }
        }
        if(isset($aTmpFieldInfo[3]) and 'unsigned' == $aTmpFieldInfo[3]){
            ++$aFieldInfo['iLength'];
        }
        return $aFieldInfo;
    }

    /**
     * 获取ORM获取数据所需SQL信息
     * @param array $p_aTblFields
     * @return string
     */
    private static function _joinSelectString($p_aTblFields){
        $sFields = '';
        foreach($p_aTblFields as $sField => $aFieldSet){
            $sFields .= ', ' . $sField;
        }
        if(isset($sFields[0])){
            return substr($sFields, 2);
        }else{
            throw new Exception('Invalid ORM.');
            return false;
        }
    }

    /**
     * 获取ORM添加信息所需SQL信息
     * @param array $p_aTblFields
     * @param array $p_aData
     * @return array
     */
    private static function _joinAddString($p_aTblFields, $p_aData){
        $sFields = '';
        $sParams = '';
        $aValues = '';
        foreach($p_aTblFields as $sField => $aFieldSet){
            if(isset($p_aData[$sField])){
                $sFields .= ', ' . $sField;
                $sParams .= ', ' . self::$_sBindHolder . $sField;
                $aValues[$sField] = $p_aData[$sField];
            }
        }
        if(isset($sFields[0])){
            return array(
                'sFieldStr' => substr($sFields, 2),
                'sParamStr' => substr($sParams, 2),
                'aValue' => $aValues
            );
        }else{
            throw new Exception('Invalid ORM');
            return false;
        }
    }

    /**
     * 获取查询条件
     * @param array $p_aFilter
     * @return array
     */
    public static function _joinWhereString($p_aFilter){
        $sFields = '';
        $aValues = array();
        $iIndex = 0;
        $aFields = array();
        foreach($p_aFilter as $aFilter){
            if(!empty($aFilter['sOperator']) && strtolower($aFilter['sOperator']) == 'in'){
                $aPattern = '';
                $iCnt = count($aFilter['mValue']);
                for($i = 0; $i < $iCnt; ++$i){
                    $aPattern[] = self::$_sBindHolder . $aFilter['sField'] . '_' . $i;
                    $aValues[$aFilter['sField'] . '_' . $i] = $aFilter['mValue'][$i];
                }
                $sFields .= ' and ' . $aFilter['sField'] . ' ' . $aFilter['sOperator'] . ' (' . join(',', $aPattern) . ')';
                $aFields[] = $aFilter['sField'];
                continue;
            }
            if(in_array($aFilter['sField'], $aFields)){
                $sFields .= ' and ' . $aFilter['sField'] . ' ' . $aFilter['sOperator'] . ' ' . self::$_sBindHolder . $aFilter['sField'] . '_' . $iIndex;
                $aValues[$aFilter['sField'] . '_' . $iIndex] = $aFilter['mValue'];
                $iIndex++;
            }else{
                $sFields .= ' and ' . $aFilter['sField'] . ' ' . $aFilter['sOperator'] . ' ' . self::$_sBindHolder . $aFilter['sField'];
                $aValues[$aFilter['sField']] = $aFilter['mValue'];
            }
            $aFields[] = $aFilter['sField'];
        }

        if(isset($sFields[0])){
            return array(
                'sFieldStr' => substr($sFields, 5),
                'aValue' => $aValues
            );
        }else{
            throw new Exception('ORM do not allowed to get all data.');
            return false;
        }
    }

    /**
     * 获取ORM更新信息所需SQL信息
     * @param array $p_aTblFields
     * @param array $p_aData
     * @param string $p_sPKIDField
     * @return array
     */
    private static function _joinUpdString($p_aTblFields, $p_aData, $p_sPKIDField){
        $sFields = '';
        $aValues = array();
        foreach($p_aTblFields as $sField => $aFieldSet){
            if($p_sPKIDField != $sField and isset($p_aData[$sField])){
                $sSelfOperator = $iSelfParam = '';
                if(self::_isSelfOperate($sField, $p_aData[$sField], $sSelfOperator, $iSelfParam)){
                    $sFields .= ', ' . $sField . '=' . $sField . $sSelfOperator . self::$_sBindHolder . $sField . '_update';
                    $aValues[$sField . '_update'] = $iSelfParam;
                }else{
                    $sFields .= ', ' . $sField . '=' . self::$_sBindHolder . $sField . '_update';
                    $aValues[$sField . '_update'] = $p_aData[$sField];
                }
            }
        }
        if(isset($sFields[0])){
            return array(
                'sFieldStr' => substr($sFields, 2),
                'aValue' => $aValues
            );
        }else{
            throw new Exception('Invalid ORM.');
            return false;
        }
    }

    /**
     * 判断是否为自运算
     * @param string $p_sField
     * @param mix $p_mValue
     * @param string $o_sOperator
     * @param int $o_iParam
     * @return true/false
     */
    private static function _isSelfOperate($p_sField, $p_mValue, &$o_sOperator, &$o_iParam){
        $sPattern = '/^' . $p_sField . '([+\-*\/])(\d+)/i';
        $aResult = array();
        if(1 == preg_match($sPattern, $p_mValue, $aResult)){
            $o_sOperator = $aResult[1];
            $o_iParam = $aResult[2];
            return true;
        }else{
            return false;
        }
    }

    /**
     * 重新生成新的主键列表
     * @param array $p_mIDs
     * @return array
     */
    private static function _rebuildPKIDs($p_mIDs, $p_sPKField){
        if(is_array($p_mIDs)){
            if(empty($p_mIDs)){
                return array();
            }else{
                $mPKID = array_pop($p_mIDs);
                if(is_array($mPKID)){
                    $aPKIDs = array();
                    foreach($p_mIDs as $aIDs){
                        $aPKIDs[] = $aIDs[$p_sPKField];
                    }
                    $aPKIDs[] = $mPKID[$p_sPKField];
                }else{
                    $aPKIDs = $p_mIDs;
                    $aPKIDs[] = $mPKID;
                }
                array_unique($aPKIDs);
                ksort($aPKIDs);
                return $aPKIDs;
            }
        }else{
            return array_unique(explode(',', $p_mIDs));
        }
    }

    /**
     * 根据主键数据生成where条件
     * @param string $p_sPKIDField
     * @param array $p_aData
     * @return array
     */
    private static function _joinPKWhereString($p_sPKIDField, $p_aData){
        if(isset($p_aData[$p_sPKIDField])){
            return array(
                'sFieldStr' => $p_sPKIDField . '=' . self::$_sBindHolder . $p_sPKIDField,
                'aValue' => array(
                    $p_sPKIDField => $p_aData[$p_sPKIDField]
                )
            );
        }else{
            throw new Exception('You missed ORM PKID(' . $p_sPKIDField . ').');
            return false;
        }
    }

    /**
     * 获取数据库表结构缓存key
     * @param string $p_sORMName
     * @param string $p_sTblName
     * @return string
     */
    private static function _getTblFieldCacheKey($p_sORMName, $p_sTblName){
        return $p_sORMName . '_tblfield_' . $p_sTblName;
    }

    /**
     * 获取ORM数据缓存Key
     * @param string $p_sORMName
     * @param string $p_sTblName
     * @param string $p_sPKIDField
     * @param int $p_iPKID
     * @return string
     */
    private static function _getCacheRowKey($p_sORMName, $p_sTblName, $p_sPKIDField, $p_iPKID){
        return $p_sORMName . '_r_' . $p_sTblName . '_' . $p_sPKIDField . '_' . $p_iPKID;
    }

    /**
     * 生成cache的数据
     * @param mix $p_mValue
     * @param int $p_iLifeTime
     * @return array
     */
    private static function _implodeCache($p_mValue, $p_iLifeTime){
        return array(
            'mData' => $p_mValue,
            'iCreateTime' => $_SERVER['REQUEST_TIME'],
            'iLifeTime' => $p_iLifeTime
        );
    }
}
