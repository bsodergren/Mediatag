<?php 
namespace Mediatag\Modules\Display;

use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Helper\ProgressIndicator;


class MediaIndicator
{
    public $BarStyle = ['*----', '-*---', '--*--', '---*-', '----*', '---*-', '--*--', '-*---'];
    public $section;

    public $progress;

    public function __construct($section)
    {
        if (null === $section) {
            $section = Mediatag::$output;
        } else {
            $section = Mediatag::$output->section();
        }
        $this->progress = new ProgressIndicator($section, 'normal', 50, $this->BarStyle, '🎉');
        
    }
    public function advance()
    {
        $this->progress->advance();
    }

    public function startIndicator($message)
    {
        $this->progress->start('<fg=bright-cyan>'.$message.'</>');
    }

    public function finishIndicator($message)
    {
        $this->progress->finish('<fg=green>'.$message.'</>');
    }



}