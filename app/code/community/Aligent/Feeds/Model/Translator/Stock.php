<?php
class Aligent_Feeds_Model_Translator_Stock extends Varien_Object {


    /**
     * Ads stock information to table
     *
     * @param array $aRow A flat product row
     * @param string $vField The name of the filed in which the category id is found.
     * @param Mage_Core_Model_Store $oStore The store context we're currently exporting.
     * @return mixed
     */
    public function translate($aRow, $vField, $oStore) {

        //Put following in before_query_filter
        //$oSelect->joinInner(array('cinv' => 'cataloginventory_stock_status'), 'cinv.product_id=main_table.entity_id');
        if (isset($aRow['stock_status'])){
            if ((int) $aRow['stock_status']){
                return 'in stock';
            }
            else{
                return 'out of stock';
            }
        }
    }
}