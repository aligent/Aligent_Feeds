<?php

/**
 * Very lightweight CSV file writer.  No formatting or validation of the export
 * data is performed, however care is taken to ensure fields are always
 * exported in the correct order even when fields are missing.
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 * @method setHeader()
 * @method getHeader()
 * @method setStreamWriter()
 * @method getStreamWriter()
 */
class Aligent_Feeds_Model_Writer_Csv extends Aligent_Feeds_Model_Writer_Abstract {

    protected $_vFileExtension = 'csv';

    public function __construct() {
        $this
            ->setDelimiter("\t")
            ->setEnclosure('"');
    }


    public function init($vStoreCode, $vFeedname, Mage_Core_Model_Config_Element $oConfig, Mage_Core_Model_Config_Element $oFields) {
        parent::init($vStoreCode, $vFeedname, $oConfig, $oFields);

        // Bail if there are issues creating the output file.
        if ($this->getFilename() === false) {
            return false;
        } else {
            // Open the output file
            $vFileName = $this->getFilename();
            $oIo = new Varien_Io_File();
            $oIo->open(array('path' => dirname($vFileName)));
            $oIo->streamOpen($vFileName);

            $this->setStreamWriter($oIo);
        }

        if ($oConfig->delimiter) {
            $this->setDelimiter(str_replace("\\t", "\t", (string) $oConfig->delimiter));
        }

        if ($oConfig->enclosure) {
            $this->setEnclosure((string) $oConfig->enclosure);
        }

        return $this;
    }


    /**
     * Writes the header row to the csv
     *
     * @return $this
     */
    public function writeHeaderRow() {
        $this->getStreamWriter()->streamWriteCsv(array_values($this->getHeader()), $this->getDelimiter(), $this->getEnclosure());
        return $this;
    }


    /**
     * Writes a data row to the csv
     *
     * @param array $aData Data to write.  keys in this array must be the same as the keys in the setHeader array.
     * @return $this
     */
    public function writeDataRow($aData) {
        $aRow = array();
        foreach ($this->getHeader() as $vKey => $vTitle) {
            if (array_key_exists($vKey, $aData)) {
                if (is_array($aData[$vKey])) {
                    $aRow[] = implode(',', $aData[$vKey]);
                } else {
                    $aRow[] = $aData[$vKey];
                }
            } else {
                $aRow[] = '';
            }
        }
        $this->getStreamWriter()->streamWriteCsv($aRow, $this->getDelimiter(), $this->getEnclosure());

        return $this;
    }


    /**
     * Closes the CSV file once finished.
     */
    public function close() {
        $this->getStreamWriter()->streamClose();
        return $this;
    }
}