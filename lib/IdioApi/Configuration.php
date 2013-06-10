<?php

namespace IdioApi;

class Configuration {

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

    protected $strBaseUrl = "";
    protected $strVersion = "";

    static function setUrl($strBaseUrl, $strVersion = false) {
        self::Instance()->strBaseUrl = $strBaseUrl;
        self::Instance()->strVersion = $strVersion;
    }

    static function getUrl() {
        return self::Instance()->strBaseUrl . self::Instance()->strVersion;
    }

}