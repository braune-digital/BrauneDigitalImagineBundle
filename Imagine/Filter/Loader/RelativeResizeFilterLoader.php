<?php

namespace BrauneDigital\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Exception\InvalidArgumentException;
use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

use Liip\ImagineBundle\Imagine\Filter\RelativeResize;

/**
* Loader for this bundle's relative resize filter.
*
* @author Jeremy Mikola <jmikola@gmail.com>
*/
class RelativeResizeFilterLoader implements LoaderInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ImageInterface $image, array $options = array())
    {

		list($maxWidth, $maxHeight) = $options['max'];

		$size = $image->getSize();
		$factor = min(($maxHeight / $size->getHeight()), ($maxWidth / $size->getWidth()));

		$filter = new RelativeResize('widen', $factor *  $size->getWidth());
		return $filter->apply($image);


        throw new InvalidArgumentException('Expected method/parameter pair, none given');
    }
}
