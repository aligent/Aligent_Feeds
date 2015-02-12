<?php

/**
 * Performs translations specific to the "Condition" value for Google Shopping.
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
class Aligent_Feeds_Model_Translator_Googlecondition extends Aligent_Feeds_Model_Translator_Abstract_Condition {
    protected $_vConfigHandle = 'feeds/googleshopping/condition';

    public function _getSourceValue($vFieldValue) {
        return Mage::getSingleton('aligent_feeds/source_condition')->getGoogleValue($vFieldValue ? $vFieldValue : $this->_iDefaultCondition);
    }
}