<?php

class Aligent_Feeds_Model_Writer_Xml extends Aligent_Feeds_Model_Writer_Abstract {

    protected $_vFileExtension = 'xml';

    /* @var XMLWriter $_oXmlWriter */
    protected $_oXmlWriter;

    protected $_aTagMap;

    public function init($vStoreCode, $vFeedname, Mage_Core_Model_Config_Element $oConfig, Mage_Core_Model_Config_Element $oFields) {
        parent::init($vStoreCode, $vFeedname, $oConfig);

        // Bail if there are issues creating the output file.
        if ($this->getFilename() === false) {
            return false;
        }

        // Open the output file
        $vFileName = $this->getFilename();

        $this->_oXmlWriter = new XMLWriter();
        $this->_oXmlWriter->openUri($vFileName);

        // Write Atom feed header for Google Shopping
        $this->_oXmlWriter->startElement('feed');
        $this->_oXmlWriter->writeElement('title', Mage::app($vStoreCode)->getDefaultStoreView()->getFrontendName());
        $this->_oXmlWriter->startElement('link');
        $this->_oXmlWriter->writeAttribute('rel', 'self');
        $this->_oXmlWriter->writeAttribute('href', Mage::getUrl());
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
        }
        $this->_aTagMap = $aTagMap;

        return $this;
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
                if ($vTag == 'link') {
                    $this->_oXmlWriter->startElement('link');
                    $this->_oXmlWriter->writeAttribute('href', $aRow[$vIdx]);
                    $this->_oXmlWriter->endElement();
                } else {
                    if (is_array($aRow[$vIdx])) {
                        foreach ($aRow[$vIdx] as $vValue) {
                            $this->_oXmlWriter->writeElement($vTag, $vValue);
                        }
                    } else {
                        $this->_oXmlWriter->writeElement($vTag, $aRow[$vIdx]);
                    }

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