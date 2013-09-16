<?php

/**
 * Aligent Feeds Module
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
class Aligent_Feeds_Model_Cron {

    const CONFIG_REINDEX = 'feeds/general/reindex';

    // The system config value that specifies whether the feed is enabled for this store.
    const CONFIG_ENABLED_PREFIX = 'feeds/enable/';

    const XML_PATH_FEEDS = 'feeds';

    /**
     * Cron job which kicks off the feed export.
     */
    public function exportFeeds() {
        if (Mage::getStoreConfigFlag(self::CONFIG_REINDEX)) {
            Mage::getSingleton('aligent_feeds/log')->log('Starting Catalog Flat Product reindex...');
            $indexProcess = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_flat');
            $indexProcess->reindexEverything();
            Mage::getSingleton('aligent_feeds/log')->log('Catalog Flat Product Reindex finished!');
            Mage::getSingleton('aligent_feeds/log')->log('Starting Catalog Flat Category reindex...');
            $indexProcess = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_category_flat');
            $indexProcess->reindexEverything();
            Mage::getSingleton('aligent_feeds/log')->log('Catalog Flat Category Reindex finished!');
        }

        Mage::getModel('core/store')->getCollection()->walk(function($oStore) {
            $oFeeds = Mage::getConfig()->getNode(Aligent_Feeds_Model_Cron::XML_PATH_FEEDS);
            foreach($oFeeds->children() as $vFeedName => $oFeed) {
                Mage::getSingleton('aligent_feeds/status')->setFeedName($vFeedName);
                $vConfigName = Aligent_Feeds_Model_Cron::CONFIG_ENABLED_PREFIX.$vFeedName;
                if (Mage::getStoreConfigFlag($vConfigName, $oStore->getId())) {
                    Mage::getModel('aligent_feeds/feed')->export($oStore, $vFeedName, $oFeed);
                }
            }
        });

        //die("Argh!"); // Debugging
    }

}