<?php

namespace BrauneDigital\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Filter\Basic\Thumbnail;
use Imagine\Filter\FilterInterface;
use Imagine\Gd\Effects;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\Color\RGB;
use Imagine\Image\Point;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Box;
use Liip\ImagineBundle\Imagine\Filter\RelativeResize;


class CropAndCenterLoader implements LoaderInterface {

    protected $imagine;
    protected $logger;

    public function __construct(ImagineInterface $imagine, $logger)
    {
        $this->imagine = $imagine;
        $this->logger = $logger;
    }
   
    /**
     * {@inheritdoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {

        list($width, $height) = $options['size'];

        $originalWidth = $image->getSize()->getWidth();
        $originalHeight= $image->getSize()->getHeight();

        if (is_null($width) && !is_null($height)) {
            $widthScale = null;
            $heightScale = $originalHeight / $height;
            $scaling = $heightScale;
            $width = $originalWidth / $scaling;
        } elseif (!is_null($width) && is_null($height)) {
            $widthScale = $originalWidth / $width;
            $heightScale = null;
            $scaling = $widthScale;
            $height = $originalHeight / $scaling;
        } elseif (is_null($width) && is_null($height)) {
            return $scaling = 1;
        } else {
            $widthScale = $originalWidth / $width;
            $heightScale = $originalHeight / $height;

            if ($widthScale > 1) {
                // We must shrink width-wise
                if ($heightScale > 1) {
                    // We must shrink height-wise
                    if ($widthScale < $heightScale) {
                        // We have to reduce less in the width direction. We do so.
                        $scaling = $widthScale;
                    } else {
                        $scaling = $heightScale;
                    }
                } elseif ($heightScale < 1) {
                    // We must grow height-wise
                    $scaling = $heightScale;
                } else {
                    // We are exactly OK weight-wise
                    $scaling = $widthScale;
                }
            } elseif ($widthScale < 1) {
                // We must expand width-wise
                if ($heightScale > 1) {
                    // We must shrink height-wise
                    $scaling = $widthScale;
                } elseif ($heightScale < 1) {
                    // We must grow height-wise
                    if ($widthScale < $heightScale) {
                        // We have to reduce less in the width direction. We do so.
                        $scaling = $widthScale;
                    } else {
                        $scaling = $heightScale;
                    }
                } else {
                    // We are exactly OK weight-wise
                    $scaling = $widthScale;
                }

            } else {
                $scaling = $heightScale;
            }
        }

        // We must go in reverse direction to the proportions right now.
        $scaleTransformation = 1 / $scaling;
        $newWidth = $originalWidth * $scaleTransformation;
        $newHeight = $originalHeight * $scaleTransformation;

        
        $image->resize(new Box($newWidth, $newHeight));
        $filter = new Thumbnail(new Box($width, $height), ImageInterface::THUMBNAIL_INSET);
        $image = $filter->apply($image);


        // Calculate the offset on the original image to center
        if ($scaling == $widthScale) {
            $widthOffset = abs($image->getSize()->getWidth() - $width) / 2;
            $heightOffset = 0;
        } elseif ($scaling == $heightScale) {
            $heightOffset = abs($image->getSize()->getHeight() - $height) / 2;
            $widthOffset = 0;
        } else {
            $widthOffset = 0;
            $heightOffset = 0;
        }

        $background = $image->palette()->color(
            isset($options['background']) ? $options['background'] : '#fff',
            isset($options['transparency']) ? $options['transparency'] : null
        );

        $canvas = $this->imagine->create(new Box($width, $height), $background);
        $canvas->paste($image, new Point($widthOffset, $heightOffset));

        return $canvas;
    }

}