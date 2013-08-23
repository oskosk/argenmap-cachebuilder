#! /bin/env php
<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Builds an index and deploys a portable autoloader for you project
 *
 * autoloader-build.php -c <classpath> {-c <classpath>} [-d <deploypath] [-r]
 *
 * -c, --classpath=CP  Search for classes in classpath CP. You can add more
 *                     classpaths by adding more --classpath options.
 * -d, --deploypath=DP Deploy the generated index and the Autoloader
 *                     in the path DP.
 * -r, --require       Don't use autoloading. All classes of the
 *                     generated index will be included.
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
 * @copyright 2009 - 2011 Markus Malkusch
 * @license   http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version   SVN: $Id$
 * @link      http://php-autoloader.malkusch.de/en/
 */

namespace malkusch\autoloader;

/**
 * fix PHP version 
 */
include_once __DIR__ . "/../Autoloader/OldPHPAPI.php";
$__oldAPI = new OldPHPAPI();
$__oldAPI->checkAPI();
unset($__oldAPI);

/**
 * Autoloader classes
 */
require __DIR__ . "/../Autoloader/Autoloader.php";
InternalAutoloader::getInstance()->registerClass(
    "AutoloaderBuilder",
    __DIR__ . "/../Autoloader/AutoloaderBuilder.php"
);

/**
 * Run the script
 */
$_script = new AutoloaderBuildScript();
$_script->run();
/**
 * run() might kill the script. So the following code might not be executed.
 * Who cares?
 */
unset($_script);

/**
 * Build script
 *
 * @category PHP
 * @package  Autoloader
 * @author   Markus Malkusch <markus@malkusch.de>
 * @license  http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version  Release: 1.12
 * @link     http://php-autoloader.malkusch.de/en/
 * @see      AutoloaderBuilder
 */
class AutoloaderBuildScript
{

    const
    /**
     * Any error
     */
    ERROR_ANY = 1;

    private
    /**
     * @var AutoloaderLocale
     */
    $_locale,
    /**
     * @var array
     */
    $_classPaths = array(),
    /**
     * @var array
     */
    $_deployPath = "",
    /**
     * @var string
     */
    $_mode = AutoloaderBuilder::MODE_AUTOLOAD;

    /**
     * Reads the CLI parameters
     */
    public function __construct()
    {
        $this->_locale = new AutoloaderLocale(AutoloaderBuilder::TEXT_DOMAIN);
        $options
            = getopt(
                "c:d:rh",
                array("classpath:", "deploypath:", "require", "help")
            );

        // Print usage and exit
        $isHelp = empty($options) || $this->_getFlag($options, "h", "help");
        if ($isHelp) {
            $this->printUsage();
            exit();

        }

        $this->_classPaths
            = $this->_getArguments($options, "c", "classpath");
        if (empty($this->_classPaths)) {
            $this->_classPaths = array(getcwd());

        }
        
        $this->_deployPath
            = $this->_getArgument($options, "d", "deploypath");
        if (empty($this->_deployPath)) {
            $this->_deployPath = getcwd();

        }

        $this->_mode
            = $this->_getFlag($options, "r", "require")
            ? AutoloaderBuilder::MODE_REQUIRE_ALL
            : AutoloaderBuilder::MODE_AUTOLOAD;
    }

    /**
     * Prints the usage and exits the script
     *
     * @return void
     */
    public function printUsage()
    {
        echo $this->_locale->sprintf("USAGE", basename(__FILE__)), "\n";
    }

    /**
     * Runs the script
     *
     * @return void
     */
    public function run()
    {
        try {
            $builder = new AutoloaderBuilder();
            $builder->setClassLoaderMode($this->_mode);
            $builder->setDeployPath($this->_deployPath);
            foreach ($this->_classPaths as $classPath) {
                $builder->addClassPath($classPath);

            }
            $builder->build();

        } catch (AutoloaderException $e) {
            echo $e->getMessage(), "\n";
            die(self::ERROR_ANY);

        }
    }

    /**
     * Returns all values for one argument
     *
     * @param array  $options     CLI options
     * @param string $shortOption Short option name
     * @param string $longOption  Long option name
     *
     * @return array
     */
    private function _getArguments(
        array $options,
        $shortOption,
        $longOption
    ) {
        $arguments  = array();
        $optionKeys = array($shortOption, $longOption);

        foreach ($optionKeys as $option) {
            if (! array_key_exists($option, $options)) {
                continue;

            }
            $value = $options[$option];
            if (is_array($value)) {
                $arguments = array_merge($arguments, $value);
                
            } else {
                $arguments[] = $value;
                
            }
            
        }
        return $arguments;
    }

    /**
     * Returns one value for one argument
     *
     * @param array  $options     CLI options
     * @param string $shortOption Short option name
     * @param string $longOption  Long option name
     *
     * @return string
     */
    private function _getArgument(
        array $options,
        $shortOption,
        $longOption
    ) {
        $values = $this->_getArguments($options, $shortOption, $longOption);
        return empty($values) ? "" : array_pop($values);
    }

    /**
     * Returns TRUE if the flag was set
     *
     * @param array  $options     CLI options
     * @param string $shortOption Short option name
     * @param string $longOption  Long option name
     *
     * @return bool
     */
    private function _getFlag(
        array $options,
        $shortOption,
        $longOption
    ) {
        $values = $this->_getArguments($options, $shortOption, $longOption);
        return ! empty($values);
    }

}