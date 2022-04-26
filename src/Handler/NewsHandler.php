<?php

namespace App\Handler;

use App\Entity\Import;
use App\Entity\News;
use App\Repository\UserRepository;
use App\Service\ImportService;
use Doctrine\Persistence\ObjectManager;

class NewsHandler implements HandlerInterface
{
    const SCHEME = ['title', 'text', 'user', 'image'];

    public function __construct(private UserRepository $repository, private ImportService $importService)
    {
    }

    public function handle(Import $import, ObjectManager $entityManager)
    {
        //Получаем данные из файла от клиента
        $data = $this->importService->parse($import, self::SCHEME);
        if ($data instanceof Import) {
            return $data;
        }

        //Создаем новый объект и записываем в него полученные свойства
        $news = new News();
        $user = $this->repository->findOneBy(['email' => $data['user']]);

        $news->setTitle($data['title']);
        $news->setText($data['text']);
        $news->setImage($data['image']);
        $news->setUser($user);

        $entityManager->persist($news);
        $entityManager->flush();
    }
}
