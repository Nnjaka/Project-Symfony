<?php

namespace App\Service;

use App\Service\ServiceInterface;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Doctrine\Persistence\ObjectManager;
use Exception;

class ImportService implements ServiceInterface
{
    public function handle(string $pathToFile, string $entityName, array $schema, ObjectManager $entityManager): void
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
                    if (count($schema) != count($dataFromFile)) {
                        throw new Exception("Количество столбцов в файле не соответствует схеме");
                    }

                    //Формируем ассоциативный массив, где ключ - заранее переданная схема, значение - из массива $dataFromFile
                    $newsFromValues = array_combine($schema, $dataFromFile);

                    //Создаем Entity исходя из значения $entityName, переданного в метод
                    $creatorEntity = new CreateEntity();
                    $entity = $creatorEntity->create($entityName);

                    //Устанавливаем свойства Entity
                    $entity = $entity->setAllProperty($newsFromValues);

                    //записываем объект в БД
                    $entityManager->persist($entity);
                    $entityManager->flush();
                }
            };
        }
    }
}
