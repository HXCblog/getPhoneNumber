<?php
include_once "errorCode.php";
/**【填充】**（全过程没用到）
 * 用于AES的PKCS#7填充
 * 提供基于PKCS#7算法(加解密接口)
 * 对称解密使用的算法为 AES-128-CBC，数据采用PKCS#7填充。
 */
class PKCS7Encoder
{   
    //块大小为16个字节
    public static $block_size = 16;
    /**
     * 对需要加密的明文进行填充补位
     * @param $text 需要进行填充补位操作的明文
     * @return 补齐明文字符串
     */
    function encode( $text )
    {
        $block_size = PKCS7Encoder::$block_size;
        $text_length = strlen( $text );
        //计算需要填充的位数
        $amount_to_pad = PKCS7Encoder::$block_size - ( $text_length % PKCS7Encoder::$block_size );
        if ( $amount_to_pad == 0 ) {
            $amount_to_pad = PKCS7Encoder::block_size;
        }
        //获得补位所用的字符
        $pad_chr = chr( $amount_to_pad );
        $tmp = "";
        for ( $index = 0; $index < $amount_to_pad; $index++ ) {
            $tmp .= $pad_chr;
        }
        return $text . $tmp;
    }
    /**【去除填充】**
     * 对解密后的明文进行补位删除
     * @param decrypted 解密后的明文
     * @return 删除填充补位后的明文
     */
    function decode($text)
    {
        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }
}
/**
 * AES的解密**********************
 *
 * 用于encryptedData
 *  
 **********************************/
class Prpcrypt
{
    public $key;
    //构造函数，用密钥初始化
    function Prpcrypt( $k )
    {
        $this->key = $k;
    }
    /**
     * 对密文进行解密
     * @param string $aesCipher 需要解密的密文
     * @param string $aesIV 解密的初始向量
     * @return string 解密得到的明文
     */
    public function decrypt( $aesCipher, $aesIV )
    {
        try {
            //设置为“128位、CBC模式的AES解密”
            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            //用密钥key、初始化向量初始化
            mcrypt_generic_init($module, $this->key, $aesIV);
            //**执行解密**（得到带有PKCS#7填充的半原文，所以要去除填充）
            $decrypted = mdecrypt_generic($module, $aesCipher);
            //清理工作与关闭解密
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
        } catch (Exception $e) {
            return array(ErrorCode::$IllegalBuffer, null);
        }
        try {
            //去除补位字符（对半原文去除PKCS#7填充）
            $pkc_encoder = new PKCS7Encoder;
            //最终得到结果$result
            $result = $pkc_encoder->decode($decrypted);
        } catch (Exception $e) {
            //print $e;
            return array(ErrorCode::$IllegalBuffer, null);
        }
        return array(0, $result);
    }
}
?>