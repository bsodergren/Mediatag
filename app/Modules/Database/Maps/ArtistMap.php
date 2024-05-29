<?php 
namespace Mediatag\Modules\Database\Maps;

trait ArtistMap
{

    
    public function addArtist($artist, $ignore = 0, $replacement = null)
    {

        if (null !== $replacement) {
            $replacement = '"'.$replacement.'"';
        } else {
            $replacement = 'NULL';
        }

        $query = 'INSERT IGNORE INTO '.__MYSQL_ARTISTS__.' (name,hide,replacement) VALUES ("'.$artist.'",'.$ignore.','.$replacement.')';
        $query = $query.' ON DUPLICATE KEY UPDATE name="'.$artist.'",hide='.$ignore.',replacement='.$replacement;
        $this->dbConn->rawQuery($query);
    }

    public function dropArtist($artist)
    {
        $query = 'DELETE FROM '.__MYSQL_ARTISTS__.' WHERE name = "'.$artist.'"';
        $this->dbConn->rawQuery($query);
    }

    public function getArtistMap()
    {
        $query = 'SELECT name,replacement FROM '.__MYSQL_ARTISTS__.' WHERE hide = 0';
        //   $query = 'SELECT name FROM '.__MYSQL_ARTISTS__.' WHERE hide = 0';
        $res = $this->dbConn->rawQuery($query);
        foreach ($res as $k => $val) {
            // $namesArray[] = $val['name'];//,$val['replacement']];
            $namesArray[] = [$val['name'], $val['replacement']];
        }

        return $namesArray;
    }

    public function getIgnoredArists()
    {
        $query = 'SELECT name FROM '.__MYSQL_ARTISTS__.' WHERE hide = 1';
        $res = $this->dbConn->rawQuery($query);
        foreach ($res as $k => $val) {
            $namesArray[] = $val['name'];
        }
        return $namesArray;
    }

}