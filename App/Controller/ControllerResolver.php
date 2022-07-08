<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;

use function is_array;

class ControllerResolver extends ContainerControllerResolver
{
    /**
     * {@inheritdoc}
     */
    protected function instantiateController($class) : object
    {
        $controller = parent::instantiateController($class);

        if ($controller instanceof ContainerAwareInterface) {
            $controller->setContainer($this->container);
        }

        if ($controller instanceof AbstractController) {
            $controller->setContainer(
                $controller->setContainer($this->container)
            );
        }

        return $controller;
    }

    /**
     * Overridden to update container.
     * {@inheritDoc}
     */
    protected function createController(string $controller) : callable
    {
        $controllerInstatnce = parent::createController($controller);

        if (is_array($controllerInstatnce) && isset($controllerInstatnce[0])) {
            $controllerInstatnce[0]->setContainer($this->container);
        }

        return $controllerInstatnce;
    }
}
