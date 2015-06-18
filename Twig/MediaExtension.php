<?php

namespace BrauneDigital\ImagineBundle\Twig;

use Sonata\MediaBundle\Twig\TokenParser\MediaTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\ThumbnailTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\PathTokenParser;
use Sonata\CoreBundle\Model\ManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;

class MediaExtension extends \Twig_Extension
{
    protected $mediaService;

    protected $resources = array();

    protected $mediaManager;

    protected $environment;

    /**
     * @param Pool             $mediaService
     * @param ManagerInterface $mediaManager
     */
    public function __construct(Pool $mediaService, ManagerInterface $mediaManager)
    {
        $this->mediaService = $mediaService;
        $this->mediaManager = $mediaManager;
    }

	public function getFunctions()
	{
		$functions = array(
			new \Twig_SimpleFunction('original_file_path', array($this, 'getOriginalFilePath')),
		);

		return $functions;
	}

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return array(
            new MediaTokenParser($this->getName()),
            new ThumbnailTokenParser($this->getName()),
            new PathTokenParser($this->getName()),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'braunedigital_media';
    }



    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string                                   $format
     *
     * @return string
     */
    public function getOriginalFilePath($media = null)
    {
        $media = $this->getMedia($media);

        if (!$media) {
             return '';
        }

        $provider = $this->getMediaService()
           ->getProvider($media->getProviderName());


        return '/uploads/media/' . $provider->generatePath($media) . '/' .  $media->getProviderReference();
    }

    /**
     * @return \Sonata\MediaBundle\Provider\Pool
     */
    public function getMediaService()
    {
        return $this->mediaService;
    }

	/**
	 * @param mixed $media
	 *
	 * @return null|\Sonata\MediaBundle\Model\MediaInterface
	 */
	private function getMedia($media)
	{
		if (!$media instanceof MediaInterface && strlen($media) > 0) {
			$media = $this->mediaManager->findOneBy(array(
				'id' => $media
			));
		}

		if (!$media instanceof MediaInterface) {
			return false;
		}

		if ($media->getProviderStatus() !== MediaInterface::STATUS_OK) {
			return false;
		}

		return $media;
	}

}
