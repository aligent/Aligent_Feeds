<?php

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