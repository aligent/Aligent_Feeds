<?php

class Aligent_Feeds_Model_Cron {

    // The system config value that specifies whether the feed is enabled for this store.
    const CONFIG_ENABLED = 'google/shopping/enable';

    // Name of the log file in var/log
    const LOG_FILE = 'feed.log';

    /**
     * Cron job which kicks off the google shopping feed export.
     */
    public function exportGoogleShopping() {

        $this->_log('Starting Catalog Flat Product reindex...');
        $indexProcess = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_flat');
        $indexProcess->reindexEverything();
        $this->_log('Catalog Flat Product Reindex finished!');
        $this->_log('Starting Catalog Flat Category reindex...');
        $indexProcess = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_category_flat');
        $indexProcess->reindexEverything();
        $this->_log('Catalog Flat Category Reindex finished!');

        Mage::getModel('core/store')->getCollection()->walk(function($oStore) {
            if (Mage::getStoreConfigFlag(Modelflight_Feeds_Model_Cron::CONFIG_ENABLED, $oStore->getId())) {
                Mage::getModel('mffeeds/googleshopping')->export($oStore);
            }
        });
    }

    /**
     * Logging for Feed exporter
     * @param string $message
     * @param int $level  ZEND_LOG log level
     * @param boolean $bDeveloperModeOnly True to log only in Develooper mode
     */
    protected function _log($message, $level = Zend_Log::INFO, $bDeveloperModeOnly = false) {
        if ($bDeveloperModeOnly == false || ($bDeveloperModeOnly == true && Mage::getIsDeveloperMode())) {
            Mage::log($message, $level, self::LOG_FILE);
        }
    }
}