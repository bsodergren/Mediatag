<?php

namespace Mediatag\Commands\Clip\Commands\Show\Trait;

use Mediatag\Core\Mediatag;
use Symfony\Component\Console\Helper\Table;

trait Playlist
{
    public function showPlaylist()
    {
        $sql     = ' SELECT id,name,genre,Library FROM ' . __MYSQL_PLAYLIST_DATA__ . ' WHERE hide =0';
        $results = Mediatag::$dbconn->query($sql);

        $table = new Table(Mediatag::$output);
        $table->setHeaderTitle('Playlist');
        $table->setHeaders(['id', 'name', 'genre', 'library']);
        foreach ($results as $cmd => $info) {
            //
            $tableRows[] = $info;
        }

        $table->setRows($tableRows);
        // $table->setStyle($tableStyle);
        $table->setStyle('borderless');
        $table->render();
        utmdd($results);
        $this->getPlaylistVideos(4);
        Mediatag::$output->writeLn('<info> show playlist name</info>');
    }

    public function getPlaylistVideos($playlist_id)
    {
        $sql = '        select CONCAT(v.fullpath,\'/\',v.filename) as file_name
        from   ' . __MYSQL_PLAYLIST_DATA__ . ' as d,
        ' . __MYSQL_VIDEO_FILE__ . '  as v,
        ' . __MYSQL_PLAYLIST_VIDEOS__ . ' as p

        where (p.playlist_id = ' . $playlist_id . ' and
        p.playlist_video_id = v.id and
         d.id = p.playlist_id ) ORDER BY v.filename ASC';

        $results = Mediatag::$dbconn->query($sql);
        utmdd($results);

        return $results;
    }
}
