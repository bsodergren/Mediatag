<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Watch;

use Mediatag\Bundle\Monitor\Monitor;
use Mediatag\Bundle\Monitor\MonitorConfigurator;
use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\Display;
use Mediatag\Modules\Display\MediaIndicator;
use React\EventLoop\Loop;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process as execProcess;

class Process extends Mediatag
{
    use Helper;

    public $VideoList = [];

    public $defaultCommands = [
        'exec' => null,
    ];

    public $commandList = [
        'colors'     => [
            'colors'      => null,
        ],
    ];

    public static $progressIndicator;
    public static $progressIndicator3;

    public static $outLn;

    public static $outCmd;
    public static $out_e;
    public static $out_x;

    public function __construct(?InputInterface $input = null, ?OutputInterface $output = null, $args = null)
    {
        parent::boot($input, $output);

        $display = new Display(Mediatag::$output);
        //    self::$outCmd = $display->BarBottom;

        self::$progressIndicator  = new MediaIndicator('Top');
        self::$progressIndicator3 = new MediaIndicator('second');
        self::$progressIndicator->startIndicator('watching folder '.__CURRENT_DIRECTORY__);
        self::$outLn  = $display->MetaBlockSection;
        self::$outCmd = $display->VideoInfoSection;
    }

    public function exec($option = null)
    {
        //     //        Mediatag::$output->write('Updating timestamp... ');
        //     // sleep(2);
        //     // Mediatag::$output->writeln('Updating timestamp...  ' . time());

        $monitor = new Monitor(MonitorConfigurator::factory()
        ->setBaseDirectory(__CURRENT_DIRECTORY__)
        ->setLevel(4)
        ->setFilesToMonitor([
            '*.mp4',
        ]));

        $loop = Loop::get();
        $loop->addPeriodicTimer(0.5, function () {
            self::$progressIndicator->advance();
        });

        $monitor
        ->on(Monitor::EV_CLOSE, function ($path, $monitor) {
            if (!str_contains($path, '-temp-')) {
                // self::writeOut('file closed: ', dirname($path), 'update');
                // sleep(2);
                self::$progressIndicator3->startIndicator('Updating file '.\dirname($path));

                //                self::writeOut('file closed: ', \dirname($path), 'update');
                $this->update($path);

                $this->dbupdate();
                $this->dbupdateAll();
                //         }
                // self::writeOut('file closed: ', basename($path), 'update');
                // echo "file closed:   $path\n";
            }
        })
          ->on(Monitor::EV_CREATE, function ($path, $monitor) {
              //   self::writeOut('File created: ', basename($path), 'info');
          })
          ->on(Monitor::EV_MODIFY, function ($path, $monitor) {
              // echo "modified:  $path\n";
          })
          ->on(Monitor::EV_DELETE, function ($path, $monitor) {
              // $this->dbupdate();
              self::writeOut('File deleted: ', basename($path), 'error');
          })
          ->run();
    }

    public static function writeOut($type, $line, $style = 'info')
    {
        self::$outLn->writeln('');
        self::$outLn->writeln('<'.$style.'>'.$type.'</> <comment>'.trim($line).'</comment>');
    }

    public static function cmdOut($line, $style = 'info')
    {
        // $this->out->writeln('');
        self::$outCmd->writeln('<'.$style.'>'.$line.'</>');
    }

    private function update($file)
    {
        $command = ['mediaupdate', '--nocache', '-f', $file];
        $process = new execProcess($command);
        $process->setTimeout(60000);
        $this->runCommand = $process->getCommandLine();
        utmdump($this->runCommand);
        $process->run(function ($type, $buffer): void {
            // if (execProcess::ERR === $type) {
            //     echo 'ERR > '.$buffer;
            // } else {
            //     echo 'OUT > '.$buffer;
            // }

            if (!str_contains($buffer, 'Progress')) {
                self::cmdOut($buffer);
            } else {
                self::$progressIndicator3->advance();
            }
        });
    }

    private function dbupdate()
    {
        $command = ['mediadb'];
        $process = new execProcess($command);
        $process->setTimeout(60000);
        $this->runCommand = $process->getCommandLine();
        // utmdd($this->runCommand);
        $process->run(function ($type, $buffer): void {
            self::cmdOut($buffer);
        });
    }

    private function dbupdateAll()
    {
        $command = ['mediadb', 'all'];
        $process = new execProcess($command);
        $process->setTimeout(60000);
        $this->runCommand = $process->getCommandLine();
        // utmdd($this->runCommand);
        $process->run(function ($type, $buffer): void {
            self::cmdOut($buffer);
        });
    }
}
