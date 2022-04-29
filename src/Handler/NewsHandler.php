<?php

namespace App\Handler;

use App\Entity\Import;
use App\Entity\News;
use App\Repository\UserRepository;
use App\Service\ImportService;
use Doctrine\Persistence\ManagerRegistry;

class NewsHandler implements HandlerInterface
{
    const SCHEME = ['title', 'text', 'user', 'image'];

    public function __construct(private UserRepository $repository, private ManagerRegistry $doctrine, private ImportService $importService)
    {
    }

    public function handle(Import $import)
    {
        //Получаем данные из файла от клиента
        foreach ($this->importService->parse($import, self::SCHEME) as $key => $importRow) {
            if (!$importRow->getData()->getErrors()) {
                $this->handleRow($importRow);
            } else {
                return $importRow->getData()->getErrors();
            }
        }
    }

    //Создаем новый объект и записываем в него полученные свойства
    private function handleRow($importRow)
    {
        $entityManager = $this->doctrine->getManager();

        $news = new News();
        $user = $this->repository->findOneBy(['email' => $importRow->getRow()['user']]);

        $news->setTitle($importRow->getRow()['title']);
        $news->setText($importRow->getRow()['text']);
        $news->setImage($importRow->getRow()['image']);
        $news->setUser($user);

        $entityManager->persist($news);
        $entityManager->flush();
    }
}
