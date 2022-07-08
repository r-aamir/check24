<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator) : void {
    $serviceConfigurator = $configurator->services();

    $serviceConfigurator->defaults()
        ->autowire(false)
        ->autoconfigure(false)
        ->bind('$kernelCacheDir', '%kernel.cache_dir%')
        ->bind('$kernelEnvironment', '%kernel.environment%')
        ->bind('$sessionSavePath', '%session.save_path%')
        ->bind('$formDefinition', '%forms%')
        ->autowire()->autoconfigure();

    $serviceConfigurator->load('App\\', APPLICATION_PATH)
        ->exclude([
            APPLICATION_PATH . 'Config/**/*.php',
            APPLICATION_PATH . '*.php'
        ])
        ->autowire()->autoconfigure();
};
