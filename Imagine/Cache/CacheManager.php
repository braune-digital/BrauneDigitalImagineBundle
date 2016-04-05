<?php

namespace BrauneDigital\ImagineBundle\Imagine\Cache;
use Liip\ImagineBundle\Imagine\Cache\CacheManager as BaseCacheManager;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;
use Liip\ImagineBundle\ImagineEvents;
use Liip\ImagineBundle\Events\CacheResolveEvent;

class CacheManager extends BaseCacheManager
{

	protected $newName;

	/**
	 * @param $path
	 * @param $filter
	 * @param array $runtimeConfig
	 * @param $newName
	 */
	public function getBrowserPathWithNewName($path, $filter, array $runtimeConfig = array(), $newName = 'null') {
		$this->newName = $newName;
		return $this->getBrowserPath($path, $filter, $runtimeConfig);
	}

	public function getBrowserPath($path, $filter, array $runtimeConfig = array())
	{

		$newPath = $path;
		if ($this->newName != null && $this->newName != 'null') {
			$pathInfo = pathinfo($path);
			$newPath = str_replace($pathInfo['basename'], $this->newName . '.' . $pathInfo['extension'], $path);
		}

		if (!empty($runtimeConfig)) {
			$rcPath = $this->getRuntimePath($path, $runtimeConfig);
			return $this->isStored($rcPath, $filter) ?
				$this->resolve($rcPath, $filter) :
				$this->generateUrl($path, $filter, $runtimeConfig, $this->newName)
				;
		}

		return $this->isStored($newPath, $filter) ?
			$this->resolve($newPath, $filter) :
			$this->generateUrl($path, $filter, array(), $this->newName)
			;
	}


	public function generateUrl($path, $filter, array $runtimeConfig = array(), $newName = 'null')
	{
		$params = array(
			'path' => ltrim($path, '/'),
			'filter' => $filter
		);

		if ($newName != 'null') {
			$params['newName'] = $newName;
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
