<?php

namespace App\Form;

use App\Entity\User;
use App\Service\ProfileThemeService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, ['label' => 'Нікнейм'])
            ->add('bio', TextareaType::class, [
                'label' => 'Біографія',
                'required' => false,
                'attr' => ['rows' => 4, 'placeholder' => 'Розкажіть про себе...'],
            ])
            ->add('age', IntegerType::class, [
                'label' => 'Вік',
                'required' => false,
                'attr' => ['min' => 6, 'max' => 60],
                'constraints' => [
                    new Range(min: 6, max: 60, notInRangeMessage: 'Вік має бути від {{ min }} до {{ max }} років'),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'Місто',
                'required' => false,
            ])
            ->add('language', ChoiceType::class, [
                'label' => 'Мова',
                'required' => false,
                'choices' => [
                    'Українська' => 'uk',
                    'English' => 'en',
                    'Polska' => 'pl',
                    'Deutsch' => 'de',
                ],
                'placeholder' => 'Оберіть мову',
            ])
            ->add('steamId', TextType::class, [
                'label' => 'Steam профіль',
                'required' => false,
                'attr' => ['placeholder' => 'https://steamcommunity.com/id/your_name або https://steamcommunity.com/profiles/76561198...'],
                'help' => 'Вставте посилання на ваш Steam профіль — ID визначиться автоматично',
            ])
            ->add('profileTheme', ChoiceType::class, [
                'label' => 'Тема публічного профілю',
                'required' => false,
                'choices' => ProfileThemeService::getChoices(),
                'placeholder' => '— Типова —',
                'help' => 'Відображатиметься відвідувачам вашої сторінки.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
