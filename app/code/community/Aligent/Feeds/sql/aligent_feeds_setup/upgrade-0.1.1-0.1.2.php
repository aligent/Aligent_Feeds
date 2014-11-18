<?php
    $new_vals = array();
    $fh = fopen(__DIR__.'/taxonomy.en-US-update-0.1.2.txt', 'r');
    // Discard the first line, it's a comment.
    $vLine = fgets($fh);
    while (!feof($fh)) {
        $new_vals[] = trim(fgets($fh));
    }
    fclose($fh);

    $attrId = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product','gshopping_category')->getAttributeId();
    $collection = Mage::getModel('eav/entity_attribute_option')->getCollection()
                                        ->addFieldToFilter('attribute_id',$attrId);
    $collection->getSelect()->join(array('ov'=>$this->getTable('eav/attribute_option_value')),'main_table.option_id=ov.option_id',array('ov.*'));
    $old_vals = $collection->getColumnValues('value');
    $to_be_added = array_diff($new_vals,$old_vals);

    $combined = array_merge($old_vals,$to_be_added);
    asort($combined); //sort by value
    $combined = array_values($combined); //renumber the elements to establish correct sort order

    $optionTable        = $this->getTable('eav/attribute_option');
    $optionValueTable   = $this->getTable('eav/attribute_option_value');
    /**
     * For each inserted item, the offset increases by 1 to maintain the correct sort order
     */
    $_offset=1;
    foreach ($to_be_added as $_idx => $label) {
        // add option
        $sortOrder = array_search($label,$combined) - $_offset;
        $data = array(
            'attribute_id' => $attrId,
            'sort_order'   => $sortOrder,
        );
        $this->_conn->insert($optionTable, $data);
        $_offset++;
        $intOptionId = $this->_conn->lastInsertId($optionTable);

        $data = array(
            'option_id' => $intOptionId,
            'store_id'  => 0,
            'value'     => $label,
        );
        $this->_conn->insert($optionValueTable, $data);
    }
