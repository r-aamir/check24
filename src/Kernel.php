<?php

declare(strict_types=1);

namespace Src;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class Kernel extends \App\Kernel
{
    protected function configureContainer(ContainerConfigurator $container) : void
    {
        parent::configureContainer($container);

        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/' . $this->environment . '/*.yaml');

        $container->import('../config/services.yaml');
        $container->import('../config/{services}_' . $this->environment . '.yaml');
    }
}
