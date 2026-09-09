<?php
/**
 * Command like Metatag writer for video files.
 */

namespace Mediatag\Bundle\Dialog\Options;

class Field
{
    protected $label = '';

    protected $label_pos = [];

    protected $field = '';

    protected $field_pos = [];

    protected $flen = 0;

    protected $ilen = 0;

    public function __construct(string $label, array $label_pos, string $field, array $field_pos, int $flen, int $ilen)
    {
        $this->label = $label;
        $this->label_pos = $label_pos;
        $this->field = $field;
        $this->field_pos = $field_pos;
        $this->flen = $flen;
        $this->ilen = $ilen;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function label_pos(): array
    {
        return $this->label_pos;
    }

    public function field(): string
    {
        return $this->field;
    }

    public function field_pos(): array
    {
        return $this->field_pos;
    }

    public function flen(): int
    {
        return $this->flen;
    }

    public function ilen(): int
    {
        return $this->ilen;
    }

    public function parseItem(): string
    {
        return " '{$this->label()}' {$this->label_pos()[0]} {$this->label_pos()[1]} '{$this->field}' {$this->field_pos[0]} {$this->field_pos[1]} {$this->flen()} {$this->ilen()}";
    }
}
