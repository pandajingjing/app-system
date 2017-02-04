<?php
/**
 * browser client
 * @package system_common_lib_client
 */
load_lib('/client/pooling');
/**
 * browser client
 * @author jxu
 * @package system_common_lib_client
 */
class client_browser
{

    /**
     * 配置数据
     * @var array
     */
    private static $_aOpt = array();

    /**
     * 设置浏览器头部信息
     * @param string $p_sUserAgent
     */
    static function setUserAgent($p_sUserAgent = '')
    {
        if ('' == $p_sUserAgent) {
            self::$_aOpt[CURLOPT_USERAGENT] = get_config('sUserAgent', 'client');
        } else {
            self::$_aOpt[CURLOPT_USERAGENT] = $p_sUserAgent;
        }
    }
    
    /**
     * 设置curl选项参数
     * @param mixed $mKey
     * @param mixed $mVal
     */
    static function setOption($mKey, $mVal)
    {
        self::$_aOpt[$mKey] = $mVal;
    }

    /**
     * 设置浏览器cookie
     * @param array $p_aCookie
     */
    static function setCookie($p_aCookie = array())
    {
        if (empty($p_aCookie)) {
            $p_aCookie = sys_variable::getInstance()->getParams('cookie');
        }
        $sTmp = http_build_query($p_aCookie);
        if (strstr($sTmp, '&')) {
            $sTmp = str_replace('&', ';', $sTmp);
        }
        self::$_aOpt[CURLOPT_COOKIE] = $sTmp;
    }

    /**
     * Get获取数据
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
        if (isset($_COOKIE[Version::VERSION_COOKIE_NAME])) {
            self::setCookie([Version::VERSION_COOKIE_NAME => $_COOKIE[Version::VERSION_COOKIE_NAME]]);
        } else {
            if(isset($_SERVER['HTTP_HOST'])){
                $subDomain = explode('.', $_SERVER['HTTP_HOST'])[0];
                $oVersion = Version::getInstance();
                $oVersion->setConfigFile('/data1/www/other/' . $subDomain . '.version.json');
                $aCurrentVersion = $oVersion->getCurrentVersion();

                $aVersionNeedCookie = [
                    Version::VERSION_TYPE_BETA,
                    Version::VERSION_TYPE_GA
                ];

                if (in_array($aCurrentVersion['type'], $aVersionNeedCookie)) {
                    $sCookie = $oVersion->genCookieValue($aCurrentVersion['version'], $aCurrentVersion['type']);
                    self::setCookie([Version::VERSION_COOKIE_NAME => $sCookie]);
                }
            }
        }

        $oCURL = client_pooling::getInstance()->getClient();
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
            sys_debugger::getInstance()->showMsg('Browser [' . $p_sMethod . '] URL: ' . $p_sURL . '<br />Result: ' . var_export($bResult, true) . '<br />Params: ' . var_export($p_mData, true) . '<br />Data: ' . var_export($mData, true), true);
            return $mData;
        } else {
            sys_debugger::getInstance()->showMsg('Browser [' . $p_sMethod . '] URL: ' . $p_sURL . '<br />Result: ' . var_export($bResult, true) . '<br />Params: ' . var_export($p_mData, true) . '<br />Info: ' . var_export($oCURL->getInfo(), true) . '<br />ErrMsg: ' . var_export($oCURL->getErrMsg(), true) . '<br />ErrNo: ' . var_export($oCURL->getErrNo(), true), true);
            return false;
        }
    }

}
