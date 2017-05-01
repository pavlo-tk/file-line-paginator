<?php
/**
 * This file is a part of the FileReader package.
 *
 * @copyright Pavel Tkachov <brabus.adm@gmail.com>
 */
namespace Sbs\FileReader\Processor;

/**
 * Processes a line and extracts all placeholders, specified by a format string, into an array of key => value pairs.
 *
 * @author Pavel Tkachov <brabus.adm@gmail.com>
 */
class LineFormatProcessor implements ProcessorInterface
{
    protected $format;
    private $placeholders;
    private $customPlaceholdersRegex;
    private $lineRegex;
    /**
     * Placeholders in the format string should be wrapped with "%".
     * Example (Monolog format):
     *     "[%datetime%] %channel%.%level_name%: %message% %context% %extra%".
     *
     * @param string $format
     * @param array  $customPlaceholdersRegex An array of key/value pairs,
     *                                        where keys are placeholders' names and values are their custom regex,
     *                                        which will be used instead of a default one.
     *
     * @throws \RuntimeException When there are no properly formatted placeholders in the format string.
     */
    public function __construct($format, $customPlaceholdersRegex = array())
    {
        $this->format = (string) $format;
        $this->customPlaceholdersRegex = $customPlaceholdersRegex;

        preg_match_all('/%(\w+)%/', $this->format, $placeholders);
        $placeholders = $placeholders[1];
        if (!$placeholders) {
            throw new \RuntimeException(sprintf(
                'There are no placeholders found in the format string. Expected placeholders pattern is "%%name%%", given "%s".',
                $this->format
            ));
        }
        $this->placeholders = $placeholders;
        $this->lineRegex = $this->buildLineRegex();
    }
    /**
     * @inheritdoc
     *
     * @return array An array of key/value pairs,
     *               where keys are placeholders' names and values are their fragments from the line.
     * @throws \RuntimeException When there are no found values in a line, specified by the format.
     */
    public function process($line, $lineNumber)
    {
        preg_match($this->lineRegex, $line, $values);
        if (!$values) {
            throw new \RuntimeException(sprintf('There are no values found on the line %d accordingly to the format "%s".', $lineNumber, $this->format));
        }
        array_shift($values); // Removing full pattern match.

        return array_combine($this->placeholders, $values);
    }
    /**
     * @return string Full regex for the current line.
     */
    protected function buildLineRegex()
    {
        $defaultRegex = '(.+?)';
        foreach ($this->placeholders as $placeholder) {
            $placeholderToRegex['%'.$placeholder.'%'] = isset($this->customPlaceholdersRegex[$placeholder])
                ? $this->customPlaceholdersRegex[$placeholder]
                : $defaultRegex;
        }

        return '/'.str_replace(array_keys($placeholderToRegex), array_values($placeholderToRegex), preg_quote($this->format)).'/';
    }
}