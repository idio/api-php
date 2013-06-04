<?php

namespace IdioApi;

class Authentication {

    /**
     * Call this method to get singleton
     *
     * @return Authentication
     */
    public static function Instance()
    {
        static $objInstance = null;
        if ($objInstance === null) {
            $objInstance = new Authentication();
        }
        return $objInstance;
    }

    protected $arrCredentials = array(
        'App' => array(
            'key' => false,
            'secret' => false
        ),
        'Delivery' => array(
            'key' => false,
            'secret' => false
        )
    );

    static function setAppCredentials($strAppApiKey, $strAppApiSecret) {
        self::Instance()->arrCredentials['App'] = array(
            'key' => $strAppApiKey,
            'secret' => $strAppApiSecret
        );
    }

    static function setDeliveryCredentials($strDeliveryApiKey, $strDeliveryApiSecret) {
        self::Instance()->arrCredentials['Delivery'] = array(
            'key' => $strDeliveryApiKey,
            'secret' => $strDeliveryApiSecret
        );
    }
    
    static function buildSignature($strRequestMethod, $strRequestPath, $strSecretKey) {
        $strStringToSign = utf8_encode(
            strtoupper($strRequestMethod) . "\n"
          . $strRequestPath . "\n"
          . date('Y-m-d')
        );
        return base64_encode(hash_hmac("sha1", $strStringToSign, $strSecretKey));
    }

    static function getHeaders($strMethod, $strPath) {

        foreach (self::Instance()->arrCredentials as $strKey => $arrCredentials) {
            if (isset($arrCredentials['key']) && isset($arrCredentials['secret'])) {
                $strSignature = self::buildSignature($strMethod, $strPath, $arrCredentials['secret']);
                $arrHeaders[] = "X-{$strKey}-Authentication: {$arrCredentials['key']}:{$strSignature}";
            }
        }

        return $arrHeaders;

    }

}