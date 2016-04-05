<?php

namespace BrauneDigital\ImagineBundle\Imagine\Cache;
use Sonata\MediaBundle\Model\Media;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

//a little helper manager, if one is using SonataMediaBundle for MediaManagement
class SonataMediaCacheManager extends CacheManager implements ContainerAwareInterface
{
	protected $container;

	public function setContainer(ContainerInterface $container = null) {
		$this->container = $container;
	}

	public function getBrowserPathWithNewName($path, $filter, array $runtimeConfig = array(), $newName = null) {

		if($path instanceof Media) {
			$media = $path;
			$path = $this->getPath($media);
			$newName = $this->getName($media, $newName);
		}
		return parent::getBrowserPathWithNewName($path, $filter, $runtimeConfig, $newName);
	}

	public function getBrowserPath($path, $filter, array $runtimeConfig = array())
	{
		if($path instanceof Media) {
			$media = $path;
			$path = $this->getPath($media);
		}


		return parent::getBrowserPath($path, $filter, $runtimeConfig);
	}

	public function generateUrl($path, $filter, array $runtimeConfig = array(), $newName = 'null')
	{
		return parent::generateUrl($path, $filter, $runtimeConfig, $newName);
	}

	protected function getPath(Media $media) {
		$provider = $this->container->get($media->getProviderName());
		return $provider->generatePublicUrl($media, 'reference');
	}

	protected function getName(Media $media, $newName = null) {

		if($newName != null) {
			return $newName;
		} else {
			if($media->getName()) {
				return urlencode($media->getName());
			} else {
				return 'null';
			}
		}
	}
}
