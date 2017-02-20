<?php
/**
 * util solr
 * @package system_common_lib_util
 */
load_lib('/vendor/autoload');

class util_solr
{

    private static $_aCache;

    static function getInstance($sInstance)
    {
        if (! empty(self::$_aCache[$sInstance])) {
            return self::$_aCache[$sInstance];
        }
        
        self::$_aCache[$sInstance] = new Solarium\Client(get_config($sInstance, 'solr'));
        return self::$_aCache[$sInstance];
    }
}
