<?php

namespace Docs\MainBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Docs\MainBundle\DependencyInjection\Compiler\SerializationHandlerPass;

class MainBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SerializationHandlerPass());
    }
}
