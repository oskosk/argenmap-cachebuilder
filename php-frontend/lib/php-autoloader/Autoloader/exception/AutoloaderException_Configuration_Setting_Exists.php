<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the AutoloaderException_Configuration_Setting_Exists
 *
 * PHP version 5
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.
 * If not, see <http://php-autoloader.malkusch.de/en/license/>.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Exception
 * @author     Markus Malkusch <markus@malkusch.de>
 * @copyright  2009 - 2010 Markus Malkusch
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    SVN: $Id$
 * @link       http://php-autoloader.malkusch.de/en/
 */

namespace malkusch\autoloader;

/**
 * The parent class must be loaded.
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Configuration_Setting',
    __DIR__ . '/AutoloaderException_Configuration_Setting.php'
);

/**
 * Setting doesn't exist
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Exception
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.12
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        AutoloaderConfiguration::getValue()
 */
class AutoloaderException_Configuration_Setting_Exists extends
    AutoloaderException_Configuration_Setting
{

    /**
     * sets the missing setting
     *
     * @param String $setting setting name
     */
    public function __construct($setting)
    {
        parent::__construct("setting '$setting' was not defined.");
    }

}