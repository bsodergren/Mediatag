<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Config;

use Mediatag\Commands\Create\Command as CreateCommand;
use Mediatag\Commands\Db\Command as DBCommand;
use Mediatag\Commands\Download\Command as DlCommand;
use Mediatag\Commands\Gallery\Command as GalleryCommand;
use Mediatag\Commands\Map\Command as MapCommand;
use Mediatag\Commands\Playlist\Command as PlaylistCommand;
use Mediatag\Commands\Rename\Command as RenameCommand;
use Mediatag\Commands\Show\Command as ShowCommand;
use Mediatag\Commands\Test\Command as TestCommand;
use Mediatag\Commands\Clip\Command as ClipCommand;

use Mediatag\Commands\Update\Command as UpdateCommand;
// %%NEW_USE%%

use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

return new FactoryCommandLoader([
    //    'app:heavy' => function () { return new HeavyCommand(); },
    'create'    => function () {return new CreateCommand(); },
    'playlist'  => function () {return new PlaylistCommand(); },
    'download'  => function () {return new DlCommand(); },

    'map'       => function () {return new MapCommand(); },
    'test'      => function () {return new TestCommand(); },
    'update'    => function () {return new UpdateCommand(); },
    'show'      => function () {return new ShowCommand(); },
    'db'        => function () {return new DBCommand(); },
    'gallery'   => function () {return new GalleryCommand(); },
    'rename'    => function () {return new RenameCommand(); },
    'clip'    => function () {return new ClipCommand(); },
    
    // %%NEW_CMD%%
]);
