<?php

/**
 * lib_mail_phpmail
 *
 * php邮件
 *
 * @package lib_mail
 */

/**
 * lib_mail_phpmail
 *
 * php邮件
 */
class lib_mail_phpmail
{

    /**
     * 邮件字符集
     *
     * @var string
     */
    private $_sCharset = 'utf-8';

    /**
     * 邮件标题
     *
     * @var string
     */
    private $_sSubject = '';

    /**
     * 发信人
     *
     * @var array
     */
    private $_aFrom = [];

    /**
     * 抄送人
     *
     * @var array
     */
    private $_aCC = [];

    /**
     * 暗送人
     *
     * @var array
     */
    private $_aBCC = [];

    /**
     * 收件人
     *
     * @var array
     */
    private $_aTo = [];

    /**
     * 回信目的地址
     *
     * @var array
     */
    private $_aReplyTo = [];

    /**
     * 退信目的地址
     *
     * @var array
     */
    private $_aReturnTo = [];

    /**
     * 自定义头部信息
     *
     * @var string
     */
    private $_sHeader = '';

    /**
     * 附件
     *
     * @var array
     */
    private $_aAttachs = [];

    /**
     * 邮件内图片
     *
     * @var array
     */
    private $_aMailImages = [];

    /**
     * 是否HTML
     *
     * @var boolean
     */
    private $_bIsHTML = false;

    /**
     * 邮件内容
     *
     * @var string
     */
    private $_sBody = '';

    /**
     * 构造函数
     */
    function __construct()
    {}

    /**
     * 析构函数
     */
    function __destruct()
    {}

    /**
     * 初始化邮件
     */
    function initMail()
    {
        $this->_aTo = [];
        $this->_aCC = [];
        $this->_aBCC = [];
        $this->_sHeader = '';
        $this->_sSubject = '';
        $this->_aAttachs = [];
        $this->_aMailImages = [];
        $this->_bIsHTML = false;
        $this->_sBody = '';
    }

    /**
     * 设置来源邮箱
     *
     * @param string $p_sAddr            
     * @param string $p_sName            
     * @return void
     */
    function setFrom($p_sAddr, $p_sName = '')
    {
        $this->_aFrom = [
            $p_sAddr => $p_sName
        ];
    }

    /**
     * 添加抄送者
     *
     * @param string $p_sAddr            
     * @param string $p_sName            
     * @return void
     */
    function addCC($p_sAddr, $p_sName = '')
    {
        $this->_aCC[$p_sAddr] = $p_sName;
    }

    /**
     * 添加暗送者
     *
     * @param string $p_sAddr            
     * @param string $p_sName            
     * @return void
     */
    function addBCC($p_sAddr, $p_sName = '')
    {
        $this->_aBCC[$p_sAddr] = $p_sName;
    }

    /**
     * 添加收件人
     *
     * @param string $p_sAddr            
     * @param string $p_sName            
     * @return void
     */
    function addTo($p_sAddr, $p_sName = '')
    {
        $this->_aTo[$p_sAddr] = $p_sName;
    }

    /**
     * 设置回复邮箱
     *
     * @param string $p_sAddr            
     * @param string $p_sName            
     * @return void
     */
    function setReplyTo($p_sAddr, $p_sName = '')
    {
        $this->_aReplyTo = [
            $p_sAddr => $p_sName
        ];
    }

    /**
     * 设置退信邮箱
     *
     * @param string $p_sAddr            
     * @param string $p_sName            
     * @return void
     */
    function setReturnTo($p_sAddr, $p_sName = '')
    {
        $this->_aReturnTo = [
            $p_sAddr => $p_sName
        ];
    }

    /**
     * 设置邮件头信息
     *
     * @param string $p_sHeader            
     * @return void
     */
    function setHeader($p_sHeader)
    {
        $this->_sHeader = $p_sHeader;
    }

    /**
     * 设置邮件标题
     *
     * @param string $p_sSubject            
     * @return void
     */
    function setSubject($p_sSubject)
    {
        $this->_sSubject = $p_sSubject;
    }

    /**
     * 设置邮件体
     *
     * @param string $p_sBody            
     * @param boolean $p_bIsHTML            
     * @return void
     */
    function setBody($p_sBody, $p_bIsHTML = false)
    {
        $this->_bIsHTML = $p_bIsHTML;
        $this->_sBody = $p_sBody;
    }

    /**
     * 添加本地附件
     *
     * @param string $p_sName            
     * @param string $p_sPath            
     * @return void
     */
    function addLocalAttachment($p_sName, $p_sPath)
    {
        $oFInfo = finfo_open();
        $sMimeType = finfo_file($oFInfo, $p_sPath, FILEINFO_MIME_TYPE);
        finfo_close($oFInfo);
        $this->addStreamAttachment($p_sName, util_file::tryReadFile($p_sPath), $sMimeType);
    }

    /**
     * 添加数据流附件
     *
     * @param string $p_sName            
     * @param blob $p_oContent            
     * @param string $p_sMimeType            
     * @return void
     */
    function addStreamAttachment($p_sName, $p_oContent, $p_sMimeType)
    {
        $this->_aAttachs[] = [
            'oContent' => $p_oContent,
            'sName' => $p_sName,
            'sMimeType' => $p_sMimeType
        ];
    }

    /**
     * 添加邮件内图片
     *
     * @param string $p_sName            
     * @param string $p_sPath            
     * @return void
     */
    function addBodyImage($p_sName, $p_sPath)
    {
        $oFInfo = finfo_open();
        $sMimeType = finfo_file($oFInfo, $p_sPath, FILEINFO_MIME_TYPE);
        finfo_close($oFInfo);
        $this->addStreamBodyImage($p_sName, util_file::tryReadFile($p_sPath), $sMimeType);
    }

    /**
     * 添加数据流邮件内图片
     *
     * @param string $p_sName            
     * @param blob $p_oContent            
     * @param string $p_sMimeType            
     * @return void
     */
    function addStreamBodyImage($p_sName, $p_oContent, $p_sMimeType)
    {
        $this->_aMailImages[] = array(
            'oContent' => $p_oContent,
            'sName' => $p_sName,
            'sMimeType' => $p_sMimeType,
            'sCID' => md5(uniqid(lib_sys_var::getInstance()->getRealTime()))
        );
    }

    /**
     * 发送邮件
     *
     * @return boolean
     */
    function sendMail()
    {
        $sSubject = '=?' . $this->_sCharset . '?B?' . base64_encode($this->_sSubject) . '?=';
        $sHeader = 'MIME-Version: 1.0' . PHP_EOL;
        $sHeader .= 'From: ' . self::_mkSBody($this->_aFrom, $this->_sCharset) . PHP_EOL;
        if (! empty($this->_aReplyTo)) {
            $sHeader .= 'Reply-To: ' . self::_mkSBody($this->_aReplyTo, $this->_sCharset) . PHP_EOL;
        }
        if (! empty($this->_aReturnTo)) {
            $sHeader .= 'Return-Path: ' . self::_mkSBody($this->_aReturnTo, $this->_sCharset) . PHP_EOL;
        }
        if (! empty($this->_aCC)) {
            $sHeader .= 'CC: ' . self::_mkSBody($this->_aCC, $this->_sCharset) . PHP_EOL;
        }
        if (! empty($this->_sBCC)) {
            $sHeader .= 'BCC: ' . self::_mkSBody($this->_sBCC, $this->_sCharset) . PHP_EOL;
        }
        $sBoundary = '=_' . md5(uniqid(lib_sys_var::getInstance()->getRealTime()));
        $sHeader .= 'Content-Type: multipart/related;charset="' . $this->_sCharset . '"; boundary="' . $sBoundary . '"' . PHP_EOL;
        $sHeader .= $this->_sHeader;
        return mail(self::_mkSBody($this->_aTo, $this->_sCharset), $sSubject, self::_buildMail($sBoundary, $this->_bIsHTML, $this->_sCharset, $this->_sBody, $this->_aAttachs, $this->_aMailImages), $sHeader);
    }

    /**
     * 生成邮件内容
     *
     * @param string $p_sBoundary            
     * @param boolean $p_bIsHTML            
     * @param string $p_sCharset            
     * @param string $p_sBody            
     * @param array $p_aAttachs            
     * @param array $p_aMailImages            
     * @return string
     */
    private static function _buildMail($p_sBoundary, $p_bIsHTML, $p_sCharset, $p_sBody, $p_aAttachs, $p_aMailImages)
    {
        if ($p_bIsHTML) {
            $sMultipart = self::_buildHTML($p_sBoundary, $p_sCharset, $p_sBody, $p_aMailImages);
        } else {
            $sMultipart = self::_buildTxt($p_sBoundary, $p_sCharset, $p_sBody);
            $p_aAttachs[] = [
                'oContent' => $p_sBody,
                'sName' => 'body.txt',
                'sMimeType' => 'text/plain'
            ];
        }
        foreach ($p_aAttachs as $aAttach) {
            $sMultipart .= '--' . $p_sBoundary . PHP_EOL;
            $sMultipart .= self::_buildAttach($aAttach, $p_sCharset);
        }
        $sMultipart .= '--' . $p_sBoundary . '--' . PHP_EOL;
//         debug($sMultipart);
        return $sMultipart;
    }

    /**
     * 组合邮箱信息
     *
     * @param array $p_aSBody            
     * @param string $p_sCharset            
     * @return string
     */
    private static function _mkSBody($p_aSBody, $p_sCharset)
    {
        $aTmp = [];
        foreach ($p_aSBody as $sAddr => $sName) {
            if ('' == $sName) {
                $aTmp[] = $sAddr;
            } else {
                $aTmp[] = '"=?' . $p_sCharset . '?B?' . base64_encode($sName) . '?=" <' . $sAddr . '>';
            }
        }
        return implode(',', $aTmp);
    }

    /**
     * 生成txt内容
     *
     * @param string $p_sOrigBoundary            
     * @param string $p_sCharset            
     * @param string $p_sBody            
     * @return string
     */
    private static function _buildTxt($p_sOrigBoundary, $p_sCharset, $p_sBody)
    {
        $sMultipart = '--' . $p_sOrigBoundary . PHP_EOL;
        $sMultipart .= 'Content-Type: text/plain;charset="' . $p_sCharset . '"' . PHP_EOL;
        $sMultipart .= 'Content-Transfer-Encoding: base64' . PHP_EOL . PHP_EOL;
        $sMultipart .= chunk_split(base64_encode($p_sBody), 76, PHP_EOL) . PHP_EOL;
        return $sMultipart;
    }

    /**
     * 生成HTML内容
     *
     * @param string $p_sOrigBoundary            
     * @param string $p_sCharset            
     * @param string $p_sBody            
     * @param array $p_aMailImages            
     * @return string
     */
    private static function _buildHTML($p_sOrigBoundary, $p_sCharset, $p_sBody, $p_aMailImages = [])
    {
        if (count($p_aMailImages) > 0) {
            $aPatterns = $aReplaces = [];
            foreach ($p_aMailImages as $aImage) {
                $aPatterns[] = '/' . $aImage['sName'] . '/i';
                $aReplaces[] = 'cid:' . $aImage['sCID'];
            }
            $p_sBody = preg_replace($aPatterns, $aReplaces, $p_sBody);
            
            $sMultipart = '--' . $p_sOrigBoundary . PHP_EOL;
            $sMultipart .= 'Content-Type: text/html;charset="' . $p_sCharset . '"' . PHP_EOL;
            $sMultipart .= 'Content-Transfer-Encoding: base64' . PHP_EOL . PHP_EOL;
            $sMultipart .= chunk_split(base64_encode($p_sBody), 76, PHP_EOL) . PHP_EOL;
            foreach ($p_aMailImages as $aImage) {
                $sMultipart .= '--' . $p_sOrigBoundary . PHP_EOL;
                $sMultipart .= self::_buildHTMLImage($aImage);
            }
        } else {
            $sMultipart = '--' . $p_sOrigBoundary . PHP_EOL;
            $sMultipart .= 'Content-Type: text/html;charset="' . $p_sCharset . '"' . PHP_EOL;
            $sMultipart .= 'Content-Transfer-Encoding: base64' . PHP_EOL . PHP_EOL;
            $sMultipart .= chunk_split(base64_encode($p_sBody), 76, PHP_EOL) . PHP_EOL;
        }
        return $sMultipart;
    }

    /**
     * 编译HTML中带的图片
     *
     * @param array $p_aImage            
     * @return string
     */
    private static function _buildHTMLImage($p_aImage)
    {
        $sMultipart = 'Content-Type: ' . $p_aImage['sMimeType'];
        if ($p_aImage['sName'] != '') {
            $sMultipart .= '; name="' . $p_aImage['sName'] . '"' . PHP_EOL;
        } else {
            $sMultipart .= PHP_EOL;
        }
        $sMultipart .= 'Content-ID: <' . $p_aImage['sCID'] . '>' . PHP_EOL;
        $sMultipart .= 'Content-Transfer-Encoding: base64' . PHP_EOL;
        $sMultipart .= 'Content-Disposition: inline; filename="' . $p_aImage['sName'] . '"' . PHP_EOL . PHP_EOL;
        $sMultipart .= chunk_split(base64_encode($p_aImage['oContent']), 76, PHP_EOL) . PHP_EOL;
        return $sMultipart;
    }

    /**
     * 编译附件内容
     *
     * @param array $p_aAttachs            
     * @param string $p_sCharset            
     * @return string
     */
    private static function _buildAttach($p_aAttachs, $p_sCharset)
    {
        $sMultipart = 'Content-Type: ' . $p_aAttachs['sMimeType'] . ';charset="' . $p_sCharset;
        if ($p_aAttachs['sName'] != '') {
            $sMultipart .= '"; name="' . $p_aAttachs['sName'] . '"' . PHP_EOL;
        } else {
            $sMultipart .= '"' . PHP_EOL;
        }
        $sMultipart .= 'Content-Transfer-Encoding: base64' . PHP_EOL;
        $sMultipart .= 'Content-Disposition: attachment; filename="' . $p_aAttachs['sName'] . '"' . PHP_EOL . PHP_EOL;
        $sMultipart .= chunk_split(base64_encode($p_aAttachs['oContent']), 76, PHP_EOL) . PHP_EOL;
        return $sMultipart;
    }
}
?>