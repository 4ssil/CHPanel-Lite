<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

/**
 * CHPanel Helper
 */
class CHPanelHelper
{

	/**
	 * Return component views
	 */
	public static function getViews()
	{
		return array('manage', 'bookings', 'hotels', 'rooms', 'images');
	}

	/**
	 * Create Submenu
	 */
	public static function addSubmenu($vName)
	{

		JHtmlSidebar::addEntry(JText::_('COM_CHPANEL_VIEW_PANEL'), 'index.php?option=com_chpanel&view=panel', $vName == 'panel');

		foreach (self::getViews() as $view)
		{
			JHtmlSidebar::addEntry(JText::_('COM_CHPANEL_VIEW_' . strtoupper($view)), 'index.php?option=com_chpanel&view=' . $view, $vName == $view);
		}
	}

	/**
	 * Toolbar Helper
	 */
	public static function getToolbar($view = '', $show = false, $title = false)
	{

		// set view
		$view = $view ? $view : JRequest::getWord('view', 'panel');

		// view type
		if (substr($view, -1) == 's' || $view == 'panel' | $view == 'manage')
		{
			$controller = substr($view, 0, -1);
			$controller_list = $view;
			$list = true;
			self::addSubmenu($view);
		}
		else
		{
			$controller = $view;
			$controller_list = $view . 's';
			$list = false;
		}

		// load assets
		JHtml::_('behavior.framework');
		JHtml::_('behavior.formvalidation');
		JHtml::_('bootstrap.framework');
		JHtml::script(JUri::base() . 'components/com_chpanel/assets/chpanel.js');
		JHtml::stylesheet(JUri::base() . 'components/com_chpanel/assets/chpanel.css');

		// page title
		$title = $title ? $title : $title = 'CloudHotelier Panel Lite: ' . JText::_('COM_CHPANEL_VIEW_' . strtoupper($view));
		JToolBarHelper::title($title, $view);
		JFactory::getDocument()->setTitle('CHPanel: ' . $title);

		// buttons
		if ($list && $view != 'panel' && $view != 'manage')
		{

			// new item
			JToolBarHelper::custom($controller . '.edit', 'new.png', 'new_f2.png', 'JTOOLBAR_NEW', false);

			// publish state
			if ($view != 'bookings')
			{
				JToolBarHelper::custom($controller_list . '.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom($controller_list . '.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
				JToolBarHelper::archiveList($controller_list . '.archive', 'JTOOLBAR_ARCHIVE');
			}
			else
			{
				$filters = JFactory::getApplication()->getUserState('com_chpanel.bookings.filter');
				$archived = $filters['state'] == '2';
				if ($archived)
				{
					JToolBarHelper::custom($controller_list . '.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				}
				else
				{
					JToolBarHelper::archiveList($controller_list . '.archive', 'JTOOLBAR_ARCHIVE');
				}
			}

			// delete / trash
			if ($view != 'bookings')
			{
				if ($show)
				{
					JToolBarHelper::custom($controller_list . '.delete', 'delete.png', 'delete_f2.png', 'JTOOLBAR_DELETE', true);
				}
				else
				{
					JToolBarHelper::trash($controller_list . '.trash', 'JTOOLBAR_TRASH');
				}
			}
		}

		if ($view == 'panel')
		{
			JToolBarHelper::preferences('com_chpanel');
		}

		if (!$list)
		{
			JToolBarHelper::apply($controller . '.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save($controller . '.save');
			if ($view != 'translation')
			{
				JToolBarHelper::custom($controller . '.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				if ($show)
				{
					JToolBarHelper::custom($controller . '.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
				}
			}
			else
			{
				JToolBarHelper::custom($controller . '.delete', 'delete.png', 'delete_f2.png', 'JTOOLBAR_DELETE', false);
			}
			JToolBarHelper::cancel($controller . '.cancel');
			JRequest::setVar('hidemainmenu', true);
		}
	}

	/**
	 * Perform date modifications to make it suitable for JDate
	 */
	static function correctDateFormat($date)
	{

		$date_format = self::getDateFormat(JText::_('COM_CHPANEL_LOCALE'));

		if ($date_format == 'd/m/Y')
		{
			return str_replace('/', '-', $date);
		}

		if ($date_format == 'd.m.Y')
		{
			return str_replace('.', '-', $date);
		}

		return $date;
	}

	/**
	 * Avoid date errors on language debug and language switch
	 */
	static function getDateFormat($locale)
	{
		return trim(str_replace('*', '', $locale));
	}

	/**
	 * ControlGroup for param
	 * @param type $params
	 * @param type $field
	 * @return type
	 */
	public static function getControlGroup($params, $field, $multiple = false)
	{
		return '<div class="control-group"><div class="control-label">' . $params['jform_params_' . $field]->label . '</div><div class="controls">' . $params['jform_params_' . $field]->input . '</div></div>';
	}

	/**
	 * Bookings Filter Dates
	 */
	public static function getBookingFilterDates()
	{
		return array(
			'created' => JText::_('COM_CHPANEL_ANY_DATE'),
			'checkin' => JText::_('COM_CHPANEL_BOOKING_CHECKIN'),
			'checkout' => JText::_('COM_CHPANEL_BOOKING_CHECKOUT'),
		);
	}

	/**
	 * Bookings statuses
	 */
	public static function getBookingStatus()
	{

		return array(
			100 => JText::_('COM_CHPANEL_BOOKING_STATUSES_PROCESSING'),
			101 => JText::_('COM_CHPANEL_BOOKING_STATUS_PROCESSING'),
			102 => JText::_('COM_CHPANEL_BOOKING_STATUS_PROCESSING_PAYMENT'),
			200 => JText::_('COM_CHPANEL_BOOKING_STATUSES_CONFIRMED'),
			201 => JText::_('COM_CHPANEL_BOOKING_STATUS_CONFIRMED_WEBSITE'),
			202 => JText::_('COM_CHPANEL_BOOKING_STATUS_CONFIRMED_USER'),
			203 => JText::_('COM_CHPANEL_BOOKING_STATUS_CONFIRMED_BLOCKED'),
			300 => JText::_('COM_CHPANEL_BOOKING_STATUSES_CANCELLED'),
			301 => JText::_('COM_CHPANEL_BOOKING_STATUS_CANCELLED_GUEST'),
			302 => JText::_('COM_CHPANEL_BOOKING_STATUS_CANCELLED_USER'),
			303 => JText::_('COM_CHPANEL_BOOKING_STATUS_CANCELLED_NOSHOW'),
			400 => JText::_('COM_CHPANEL_BOOKING_STATUSES_ABORTED'),
			401 => JText::_('COM_CHPANEL_BOOKING_STATUS_ABORTED'),
			402 => JText::_('COM_CHPANEL_BOOKING_STATUS_ABORTED_EXPIRED_PENDING'),
			403 => JText::_('COM_CHPANEL_BOOKING_STATUS_ABORTED_EXPIRED_PENDING_PAYMENT'),
		);
	}

	/**
	 * Render a translation text input control-group 
	 * @param $field
	 * @param $original
	 * @param $translation
	 * @param $label
	 * @param $gtranslate
	 */
	public static function renderTranslateText($field, $original, $translation, $label, $gtranslate)
	{
		?>
		<div class="control-group">
			<label class="control-label"><?php echo $label; ?></label>
			<div class="controls">
				<input type="text" name="" id="<?php echo $field; ?>_original" value="<?php echo htmlspecialchars($original); ?>" disabled class="input-xlarge original" >
				<input type="text" name="<?php echo $field; ?>" id="<?php echo $field; ?>" value="<?php echo htmlspecialchars($translation); ?>" class="input-xlarge" >
				<a href="javascript:;" class="btn btn-mini copy" rel="<?php echo $field; ?>"><?php echo JText::_('COM_CHPANEL_TRANSLATION_COPY'); ?></a>
		<?php if ($gtranslate) : ?><a href="javascript:;" class="btn btn-mini translate" rel="<?php echo $field; ?>"><?php echo JText::_('COM_CHPANEL_TRANSLATION_TRANSLATE'); ?></a><?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a translation textarea control-group 
	 * @param $field
	 * @param $original
	 * @param $translation
	 * @param $label
	 * @param $gtranslate
	 */
	public static function renderTranslateTextArea($field, $original, $translation, $label, $gtranslate)
	{
		?>
		<div class="control-group">
			<label class="control-label"><?php echo $label; ?></label>
			<div class="controls">
				<textarea name="" id="<?php echo $field; ?>_original" disabled class="input-xlarge original" ><?php echo htmlspecialchars($original); ?></textarea>
				<textarea name="<?php echo $field; ?>" id="<?php echo $field; ?>" class="input-xlarge" ><?php echo htmlspecialchars($translation); ?></textarea>
				<a href="javascript:;" class="btn btn-mini copy" rel="<?php echo $field; ?>"><?php echo JText::_('COM_CHPANEL_TRANSLATION_COPY'); ?></a>
		<?php if ($gtranslate) : ?><a href="javascript:;" class="btn btn-mini translate" rel="<?php echo $field; ?>"><?php echo JText::_('COM_CHPANEL_TRANSLATION_TRANSLATE'); ?></a><?php endif; ?>
			</div>
		</div>
		<?php
	}

}
