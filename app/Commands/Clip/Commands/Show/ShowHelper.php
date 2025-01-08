<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Show;

use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use UTM\Utilities\Option;

trait ShowHelper
{
    
    
    private $cmdOptions = [
        'filter' => ['cmd'=>'showTransitionType','desc'=>'Show all transition types'],
        
    ];


    
    
    public function filters()
    {
        $showCmd = Option::getValue('show', 1);
        if(array_key_exists($showCmd,$this->cmdOptions))
        {
            $method = $this->cmdOptions[$showCmd]['cmd'];
            $this->$method();
            return 1;
        }

        $this->defaultCmd();

        utmdd($showCmd);
    }

    public function defaultCmd()
    {


 $table = new Table(Mediatag::$output);
        foreach($this->cmdOptions as $cmd => $info){
           
            // utmdd($info);
            // $table->setHeaderTitle($cmd);
            $table->setRows([[$cmd,$info['desc']]]);
            
        }
        // $table->setStyle($tableStyle);
        $table->setStyle('borderless');
        $table->render();
        Mediatag::$output->writeLn('<info> No option found </info>');
        
    }

    public function showTransitionType()
    {
        $array = array_chunk($this->transition_types, 4);

        $tableStyle = new TableStyle();
        $tableStyle->setHorizontalBorderChars('<fg=magenta>-</>');
        $tableStyle->setVerticalBorderChars('<fg=magenta>|</>');
        $tableStyle->setDefaultCrossingChar(' ');

        $table = new Table(Mediatag::$output);
        // $table->setHeaderTitle('Transition types');
        $table->setRows($array);
        $table->setStyle($tableStyle);
        // $table->setStyle('borderless');
        $table->render();
    }
}
