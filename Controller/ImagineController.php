<?php

namespace BrauneDigital\ImagineBundle\Controller;

use Imagine\Exception\RuntimeException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Imagine\Cache\SignerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Liip\ImagineBundle\Controller\ImagineController as BaseController;

class ImagineController extends BaseController
{

    /**
     * This action applies a given filter to a given image, optionally saves the image and outputs it to the browser at the same time.
     *
     * @param Request $request
     * @param string $filter
     * @param string $newName
     * @param string $path
     *
     * @throws \RuntimeException
     * @throws BadRequestHttpException
     *
     * @return RedirectResponse
     */
    public function filterWithNewNameAction(Request $request, $filter, $newName, $path = '')
    {

		$newPath = $path;
		if ($newName != 'null') {
			$pathInfo = pathinfo($path);
			$newPath = str_replace($pathInfo['basename'], $newName . '.' . $pathInfo['extension'], $path);
		}

        try {
            if (!$this->cacheManager->isStored($newPath, $filter)) {
				try {
					$binary = $this->dataManager->find($filter, $path);
				} catch (NotLoadableException $e) {
					if ($defaultImageUrl = $this->dataManager->getDefaultImageUrl($filter)) {
						return new RedirectResponse($defaultImageUrl);
					}

					throw new NotFoundHttpException('Source image could not be found', $e);
				}

				$this->cacheManager->store(
					$this->filterManager->applyFilter($binary, $filter),
					$newPath,
					$filter
				);
            }
			return new RedirectResponse($this->cacheManager->resolve($newPath, $filter), 301);

        } catch (RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $path, $filter, $e->getMessage()), 0, $e);
        }
    }

    /**
     * This action applies a given filter to a given image, optionally saves the image and outputs it to the browser at the same time.
     *
     * @param Request $request
     * @param string  $hash
     * @param string  $path
     * @param string  $newName
     * @param string  $filter
     *
     * @throws \RuntimeException
     * @throws BadRequestHttpException
     *
     * @return RedirectResponse
     */
    public function filterRuntimeWithNewNameAction(Request $request, $hash, $newName, $path, $filter)
    {
		$newPath = $path;
		if ($newName != 'null') {
			$pathInfo = pathinfo($path);
			$newPath = str_replace($pathInfo['basename'], $newName . '.' . $pathInfo['extension'], $path);
		}


        try {
            $filters = $request->query->get('filters', array());

            if (true !== $this->signer->check($hash, $path, $filters)) {
                throw new BadRequestHttpException(sprintf(
                    'Signed url does not pass the sign check for path "%s" and filter "%s" and runtime config %s',
                    $path,
                    $filter,
                    json_encode($filters)
                ));
            }

            try {
                $binary = $this->dataManager->find($filter, $path);
            } catch (NotLoadableException $e) {
                if ($defaultImageUrl = $this->dataManager->getDefaultImageUrl($filter)) {
                    return new RedirectResponse($defaultImageUrl);
                }

                throw new NotFoundHttpException(sprintf('Source image could not be found for path "%s" and filter "%s"', $path, $filter), $e);
            }

            $rcPath = $this->cacheManager->getRuntimePath($newPath, $filters);

            $this->cacheManager->store(
                $this->filterManager->applyFilter($binary, $filter, array(
                    'filters' => $filters,
                )),
                $rcPath,
                $filter
            );

            return new RedirectResponse($this->cacheManager->resolve($rcPath, $filter), 301);
        } catch (RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $hash.'/'.$path, $filter, $e->getMessage()), 0, $e);
        }
    }
}
