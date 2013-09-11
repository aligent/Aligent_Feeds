<?php

/**
 * Collects errors during the export process so they can be reported in a status email at the end.
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 * @method      getFeedName()
 * @method      setFeedName($vFeedName)
 * @method      getErrors()
 * @method      setErrors($aErrors)
 */
class Aligent_Feeds_Model_Status extends Varien_Object {

    /**
     * Add an error message which should be reported in the status email at the
     * end of the process.
     *
     * @param string $vSku The product sku affected
     * @param string $vMessage Error message
     */
    public function addError($vSku, $vMessage) {
        $aErrors = $this->getErrors();
        if (!$aErrors) {
            $aErrors = array();
        }
        $aErrors[] = array(
            'feed' => $this->getFeedName(),
            'sku' => $vSku,
            'message' => $vMessage,
        );

        $this->setErrors($aErrors);
    }
}