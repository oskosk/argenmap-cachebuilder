<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the AutoloaderException_Configuration_Setting_Object_Exists
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
 * Class does not exist
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage Exception
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.12
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        AutoloaderConfiguration::getObject()
 */
class AutoloaderException_Configuration_Setting_Object_Exists extends
    AutoloaderException_Configuration_Setting_Object
{

    /**
     * Sets class- and settings-name
     *
     * @param String $setting   setting name
     * @param String $classname class name
     */
    public function __construct($setting, $classname)
    {
        parent::__construct(
            "The class '$classname' for setting '$setting' doesn't exist."
        );
    }

}