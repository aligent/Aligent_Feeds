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
class Aligent_Feeds_Model_Simplefilewriter extends Varien_Object {
    
    const DELIMITER = "\t";
    const ENCLOSURE = '"';

    /**
     * Writes the header row to the csv
     *
     * @return $this
     */
    public function writeHeaderRow() {
        $this->getStreamWriter()->streamWriteCsv(array_values($this->getHeader()), self::DELIMITER, self::ENCLOSURE);
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
                $aRow[] = $aData[$vKey];
            } else {
                $aRow[] = '';
            }
        }
        $this->getStreamWriter()->streamWriteCsv($aRow, self::DELIMITER, self::ENCLOSURE);

        return $this;
    }
}