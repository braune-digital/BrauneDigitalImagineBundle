<?php

namespace BrauneDigital\ImagineBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class BrauneDigitalImagineExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter('braune_digital_imagine.resolve_instant', (isset($config['resolve_instant']) && $config['resolve_instant']) ? true : false);

        if(isset($config['use_sonata_media_manager']) && $config['use_sonata_media_manager']) {
            $container->setParameter('liip_imagine.cache.manager.class', 'BrauneDigital\ImagineBundle\Imagine\Cache\SonataMediaCacheManager');
        }
    }
}