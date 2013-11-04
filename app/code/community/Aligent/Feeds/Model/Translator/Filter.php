<?php

/**
 * Performs a strip_tags on data values that might contain HTML before export.
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
class Aligent_Feeds_Model_Translator_Filter {

    /**
     * Performs a strip_tags on data values that might contain HTML before export.
     *
     * @param array $aRow A flat product row
     * @param string $vField The name of the field to filter.
     * @param Mage_Core_Model_Store $oStore The store context we're currently exporting.
     * @return mixed
     */
    public function translate($aRow, $vField, $oStore) {
        return str_replace(array("\r", "\n", "\t"), " ", strip_tags(iconv('UTF-8', 'ASCII//TRANSLIT', $aRow[$vField])));
    }
}