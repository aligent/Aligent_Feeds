<?php
/**
 * Feed Store Config Value Translator
 * Class Aligent_Feeds_Model_Translator_Storeconfig
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
class Aligent_Feeds_Model_Translator_Configvalue
{
    public function translate($aRow, $vField, $oStore) {
        return Mage::getStoreConfig($vField, $oStore);
    }
}