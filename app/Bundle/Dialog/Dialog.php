<?php
namespace Mediatag\Bundle\Dialog;

class Dialog
{
    /*
     * Define any of these variables to change the exit code on
     * Cancel (1), error (-1), ESC (255), Extra (3), Help  (2),
     * Help  with  --item-help  (4), or OK (0).  Normally shell
     * scripts cannot distinguish between -1 and 255.
     */

    const BUTTON_YES = 0;
    const BUTTON_OK = 0;
    const BUTTON_NO = 1;
    const BUTTON_CANCEL = 1;
    const BUTTON_HELP = 2;
    const BUTTON_EXTRA = 3;
    const BUTTON_ITEM_HELP = 4;
    const ERROR = -1;
    const ESC = 255;

    protected $dialog_bin = 'dialog';
    protected $common_options = null;
    protected $box_options = null;
    protected $exit_code = 0;
    protected $output = '';
    protected $pipes = [];
    protected $process = null;

    public function __construct(\Mediatag\Bundle\Dialog\Options\Common $common_options, \Mediatag\Bundle\Dialog\Options\Box $box_options)
    {
        $this->common_options = $common_options;
        $this->box_options = $box_options;
        $this->box_options->dialog($this);
    }

    public function run(): void
    {

        if ($this->box_options->widget() === 'programbox' || $this->box_options->widget() === 'progressbox') {
            $cmd = "{$this->box_options->cmd()} | {$this->bin()}{$this->common_options->parseToString()} {$this->box_options->parseToString()}";
        } else {
            $cmd = "{$this->bin()}{$this->common_options->parseToString()} {$this->box_options->parseToString()}";
        }
//    	exit($cmd.PHP_EOL);
        $pipes = [];
        $in = ['pipe', 'r'];
        $out = fopen('php://stdout', 'w');
        $err = ['pipe', 'w'];
        $descriptorspec = [
            0 => $in,
            1 => $out,
            2 => $err
        ];
utmdump($cmd);
        $process = $this->process = proc_open($cmd, $descriptorspec, $pipes);

        if (!is_resource($process)) {
            throw new Exception("Fail to run $cmd");
        }

        $this->pipes = $pipes;

        if ($this->box_options->widget() === 'gauge') {
            $updater = $this->box_options->updater();
            $updater($this);
            $this->write("\n\r100"); //Ensures completion of the gauge
            $this->output = 100;
        } elseif ($this->box_options->widget() === 'mixedgauge') {
            //dont do nothing
        } elseif ($this->box_options->widget() === 'programbox') {
            //dont do nothing
        } else {//Required to prevent PHP from waiting forever for an output.
            $this->output = stream_get_contents($pipes[2]);
        }

        $proc_status = proc_get_status($process);
        $this->exit_code = $proc_status['exitcode'];

        $this->box_options->output($this->output);
        $this->box_options->exit_code($this->exit_code);
        $this->box_options->common_options($this->common_options);

        fclose($pipes[0]);
        fclose($out);
        fclose($pipes[2]);
        proc_close($process);
        return;
    }

    public function bin(string $dialog_bin = null): string
    {
        if (is_null($dialog_bin)) {
            return $this->dialog_bin;
        } else {
            return $this->dialog_bin = $dialog_bin;
        }
    }

    public function output(): string
    {
        return (string) $this->output;
    }

    public function exit_code(): int
    {
        return (int) $this->exit_code;
    }

    public function write($data): void
    {
        fwrite($this->pipes[0], $data);
        return;
    }

    public function __destruct()
    {
        if (is_resource($this->process)) {
            $status = proc_get_status($this->process);
            if ($status['running'] === true) {
                proc_terminate($this->process, SIGTERM);
            }
        }
    }
    
    public function box(): \Mediatag\Bundle\Dialog\Options\Box
    {
        return $this->box_options;
    }
}
