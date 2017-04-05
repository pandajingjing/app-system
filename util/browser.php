<?php

/**
 * util_browser
 *
 * 仿浏览器客户端
 *
 * @package lib_client
 */

/**
 * util_browser
 *
 * 仿浏览器客户端
 */
class util_browser
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
     * @return void
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
     * @return void
     */
    static function setOption($mKey, $mVal)
    {
        self::$_aOpt[$mKey] = $mVal;
    }

    /**
     * 设置来源网页
     * 
     * @param string $p_sReferer            
     * @return void
     */
    static function setReferer($p_sReferer)
    {
        self::$_aOpt[CURLOPT_REFERER] = $p_sReferer;
    }

    /**
     * 设置浏览器cookie
     *
     * @param array $p_aCookie            
     * @return void
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
     * @param array $p_aData            
     * @param string $p_sResultType            
     * @return array|string
     */
    private static function _fetchData($p_sMethod, $p_sURL, $p_aData, $p_sResultType = 'json')
    {
        $oCURL = lib_client_pooling::getInstance()->getClient('curl');
        if ('post' == $p_sMethod) {
            $oCURL->setPost(true);
            $oCURL->setPostParams($p_aData);
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

class mycurl
{

    protected $_useragent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1';

    protected $_url;

    protected $_followlocation;

    protected $_timeout;

    protected $_maxRedirects;

    protected $_cookieFileLocation = './cookie.txt';

    protected $_post;

    protected $_postFields;

    protected $_referer = "http://www.google.com";

    protected $_session;

    protected $_webpage;

    protected $_includeHeader;

    protected $_noBody;

    protected $_status;

    protected $_binaryTransfer;

    public $authentication = 0;

    public $auth_name = '';

    public $auth_pass = '';

    public function useAuth($use)
    {
        $this->authentication = 0;
        if ($use == true)
            $this->authentication = 1;
    }

    public function setName($name)
    {
        $this->auth_name = $name;
    }

    public function setPass($pass)
    {
        $this->auth_pass = $pass;
    }

    public function __construct($url, $followlocation = true, $timeOut = 30, $maxRedirecs = 4, $binaryTransfer = false, $includeHeader = false, $noBody = false)
    {
        $this->_url = $url;
        $this->_followlocation = $followlocation;
        $this->_timeout = $timeOut;
        $this->_maxRedirects = $maxRedirecs;
        $this->_noBody = $noBody;
        $this->_includeHeader = $includeHeader;
        $this->_binaryTransfer = $binaryTransfer;
        
        $this->_cookieFileLocation = dirname(__FILE__) . '/cookie.txt';
    }

    public function setReferer($referer)
    {
        $this->_referer = $referer;
    }

    public function setCookiFileLocation($path)
    {
        $this->_cookieFileLocation = $path;
    }

    public function setPost($postFields)
    {
        $this->_post = true;
        $this->_postFields = $postFields;
    }

    public function setUserAgent($userAgent)
    {
        $this->_useragent = $userAgent;
    }

    public function createCurl($url = 'nul')
    {
        if ($url != 'nul') {
            $this->_url = $url;
        }
        
        $s = curl_init();
        
        curl_setopt($s, CURLOPT_URL, $this->_url);
        curl_setopt($s, CURLOPT_HTTPHEADER, array(
            'Expect:'
        ));
        curl_setopt($s, CURLOPT_TIMEOUT, $this->_timeout);
        curl_setopt($s, CURLOPT_MAXREDIRS, $this->_maxRedirects);
        curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($s, CURLOPT_FOLLOWLOCATION, $this->_followlocation);
        curl_setopt($s, CURLOPT_COOKIEJAR, $this->_cookieFileLocation);
        curl_setopt($s, CURLOPT_COOKIEFILE, $this->_cookieFileLocation);
        
        if ($this->authentication == 1) {
            curl_setopt($s, CURLOPT_USERPWD, $this->auth_name . ':' . $this->auth_pass);
        }
        if ($this->_post) {
            curl_setopt($s, CURLOPT_POST, true);
            curl_setopt($s, CURLOPT_POSTFIELDS, $this->_postFields);
        }
        
        if ($this->_includeHeader) {
            curl_setopt($s, CURLOPT_HEADER, true);
        }
        
        if ($this->_noBody) {
            curl_setopt($s, CURLOPT_NOBODY, true);
        }
        /*
         * if($this->_binary)
         * {
         * curl_setopt($s,CURLOPT_BINARYTRANSFER,true);
         * }
         */
        curl_setopt($s, CURLOPT_USERAGENT, $this->_useragent);
        curl_setopt($s, CURLOPT_REFERER, $this->_referer);
        
        $this->_webpage = curl_exec($s);
        $this->_status = curl_getinfo($s, CURLINFO_HTTP_CODE);
        curl_close($s);
    }

    public function getHttpStatus()
    {
        return $this->_status;
    }

    public function __tostring()
    {
        return $this->_webpage;
    }
}