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


		if (is_int($x) && is_int($y)) {

			$point = new Point($x, $y);
			$size = $image->getSize();
			$filter = new Crop($point, new Box($width, $height));
			$image = $filter->apply($image);

			if (isset($options['resize']) && isset($options['resize'][0]) && isset($options['resize'][1])) {

				$size = $image->getSize();
				$factor = $options['resize'][0] / $size->getWidth();

				if ($factor * $size->getHeight() < $options['resize'][1]) {
					$filter = new RelativeResize('heighten', $options['resize'][1]);
					$filter->apply($image);
				} else {
					$filter = new RelativeResize('widen', $options['resize'][0]);
					$filter->apply($image);
				}

				$size = $image->getSize();

				$x = abs(ceil(($size->getWidth() - $options['resize'][0]) / 2));
				$y = abs(ceil(($size->getHeight() - $options['resize'][1]) / 2));

				$point = new Point($x, $y);
				$filter = new Crop($point, new Box($options['resize'][0], $options['resize'][1]));
				$image = $filter->apply($image);

				$image->resize(new Box($options['resize'][0], $options['resize'][1]));
			}

		} else {

			$factor = $width / $size->getWidth();
			if ($factor * $size->getHeight() < $height) {
				$filter = new RelativeResize('heighten', $height);
				$filter->apply($image);
			} else {
				$filter = new RelativeResize('widen', $width);
				$filter->apply($image);
			}

			$x = abs(ceil(($image->getSize()->getWidth() - $width) / 2));
			$y = abs(ceil(($image->getSize()->getHeight() - $height) / 2));

			$point = new Point($x, $y);
			$filter = new Crop($point, new Box($width, $height));
			$image = $filter->apply($image);

			$image->resize(new Box($width, $height));

		}


        return $image;
    }
}
