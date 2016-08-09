<?php

/**
 * Performs translations specific to the "Condition" value for Ebay.
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
class Aligent_Feeds_Model_Translator_Ebaycondition extends Aligent_Feeds_Model_Translator_Abstract_Condition {
    protected $_vConfigHandle = 'feeds/ebay/condition';

    public function _getSourceValue($vFieldValue) {
        return Mage::getSingleton('aligent_feeds/source_condition')->getEbayValue($vFieldValue ? $vFieldValue : $this->_iDefaultCondition);
    }
}