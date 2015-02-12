<?php
/**
 * Feed Availability Translator
 * Class Aligent_Feeds_Model_Translator_Availability
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
class Aligent_Feeds_Model_Translator_Availability
{
    public function translate($aRow, $vField, $oStore) {
        $oProduct = Mage::getModel('catalog/product')->load($aRow['entity_id']);

        if(!$oProduct){
            return false;
        }

        return $oProduct->isInStock() ? 'In Stock' : 'Out Of Stock';
    }
}