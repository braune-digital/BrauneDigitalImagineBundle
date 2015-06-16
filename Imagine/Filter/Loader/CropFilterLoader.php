<?php

namespace BrauneDigital\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Filter\Advanced\RelativeResize;
use Imagine\Filter\Basic\Crop;
use Imagine\Filter\Basic\Resize;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

class CropFilterLoader implements LoaderInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {
        list($width, $height) = $options['size'];

		$size = $image->getSize();
		$factor = $width / $size->getWidth();

		if ($size->getWidth() > $size->getHeight()) {
			$format = 'landscape';
		} else {
			$format = 'portrait';
		}

		if ($factor * $size->getHeight() < $height) {
			$factor = $height / $size->getHeight();
			$filter = new RelativeResize('heighten', $height);
			$filter->apply($image);
		} else {
			$filter = new RelativeResize('widen', $width);
			$filter->apply($image);
		}

		$x = abs(ceil(($image->getSize()->getWidth() - $width) / 2));
		$y = abs(ceil(($image->getSize()->getHeight() - $height) / 2));
		$point = new Point($x, $y);

		$size = $image->getSize();
        $filter = new Crop($point, new Box($width, $height));
        $image = $filter->apply($image);
		$image->resize(new Box($width, $height));

        return $image;
    }
}
