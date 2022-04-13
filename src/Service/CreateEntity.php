<?php

namespace App\Service;

use App\Entity\EntityInterface;
use App\Entity\News;

class CreateEntity implements ServiceInterface
{
    public function create(string $entityName): EntityInterface
    {
        return match ($entityName) {
            'news' => new News()
        };
    }
}
