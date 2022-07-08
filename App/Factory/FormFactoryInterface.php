<?php

namespace App\Factory;

use App\Form\Base\BaseForm;
use Symfony\Component\Form\Extension\Core\Type\FormType;

interface FormFactoryInterface
{
    public function buildForm(string $name, array $data = [], array $options = [], string $type = FormType::class) : BaseForm;
}
