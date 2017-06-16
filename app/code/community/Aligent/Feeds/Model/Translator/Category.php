<?php

/**
 * Translates category ids into a complete path.
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
class Aligent_Feeds_Model_Translator_Category {

    /**
     * Translates a category id stored in the supplied field to a full category path.
     *
     * @param array $aRow A flat product row
     * @param string $vField The name of the filed in which the category id is found.
     * @param Mage_Core_Model_Store $oStore The store context we're currently exporting.
     * @return mixed
     */
    public function translate($aRow, $vField, $oStore) {
        return Mage::helper('aligent_feeds')->getCategoryPath($aRow[$vField], $oStore);
    }
}