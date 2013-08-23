<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the class AutoloaderLocale
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
 * @category  PHP
 * @package   Autoloader
 * @author    Markus Malkusch <markus@malkusch.de>
 * @copyright 2009 - 2010 Markus Malkusch
 * @license   http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version   SVN: $Id$
 * @link      http://php-autoloader.malkusch.de/en/
 */

namespace malkusch\autoloader;

/**
 * Locale
 *
 * @category PHP
 * @package  Autoloader
 * @author   Markus Malkusch <markus@malkusch.de>
 * @license  http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version  Release: 1.12
 * @link     http://php-autoloader.malkusch.de/en/
 */
class AutoloaderLocale
{

    private
    /**
     * @var string
     */
    $_domain = "";

    /**
     * Initializes the locale
     *
     * @return void
     */
    static public function classConstructor()
    {
        \setlocale(LC_ALL, NULL);
    }

    /**
     * Binds the domain
     *
     * @param string $domain Text domain
     */
    public function __construct($domain)
    {
        \bindtextdomain($domain, __DIR__);
        $this->_domain = $domain;
    }

    /**
     * Prints a translated text
     *
     * @param string $message   Message
     * @param string $parameter Message
     *
     * @return string
     */
    public function sprintf($message, $parameter = "")
    {
        \textdomain($this->_domain);
        $arguments = \func_get_args();
        \array_shift($arguments);
        return \vsprintf(_($message), $arguments);
    }

}