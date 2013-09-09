<?php

/**
 * Simple logging interface for this extension
 *
 * @category    Aligent
 * @package     Aligent_Feeds
 * @copyright   Copyright (c) 2013 Aligent Consulting
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
class Aligent_Feeds_Model_Log {

    // Name of the log file in var/log
    const LOG_FILE = 'feed.log';


    /**
     * Logging for Feed exporter
     * @param string $message
     * @param int $level  ZEND_LOG log level
     * @param boolean $bDeveloperModeOnly True to log only in Developer mode
     */
    public function log($message, $level = Zend_Log::INFO, $bDeveloperModeOnly = false) {
        if ($bDeveloperModeOnly == false || ($bDeveloperModeOnly == true && Mage::getIsDeveloperMode())) {
            Mage::log($message, $level, self::LOG_FILE);
        }
        return $this;
    }

    /**
     * Logs the current php memory usage.
     *
     */
    public function logMemoryUsage() {
        $iCurrentKb = ceil(memory_get_usage(true) / 1024);
        $iPeakKb = ceil(memory_get_peak_usage(true) / 1024);
        $this->log("Memory Usage - Current (Kb): ".$iCurrentKb."   Peak (Kb): ".$iPeakKb, Zend_Log::DEBUG);
        return $this;
    }


}