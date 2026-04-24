<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Електронна пошта',
                'constraints' => [new NotBlank(), new Email()],
                'attr' => ['placeholder' => 'email@example.com'],
            ])
            ->add('username', TextType::class, [
                'label' => 'Нікнейм',
                'constraints' => [new NotBlank(), new Length(min: 3, max: 50)],
                'attr' => ['placeholder' => 'Ваш ігровий нікнейм'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => ['label' => 'Пароль', 'attr' => ['placeholder' => 'Мінімум 6 символів']],
                'second_options' => ['label' => 'Підтвердіть пароль', 'attr' => ['placeholder' => 'Повторіть пароль']],
                'constraints' => [new NotBlank(), new Length(min: 6, max: 4096)],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
