<?php 
namespace Mediatag\Modules\Database\Maps;

trait StudioMap
{
    
    public function getStudioPath($text)
    {
        $query = 'SELECT library, path,studio FROM '.__MYSQL_STUDIOS__." WHERE name LIKE '".$text."'";
        $result = $this->dbConn->rawQueryOne($query);

        if (null !== $result) {
            utmdump($result);
            if($result['path'] === null ||
                $result['path'] == "")
            {
                 unset($result['path']);
            }
            $path = implode('/', $result);

            return rtrim($path, '/');
        }

        return false;
    }

    public function addStudioMap($library, $name, $studio, $path) // $library,$name, $path, $studio)
    {
        $library = "'".$library."'";
        $name = "'".$name."'";
        $studio = "'".$studio."'";

        if (null !== $path) {
            $path = "'".$path."'";
        } else {
            $path = 'NULL';
        }

        $query = 'INSERT IGNORE INTO '.__MYSQL_STUDIOS__.'  (library, name, studio, path) VALUES ('.$library.','.$name.','.$studio.', '.$path.') ';
        $query = $query.' ON DUPLICATE KEY UPDATE library='.$library.',studio='.$studio.',path='.$path;

        $this->dbConn->rawQuery($query);
    }

    public function dropStudio($library, $name) // $library,$name, $path, $studio)
    {
        $library = "'".$library."'";
        $name = "'".$name."'";
        $query = 'DELETE FROM '.__MYSQL_STUDIOS__.' WHERE Library = '.$library.' and name = '.$name.'';
        $this->dbConn->rawQuery($query);
    }
}