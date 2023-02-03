<?php

namespace App\Common;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CommonHelper
{

   /**
     * encrypt data by openssl_cipher
     * @return false|string|void
     * @throws \Exception
     */

    public function encryptData ($plaintext) {
        if (empty($plaintext)){
            return false;
        }
        $cipher= CodeDefinition::CIPHER_METHOD;
        $passphraseoriginal = config('hashing.passphrase_for_encrypt');
        $passphrase = utf8_encode(md5(utf8_encode($passphraseoriginal)));

        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $passwordOriginal = utf8_encode($plaintext);
        $ciphertextRaw = openssl_encrypt($passwordOriginal, $cipher, $passphrase, OPENSSL_RAW_DATA, $iv);
        return utf8_decode(base64_encode( $iv.$ciphertextRaw ));
    }

    /**
     * decrypt data by openssl_cipher
     */
    public function decryptData ($password) {
        if (empty($password)){
            return false;
        }
        $cipher= CodeDefinition::CIPHER_METHOD;
        $passphraseoriginal = config('hashing.passphrase_for_encrypt');
        $passphrase = utf8_encode(md5(utf8_encode($passphraseoriginal)));
        $decodeData = base64_decode(utf8_encode($password));

        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = substr($decodeData, 0, $ivlen);
        $ciphertextRaw = substr($decodeData, $ivlen);
        $originalPlaintext = openssl_decrypt($ciphertextRaw, $cipher, $passphrase, OPENSSL_RAW_DATA, $iv);
        if (strpos($originalPlaintext, "b'") === 0) {
            $strReturn = substr($originalPlaintext, 2);
            $strReturn = substr($strReturn, 0, strlen($strReturn) - 1);
            return $strReturn;
        }
        return $originalPlaintext;
    }

    /**
     * generate random string
     */
    public function generateString ($length) {
        $chars = CodeDefinition::CHARACTER;
        $str = '';
        $max = strlen($chars) - 1;
        for ($i=0; $i < $length; $i++)
            $str .= $chars[random_int(0, $max)];

        return $str;
    }

    /**
     * convert DateTime to JST
     */

    public function convertTimeUTCToJST ($date) {
        if (empty($date)){
            return "";
        }
        
        return Carbon::parse($date, 'UTC')->setTimezone('Asia/Tokyo')->format('Y-m-d H:i:s');
    }
}
