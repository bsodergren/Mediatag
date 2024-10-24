<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Phdb;

use Mediatag\Core\Mediatag;
use Nette\Utils\Strings;
use Nette\Utils\Callback;
use UTM\Utilities\Option;
use Nette\Utils\FileSystem;
use Mediatag\Traits\Callables;
use Mediatag\Modules\Display\MediaBar;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Modules\Filesystem\MediaFinder;
use Mediatag\Modules\Filesystem\MediaFilesystem;
use Symfony\Component\Process\Process as ExecProcess;
use Symfony\Component\Filesystem\Filesystem as SFilesystem;

trait CaseHelperCreator
{
    public $phDbCaseHelper   =  __PORNHUB_DB_MAP_FILE__;

    public $CaseHelperHeader = <<<'EOT'
<?php

namespace Mediatag\Traits;
use Mediatag\Core\Mediatag;


trait CaseHelper {

    public function in_range( $number,  $lowerBound,  $upperBound) {
        return $number >= $lowerBound &&  $number <=  $upperBound;
    }
    public function getphdbUrl($number)
    {


EOT;

    public $CaseHelperFooter = <<<'EOF'
        return false;
    }
}
EOF;

    public function csvmapCallback($type, $buffer)
    {

        if (ExecProcess::ERR === $type) {
            // echo 'ERR > '.$buffer;
        } else {
            $this->lineOut = trim($buffer);
        }
    }
    private function firstLine($file)
    {
        utminfo(func_get_args());

        $callback = Callback::check([$this, 'csvmapCallback']);

        $command  = [
            '/usr/bin/head',
            '-1',
            $file,
        ];
        $proccess = new ExecProcess($command);
        // utmdd($proccess->getCommandLine());
        $proccess->run($callback);
        return $this->lineOut;
    }
    private function lastLine($file)
    {
        utminfo(func_get_args());

        $callback = Callback::check([$this, 'csvmapCallback']);

        $command  = [
            '/usr/bin/tail',
            '-1',
            $file,
        ];
        $proccess = new ExecProcess($command);
        // utmdd($proccess->getCommandLine());
        $proccess->run($callback);
        return $this->lineOut;
    }

    public function map()
    {
        utminfo(func_get_args());

        $filesystem = new SFilesystem();

        if (!is_array($this->ph_csv)) {
            $this->ph_csv = [$this->ph_csv];
        }
        // utmdd($this->ph_csv);
        //utmdd($this->phDbCaseHelper);
        $filesystem->remove($this->phDbCaseHelper);


        $filesystem->appendToFile($this->phDbCaseHelper, $this->CaseHelperHeader);

        foreach ($this->ph_csv as $thisFile) {
            [$_a,$firstLine] = explode("|", $this->firstLine($thisFile));
            [$_b,$lastLine]  = explode("|", $this->lastLine($thisFile));

            $string          = <<<"EOT"
                    if (\$this->in_range(\$number, $firstLine,$lastLine )) {
                        return "$thisFile";
                    }

            EOT;
            $filesystem->appendToFile($this->phDbCaseHelper, $string);

        }
        $filesystem->appendToFile($this->phDbCaseHelper, $this->CaseHelperFooter);
        return true;
    }


}
