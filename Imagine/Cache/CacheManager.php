<?php

namespace BrauneDigital\ImagineBundle\Imagine\Cache;
use Liip\ImagineBundle\Imagine\Cache\CacheManager as BaseCacheManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CacheManager extends BaseCacheManager implements ContainerAwareInterface
{

    protected $newName;

    protected $resolveInstant = false;

    /**
     * @var ContainerInterface
     */
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

    public function getBrowserPath(
        $path,
        $filter,
        array $runtimeConfig = array(),
        $resolver = null,
        $referenceType = UrlGeneratorInterface::ABSOLUTE_URL
    ) {
        $newPath = $path;
        if ($this->newName != null && $this->newName != 'null') {
            $pathInfo = pathinfo($path);
            $newPath = str_replace($pathInfo['basename'], $this->newName . '.' . $pathInfo['extension'], $path);
        }

        if (!empty($runtimeConfig)) {
            if ($this->newName != null && $this->newName != 'null') {
                $newPath = $this->getRuntimePath($path, $runtimeConfig);
                $pathInfo = pathinfo($newPath);
                $newPath = str_replace($pathInfo['basename'], $this->newName . '.' . $pathInfo['extension'], $newPath);
            }
        } else {
            $runtimeConfig = array();
        }

        $newPath = $this->slugGenerator($newPath);

        if($this->isStored($newPath, $filter)) {
            return $this->resolve($newPath, $filter);
        } else if($this->resolveInstant) {

            try {
                $binary = $this->dataManager->find($filter, $path);
                if (empty($runtimeConfig)) {
                    $convertedBinary = $this->filterManager->applyFilter($binary, $filter);
                    $this->store(
                        $convertedBinary,
                        $newPath,
                        $filter
                    );
                    $path = $this->resolve($newPath, $filter);
                } else {
                    $rcPath = $newPath;
                    $filterConfig = array();
                    if (!empty($runtimeConfig)) {
                        $filterConfig = array(
                            'filters' => $runtimeConfig,
                        );
                    }
                    $this->store(
                        $this->filterManager->applyFilter($binary, $filter, $filterConfig),
                        $rcPath,
                        $filter
                    );
                    $path = $this->resolve($rcPath, $filter);
                }
            } catch (\Exception $e) {
                $path = $filter . ': ' . json_encode($runtimeConfig) . ': ' . $e->getMessage();
            }

            return $path;
        } else {

            return $this->generateUrl($path, $filter, $runtimeConfig);
        }
    }

    public function generateUrl(
        $path,
        $filter,
        array $runtimeConfig = array(),
        $resolver = null,
        $referenceType = UrlGeneratorInterface::ABSOLUTE_URL
    ) {
        $params = array(
            'path' => ltrim($path, '/'),
            'filter' => $filter
        );

        if ($this->newName != null) {
            $params['newName'] = $this->newName;
        } else {
            $params['newName'] = 'null';
        }
        if (empty($runtimeConfig)) {
            $filterUrl = $this->router->generate('liip_imagine_filter', $params, UrlGeneratorInterface::ABSOLUTE_URL);
        } else {
            $params['filters'] = $runtimeConfig;
            $params['hash'] = $this->signer->sign($path, $runtimeConfig);
            $filterUrl = $this->router->generate('liip_imagine_filter_runtime', $params, UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $filterUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function slugGenerator($name)
    {
        $pathParts = pathinfo($name);
        if (!isset($pathParts['extension'])) {
            return $name;
        }
        $exploded = explode('/',$pathParts['dirname']);
        $slug = array_map([$this, 'transformSlugParts'], $exploded);
        $filename = str_replace('.' . $pathParts['extension'], '', $pathParts['filename']);
        return implode('/', $slug) . '/' . $this->transformSlugParts($filename) .'.'. $pathParts['extension'];
    }

    private function transformSlugParts(string $part){
        $part = str_replace('\'', '-', $part);
        return \Behat\Transliterator\Transliterator::transliterate($part);
    }
}
