<?php

namespace App\Form;

use App\Data\UkrainianCities;
use App\Entity\Game;
use App\Entity\Lobby;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LobbyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Назва лобі',
                'attr' => ['placeholder' => 'Наприклад: CS2 Ranked, шукаю тіммейтів'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Опис',
                'required' => false,
                'attr' => ['rows' => 3, 'placeholder' => 'Деталі про гру...'],
            ])
            ->add('game', EntityType::class, [
                'class' => Game::class,
                'choice_label' => 'name',
                'label' => 'Гра',
                'placeholder' => 'Оберіть гру',
            ])
            ->add('maxMembers', IntegerType::class, [
                'label' => 'Макс. гравців',
                'attr' => ['min' => 2, 'max' => 50],
            ])
            ->add('skillLevel', ChoiceType::class, [
                'label' => 'Рівень гри',
                'choices' => [
                    'Будь-який' => 'any',
                    'Новачок' => 'beginner',
                    'Середній' => 'intermediate',
                    'Досвідчений' => 'advanced',
                    'Професіонал' => 'pro',
                ],
            ])
            ->add('minAge', IntegerType::class, [
                'label' => 'Мін. вік',
                'required' => false,
                'constraints' => [
                    new Range(min: 6, max: 60, notInRangeMessage: 'Вік має бути від {{ min }} до {{ max }} років'),
                ],
                'attr' => ['min' => 6, 'max' => 60],
            ])
            ->add('maxAge', IntegerType::class, [
                'label' => 'Макс. вік',
                'required' => false,
                'constraints' => [
                    new Range(min: 6, max: 60, notInRangeMessage: 'Вік має бути від {{ min }} до {{ max }} років'),
                ],
                'attr' => ['min' => 6, 'max' => 60],
            ])
            ->add('language', ChoiceType::class, [
                'label' => 'Мова',
                'required' => false,
                'choices' => [
                    'Українська' => 'uk',
                    'English' => 'en',
                    'Polska' => 'pl',
                ],
                'placeholder' => 'Будь-яка',
            ])
            ->add('city', ChoiceType::class, [
                'label' => 'Місто',
                'required' => false,
                'choices' => UkrainianCities::getChoices(),
                'placeholder' => 'Оберіть місто',
                'attr' => ['class' => 'tom-select-city'],
            ])
            ->add('isPrivate', CheckboxType::class, [
                'label' => 'Приватне лобі',
                'required' => false,
            ])
            ->add('voiceChat', CheckboxType::class, [
                'label' => 'Голосовий чат',
                'required' => false,
            ])
            ->add('scheduledAt', DateTimeType::class, [
                'label' => 'Запланувати на',
                'required' => false,
                'widget' => 'single_text',
                'attr' => ['max' => '2030-12-31T23:59'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lobby::class,
            'constraints' => [
                new Callback(function (Lobby $lobby, ExecutionContextInterface $context) {
                    if ($lobby->getMaxAge() && !$lobby->getMinAge()) {
                        $context->buildViolation('Вкажіть мінімальний вік, якщо вказано максимальний.')
                            ->atPath('minAge')
                            ->addViolation();
                    }
                    if ($lobby->getMinAge() && $lobby->getMaxAge() && $lobby->getMaxAge() <= $lobby->getMinAge()) {
                        $context->buildViolation('Максимальний вік має бути більшим за мінімальний.')
                            ->atPath('maxAge')
                            ->addViolation();
                    }
                }),
            ],
        ]);
    }
}
