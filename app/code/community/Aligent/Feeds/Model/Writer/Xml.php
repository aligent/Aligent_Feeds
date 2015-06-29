<?php

class Aligent_Feeds_Model_Writer_Xml extends Aligent_Feeds_Model_Writer_Abstract {

    protected $_vFileExtension = 'xml';

    /* @var XMLWriter $_oXmlWriter */
    protected $_oXmlWriter;

    protected $_aTagMap;
    protected $_aTagConfig = array();
    const FIELDS_TAG = 'fields';

    public function init($vStoreCode, $vFeedname, Mage_Core_Model_Config_Element $oConfig, Mage_Core_Model_Config_Element $oFields) {
        parent::init($vStoreCode, $vFeedname, $oConfig, $oFields);

        // Bail if there are issues creating the output file.
        if ($this->getFilename() === false) {
            return false;
        }

        // Open the output file
        $vFileName = $this->getFilename();

        $this->_oXmlWriter = new XMLWriter();
        $this->_oXmlWriter->openUri($vFileName);

        //Write rss version
        $this->_oXmlWriter->startElement('rss');
        $this->_oXmlWriter->writeAttribute('version','2.0');
        $this->_oXmlWriter->writeAttribute('xmlns:g','http://base.google.com/ns/1.0');

        // Write Atom feed header for Google Shopping
        $this->_oXmlWriter->startElement('feed');
        $this->_oXmlWriter->writeElement('title', Mage::app($vStoreCode)->getDefaultStoreView()->getFrontendName());
        $this->_oXmlWriter->startElement('link');
        $this->_oXmlWriter->writeAttribute('rel', 'self');
        $this->_oXmlWriter->writeAttribute('href', Mage::app()->getStore($vStoreCode)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK));
        $this->_oXmlWriter->endElement();
        $this->_oXmlWriter->writeElement('updated', date(DATE_ATOM));

        // Map array keys to XML tags
        $aTagMap = array();
        foreach ($oFields->children() as $vKey => $oNode) {
            if ((string) $oNode->xml_tag) {
                $aTagMap[$vKey] = (string) $oNode->xml_tag;
            } elseif ((string) $oNode->header) {
                $aTagMap[$vKey] = (string) $oNode->header;
            } else {
                $aTagMap[$vKey] = $vKey;
            }
            if (count($oNode->config)) {
                $oConfig = $oNode->config;
                $this->_aTagConfig[$vKey] = $this->nodeToArray($oConfig);
            }
        }
        $this->_aTagMap = $aTagMap;
        return $this;
    }

    /**
     * @param $oNode Mage_Core_Model_Config_Element
     */
    protected function nodeToArray($oNode)
    {
        $aResult = array();
        /** @var   Mage_Core_Model_Config_Element $oChildNode          */
        foreach ($oNode->children() as $vKey => $oChildNode) {
            if (count($oChildNode->children())){
                $value = $this->nodeToArray($oChildNode);
            }
            else{
                $value = (string) $oChildNode;
            }
            $aResult[$vKey] = $value;
        }
        return $aResult;
    }

    /**
     * Writes the header row to the file where appropriate
     *
     * @return $this
     */
    function writeHeaderRow() {
        // Not required with XML.
        return $this;
    }


    /**
     * Writes a data row to the file
     *
     * @param array $aData Data to write.  keys in this array must be the same as the keys in the setHeader array.
     * @return $this
     */
    function writeDataRow($aRow) {
        $this->_oXmlWriter->startElement('entry');
        foreach ($this->_aTagMap as $vIdx => $vTag) {
            if (array_key_exists($vIdx, $aRow)) {
                    if (is_array($aRow[$vIdx])) {
                        $aFields = array();
                        if (isset($this->_aTagConfig[$vIdx]) && !empty($this->_aTagConfig[$vIdx][self::FIELDS_TAG])){
                            $aFields = $this->_aTagConfig[$vIdx][self::FIELDS_TAG];
                        }
                        //custom subfields in xml for example for shipping
                        if ($aFields){
                            $this->_oXmlWriter->startElement($vTag);
                            foreach ($aRow[$vIdx] as $vKey => $vValue) {
                                $vSubTag = $this->_aTagConfig[$vIdx][self::FIELDS_TAG][$vKey]['xml_tag'];
                                $this->_oXmlWriter->writeElement($vSubTag, $vValue);
                            }
                            $this->_oXmlWriter->endElement();
                        }
                        else{
                            foreach ($aRow[$vIdx] as $vValue) {
                                $this->_oXmlWriter->writeElement($vTag, $vValue);
                            }
                        }
                    } else {
                        $this->_oXmlWriter->writeElement($vTag, $aRow[$vIdx]);
                    }
            }
        }
        $this->_oXmlWriter->endElement();
        return $this;
    }


    /**
     * Closes the file once finished.
     */
    function close() {
        $this->_oXmlWriter->endElement();
        $this->_oXmlWriter->endDocument();
        $this->_oXmlWriter->flush();
        return $this;
    }


}