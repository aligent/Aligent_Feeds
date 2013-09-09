<?php

/**
 * Performs translations specific to the "Special Price Start/End Date" value for
 * Google Shopping (mainly foematting).
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
class Aligent_Feeds_Model_Translator_Pricestartend {

    /**
     * Translates a category id stored in the supplied field to a full category path.
     *
     * @param array $aRow A flat product row
     * @param string $vField The name of the filed in which the category id is found.
     * @param Mage_Core_Model_Store $oStore The store context we're currently exporting.
     * @return mixed
     */
    public function translate($aRow, $vField, $oStore) {
        return $this->getIso8601Date($aRow['special_from_date']).'/'.$this->getIso8601Date($aRow['special_to_date']);
    }

    protected function getIso8601Date($vMySqlDate) {
        return date('c', strtotime($vMySqlDate));
    }


}