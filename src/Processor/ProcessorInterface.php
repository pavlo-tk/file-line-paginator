<?php
/**
 * This file is a part of the FileReader package.
 *
 * @copyright Pavel Tkachov <brabus.adm@gmail.com>
 */
namespace Sbs\FileReader\Processor;

/**
 * @author Pavel Tkachov <brabus.adm@gmail.com>
 */
interface ProcessorInterface
{
    /**
     * @param string $line
     * @param int    $lineNumber
     *
     * @return array
     */
    public function process($line, $lineNumber);
}