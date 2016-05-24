<?php

namespace BrauneDigital\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Filter\FilterInterface;
use Imagine\Gd\Effects;
use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

class GrayScale implements LoaderInterface {



    /**
     * @param int $sigma
     */
    public function __construct()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {

        $image->effects()->grayscale();

        return $image;
    }
}