<?php

namespace BrauneDigital\ImagineBundle;

use BrauneDigital\ImagineBundle\DependencyInjection\InjectionCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BrauneDigitalImagineBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new InjectionCompilerPass());
    }
}