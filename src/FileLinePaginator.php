<?php
/**
 * This file is a part of the FileReader package.
 *
 * @copyright Pavel Tkachov <brabus.adm@gmail.com>
 */
namespace Sbs\FileReader;

use Sbs\FileReader\Processor\ProcessorInterface;

/**
 * @author Pavel Tkachov <brabus.adm@gmail.com>
 */
class FileLinePaginator
{
    protected $file;
    /**
     * @var ProcessorInterface[]
     */
    protected $processors = array();

    public function __construct($file)
    {
        if (!$file instanceof \SplFileObject) {
            $file = new \SplFileObject($file);
        }
        $this->file = $file;
    }
    public function addProcessor(ProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    public function getTotalLines()
    {
        $this->file->seek($this->file->getSize());
        return $this->file->key();
    }
    public function get($offset, $limit = 30)
    {
        if ($offset < 0) $offset = 0;
        $rows = array();

        $limitIterator = new \LimitIterator($this->file, $offset, $limit);
        foreach ($limitIterator as $line) {
            if (!$line) continue; // Pass empty lines.
            foreach ($this->processors as $processor) {
                $row = $processor->process($line, $limitIterator->getPosition() + 1);
            }
            $rows[] = isset($row) ? $row : $line;
        }

        return $rows;
    }
}