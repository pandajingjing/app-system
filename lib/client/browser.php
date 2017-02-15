<?php

/**
 * browser client
 * @package system_common_lib_client
 */
/**
 * browser client
 *
 * @author jxu
 * @package system_common_lib_client
 */
class client_browser
{

    /**
     * 配置数据
     *
     * @var array
     */
    private static $_aOpt = [];

    /**
     * 设置浏览器头部信息
     *
     * @param string $p_sUserAgent            
     */
    static function setUserAgent($p_sUserAgent = '')
    {
        if ('' == $p_sUserAgent) {
            self::$_aOpt[CURLOPT_USERAGENT] = lib_sys_var::getInstance()->getConfig('sUserAgent', 'client');
        } else {
            self::$_aOpt[CURLOPT_USERAGENT] = $p_sUserAgent;
        }
    }

    /**
     * 设置curl选项参数
     *
     * @param mixed $mKey            
     * @param mixed $mVal            
     */
    static function setOption($mKey, $mVal)
    {
        self::$_aOpt[$mKey] = $mVal;
    }

    /**
     * 设置浏览器cookie
     *
     * @param array $p_aCookie            
     */
    static function setCookie($p_aCookie = [])
    {
        if (empty($p_aCookie)) {
            $p_aCookie = lib_sys_var::getInstance()->getAllParams('cookie');
        }
        $sTmp = http_build_query($p_aCookie);
        if (strstr($sTmp, '&')) {
            $sTmp = str_replace('&', ';', $sTmp);
        }
        self::$_aOpt[CURLOPT_COOKIE] = $sTmp;
    }

    /**
     * Get获取数据
     *
     * @param string $p_sURL            
     * @param string $p_sResultType            
     * @return mix
     */
    static function getData($p_sURL, $p_sResultType = 'json')
    {
        return self::_fetchData('get', $p_sURL, null, $p_sResultType);
    }

    /**
     * Post获取数据
     *
     * @param string $p_sURL            
     * @param array $p_aData            
     * @param string $p_sResultType            
     * @return mix
     */
    static function postData($p_sURL, $p_aData, $p_sResultType = 'json')
    {
        return self::_fetchData('post', $p_sURL, $p_aData, $p_sResultType);
    }

    /**
     * 获取数据
     *
     * @param string $p_sMethod            
     * @param string $p_sURL            
     * @param mix $p_mData            
     * @param string $p_sResultType            
     * @return mix
     */
    private static function _fetchData($p_sMethod, $p_sURL, $p_mData, $p_sResultType = 'json')
    {
        $oCURL = lib_client_pooling::getInstance()->getClient();
        if ('post' == $p_sMethod) {
            $oCURL->setPost(true);
            $oCURL->setPostParams($p_mData);
        } else {
            $oCURL->setPost(false);
        }
        $oCURL->setURL($p_sURL);
        foreach (self::$_aOpt as $iKey => $mVal) {
            $oCURL->setOption($iKey, $mVal);
        }
        $bResult = $oCURL->executeURL();
        if ($bResult) {
            $sResource = $oCURL->getContent();
            switch ($p_sResultType) {
                case 'json':
                    $mData = json_decode($sResource, true);
                    break;
                case 'string':
                default:
                    $mData = $sResource;
            }
            return $mData;
        } else {
            return false;
        }
    }
}
