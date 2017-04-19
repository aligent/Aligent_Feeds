<?php

abstract class Aligent_Feeds_Model_Writer_Abstract extends Varien_Object {

    protected $_vFileExtension = 'txt';
    const FEED_PATH = '/feeds';

    public function init($vStoreCode, $vFeedname, Mage_Core_Model_Config_Element $oConfig, Mage_Core_Model_Config_Element $oFields) {
        $this->setFilename($this->_getFilename($vStoreCode, $vFeedname));
    }

    /**
     * Writes the header row to the file where appropriate
     *
     * @return $this
     */
    abstract function writeHeaderRow();

    /**
     * Writes a data row to the file
     *
     * @param array $aData Data to write.  keys in this array must be the same as the keys in the setHeader array.
     * @return $this
     */
    abstract function writeDataRow($aRow);

    /**
     * Closes the file once finished.
     */
    abstract function close();

    protected function _getFilename($vStoreCode, $vFeedname) {
        $vFeedDir = $this->_getFeedDir();
        if (!is_dir($vFeedDir)) {
            Mage::getSingleton('aligent_feeds/log')->log("Feed export directory does not exist: ".$vFeedDir);
            return false;
        }
        $vFileName = $vFeedDir.'/'.$vFeedname.'-'.$vStoreCode.'.'.$this->_vFileExtension;
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

    /**
     * Separate function so local module can potentially overwrite it
     * @return string
     */
    protected function _getFeedDir()
    {
        return $vFeedDir = Mage::getBaseDir().self::FEED_PATH;
    }

}