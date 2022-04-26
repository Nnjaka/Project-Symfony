<?php

namespace App\Entity;

use App\Entity\Import;

class ImportRow
{
    public function __construct(protected Import $data, private $rowIndex, private $sheetIndex)
    {
    }

    public function getData(): Import
    {
        return $this->data;
    }

    public function getRowIndex(): int
    {
        return $this->rowIndex;
    }

    public function getSheetIndex(): int
    {
        return $this->sheetIndex;
    }
}
