<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the AutoloaderFileIterator_Simple
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
 * @subpackage FileIterator
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
    'AutoloaderFileIterator',
    __DIR__ . '/AutoloaderFileIterator.php'
);

/**
 * Searches all files without any logic
 *
 * It uses RecursiveDirectoryIterator.
 *
 * @category   PHP
 * @package    Autoloader
 * @subpackage FileIterator
 * @author     Markus Malkusch <markus@malkusch.de>
 * @license    http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version    Release: 1.12
 * @link       http://php-autoloader.malkusch.de/en/
 * @see        Autoloader::searchPath()
 */
class AutoloaderFileIterator_Simple extends AutoloaderFileIterator
{

    private
    /**
     * @var \RecursiveDirectoryIterator
     */
    $_iterator;

    /**
     * Returns the path of the current file
     *
     * @see Iterator::current()
     * @return String
     */
    public function current()
    {
        return $this->_iterator->current()->getPathname();
    }

    /**
     * Returns the key of the current file.
     *
     * This key is not meant to be used or to be distinct.
     *
     * @see Iterator::key()
     * @return String
     */
    public function key()
    {
        return $this->_iterator->key();
    }

    /**
     * Calls next() on the iterator
     *
     * @see Iterator::next()
     * @return void
     */
    public function next()
    {
        $this->_iterator->next();
    }

    /**
     * Rewinds the iterator
     *
     * @see Iterator::rewind()
     * @return void
     */
    public function rewind()
    {
        $flags = \FilesystemIterator::SKIP_DOTS
            | \FilesystemIterator::FOLLOW_SYMLINKS
            | \FilesystemIterator::CURRENT_AS_FILEINFO;
        $this->_iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->autoloader->getPath(), $flags)
        );
        $this->_iterator->rewind();
    }

    /**
     * Filters the result set
     * 
     * @see Iterator::valid()
     * @return bool
     */
    public function valid()
    {
        if (! $this->_iterator->valid()) {
            return false;
            
        }
        
        // skip unreadable nodes
        if (! $this->_iterator->current()->isReadable()) {
            $this->_iterator->next();
            return $this->valid();
            
        }
        
        $path = $this->_iterator->current()->getPathname();

        // apply file filters
        foreach ($this->skipPatterns as $pattern) {
            if (preg_match($pattern, $path)) {
                $this->_iterator->next();
                return $this->valid();

            }
        }

        // skip too big files
        $isTooBig
            = ! empty($this->skipFilesize)
            && $this->_iterator->current()->getSize() > $this->skipFilesize;
        if ($isTooBig) {
            $this->_iterator->next();
            return $this->valid();

        }

        return true;
    }

}