<?php

namespace App\Service;

use App\Entity\News;
use App\Service\ServiceInterface;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Doctrine\Persistence\ObjectManager;

class NewsFromFile implements ServiceInterface
{
    public function getNews(string $pathToFile, ObjectManager $entityManager): void
    {
        $reader = ReaderEntityFactory::createXLSXReader();

        $reader->open($pathToFile);

        $news = new News();
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $news->setTitle($row->getCellAtIndex(0)->getValue());
                $news->setText($row->getCellAtIndex(1)->getValue());
                $news->setImage($row->getCellAtIndex(2)->getValue());
            };
        }
        $entityManager->persist($news);
        $entityManager->flush();

        $reader->close();
    }
}
