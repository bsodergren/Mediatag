<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Modules\Display;

use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;

class MediaTable
{
    public $meta;

    public $table;

    public function __construct(OutputInterface $output)
    {
        utminfo(func_get_args());

        $output->{$this}->output = $output;
        $this->section1          = $output->section();
    }

    public function displayTable($videoInfo)
    {
        utminfo(func_get_args());

        $this->table = new Table($this->section1);
        $this->table->setStyle('box');
        $this->table->setRow(1, ['Video', $videoInfo['video_name']]);
        $this->table->addrow(new TableSeparator());
        $idx         = 2;
        foreach ($videoInfo['metatags'] as $tag => $value) {
            $this->table->setRow($idx, [$tag, $value]);
            ++$idx;
        } $this->table->setColumnWidth(0, 12);
        $this->table->setColumnWidth(1, 100);
        $this->table->render();
        $this->output->clear();
    }

    public function row($key, $value)
    {
        utminfo(func_get_args());

        return [
            new TableCell($key, ['style' => new TableCellStyle(['cellFormat' => '<info>%s</info>'])]),
            new TableCell($value, ['style' => new TableCellStyle(['cellFormat' => '<info>%s</info>'])]),
        ];
    }
}
