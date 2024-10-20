<?php

namespace Mediatag\Traits;
use Mediatag\Core\Mediatag;


trait CaseHelper {

    public function in_range( $number,  $lowerBound,  $upperBound) {
        return $number >= $lowerBound &&  $number <=  $upperBound;
    }
    public function getphdbUrl($number)
    {

        if ($this->in_range($number, 347,43726111 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_00.txt";
        }
        if ($this->in_range($number, 43726121,77351051 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_01.txt";
        }
        if ($this->in_range($number, 77351251,126132991 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_02.txt";
        }
        if ($this->in_range($number, 126133011,161275972 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_03.txt";
        }
        if ($this->in_range($number, 161276572,186790961 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_04.txt";
        }
        if ($this->in_range($number, 186791461,208851311 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_05.txt";
        }
        if ($this->in_range($number, 208851351,226879181 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_06.txt";
        }
        if ($this->in_range($number, 226879301,243708961 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_07.txt";
        }
        if ($this->in_range($number, 243709011,260487812 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_08.txt";
        }
        if ($this->in_range($number, 260488292,278656651 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_09.txt";
        }
        if ($this->in_range($number, 278657081,297867921 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_10.txt";
        }
        if ($this->in_range($number, 297868071,316584941 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_11.txt";
        }
        if ($this->in_range($number, 316585211,334458902 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_12.txt";
        }
        if ($this->in_range($number, 334458962,351515842 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_13.txt";
        }
        if ($this->in_range($number, 351515852,367662562 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_14.txt";
        }
        if ($this->in_range($number, 367662672,379148302 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_15.txt";
        }
        if ($this->in_range($number, 379148362,384054282 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_16.txt";
        }
        if ($this->in_range($number, 384054292,387047811 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_17.txt";
        }
        if ($this->in_range($number, 387047861,389987751 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_18.txt";
        }
        if ($this->in_range($number, 389987761,392905701 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_19.txt";
        }
        if ($this->in_range($number, 392905721,395783671 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_20.txt";
        }
        if ($this->in_range($number, 395783681,398592331 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_21.txt";
        }
        if ($this->in_range($number, 398592341,401256261 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_22.txt";
        }
        if ($this->in_range($number, 401256281,403805261 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_23.txt";
        }
        if ($this->in_range($number, 403805281,406324871 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_24.txt";
        }
        if ($this->in_range($number, 406324891,408795201 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_25.txt";
        }
        if ($this->in_range($number, 408795221,411243321 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_26.txt";
        }
        if ($this->in_range($number, 411243331,413577611 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_27.txt";
        }
        if ($this->in_range($number, 413577621,415904791 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_28.txt";
        }
        if ($this->in_range($number, 415904811,418237651 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_29.txt";
        }
        if ($this->in_range($number, 418237661,420520401 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_30.txt";
        }
        if ($this->in_range($number, 420520411,422737421 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_31.txt";
        }
        if ($this->in_range($number, 422737451,425045821 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_32.txt";
        }
        if ($this->in_range($number, 425045851,427271821 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_33.txt";
        }
        if ($this->in_range($number, 427271831,429460661 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_34.txt";
        }
        if ($this->in_range($number, 429460671,431559141 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_35.txt";
        }
        if ($this->in_range($number, 431559161,433592301 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_36.txt";
        }
        if ($this->in_range($number, 433592321,435597151 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_37.txt";
        }
        if ($this->in_range($number, 435597161,437593061 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_38.txt";
        }
        if ($this->in_range($number, 437593071,439602751 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_39.txt";
        }
        if ($this->in_range($number, 439602761,441642011 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_40.txt";
        }
        if ($this->in_range($number, 441642021,443639191 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_41.txt";
        }
        if ($this->in_range($number, 443639231,445684091 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_42.txt";
        }
        if ($this->in_range($number, 445684111,447677421 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_43.txt";
        }
        if ($this->in_range($number, 447677441,449485371 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_44.txt";
        }
        if ($this->in_range($number, 449485381,451159831 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_45.txt";
        }
        if ($this->in_range($number, 451159841,452794911 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_46.txt";
        }
        if ($this->in_range($number, 452794921,454405001 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_47.txt";
        }
        if ($this->in_range($number, 454405021,455959281 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_48.txt";
        }
        if ($this->in_range($number, 455959291,457463981 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_49.txt";
        }
        if ($this->in_range($number, 457463991,459002271 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_50.txt";
        }
        if ($this->in_range($number, 459002291,459349801 )) {
            return "/media/Videos/Plex/XXX/.cache/phdb/txt/ph_db_raw_51.txt";
        }
        return false;
    }
}