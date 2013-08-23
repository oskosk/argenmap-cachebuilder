<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the class AutoloaderBuilder
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
 * Includes
 */
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Builder_NoClassPath',
    __DIR__ . '/exception/AutoloaderException_Builder_NoClassPath.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Builder_NoDeployPath',
    __DIR__ . '/exception/AutoloaderException_Builder_NoDeployPath.php'
);
InternalAutoloader::getInstance()->registerClass(
    'AutoloaderException_Builder_IO',
    __DIR__ . '/exception/AutoloaderException_Builder_IO.php'
);

/**
 * Builds an index and deploys a portable autoloader for you project
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

class AutoloaderBuilder
{

    const
    /**
     * Text domain
     */
    TEXT_DOMAIN = "autoloader-build",
    /**
     * Use InstantAutoloader::requireAll()
     */
    MODE_REQUIRE_ALL = "requireAll",
    /**
     * Register autoloader
     */
    MODE_AUTOLOAD = "autoload";

    private
    /**
     * @var AutoloaderLocale
     */
    $_locale,
    /**
     * @var string
     */
    $_deployPath = "",
    /**
     * @var string
     */
    $_classLoaderMode = self::MODE_AUTOLOAD,
    /**
     * @var array
     */
    $_classPaths = array();

    /**
     * Initialization
     */
    public function __construct()
    {
        $this->_locale = new AutoloaderLocale(self::TEXT_DOMAIN);
    }

    /**
     * Path for the deployed autoloader
     *
     * The builder will copy the autoloader and its indexes into this path.
     *
     * @param string $path Deploy path
     *
     * @return
     */
    public function setDeployPath($path)
    {
        $this->_deployPath = $path;
    }

    /**
     * Set the class loader mode
     *
     * If set to AutoloaderBuilder::MODE_REQUIRE_ALL autoload won't be used.
     * All classes will be included by default.
     *
     * If set to AutoloaderBuilder::MODE_AUTOLOAD autoload will be used.
     *
     * @param string $mode Mode
     *
     * @return void
     * @see AutoloaderBuilder::MODE_REQUIRE_ALL
     * @see AutoloaderBuilder::MODE_AUTOLOAD
     */
    public function setClassLoaderMode($mode)
    {
        $this->_classLoaderMode = $mode;
    }

    /**
     * Add a class path
     *
     * If you add several class paths, they have to be disjunct. The behaviour
     * is not defined, if a class path contains another added class path.
     *
     * @param string $path Class path
     *
     * @return void
     */
    public function addClassPath($path)
    {
        $this->_classPaths[] = $path;
    }

    /**
     * Returns the autoloader file
     *
     * @return string
     */
    public function getAutoloaderFile()
    {
        return $this->_deployPath . DIRECTORY_SEPARATOR . "autoloader.php";
    }

    /**
     * Build and deploy the index
     *
     * @return void
     * @throws AutoloaderException_Builder_NoClassPath
     * @throws AutoloaderException_Builder_NoDeployPath
     * @throws AutoloaderException_Builder_IO
     */
    public function build()
    {
        if (empty($this->_deployPath)) {
            throw new AutoloaderException_Builder_NoDeployPath(
                $this->_locale->sprintf("NO_DEPLOY_PATH")
            );

        }
        if (empty($this->_classPaths)) {
            throw new AutoloaderException_Builder_NoClassPath(
                $this->_locale->sprintf("NO_CLASS_PATH")
            );

        }

        $indexDirectory = $this->_deployPath . DIRECTORY_SEPARATOR . "index";

        // Create directories
        $this->_mkdir($this->_deployPath);
        $this->_mkdir($indexDirectory);

        // Build indexes
        foreach ($this->_classPaths as $i => $classPath) {
            $autoloader = new Autoloader($classPath);

            // Setup the deployed index
            $indexFile = "$i.php";
            $index = new AutoloaderIndex_PHPArrayCode();
            $index->setIndexPath(
                $indexDirectory . DIRECTORY_SEPARATOR . $indexFile
            );
            $index->addFilter(
                new AutoloaderIndexFilter_RelativePath($this->_deployPath)
            );

            // Don't index our own classes
            $autoloader->getFileIterator()->addSkipPattern(
                "/InstantAutoloader\.php$/"
            );

            // Build index
            $autoloader->setIndex($index);
            $autoloader->buildIndex();

        }

        // Copy InstantAutoloader
        $isCopied = copy(
            __DIR__ . DIRECTORY_SEPARATOR . "InstantAutoloader.php",
            $this->_deployPath . DIRECTORY_SEPARATOR . "InstantAutoloader.php"
        );
        if (! $isCopied) {
            throw new AutoloaderException_Builder_IO(
                $this->_locale->sprintf("FAILED_COPY_INSTANTAUTOLOADER")
            );

        }

        // Code for autoloading
        $autoloadMethod
            = $this->_classLoaderMode == self::MODE_AUTOLOAD
            ? "register"
            : "requireAll";
        $code
            = "<?php\n\n"

            . "/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */\n\n"

            . "/**\n"
            . " * Autoloader\n"
            . " * \n"
            . " * This code was generated automatically.\n"
            . " * Don't edit this file. Changes will get lost when\n"
            . " * building a new autoloader.\n"
            . " *\n"
            . " * @see  AutoloaderBuilder::build()\n"
            . " * @link http://php-autoloader.malkusch.de/en/\n"
            . " */\n\n"
                
            . "namespace " . __NAMESPACE__ . ";\n\n"
                
            . "require_once __DIR__ . '/InstantAutoloader.php';\n";
        foreach ($this->_classPaths as $i => $classPath) {
            $indexPath = "__DIR__ . '/index/$i.php'";
            $code
                .= "\n\$_autoloader = new InstantAutoloader($indexPath);\n"
                . "\$_autoloader->setBasePath(__DIR__);\n"
                . "\$_autoloader->$autoloadMethod();\n";
            
        }
        $code .= "unset(\$_autoloader);";

        
        $isPut = @\file_put_contents($this->getAutoloaderFile(), $code);
        if (! $isPut) {
            $error = \error_get_last();
            throw new AutoloaderException_Builder_IO(
                $this->_locale->sprintf(
                    "FAILED_GENERATING_CODE",
                    $error["message"]
                )
            );

        }
        \chmod($this->getAutoloaderFile(), 0644);
    }

    /**
     * Creates a directory recursively
     *
     * @param string $path Directory
     *
     * @return void
     * @throws AutoloaderException_Builder_IO
     */
    private function _mkdir($path)
    {
        if (\file_exists($path)) {
            return;

        }
        $result = \mkdir($path, 0755, true);
        if (! $result) {
            throw new AutoloaderException_Builder_IO(
                $this->_locale->sprintf("FAILED_CREATING_DIRECTORY", $path)
            );

        }
    }

}