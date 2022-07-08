<?php

namespace App\Form;

use App\Form\Base\BaseForm;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Description of LoginForm.
 */
class LoginForm extends BaseForm
{
    protected function buildForm() : void
    {
        $this->formBuilder
            ->add('username', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 2]),
                ],
                'label'       => 'Username',
            ])
            ->add('password', PasswordType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'label'       => 'Password',
            ])
            ->add('button', SubmitType::class, [
                'attr'  => ['class' => 'Button'],
                'label' => 'Login',
            ]);
    }

    public static function getName() : string
    {
        return 'form_login';
    }
}
