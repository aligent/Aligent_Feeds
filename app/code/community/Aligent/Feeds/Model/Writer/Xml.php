<?php

class Aligent_Feeds_Model_Writer_Xml extends Aligent_Feeds_Model_Writer_Abstract {

    const FILE_EXTENSION = 'xml';

    /**
     * Writes the header row to the file where appropriate
     *
     * @return $this
     */
    function writeHeaderRow()
    {
        // TODO: Implement writeHeaderRow() method.
    }

    /**
     * Writes a data row to the file
     *
     * @param array $aData Data to write.  keys in this array must be the same as the keys in the setHeader array.
     * @return $this
     */
    function writeDataRow($aRow)
    {
        // TODO: Implement writeDataRow() method.
    }

    /**
     * Closes the file once finished.
     */
    function close()
    {
        // TODO: Implement close() method.
    }


}