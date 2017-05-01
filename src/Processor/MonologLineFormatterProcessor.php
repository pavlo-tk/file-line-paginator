<?php
/**
 * This file is a part of the FileReader package.
 *
 * @copyright Pavel Tkachov <brabus.adm@gmail.com>
 */
namespace Sbs\FileReader\Processor;

/**
 * Line format processor specific to Monolog file logging.
 * It gets the line format from the Monolog LineFormatter class throught class reflection.
 *
 * @author Pavel Tkachov <brabus.adm@gmail.com>
 */
class MonologLineFormatterProcessor extends LineFormatProcessor
{
    /**
     * @param string $lineFormatterClass
     * @param string $formatConstantName
     *
     * @throws \RuntimeException When LineFormatter class or its constant with format string not found.
     */
    public function __construct($lineFormatterClass = 'Monolog\Formatter\LineFormatter', $formatConstantName = 'SIMPLE_FORMAT')
    {
        if (!class_exists($lineFormatterClass)) {
            throw new \RuntimeException(sprintf('Monolog line formatter class "%s" not found.', $lineFormatterClass));
        }

        $classReflection = new \ReflectionClass($lineFormatterClass);
        if (!$classReflection->hasConstant($formatConstantName)) {
            throw new \RuntimeException(sprintf('Monolog line formatter "%s" has no constant name "%s".', $lineFormatterClass, $formatConstantName));
        }

        // All available keys can be found in \Monolog\Logger.php:251
        $customPlaceholdersRegex = array(
            'channel' => '(\w+?)',
            'level' => '(\d+?)',
            'level_name' => '(\w+?)',
            'context' => '(\{.+?\}|\[\])', // Allows JSON-encoded objects ({"file":"…","line":45}) and empty arrays ([]).
            'extra' => '(\{.+?\}|\[\])',
        );

        parent::__construct($classReflection->getConstant('SIMPLE_FORMAT'), $customPlaceholdersRegex);
    }

    public function process($line, $lineNumber)
    {
        $row = parent::process($line, $lineNumber);

        if (isset($row['context'])) {
            $row['context'] = json_decode($row['context'], true);
        }
        if (isset($row['extra'])) {
            $row['extra'] = json_decode($row['extra'], true);
        }

        $row['file'] = $this->find($row, 'file');
        $row['line'] = $this->find($row, 'line');

        return $row;
    }
    /**
     * Finds given parameter in the row's data.
     *
     * @param array  $row
     * @param string $parameter
     *
     * @return mixed|null
     */
    protected function find(array &$row, $parameter)
    {
        if (isset($row['context'][$parameter])) {
            $result = $row['context'][$parameter];
            unset($row['context'][$parameter]);
        }
        else if (isset($row['extra'][$parameter])) {
            $result = $row['extra'][$parameter];
            unset($row['extra'][$parameter]);
        }
        else $result = null;

        return $result;
    }
}