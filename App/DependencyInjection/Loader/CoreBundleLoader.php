<?php

namespace App\DependencyInjection\Loader;

use App\DependencyInjection\Compiler\RegisterFormCompilerPass;
use App\DependencyInjection\Compiler\RegisterRouterCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\DependencyInjection\ControllerArgumentValueResolverPass;
use Symfony\Component\HttpKernel\DependencyInjection\RegisterControllerArgumentLocatorsPass;

class CoreBundleLoader extends Bundle
{
    public function build(ContainerBuilder $container) : void
    {
        parent::build($container);

        $container
            ->addCompilerPass(new ControllerArgumentValueResolverPass())
            ->addCompilerPass(new RegisterControllerArgumentLocatorsPass())
            ->addCompilerPass(new RegisterRouterCompilerPass())
            ->addCompilerPass(new RegisterFormCompilerPass());
    }
}
