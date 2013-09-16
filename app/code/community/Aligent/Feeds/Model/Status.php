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

    const CONFIG_EMAIL_FROM_ADDRESS = 'trans_email/ident_general/email';
    const CONFIG_EMAIL_FROM_NAME = 'trans_email/ident_general/name';
    const CONFIG_EMAIL_TO_ADDRESS = 'feeds/general/email_to';

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

    public function addSuccess($vMessage) {
        $aSuccess = $this->getSuccess();
        if (!$aSuccess) {
            $aSuccess = array();
        }
        $aSuccess[] = $vMessage;
        $this->setSuccess($aSuccess);
    }

    public function sendStatusEmail() {
        $vBody = '';

        $aSuccess = $this->getSuccess();
        if (count($aSuccess) > 0) {
            $vBody .= "The following completed successfully:\n * ".implode("\n * ", $aSuccess)."\n\n";
        }

        $aErrors = $this->getErrors();
        if (count($aErrors) > 0) {
            $vBody .= "The following errors occurred (feed - sku - error):\n";
            foreach ($aErrors as $aError) {
                $vBody .= $aError['feed'].' - '.$aError['sku'].' - '.$aError['message']."\n";
            }
        }

        if ($vBody !== '') {

            $aTo = Mage::getStoreConfig(self::CONFIG_EMAIL_TO_ADDRESS);
            if ($aTo == '') {
                return $this;
            } else {
                $aTo = explode(',', $aTo);
            }

            $mail = new Zend_Mail();
            $mail->setFrom(Mage::getStoreConfig(self::CONFIG_EMAIL_FROM_ADDRESS), Mage::getStoreConfig(self::CONFIG_EMAIL_FROM_NAME));
            $mail->addTo($aTo);
            $mail->setSubject("Feed Export Status Report");
            $mail->setBodyText($vBody);
            $mail->send();
        }

    }
}