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

		$width = $options['size'][0];
		$height = $options['size'][1];
		$x = (isset($options['size'][2])) ? $options['size'][2] : false;
		$y = (isset($options['size'][3])) ? $options['size'][3] : false;

		$size = $image->getSize();
		$factor = $width / $size->getWidth();

		if ($size->getWidth() > $size->getHeight()) {
			$format = 'landscape';
		} else {
			$format = 'portrait';
		}

		if (is_int($x) && is_int($y)) {
			$point = new Point($x, $y);
			$size = $image->getSize();
			$filter = new Crop($point, new Box($width, $height));
			$image = $filter->apply($image);
		} else {

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

		}


        return $image;
    }
}
