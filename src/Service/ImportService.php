<?php

namespace App\Service;

use App\Entity\Import;
use App\Entity\ImportRow;
use App\Service\ServiceInterface;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportService implements ServiceInterface
{
    public function __construct(protected FormFactoryInterface $formFactory, protected ValidatorInterface $validator, protected ManagerRegistry $doctrine)
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
                        $rowArrays[$rowIndex][] = $cell->getValue();
                    };
                }
            }

            foreach ($rowArrays as $rows => $rowArray) {
                //Формируем массив 
                $row = array_combine($scheme, $rowArray);

                //Создаем форму для валидации полей
                $form = $this->buildForm($import->getFormType());
                $form->submit($row);

                //Если поля валидны
                if ($form->isValid()) {
                    $import->setSuccess('true');
                    $this->saveImport($import);
                } else {
                    // Если поля не валидны
                    foreach ($form->all() as $key => $child) {
                        if (!$child->isValid()) {
                            foreach ($child->getErrors() as $error) {
                                $errors[$rows] = 'Error in row ' . (int)$rows . ' - ' . $error->getMessage();
                            }
                        }
                    }
                    $import->setErrors(implode(' ', $errors));
                    $import->setSuccess('false');
                    $this->saveImport($import);
                }
                $importRows[] = new ImportRow($import, $sheet, $rows, $row);
            };
        }
        return $importRows;
    }

    protected function buildForm(string $formType, array $formOptions = []): FormInterface
    {
        $baseOptions = [
            'allow_extra_fields' => true
        ];

        return $this->formFactory
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
