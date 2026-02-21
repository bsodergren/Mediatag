<?php

namespace Mediatag\Entities\Tags;

use Mediatag\Entities\MetaEntities;
use XMLWriter;

class Artist extends MetaEntities
{
    private static $xml;

    private static $XMLString;

    public static function MetaReaderCallback()
    {
        return '/(aART).*contains\:\ (.*)/'; //  = function ($matches) {return $matches[2];},
    }

    public static function GenerateOption()
    {
        $options   = [];
        $options[] = '--rDNSatom';
        if (self::$value != '') {
            $options[] = self::ArtistXML(self::$value);

            $options[] = 'name';
            $options[] = 'iTunMOVI';
            $options[] = 'domain';
            $options[] = 'com.apple.iTunes';
        } else {
            $options[] = '';
            $options[] = 'name';
            $options[] = '';
            $options[] = 'domain';
            $options[] = '';
        }

        $options[] = '--albumArtist';
        $options[] = self::$value;

        return $options;
    }

    public static function ArtistXML($value)
    {
        // utminfo(func_get_args());

        self::startArtistXML();
        $artist_array = explode(',', $value);
        self::$xml->startElement('array');

        foreach ($artist_array as $name) {
            self::artistElement($name);
        }
        self::$xml->fullEndElement();
        self::endArtistXML();

        return str_replace("\n", '', self::$XMLString);
    }

    private static function artistElement($name)
    {
        // utminfo(func_get_args());

        self::$xml->startElement('dict');
        self::$xml->writeElement('key', 'name');
        self::$xml->writeElement('string', $name);
        self::$xml->fullEndElement();
    }

    private static function startArtistXML()
    {
        // utminfo(func_get_args());

        self::$xml = new XMLWriter;
        self::$xml->openMemory();
        self::$xml->startDocument('1.0', 'UTF-8');
        self::$xml->writeDtd('plist', '-//Apple Computer//DTD PLIST 1.0//EN', 'http://www.apple.com/DTDs/PropertyList-1.0.dtd');
        self::$xml->startElement('plist');
        self::$xml->startElement('dict');

        self::$xml->writeElement('key', 'cast');
        // self::$xml->writeElement('key', 'screenwriters');
        // self::$xml->startElement('array');
    }

    private static function endArtistXML()
    {
        // utminfo(func_get_args());

        self::$xml->fullEndElement();
        self::$xml->fullEndElement();
        self::$xml->fullEndElement();
        self::$xml->endDocument();
        self::$XMLString = self::$xml->flush();
    }
}
