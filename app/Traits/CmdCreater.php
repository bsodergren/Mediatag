<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Traits;

use Mediatag\Core\Mediatag;


use Nette\Utils\FileSystem;

trait CmdCreater
{
    private $BIN_TEMPLATE  = __DATA_TEMPLATES__ . '/App_template.txt';

    private $CMD_TEMPLATE  = __DATA_TEMPLATES__ . '/Command/Command_template.txt';

    private $LANG_TEMPLATE = __DATA_TEMPLATES__ . '/Command/Lang_template.txt';

    private $OPT_TEMPLATE  = __DATA_TEMPLATES__ . '/Command/Options_template.txt';

    private $PROC_TEMPLATE = __DATA_TEMPLATES__ . '/Command/Process_template.txt';

    public $APP_COMMAND    = __APP_HOME__ . '/config/commands.php';

    public function template($template, $params = [])
    {
        utminfo();

        $template_text = $this->loadTemplate($template);

        return $this->parse($template_text, $params);
    }

    public function callback_replace($matches)
    {
        utminfo();

        return '';
    }

    private function whichTemplate($template_class)
    {
        utminfo();

        switch ($template_class) {
            case 'app':
                return $this->BIN_TEMPLATE;

            case 'cmd':
                return $this->CMD_TEMPLATE;

            case 'lang':
                return $this->LANG_TEMPLATE;

            case 'opt':
                return $this->OPT_TEMPLATE;

            case 'proc':
                return $this->PROC_TEMPLATE;

            default:
                return null;
        }

        return null;
    }

    private function loadTemplate($template)
    {
        utminfo();

        if (null !== $template) {
            $template_file = $this->whichTemplate($template);

            return file_get_contents($template_file);
        }

        return null;
    }

    private function parse($text, $params = [])
    {
        utminfo();

        if (\is_array($params)) {
            foreach ($params as $key => $value) {
                $key  = '%%' . strtoupper($key) . '%%';
                $text = str_replace($key, $value, $text);
            }

            $text = preg_replace_callback('|%%(\w+)%%|i', [$this, 'callback_replace'], $text);
        }

        return $text;
    }

    public function AddCommand($params = [])
    {
        utminfo();

        $cmd_template  = file_get_contents($this->APP_COMMAND);

        $command_name  = $params['COMMAND_CLASS'];
        $command_use   = $params['COMMAND_USE'];
        $command_class = $command_name . 'Command';

        if (false == str_contains($cmd_template, $command_class)) {
            $cmds_array = [
                'NEW_CMD' => "    '" . strtolower($command_name) . "' => function () { return new " . $command_class . '(); },',
                'NEW_USE' => $command_use . ' as ' . $command_class . ';',
            ];

            foreach ($cmds_array as $key => $value) {
                $find = '//%%' . strtoupper($key) . '%%';
                if (null != $value) {
                    $value        = $value . \PHP_EOL . '//%%' . $key . '%%';
                    $cmd_template = str_replace($find, $value, $cmd_template);
                }
            }
            FileSystem::write($this->APP_COMMAND, $cmd_template);
        }
    }
}
