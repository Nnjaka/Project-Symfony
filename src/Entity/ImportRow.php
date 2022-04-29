<?php

namespace App\Entity;

use App\Entity\Import;

class ImportRow
{
    public function __construct(protected Import $data, private $sheetIndex, private $rowIndex, private array $row)
    {
    }

    public function getData(): Import
    {
        return $this->data;
    }

    public function getRowIndex(): string
    {
        return $this->rowIndex;
    }

    public function getSheetIndex(): mixed
    {
        return $this->sheetIndex;
    }

    public function getRow(): array
    {
        return $this->row;
    }
}
