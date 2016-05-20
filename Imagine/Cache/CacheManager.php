<?php

namespace BrauneDigital\ImagineBundle\Imagine\Cache;
use Liip\ImagineBundle\Imagine\Cache\CacheManager as BaseCacheManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CacheManager extends BaseCacheManager implements ContainerAwareInterface
{

	protected $newName;

	protected $resolveInstant = false;

	protected $container;

	protected $dataManager;

	protected $filterManager;
	/**
	 * @param ContainerInterface|null $container
	 */
	public function setContainer(ContainerInterface $container = null) {
		$this->resolveInstant = $container->getParameter('braune_digital_imagine.resolve_instant');
		$this->container = $container;

		$this->dataManager = $this->container->get('liip_imagine.data.manager');
		$this->filterManager = $this->container->get('liip_imagine.filter.manager');
	}

	/**
	 * @param $path
	 * @param $filter
	 * @param array $runtimeConfig
	 * @param $newName
	 */
	public function getBrowserPathWithNewName($path, $filter, array $runtimeConfig = array(), $newName = 'null') {
		$this->newName = $newName;
		$result = $this->getBrowserPath($path, $filter, $runtimeConfig);
		//prevent accidental multiple usages
		$this->newName = null;
		return $result;
	}

	public function getBrowserPath($path, $filter, array $runtimeConfig = array())
	{
		$newPath = $path;
		if ($this->newName != null && $this->newName != 'null') {
			$pathInfo = pathinfo($path);
			$newPath = str_replace($pathInfo['basename'], $this->newName . '.' . $pathInfo['extension'], $path);
		}

		if (!empty($runtimeConfig)) {
			$newPath = $this->getRuntimePath($path, $runtimeConfig);
		} else {
			$runtimeConfig = array();
		}

		if($this->isStored($newPath, $filter)) {
			return $this->resolve($newPath, $filter);
		} else if($this->resolveInstant) {

			try {
				$binary = $this->dataManager->find($filter, $path);
				$convertedBinary = $this->filterManager->applyFilter($binary, $filter, $runtimeConfig);
				$this->store(
					$convertedBinary,
					$newPath,
					$filter
				);
				$path = $this->resolve($newPath, $filter);
			} catch (\Exception $e) {
				$path = '';
			}
			return $path;
		} else {
			return $this->generateUrl($path, $filter, $runtimeConfig, $this->newName);
		}
	}

	public function generateUrl($path, $filter, array $runtimeConfig = array(), $newName = 'null')
	{
		$params = array(
			'path' => ltrim($path, '/'),
			'filter' => $filter
		);

		if ($newName != null) {
			$params['newName'] = $newName;
		} else {
			$params['newName'] = 'null';
		}

		if (empty($runtimeConfig)) {
			$filterUrl = $this->router->generate('liip_imagine_filter', $params, false);
		} else {
			$params['filters'] = $runtimeConfig;
			$params['hash'] = $this->signer->sign($path, $runtimeConfig);
			$filterUrl = $this->router->generate('liip_imagine_filter_runtime', $params, false);
		}

		return $filterUrl;
	}
}
