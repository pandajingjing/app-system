<?php

//加载MONGOQB类库
include_once dirname(__FILE__).'/MongoQB/Builder.php';

/**
 * @filename db.php
 * 获取mongodb操作实例
 * 
 * @project hf-code
 * @package system
 * @author Randy Hong <hongmingwei@pinganfang.com>
 * @created at 14-8-14
 */

class mongo_db {

    static $instances = array();

    /**
     * 获取一个mongodb操作实例
     * @param string $p_sKey
     */
    public static function getInstance($p_sKey){
        if(isset(self::$instances[$p_sKey]) && self::$instances[$p_sKey] instanceof \MongoQB\Builder){
            return self::$instances[$p_sKey];
        }
        $aConfig = get_config($p_sKey,'mongo');
        $oMongo = new \MongoQB\Builder($aConfig);
        self::$instances[$p_sKey] = $oMongo;
        return self::$instances[$p_sKey];
    }

}