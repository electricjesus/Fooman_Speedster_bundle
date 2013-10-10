<?php

/**
 * Fooman Common
 *
 * @package   Fooman_Common
 * @author    Kristof Ringleff <kristof@fooman.co.nz>
 * @copyright Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_Common_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Helper function to create a hash from a base64 Fooman serial number
     *
     * @param string $serial The full serial number
     *
     * @return string
     */
    public function convertSerialToId($serial)
    {
        return hash('sha256', str_replace(array("\r\n", "\n", "\r", " ", PHP_EOL), '', $serial));
    }
}