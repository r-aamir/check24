<?php

namespace App\DependencyInjection\Compiler;

use App\Form\Base\BaseFormInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

class RegisterFormCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) : void
    {
        try {
            $formConfig = $container->getParameter('forms');
        } catch (ParameterNotFoundException $e) {
            $formConfig = [];
        }

        foreach ($container->findTaggedServiceIds('form.class') as $id => $tag) {
            $formDefinition = $container->getDefinition($id);

            /** @var BaseFormInterface $formClass */
            $formClass = $formDefinition->getClass();

            $name              = $formClass::getName();
            $formConfig[$name] = $formDefinition->getClass();
        }

        $container->setParameter('forms', $formConfig);
    }
}
