<?php

/**
 * util_ip
 *
 * 全球 IPv4地址归属地数据库(17MON.CN 版)
 *
 * @package util
 */

/**
 * util_ip
 *
 * 全球 IPv4 地址归属地数据库(17MON.CN 版)
 */
class util_ip
{

    /**
     *
     * @var string
     */
    private static $p_sIP = null;

    /**
     * 数据文件句柄
     *
     * @var object
     */
    private static $_oFileHandle = null;

    private static $offset = null;

    private static $index = null;

    /**
     * 析构函数
     *
     * @return void
     */
    function __destruct()
    {
        if (self::$_oFileHandle !== null) {
            fclose(self::$_oFileHandle);
        }
    }

    /**
     * 获取地址
     *
     * @param string $p_sIP            
     * @return string
     */
    static function findAddr($p_sIP)
    {
        if ('' == $p_sIP) {
            return 'N/A';
        }
        $p_aIP = explode('.', $p_sIP);
        if ($p_aIP[0] < 0 || $p_aIP[0] > 255 || count($p_aIP) !== 4) {
            return 'N/A';
        }
        if (self::$_oFileHandle === null) {
            self::init();
        }
        
        $nip2 = pack('N', ip2long($p_sIP));
        
        $tmp_offset = (int) $p_aIP[0] * 4;
        $start = unpack('Vlen', self::$index[$tmp_offset] . self::$index[$tmp_offset + 1] . self::$index[$tmp_offset + 2] . self::$index[$tmp_offset + 3]);
        
        $index_offset = $index_length = null;
        $max_comp_len = self::$offset['len'] - 1024 - 4;
        for ($start = $start['len'] * 8 + 1024; $start < $max_comp_len; $start += 8) {
            if (self::$index{$start} . self::$index{$start + 1} . self::$index{$start + 2} . self::$index{$start + 3} >= $nip2) {
                $index_offset = unpack('Vlen', self::$index{$start + 4} . self::$index{$start + 5} . self::$index{$start + 6} . "\x0");
                $index_length = unpack('Clen', self::$index{$start + 7});
                
                break;
            }
        }
        
        if ($index_offset === null) {
            return 'N/A';
        }
        
        fseek(self::$_oFileHandle, self::$offset['len'] + $index_offset['len'] - 1024);
        
        return explode("\t", fread(self::$_oFileHandle, $index_length['len']));
    }

    private static function init()
    {
        if (self::$_oFileHandle === null) {
            self::$p_sIP = new self();
            
            self::$_oFileHandle = fopen(__DIR__ . '/ipdb.dat', 'rb');
            if (self::$_oFileHandle === FALSE) {
                throw new Exception('Invalid ipdb.dat file!');
            }
            
            self::$offset = unpack('Nlen', fread(self::$_oFileHandle, 4));
            if (self::$offset['len'] < 4) {
                throw new Exception('Invalid ipdb.dat file!');
            }
            
            self::$index = fread(self::$_oFileHandle, self::$offset['len'] - 4);
        }
    }
}