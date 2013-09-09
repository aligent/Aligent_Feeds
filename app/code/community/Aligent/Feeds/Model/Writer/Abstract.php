<?php

abstract class Aligent_Feeds_Model_Writer_Abstract extends Varien_Object {

    const FILE_EXTENSION = 'txt';
    const FEED_PATH = '/feeds';

    public function init($vStoreCode, $vFeedname, Mage_Core_Model_Config_Element $oConfig) {
        $this->setFilename($this->_getFilename($vStoreCode, $vFeedname));
    }

    protected function _getFilename($vStoreCode, $vFeedname) {
        $vFeedDir = Mage::getBaseDir().self::FEED_PATH;
        if (!is_dir($vFeedDir)) {
            Mage::getSingleton('aligent_feeds/log')->log("Feed export directory does not exist: ".$vFeedDir);
            return false;
        }
        $vFileName = $vFeedDir.'/'$vFeedname'-'.$vStoreCode.'.'.self::FILE_EXTENSION;
        if (!file_exists($vFileName)) {
            touch($vFileName);
        }
        if (is_writable($vFileName)) {
            return $vFileName;
        } else {
            $this->_log("Feed file is not writable: ".$vFileName);
            return false;
        }

    }

}