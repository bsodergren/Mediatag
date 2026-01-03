<?php
namespace Mediatag\Modules\Executable\Helper;

use Mediatag\Utilities\Strings;

trait DownloadStrings
{


    public $line_id = null;
    public $command = null;


    public function getShortName($filename)
    {

        $file      = str_replace(__PLEX_DOWNLOAD__, '', $filename);
        $file_name = basename($file);
        $file_path = str_replace($file_name, '', $file);
        $file_name = Strings::truncateString($file_name, 60, true, true);


        // $file_name = Strings::truncateString($file_name, 30, true);
        return $file_path . $file_name;
    }


    public function ytlpDownloadBuffer($command, $buffer, $line_id = null, $newLine = false): string
    {
        $this->line_id = $line_id;
        $this->command = $command;
        $method        = "download" . $command;
        $output        = $this->$method($buffer);
        // $output        = str_replace("\n", "", $output);
        if ($newLine === true) {
            $output = TEST_EOL . $output;
        }
        return $output;

    }


    public function downloadDestination($buffer)
    {
        $buffer = $this->cleanBuffer($buffer);

        preg_match('/(\[[a-z]+\] [a-zA-Z0-9 :]+)(\[[a-z]+\]) (Destination:) (.*)/m', $buffer, $match);
        $text = $match[3];
        $file = $this->getShortName($match[4]);
        return PHP_TAB . '<text>' . $text . '<text> <file>' . $file . '</file>' . PHP_EOL;
    }

    public function downloadProgress($buffer)
    {
        $output = trim($buffer);
        $output = '<download>' . $buffer . '</>';

        if (str_contains($buffer, '100%')) {
            $output = $output . TEST_EOL;
        }

        return $output;

    }
    public function downloadExists($buffer)
    {

        return $this->line_id . '<error>' . $this->key . ' Already been downloaded </error>';
    }

    public function downloadError($buffer)
    {

        return '<error>' . $buffer . '</error>';
    }

    public function downloadFixupM3u8($buffer)
    {

        $buffer = $this->cleanBuffer($buffer);
        preg_match('/(\[[a-zA-Z0-9]+\])(.*)"(.*)"/m', $buffer, $match);
        $text = $match[1];
        $file = $this->getShortName($match[3]);
        return PHP_TAB . '<text>' . $text . '<text> <file>' . $file . '</file>';
    }

}