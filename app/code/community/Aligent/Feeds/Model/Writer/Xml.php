<?php

class Aligent_Feeds_Model_Writer_Xml extends Aligent_Feeds_Model_Writer_Abstract {

    const XML_SPEC_ORIGINAL = 0;
    const XML_SPEC_ATOM = 1;
    const XML_SPEC_RSS2 = 2;

    protected $_vFileExtension = 'xml';

    /* @var XMLWriter $_oXmlWriter */
    protected $_oXmlWriter;

    protected $_aTagMap;

    protected $_xmlSpecification = 0;

    protected function _getOutputType() {
        return $this->_xmlSpecification;
    }

    /**
     * Set the XML specification from config.
     *
     * @param $oConfig
     */
    protected function _setXMLSpecification($oConfig) {
        if (!isset($oConfig->specification)) {
            $this->_xmlSpecification = self::XML_SPEC_ORIGINAL;
        } else {
            switch(strtolower($oConfig->specification)) {
                case "atom":
                    $this->_xmlSpecification = self::XML_SPEC_ATOM;
                    break;
                case "rss2":
                    $this->_xmlSpecification = self::XML_SPEC_RSS2;
                    break;
                case self::XML_SPEC_ATOM:
                    $this->_xmlSpecification = self::XML_SPEC_ATOM;
                    break;
                case self::XML_SPEC_RSS2:
                    $this->_xmlSpecification = self::XML_SPEC_RSS2;
                    break;
                default:
                    $this->_xmlSpecification = self::XML_SPEC_ORIGINAL;
                    break;
            }
        }
    }

    /**
     * Add the opening document tags for the xml writer according to the specification being used.
     * The default remains as it currently was which does not meet either specification.
     */
    protected function _openDocumentTags() {
        switch($this->_getOutputType()) {
            case self::XML_SPEC_ATOM:
                $this->_oXmlWriter->startElement('feed');
                $this->_oXmlWriter->writeAttribute('xmlns','http://www.w3.org/2005/Atom');
                $this->_oXmlWriter->writeAttribute('xmlns:g','http://base.google.com/ns/1.0');

                break;
            case self::XML_SPEC_RSS2:
                $this->_oXmlWriter->startElement('rss');
                $this->_oXmlWriter->writeAttribute('version','2.0');
                $this->_oXmlWriter->writeAttribute('xmlns:g','http://base.google.com/ns/1.0');

                $this->_oXmlWriter->startElement('channel');
                break;
            default:
                $this->_oXmlWriter->startElement('rss');
                $this->_oXmlWriter->writeAttribute('version','2.0');
                $this->_oXmlWriter->writeAttribute('xmlns:g','http://base.google.com/ns/1.0');

                // Write Atom feed header for Google Shopping
                $this->_oXmlWriter->startElement('feed');
                break;
        }
    }

    /**
     * Add the opening product/item tag based on the xml specification.
     */
    protected function _openElementTags() {
        switch($this->_getOutputType()) {
            case self::XML_SPEC_ATOM:
                $this->_oXmlWriter->startElement('entry');
                break;
            case self::XML_SPEC_RSS2:
                $this->_oXmlWriter->startElement('item');
                break;
            default:
                $this->_oXmlWriter->startElement('entry');
                break;
        }

    }

    public function init($vStoreCode, $vFeedname, Mage_Core_Model_Config_Element $oConfig, Mage_Core_Model_Config_Element $oFields) {
        parent::init($vStoreCode, $vFeedname, $oConfig, $oFields);

        // Bail if there are issues creating the output file.
        if ($this->getFilename() === false) {
            return false;
        }

        // Set output specification
        $this->_setXMLSpecification($oConfig);

        // Open the output file
        $vFileName = $this->getFilename();

        $this->_oXmlWriter = new XMLWriter();
        $this->_oXmlWriter->openUri($vFileName);

        $this->_openDocumentTags();

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
        $this->_openElementTags();
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