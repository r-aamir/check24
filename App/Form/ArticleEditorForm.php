<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\Base\BaseForm;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Description of ArticleEditorForm.
 */
class ArticleEditorForm extends BaseForm
{
    protected function buildForm() : void
    {
        $this->formBuilder
            ->setMethod('POST')
            ->add('id', HiddenType::class)
            ->add('title', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255]),
                ],
                'label'       => 'Titel',
            ])
            ->add('image', UrlType::class, [
                'constraints' => [
                    new Length(['max' => 80]),
                ],
                'label'       => 'Link zum Bild',
            ])
            ->add('content', TextareaType::class, [
                'constraints' => [
                    new NotBlank()
                ],
                'label'       => 'Text',
            ])
            ->add('button', SubmitType::class, [
                'attr'  => ['class' => 'Button'],
                'label' => 'Absenden',
            ]);
    }

    public static function getName() : string
    {
        return 'article_edit';
    }
}