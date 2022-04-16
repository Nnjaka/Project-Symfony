<?php

namespace App\Service;

use App\Entity\News;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class NewsImportService extends ImportService
{
    public $repository;

    const SCHEME = ['title', 'text', 'author', 'image'];

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    //Валидация
    protected function getType(array $dataFromFile): array
    {
        //Проверяем ячейки файла, полученного от клиента на правильность заполнения
        $validator = Validation::createValidator();

        $constraint = new Assert\Collection([
            'title' => new Assert\Length(['max' => 256]),
            'text' => new Assert\Length(['max' => 1000]),
            'author' => new Assert\Email(),
            'image' => [
                new Assert\Length(['max' => 256]),
                new Assert\Url()
            ]
        ]);

        //Получаем ошибки
        $errors = $validator->validate($dataFromFile, $constraint);

        //Выводим ошибки
        if ($errors->count()) {
            foreach ($errors as $error) {
                $errorMessage = $error->getMessage();
                throw new Exception($errorMessage);
            }
        }

        //Проверяем, что пользователь с такой электронной почтой существует в БД
        $user = $this->repository->findOneBy(['email' => $dataFromFile['author']]);
        if (!$user) {
            throw new Exception('Пользователь с такой электронной почтой не зарегистрирован в системе');
        }

        return $dataFromFile;
    }

    public function handle(string $pathToFile, ObjectManager $entityManager): void
    {
        //Получаем данные из файла от клиента
        $newsFromFile = $this->convertFromFile($pathToFile, self::SCHEME);

        //Проверяем валидность данных
        $validatedData = $this->getType($newsFromFile);

        //Создаем новый объект News
        $news = new News();
        $user = $this->repository->findOneBy(['email' => $validatedData['author']]);

        foreach ($validatedData as $key => $property) {
            match ($key) {
                'title' => $news->setTitle($property),
                'text' => $news->setText($property),
                'author' => $news->setUser((object) $user),
                'image' => $news->setImage($property),
            };
        }

        $entityManager->persist($news);
        $entityManager->flush();
    }
}
