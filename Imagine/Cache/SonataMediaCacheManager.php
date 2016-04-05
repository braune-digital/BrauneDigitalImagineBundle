<?php

namespace BrauneDigital\ImagineBundle\Imagine\Cache;
use BrauneDigital\ImagineBundle\Model\RuntimeConfigInterface;
use Sonata\MediaBundle\Model\Media;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

//a little helper manager, if one is using SonataMediaBundle for MediaManagement
class SonataMediaCacheManager extends CacheManager
{
	/**
	 * @param       $path
	 * @param       $filter
	 * @param array $runtimeConfig
	 * @param null  $newName
	 *
	 * @return string
	 */
	public function getBrowserPathWithNewName($path, $filter, array $runtimeConfig = array(), $newName = null) {

		if($path instanceof Media) {
			$media = $path;
			$path = $this->getPath($media);
			$newName = $this->getName($media, $newName);

			$runtimeConfig = $this->getRuntimeConfig($media, $runtimeConfig);
		}
		return parent::getBrowserPathWithNewName($path, $filter, $runtimeConfig, $newName);
	}

	/**
	 * @param string $path
	 * @param string $filter
	 * @param array  $runtimeConfig
	 *
	 * @return string
	 */
	public function getBrowserPath($path, $filter, array $runtimeConfig = array())
	{
		if($path instanceof Media) {
			$media = $path;
			$path = $this->getPath($media);
			$runtimeConfig = $this->getRuntimeConfig($media, $runtimeConfig);
		}

		return parent::getBrowserPath($path, $filter, $runtimeConfig);
	}

	/**
	 * @param string $path
	 * @param string $filter
	 * @param array  $runtimeConfig
	 * @param string $newName
	 *
	 * @return string
	 */
	public function generateUrl($path, $filter, array $runtimeConfig = array(), $newName = 'null')
	{
		if($path instanceof Media) {
			$media = $path;
			$path = $this->getPath($media);
			$runtimeConfig = $this->getRuntimeConfig($media, $runtimeConfig);
		}

		return parent::generateUrl($path, $filter, $runtimeConfig, $newName);
	}

	/**
	 * @param Media $media
	 *
	 * @return mixed
	 */
	protected function getPath(Media $media) {
		$provider = $this->container->get($media->getProviderName());
		return $provider->generatePublicUrl($media, 'reference');
	}

	/**
	 * @param Media $media
	 * @param null  $newName
	 *
	 * @return null|string
	 */
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

	/**
	 * @param Media $media
	 * @param       $runtimeConfig
	 *
	 * @return array
	 * Merge Runtime Configs if the media is implementing the RuntimeConfigInterface
	 */
	protected function getRuntimeConfig(Media $media, $runtimeConfig) {

		if($media instanceof RuntimeConfigInterface) {

			$additionalConfig = $media->getRuntimeConfiguration();

			if($additionalConfig && count($additionalConfig)) {
				return array_replace_recursive($additionalConfig, $runtimeConfig);
			}
		}
		return $runtimeConfig;
	}
}
