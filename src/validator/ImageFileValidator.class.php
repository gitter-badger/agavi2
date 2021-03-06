<?php
namespace Agavi\Validator;
// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * ImageFileValidator verifies a parameter is an uploaded image
 * 
 * Parameters:
 *   'min_width'    The minimum width of the image
 *   'max_width'    The maximum width of the image
 *   'min_height'   The minimum height of the image
 *   'max_height'   The maximum height of the image
 *   'format'       list of valid formats (gif,jpeg,png,bmp,psd,swf)
 *
 * Errors:
 *   'no_image'      The uploaded file is no image
 *   'min_width'
 *   'max_width'
 *   'min_height'
 *   'max_height'
 *   'format'        The image was not in the required format
 *
 * @see        AgaviBaseFileValidator
 *
 * @package    agavi
 * @subpackage validator
 *
 * @author     Dominik del Bondio <ddb@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */
class ImageFileValidator  extends BaseFileValidator
{
	/**
	 * Validates the input.
	 * 
	 * @return     bool File is valid image according to given parameters.
	 * 
	 * @author     Dominik del Bondio <ddb@bitxtender.com>
	 * @since      0.11.0
	 */
	protected function validate()
	{
		if(!parent::validate()) {
			return false;
		}

		$file = $this->getData($this->getArgument());

		$type = @getimagesize($file->getTmpName());
		if($type === false) {
			$this->throwError('no_image');
			return false;
		}

		list($width, $height, $imageType) = $type;

		if($this->hasParameter('max_width') && $width > $this->getParameter('max_width')) {
			$this->throwError('max_width');
			return false;
		}
		if($this->hasParameter('min_width') && $width < $this->getParameter('min_width')) {
			$this->throwError('min_width');
			return false;
		}

		if($this->hasParameter('max_height') && $height > $this->getParameter('max_height')) {
			$this->throwError('max_height');
			return false;
		}
		if($this->hasParameter('min_height') && $height < $this->getParameter('min_height')) {
			$this->throwError('min_height');
			return false;
		}

		if(!$this->hasParameter('format')) {
			return true;
		}
		
		// We need this additional alias map because image_type_to_extension()
		// returns only "jpeg" but not "jpg" or "tiff" but not "tif"
		$aliases = array(
			IMAGETYPE_JPEG    => 'jpg',
			IMAGETYPE_TIFF_II => 'tif',
			IMAGETYPE_TIFF_MM => 'tif',
		);
		$ext = image_type_to_extension($imageType, false);
		
		$format = $this->getParameter('format', array());
		
		if(!is_array($format)) {
			$format = explode(' ', $this->getParameter('format'));
		}
		
		foreach($format as $name) {
			$lName = strtolower($name);
			if($ext == $lName) {
				return true;
			} elseif(isset($aliases[$imageType]) && $aliases[$imageType] == $name) {
				return true;
			}
		}
		
		$this->throwError('format');
		return false;
	}
}

?>