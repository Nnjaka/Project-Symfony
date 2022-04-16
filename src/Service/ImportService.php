<?php

namespace App\Service;

use App\Service\ServiceInterface;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Exception;

abstract class ImportService implements ServiceInterface
{
    public function convertFromFile(string $pathToFile, array $scheme): array
    {
        //Массив, в который будут записываться данные, полученные из загруженного пользователем файла 
        $dataFromFile = [];

        //Получаем данные из ячеек Excel
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($pathToFile);
        foreach ($reader->getSheetIterator() as $index => $sheet) {
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                if ($rowIndex == 1) {
                    continue;
                } else {
                    $cells = $row->getCells();
                    foreach ($cells as $cell) {
                        $dataFromFile[] = $cell->getValue();
                    };

                    //Проверяем файл на соответствие столбцов в схеме и полученном от пользователя файлею
                    if (count($scheme) != count($dataFromFile)) {
                        throw new Exception("Количество столбцов в файле не соответствует схеме");
                    }

                    //Формируем ассоциативный массив, где ключ - заранее переданная схема, значение - из массива $dataFromFile
                    return array_combine($scheme, $dataFromFile);
                }
            };
        }
    }

    abstract protected function getType(array $dataFromFile);
}
