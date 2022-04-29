<?php

namespace App\Form;

use App\Entity\News;
use App\Validator\UserExists;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class NewsImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' =>
                [
                    new NotBlank(
                        [
                            'message' => 'Title must not be empty.'
                        ]

                    ),
                    new Length([
                        'max' => 256,
                        'maxMessage' => 'Title is too long. It should have 256 characters or less.'
                    ])
                ],
            ])
            ->add('text', TextareaType::class, [
                'constraints' =>
                [
                    new NotBlank([
                        'message' => 'Text must not be empty.'
                    ]),
                    new Length([
                        'max' => 1000,
                        'maxMessage' => 'Text is too long. It should have 1000 characters or less.'
                    ])
                ],
            ])
            ->add('user', TextType::class, [
                'mapped' => false,
                'constraints' =>
                [
                    new NotBlank(
                        [
                            'message' => 'User must not be empty.'
                        ]
                    ),
                    new Email(
                        [
                            'message' => 'User is not a valid email address.'
                        ]
                    ),
                    new UserExists(
                        [
                            'message' => 'The user with this email is not registered in the system.'
                        ]
                    ),
                ],
            ])
            ->add('image', TextType::class, [
                'constraints' =>
                [
                    new NotBlank(
                        [
                            'message' => 'Image must not be empty.'
                        ]
                    ),
                    new Length([
                        'max' => 256,
                        'maxMessage' => 'Title is too long. It should have 256 characters or less.'
                    ]),
                    new Url([
                        'message' => 'Image URL is not valid.'
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'data_class' => News::class,
            'csrf_protection' => false
        ]);
    }
}
