<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

JHtml::_('behavior.keepalive');

$gtranslate = $this->params->get('gtranslate', '');
$editor = JFactory::getEditor();
$tid = isset($this->translation->id) ? $this->translation->id : '';
?>

<?php if ($gtranslate): ?>
	<input type="hidden" id="translate_key" value="<?= $gtranslate ?>" />
	<input type="hidden" id="translate_id" value="" />
	<input type="hidden" id="translate_source" value="<?= substr($this->item->language, 0, 2) ?>" />
	<input type="hidden" id="translate_target" value="<?= substr(JRequest::getCmd('lang'), 0, 2) ?>" />
<?php endif ?>

<form action="<?= JRoute::_('index.php?option=com_chpanel') ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

    <input type="hidden" name="lang" value="<?= JRequest::getCmd('lang') ?>" />
    <input type="hidden" name="id" value="<?= JRequest::getInt('id') ?>" />
    <input type="hidden" name="tid" value="<?= $tid ?>" />
    <input type="hidden" name="task" value="" />

	<?= JHtml::_('form.token') ?>

    <!-- main -->
    <div class="row-fluid" id="chpanel-view-translation">

        <div class="span12">

            <div class="form-horizontal">
                <fieldset>
                    <legend><?= JText::_('COM_CHPANEL_HOTEL_FIELDSET_INFO') ?></legend>

					<?php
					// title & alias
					foreach (array('title', 'alias') as $field)
					{
						$label = JText::_('COM_CHPANEL_HOTEL_' . strtoupper($field));
						CHPanelHelper::renderTranslateText($field, $this->item->$field, @$this->translation->$field, $label, $gtranslate);
					}

					// info
					foreach (array('info', 'text', 'conditions') as $field)
					{
						$label = JText::_('COM_CHPANEL_HOTEL_' . strtoupper($field));
						if ($this->item->$field)
						{
							CHPanelHelper::renderTranslateTextArea($field, $this->item->$field, @$this->translation->data->$field, $label, $gtranslate);
						}
					}

					// video
					CHPanelHelper::renderTranslateText('video', $this->item->video, @$this->translation->data->video, JText::_('COM_CHPANEL_HOTEL_VIDEO'), $gtranslate);
					?>

                </fieldset>

            </div>

            <div class="form-horizontal">
                <fieldset>
                    <legend><?= JText::_('COM_CHPANEL_HOTEL_FIELDSET_CONTACT') ?></legend>
					<?php
					foreach (array('street', 'zip', 'city', 'region', 'country', 'phone', 'email') as $field)
					{
						$label = JText::_('COM_CHPANEL_HOTEL_' . strtoupper($field));
						CHPanelHelper::renderTranslateText($field, $this->item->$field, @$this->translation->data->$field, $label, $gtranslate);
					}
					?>
                </fieldset>
            </div>


			<?php if ($this->item->rooms): ?>

				<div class="form-horizontal">
					<fieldset>
						<legend><?= JText::_('COM_CHPANEL_TRANSLATION_ROOMS') ?></legend>
						<?php
						foreach ($this->item->rooms as $i => $room)
						{
							$field = "room_" . $room->id;
							$label = JText::_('COM_CHPANEL_ANY_ROOM') . ' #' . ($i + 1);
							CHPanelHelper::renderTranslateText($field, $room->title, @$this->translation->data->$field, $label, $gtranslate);
							echo '<div class="chpanel-translate-room-info">';
							$field = "room_info_" . $room->id;
							$label = ' ';
							CHPanelHelper::renderTranslateTextArea($field, $room->info, @$this->translation->data->$field, $label, $gtranslate);
							echo '</div>';
							echo '<div class="chpanel-translate-room-text">';
							$field = "room_text_" . $room->id;
							$label = ' ';
							CHPanelHelper::renderTranslateTextArea($field, $room->text, @$this->translation->data->$field, $label, $gtranslate);
							echo '</div>';
							$field = "room_video_" . $room->id;
							$label = ' ';
							CHPanelHelper::renderTranslateText($field, $room->video, @$this->translation->data->$field, $label, $gtranslate);
						}
						?>
					</fieldset>
				</div>

			<?php endif ?>

			<?php if ($this->item->images): ?>

				<div class="form-horizontal">
					<fieldset>
						<legend><?= JText::_('COM_CHPANEL_TRANSLATION_IMAGES') ?></legend>
						<?php
						foreach ($this->item->images as $i => $image)
						{
							$field = "image_" . $image->id;
							$label = JText::_('COM_CHPANEL_ANY_IMAGE') . ' #' . ($i + 1);
							CHPanelHelper::renderTranslateText($field, $image->title, @$this->translation->data->$field, $label, $gtranslate);
							if ($image->info)
							{
								echo '<div class="chpanel-translate-image-info">';
								$field = "image_info_" . $image->id;
								$label = ' ';
								CHPanelHelper::renderTranslateTextArea($field, $image->info, @$this->translation->data->$field, $label, $gtranslate);
								echo '</div>';
							}
						}
						?>
					</fieldset>
				</div>

			<?php endif ?>

        </div>

    </div>
    <!--/main -->

</form>