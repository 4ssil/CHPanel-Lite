<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * Image upload and resizing utility
 */
class CHPanelHelperImage
{

	/**
	 * Constructor	
	 * @param mixed $params
	 */
	function __construct($params)
	{

		// initialize data
		$this->app = JFactory::getApplication();
		$this->path = JPATH_ROOT . '/images/chpanel';
		$this->params = $params;

		// check folders
		jimport('joomla.filesystem.folder');
		$folders = array();
		$folders[] = $this->path;
		$folders[] = $this->path . '/hotels';
		$folders[] = $this->path . '/rooms';
		$folders[] = $this->path . '/images';
		foreach ($folders as $folder)
		{
			if (!JFolder::exists($folder))
			{
				if (!JFolder::create($folder))
				{
					$this->app->enqueueMessage('COM_CHPANEL_ANY_IMAGE_ERROR_DIRECTORY', 'error');
				}
			}
		}
	}

	function imageFormat($file, $message = false)
	{
		if ($file['type'] == "image/pjpeg")
		{
			return "jpg";
		}
		if ($file['type'] == "image/jpeg")
		{
			return "jpg";
		}
		if ($message)
		{
			$this->app->enqueueMessage(JText::_('COM_CHPANEL_ANY_IMAGE_ERROR_FORMAT'), 'error');
		}
		return false;
	}

	function imageSize($file, $message = false)
	{
		$img_upload = $this->params->get('img_upload', 1500);
		$size = $file['size'] / 1024;
		$allowed_size = $img_upload;
		if ($size < $allowed_size)
		{
			return $size;
		}
		if ($message)
		{
			$this->app->enqueueMessage(JText::sprintf('COM_CHPANEL_ANY_IMAGE_ERROR_UPLOAD', round($size), $allowed_size), 'error');
		}
		return false;
	}

	function getImage($id, $folder, $message = false)
	{
		$path = "$this->path/$folder/$id.jpg";
		if (file_exists($path))
		{
			return true;
		}
		if ($message)
		{
			$this->app->enqueueMessage(JText::_('COM_CHPANEL_ANY_IMAGE_ERROR_NOTFOUND'), 'error');
		}
		return false;
	}

	function deleteImage($id, $folder, $message = false)
	{
		$image = $this->getImage($id, $folder);
		if ($image)
		{
			$path = "$this->path/$folder/$id";
			@unlink($path . '.jpg');
			@unlink($path . '-big.jpg');
			@unlink($path . '-screen.jpg');
			@unlink($path . '-med.jpg');
			@unlink($path . '-small.jpg');
			@unlink($path . '-tiny.jpg');
		}
		return true;
	}

	function renameImage($prevId, $newId, $folder)
	{
		$image = $this->getImage($prevId, $folder);
		if ($image)
		{
			$oldPath = "$this->path/$folder/$prevId";
			$newPath = "$this->path/$folder/$newId";
			@rename($oldPath . '.jpg', $newPath . '.jpg');
			@rename($oldPath . '-big.jpg', $newPath . '-big.jpg');
			@rename($oldPath . '-screen.jpg', $newPath . '-screen.jpg');
			@rename($oldPath . '-med.jpg', $newPath . '-med.jpg');
			@rename($oldPath . '-small.jpg', $newPath . '-small.jpg');
			@rename($oldPath . '-tiny.jpg', $newPath . '-tiny.jpg');
		}
	}

	function uploadImage($file, $id, $folder, $message = true)
	{

		// check file
		if (!$size = $this->imageSize($file, $message))
		{
			return false;
		}
		if (!$format = $this->imageFormat($file, $message))
		{
			return false;
		}

		// Delete previos files if exist
		$this->deleteImage($id, $folder);

		// Copy new working image
		$path = "$this->path/$folder/$id.jpg";
		if (!move_uploaded_file($file['tmp_name'], $path))
		{
			if ($message)
			{
				$this->app->enqueueMessage(JText::_('COM_CHPANEL_ANY_IMAGE_ERROR_COPY') . '<br>' . $path, 'error');
			}
			return false;
		}

		// Resize image in differents formats
		$rename = "$this->path/$folder/$id";
		$quality = $this->params->get('img_quality', 90);
		$this->resizeImage($path, $this->params->get('img_big', 800), $quality, $rename . '-big.jpg');
		$this->resizeImage($path, $this->params->get('img_screen', 520), $quality, $rename . '-screen.jpg');
		$this->resizeImage($path, $this->params->get('img_med', 240), $quality, $rename . '-med.jpg');
		$this->resizeImage($path, $this->params->get('img_small', 120), $quality, $rename . '-small.jpg');
		$this->resizeImage($path, $this->params->get('img_tiny', 60), $quality, $rename . '-tiny.jpg');

		if ($message)
		{
			$this->app->enqueueMessage(JText::_('COM_CHPANEL_ANY_IMAGE_OK_UPLOAD'));
		}

		return true;
	}

	function resizeImage($path, $width, $quality = 90, $newPath = false)
	{
		$source = @imagecreatefromjpeg($path);
		$imageWidth = imagesx($source);
		$imageHeight = imagesy($source);

		// Images will fit into a 3/4 rectangle
		$proportion = $imageHeight / $imageWidth;

		if ($proportion >= 0.75)
		{
			// Veritcal image: proporcional height to default width
			$height = $width * 0.75;
			// width will be reduced to fit original image proportions
			$width = $imageWidth / $imageHeight * $height;
		}
		else
		{
			// Horizontal image: reduce width to fit
			$height = $imageHeight / $imageWidth * $width;
		}

		$image = imagecreatetruecolor($width, $height);
		if (!imagecopyresampled($image, $source, 0, 0, 0, 0, $width, $height, $imageWidth, $imageHeight))
		{
			return false;
		}
		imagejpeg($image, $newPath, $quality);
		imagedestroy($image);
		return true;
	}

}
