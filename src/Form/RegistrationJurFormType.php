<?php

namespace App\Form;

use App\Entity\User;
use App\Service\DataMapping;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationJurFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dataMapping = new DataMapping();
        $organisationTypes = array_flip($dataMapping->getData('abcp_organisation_types'));
        $juridicalEntityTypes = array_flip($dataMapping->getData('abcp_juridical_entity_types'));

        $builder
            ->add('name', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'ФИО',
                    'class' => 'form-control'
                ]
            ])
            ->add('username', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Логин',
                    'class' => 'form-control'
                ]
            ])
            ->add('organizationName', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Наименование организации',
                    'class' => 'form-control',
                    'required' => true
                ]
            ])
            ->add('bankName', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Наименование банка',
                    'class' => 'form-control',
                    'required' => true
                ]
            ])
            ->add('bankBik', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'БИК банка',
                    'class' => 'form-control',
                    'required' => true
                ]
            ])
            ->add('city', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Город',
                    'class' => 'form-control',
                    'required' => true
                ]
            ])
            ->add('email', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'E-mail',
                    'class' => 'form-control',
                    'required' => true
                ]
            ])
            ->add('inn', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'ИНН',
                    'class' => 'form-control',
                    'required' => true
                ]
            ])
            ->add('bankName', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Наименование банка',
                    'class' => 'form-control',
                    'required' => true
                ]
            ])
            ->add('correspondentAccount', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Корреспондентский счет',
                    'class' => 'form-control',
                    'required' => true,
                ]
            ])
            ->add('checkingAccount', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Расчетный счет',
                    'class' => 'form-control',
                    'required' => true
                ]
            ])
            ->add('region', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Код региона',
                    'class' => 'form-control',
                    'required' => true
                ]
            ])
            ->add('organisationType', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Тип организации',
                'attr' => [
                    'class' => 'form-control text-muted',
                    'required' => true
                ],
                'choices' => $organisationTypes
            ])
            ->add('juridicalEntityType', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Тип юридического лица',
                'attr' => [
                    'class' => 'form-control text-muted',
                    'required' => true
                ],
                'choices' => $juridicalEntityTypes
            ])
            ->add('juridicalAddress', null, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Юридический адрес',
                    'class' => 'form-control',
                    'required' => true
                ]
            ])
            ->add('phone', NumberType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Номер телефона',
                    'class' => 'form-control'
                ]
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Пароль',
                    'class' => 'form-control'
                ],
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('client_type', HiddenType::class, [
                'data' => 2
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
