<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Commands\Map;

use Mediatag\Core\Mediatag;
use Mediatag\Modules\Display\MapDisplay;
use Mediatag\Modules\Filesystem\MediaFile;
use Mediatag\Utilities\MediaArray;
use Mediatag\Utilities\ScriptWriter;
use Mediatag\Utilities\Strings;
use UTM\Utilities\Option;

trait Helper
{
    public $wordMap  = '/home/bjorn/scripts/Mediatag/config/data/map/Words.txt';
    public $phArtist = '/home/bjorn/scripts/Mediatag/config/data/map/Studio/PornWorld.txt';
    public $wordDict = '/home/bjorn/scripts/Mediatag/node_modules/wordsninja/words-en.txt';

    public function artistMap()
    {
        // utminfo(func_get_args());

        $artistMap                  = Option::getValue('artistMap', 1);

        list($artist, $replacement) = explode(":", $artistMap);
        $artist                     = strtolower($artist);
        $contents                   = file_get_contents($this->phArtist);
        $lines                      = explode("\n", $contents);
        foreach ($lines as $idx => $line) {
            $parts = explode(":", $line);
            if ($artist == strtolower($parts[0])) {
                return null;
            }
        }
        $lines[]                    = $artist . ":" . $replacement;

        $lines                      = array_unique($lines);
        sort($lines, SORT_REGULAR);
        $contents                   = implode("\n", $lines);

        file_put_contents($this->phArtist, $contents);
        Mediatag::$Console->info('Artist Dictionary', ['Added' => $artist . ":" . $replacement]);


    }

    public function addText()
    {
        // utminfo(func_get_args());


        $word  = Option::getValue('word', 1);

        $lines = file($this->wordDict);
        foreach ($lines as $line) {
            if (strpos($line, $word) !== false) {
                return false;
            }
        }

        $lines = file($this->wordMap);
        foreach ($lines as $line) {
            if (strpos($line, $word) !== false) {
                return false;
            }
        }

        MediaFile::file_append_file($this->wordMap, PHP_EOL . $word);
        Mediatag::$Console->info('Word Dictionary', ['Added' => $word]);
    }

    public function limit()
    {
        // utminfo(func_get_args());

        $offset = null;

        if (Option::isTrue('max')) {
            if (option::isTrue('filenumber')) {
                $offset = Option::getValue('filenumber') . ',';
            }

            return ' LIMIT ' . $offset . option::getValue('max');
        }
    }

    public function SearchDB($keyword)
    {
        // utminfo(func_get_args());

        $where = '';

        if (Option::isTrue('show')) {
            $where = ' AND keep = 1 ';
        }
        if (Option::isTrue('hide')) {
            $where = ' AND keep = 0 ';
        }

        $query = 'SELECT * FROM ';

        if (Option::isTrue('genre')) {
            $query .= __MYSQL_GENRE__ . " WHERE  genre  LIKE '%" . $keyword . "%'" . $where;

            return $query . $this->limit();
        }

        if (Option::isTrue('keyword')) {
            $query .= __MYSQL_KEYWORD__ . " WHERE  keyword  LIKE '%" . $keyword . "%'" . $where;

            return $query . $this->limit();
        }

        if (Option::isTrue('channel')) {
            $channel = Option::getValue('channel');

            $query .= __MYSQL_STUDIOS__ . " WHERE  name  LIKE '%" . $keyword . "%' AND Library = '" . $channel . "'";

            return $query . $this->limit();
        }

        // if (Option::isTrue('amateur'))
        // {
        //     $query .= __MYSQL_STUDIOS__." WHERE  name  LIKE '%".$keyword."%' AND Library = 'Amateur'";

        //     return $query.$this->limit();
        // }
    }

    public function searchDBEntry($value)
    {
        // utminfo(func_get_args());

        $search  = $this->SearchDB($value);

        $results = $this->tagConn->query($search);
        if (\count($results) > 0) {
            $table = new MapDisplay(Mediatag::$output);

            $table->drawTable($results);
            $table->addRow($results);
        } else {
            Mediatag::$output->writeln('No results');
        }
    }

    public function keywordMap($text)
    {
        // utminfo(func_get_args());

        $this->tagMap('keyword', $text);
    }

    /**
     * Summary of genreMap.
     */
    public function genreMap($text)
    {
        // utminfo(func_get_args());

        $text = Option::getValue('genre', 1);
        $this->tagMap('genre', $text);
    }

    public function tagMap($tag, $text)
    {
        // utminfo(func_get_args());

        switch ($tag) {
            case 'genre':
                $addMethod    = 'addNewGenreReplacement';
                $updateMethod = 'updateGenre';

                $header[]     = [
                    'id'          => 1,
                    'genre'       => 1,
                    'replacement' => 1,
                    'keep'        => 1, ];

                break;

            case 'keyword':
                $addMethod    = 'addNewKeywordReplacement';
                $updateMethod = 'updateKeyword';
                $header[]     = [
                    'id'          => 1,
                    'keyword'     => 1,
                    'replacement' => 1,
                    'keep'        => 1, ];

                break;
        }

        if (! isset($text)) {
            echo "text vale for {$tag} not set";

            return 0;
        }

        if (str_contains($text, ',')) {
            $array = explode(',', $text);
        } else {
            $array[] = $text;
        }

        $table = new MapDisplay(Mediatag::$output);
        $table->drawTable($header);

        foreach ($array as $tagValue) {
            if (option::istrue('add')) {
                $this->StorageConn->{$addMethod}($tagValue, Option::getValue('add'), $this->Replace());
            } else {
                $replacement = null;

                if (option::istrue('replacement')) {
                    $replacement = Option::getValue('replacement');
                } else {
                    if (str_contains($tagValue, '=')) {
                        $replacement = Strings::after($tagValue, '=');
                        $tagValue    = Strings::before($tagValue, '=');
                    }
                }

                $this->StorageConn->{$updateMethod}($tagValue, $replacement, $this->Replace());
            }
            $search  = $this->SearchDB($tagValue);
            $results = $this->tagConn->query($search);
            $table->addRow($results);
        }
    }

    public function Replace()
    {
        // utminfo(func_get_args());

        if (Option::isTrue('show')) {
            return 1;
        }
        if (Option::isTrue('hide')) {
            return 0;
        }

        return null;
    }

    public function addStudioEntry($library, $text)
    {
        // utminfo(func_get_args());

        // $library         = Option::getValue('channel', 1);
        // $text         = Option::getValue('studio', 1);

        $studio = $text;
        $name   = $text;

        if (str_contains($text, '=')) {
            $name   = Strings::before($text, '=');
            $studio = Strings::after($text, '=');
        }

        $path   = Option::getValue('dir');
        if (null !== $path) {
            $t = $path;
            // $path = $studio;
            // $studio = $t;
        }

        $this->StorageConn->addStudioMap($library, $name, $studio, $path);
        Mediatag::$Console->info('Studio Map', ['Library' => $library], ['name' => $name], ['studio' => $studio], ['path' => $path]);
        //        Mediatag::$output->writeln('Studio:"'.$studio.'", Lib "'.$library .'" Name:"' . $name .  '" Path:"'.$path .'"');
    }

    public function addStudioChannelEntry()
    {
        // utminfo(func_get_args());

        $library = Option::getValue('channel');
        $studio  = Option::getValue('studio');
        $drop    = false;

        if (! isset($studio)) {
            echo 'No channels to set ';

            return 0;
        }
        if (! isset($studio[0])) {
            echo 'No Studio to map';

            return 0;
        }
        if (Option::isTrue('drop')) {
            $drop = true;
        }

        foreach ($studio as $studio_str) {
            $studio_str = trim($studio_str, '=');
            if (true === $drop) {
                $this->StorageConn->dropStudio($library, $studio_str);
            } else {
                $this->addStudioEntry($library, $studio_str);
            }
        }
    }

    public function addartistentry()
    {
        // utminfo(func_get_args());

        $name        = Option::getValue('artist');
        $replacement = null;
        $ignore      = 0;
        $drop        = false;
        if (Option::isTrue('show')) {
            $ignore = 0;
        }
        if (Option::isTrue('hide')) {
            $ignore = 1;
        }
        if (Option::isTrue('drop')) {
            $drop = true;
        }

        if (option::istrue('replacement')) {
            $replacement = Option::getValue('replacement');
            $replacement = str_replace('_', ' ', $replacement);
            $replacement = trim($replacement);
        }
        foreach ($name as $artist) {
            if (str_contains($artist, '=')) {
                $text        = $artist;
                $artist      = Strings::before($text, '=');
                $replacement = Strings::after($text, '=');
                $replacement = str_replace('_', ' ', $replacement);
                $replacement = trim($replacement);
            }

            $artist = str_replace('_', ' ', $artist);
            $artist = trim($artist);

            if (true === $drop) {
                $this->StorageConn->dropArtist($artist);
            } else {
                if (Option::isTrue('hide')) {
                    Mediatag::$Console->info('Artist Map', ['Artist' => $artist], 'Ignored');
                } else {
                    Mediatag::$Console->info('Artist Map', ['Artist' => $artist]);
                }

                $this->StorageConn->addArtist($artist, $ignore, $replacement);
            }
        }
    }

    public function addTitleEntry()
    {
        // utminfo(func_get_args());

        $ignore = 0;
        $drop   = false;
        $name   = Option::getValue('title');
        if (Option::isTrue('drop')) {
            $drop = true;
        }
        foreach ($name as $title) {
            $title = trim($title);
            Mediatag::$Console->info('Title Map', ['Title' => $title]);
            if (true === $drop) {
                $this->StorageConn->dropTitle($title);
            } else {
                $this->StorageConn->addTitle($title);
            }
        }
    }

    public function getArtistMap()
    {
        // utminfo(func_get_args());

        return $this->StorageConn->getArtistMap();
    }

    public function getIgnoredArtists()
    {
        // utminfo(func_get_args());

        return $this->StorageConn->getIgnoredArists();
    }

    public function videoTag()
    {
        // utminfo(func_get_args());

        $videos = parent::$SearchArray;
        $name   = Option::getValue('video');

    }

    public function listMap()
    {
        // utminfo(func_get_args());

        $namesArr     = [];
        $namesIgnore  = [];

        $list         = Option::getValue('list');
        switch ($list) {
            case 'artist':
                $res       = $this->getArtistMap();
                $resIgnore = $this->getIgnoredArtists();

                break;
        }
        foreach ($res as $name) {
            $name = ucwords(strtolower($name));
            if (! MediaArray::search($namesArr, $name)) {
                if (! MediaArray::search($resIgnore, $name)) {
                    $namesArr[] = $name;
                } else {
                    $resIgnore[] = $name;
                }
            }
        }

        sort($namesArr);
        foreach ($resIgnore as $name) {
            $name = ucwords(strtolower($name));
            if (! MediaArray::search($namesIgnore, $name)) {
                $namesIgnore[] = $name;
            }
        }
        sort($namesIgnore);
        $script       = new ScriptWriter('map.sh', getcwd());
        $script->addheader();

        foreach ($namesArr as $name) {
            $name         = trim($name);
            $OptionName[] = '-a';
            $OptionName[] = '"' . $name . '"';
        }
        $script->addCmd('map', $OptionName, false);

        // $options = explode("-a",$OptionName);
        $OptionName   = [];

        foreach ($namesIgnore as $name) {
            $name         = trim($name);
            $OptionName[] = '-a';
            $OptionName[] = '"' . $name . '"';
        }
        $OptionName[] = '--hide';
        $script->addCmd('map', $OptionName, false);

        $script->write(false);
    }

    public function AddLangugage()
    {
        // utminfo(func_get_args());

        $lang        = Option::getValue('lang');
        $replacement = Option::getValue('replacement');

        $file        = Option::getValue('file');
        if (null === $file) {
            preg_match('/L__([A-Z]+)_.*/', $lang, $output_array);
            $file = strtolower($output_array[1]);
        }

        $file        = str_replace('%KEY%', ucfirst($file), $this->command_lang);

        if (! file_exists($file)) {
            $file = $this->global_lang;
        }

        $this->addConst($lang, $replacement, $file);
        // utmdd([__METHOD__,$lang,$replacement,$file]);
    }

    private function addConst($const, $value, $file)
    {
        // utminfo(func_get_args());

        $contents = file_get_contents($file);
        // $contents = str_replace("\n\n","\n",$contents);
        $lines    = explode("\n", $contents);
        foreach ($lines as $idx => $line) {
            $lines[$idx] = trim($line);
            if (str_contains($line, $const)) {
                $lines[$idx] = '';
            }
            if (str_contains($line, '}')) {
                $lines[$idx] = '';
            }
        }
        $lines    = array_reverse($lines);
        $lines[0] = 'public const ' . $const . " = '" . $value . "';";
        $lines    = array_reverse($lines);
        $lines[]  = '}';
        foreach ($lines as $idx => $line) {
            if (str_contains($line, 'const')) {
                $lines[$idx] = '    ' . $line;
            }
        }
        $contents = implode("\n", $lines);
        file_put_contents($file, $contents);
    }
}
