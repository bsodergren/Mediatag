<?php

/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Clip\Commands\Show;

use Mediatag\Commands\Clip\Commands\Show\Trait\Playlist;
use Mediatag\Commands\Clip\Commands\Show\Trait\Transitions;
use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use UTM\Utilities\Option;

use function array_key_exists;

trait ShowHelper
{
    use Playlist;
    use Transitions;

    public $cmdOptions = [
        'filter'   => ['cmd' => 'showTransitionType', 'desc' => 'Show all transition types'],
        'playlist' => ['cmd' => 'showPlaylist', 'desc' => 'Show all playlist types'],
    ];

    public function filters()
    {
        $showCmd = Option::getValue('show', 1);
        if (array_key_exists($showCmd, $this->cmdOptions)) {
            $method = $this->cmdOptions[$showCmd]['cmd'];
            $this->$method();

            return 1;
        }

        $this->defaultCmd($this->cmdOptions);

        utmdd($showCmd);
    }
}
