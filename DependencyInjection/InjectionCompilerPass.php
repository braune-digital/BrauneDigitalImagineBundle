<?php

namespace BrauneDigital\ImagineBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class InjectionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if($container->hasParameter('liip_imagine.cache.manager.class') && $container->getParameter('liip_imagine.cache.manager.class') == 'BrauneDigital\ImagineBundle\Imagine\Cache\SonataMediaCacheManager') {
            $container->findDefinition('liip_imagine.cache.manager')->addMethodCall('setContainer', array(new Reference('service_container')));
        }
    }
}