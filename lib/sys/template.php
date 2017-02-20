<?php

/**
 * lib_sys_template
 *
 * 系统模板类,根据页面数据调用页面模板,并且输出数据
 *
 * @package lib_sys
 */

/**
 * lib_sys_template
 *
 * 系统模板类,根据页面数据调用页面模板,并且输出数据
 */
class lib_sys_template
{

    /**
     * 实例自身
     *
     * @var object
     */
    private static $_oInstance = null;

    /**
     * 页面数据
     *
     * @var array
     */
    private $_aPageData = [];

    /**
     * 获取实例
     *
     * @return object
     */
    static function getInstance()
    {
        if (! self::$_oInstance instanceof self) {
            self::$_oInstance = new self();
        }
        return self::$_oInstance;
    }

    /**
     * 实例化函数
     *
     * @return void
     */
    protected function __construct()
    {}

    /**
     * 克隆函数
     *
     * @return void
     */
    protected function __clone()
    {}

    /**
     * 设置页面数据
     *
     * @param array $p_aPageData            
     * @return void
     */
    function setPageData($p_aPageData)
    {
        $this->_aPageData = $p_aPageData;
    }

    /**
     * 渲染页面
     *
     * @param string $p_sPageName            
     * @return void
     */
    function render($p_sPageName)
    {
        if (! function_exists('panda')) {

            /**
             * 输出普通数据
             *
             * @param string $p_sStr            
             */
            function panda($p_sStr)
            {
                echo htmlspecialchars($p_sStr);
            }

            /**
             * 输出文本框数据
             *
             * @param string $p_sStr            
             */
            function pandaText($p_sStr)
            {
                echo str_replace([
                    "\r",
                    "\n\n",
                    "\n"
                ], [
                    "\n",
                    "\n",
                    '<br />'
                ], htmlspecialchars($p_sStr));
            }

            /**
             * 输出HTML代码
             *
             * @param string $p_sStr            
             */
            function pandaHTML($p_sStr)
            {
                echo $p_sStr;
            }

            /**
             * 获取静态资源路径
             *
             * @param string $p_sPath            
             * @return string
             */
            function pandaRes($p_sPath, $p_sDomainKey = 'sCDNSchemeDomain')
            {
                $sStaticDomain = lib_sys_var::getInstance()->getConfig($p_sDomainKey, 'domain');
                return $sStaticDomain . $p_sPath;
            }
        }
        
        $this->pandaTpl($p_sPageName);
    }

    /**
     * 加载模版
     *
     * @param string $p_sPageName            
     * @param array $p_aExtendDatas            
     * @throws Exception
     * @return true
     */
    protected function pandaTpl($p_sPageName, $p_aExtendDatas = [])
    {
        global $G_PAGE_DIR;
        $aTmp = explode('_', $p_sPageName);
        $sSubPath = strtolower(join(DIRECTORY_SEPARATOR, $aTmp));
        foreach ($G_PAGE_DIR as $sLoadDir) {
            $sLoadFilePath = $sLoadDir . DIRECTORY_SEPARATOR . $sSubPath . '.phtml';
            // echo $sLoadFilePath,'<br />';
            if (file_exists($sLoadFilePath)) {
                extract(array_merge($this->_aPageData, $p_aExtendDatas));
                unset($p_aExtendDatas);
                include $sLoadFilePath;
                return true;
            }
        }
        throw new Exception(__CLASS__ . ': can not found template(' . $p_sPageName . ').');
    }
}