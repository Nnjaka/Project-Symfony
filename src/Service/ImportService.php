<?php

namespace App\Service;

use App\Entity\Import;
use App\Entity\ImportRow;
use App\Service\ServiceInterface;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Symfony\Component\Form\Forms;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportService implements ServiceInterface
{
    public function __construct(protected ValidatorInterface $validator, protected ManagerRegistry $doctrine)
    {
    }

    public function parse(Import $import, array $scheme, array $formOptions = [])
    {
        //Массив, в который будут записываться данные, полученные из загруженного пользователем файла 
        $rowArray = [];

        //Получаем данные из ячеек Excel
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($import->getPath());
        foreach ($reader->getSheetIterator() as $index => $sheet) {
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                if ($rowIndex == 1) {
                    continue;
                } else {
                    $cells = $row->getCells();
                    foreach ($cells as $cell) {
                        $rowArray[] = $cell->getValue();
                    };

                    //Формируем массив 
                    $row = array_combine($scheme, $rowArray);

                    $importRow = new ImportRow($import, $sheet, $rowIndex, $row);

                    //Создаем форму для валидации полей
                    $form = $this->buildForm($import->getFormType());
                    $form->submit($row);

                    //Если поля валидны, возвращаем массив для создания объекта
                    if ($form->isValid()) {
                        $import->setSuccess('true');
                        $this->saveImport($import);

                        return $row;
                    }

                    //Если поля не валидны возвращаем объект Import
                    foreach ($form->all() as $key => $child) {
                        if (!$child->isValid()) {
                            foreach ($child->getErrors() as $error) {
                                $errors[$key] = $error->getMessage();
                            }
                        }
                    }

                    $import->setErrors(implode(' ', $errors));
                    $import->setSuccess('false');
                    $this->saveImport($import);

                    return $import;
                }
            };
        }
    }

    protected function buildForm(string $formType, array $formOptions = []): FormInterface
    {
        $validator = $this->validator;
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension($validator))
            ->getFormFactory();

        $baseOptions = [
            'allow_extra_fields' => true
        ];

        return $formFactory
            ->create(
                $formType,
                null,
                array_merge($formOptions, $baseOptions)

            );
    }

    protected function saveImport(Import $import): void
    {
        $em = $this->doctrine->getManager();
        $em->persist($import);
        $em->flush();
    }
}
