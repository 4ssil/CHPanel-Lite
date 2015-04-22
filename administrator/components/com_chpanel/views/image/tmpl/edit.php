<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

// chosen
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

// prepare params
$params = $this->form->getFieldset('params');

//tags
$tags = array();
if (isset($this->item->params['tags']))
{
	$tags = $this->item->params['tags'];
}

//rooms
$rooms = array();
if (isset($this->item->params['rooms']))
{
	$rooms = $this->item->params['rooms'];
}
?>

<!-- adminForm -->
<form action="<?= JRoute::_('index.php?option=com_chpanel&view=image&layout=edit&id=' . (int) $this->item->id) ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

    <!-- form fields -->
    <input type="hidden" name="task" value="" />
	<?= JHtml::_('form.token') ?>

    <!-- main -->
    <div class="row-fluid">

        <div class="span9">

            <!-- info -->
            <div class="form-horizontal">
                <fieldset>
					<?php foreach (array('title', 'alias', 'info') as $field): ?>
						<?= $this->form->getControlGroup($field) ?>
					<?php endforeach ?>
                </fieldset>
            </div>

            <!-- tags --> 
            <div class="form-inline">
                <fieldset>
                    <h3><?= JText::_('COM_CHPANEL_IMAGE_FIELDSET_TAGS') ?></h3>
					<?php foreach ($this->tags as $tag): ?>
						<?php $checked = isset($tags[$tag]) ? 'checked="checked"' : '' ?>
						<div class="control-group">
							<label class="checkbox">
								<input type="checkbox" <?= $checked ?> name="jform[params][tags][<?= $tag ?>]" id="jform_tag_<?= $tag ?>" value="1">
								<?= JText::_('COM_CHPANEL_IMAGE_TAG_' . strtoupper($tag)) ?>
							</label>
						</div>
					<?php endforeach ?>
                </fieldset>
            </div>

            <!-- rooms --> 
            <div class="form-inline">
                <fieldset>
                    <h3><?= JText::_('COM_CHPANEL_IMAGE_FIELDSET_ROOMS') ?></h3>
					<?php if ($this->rooms): ?>
						<?php foreach ($this->rooms as $room): ?>
							<?php $checked = isset($rooms[$room->id]) ? 'checked="checked"' : '' ?>
							<div class="control-group">
								<label class="checkbox">
									<input type="checkbox" <?= $checked ?> name="jform[params][rooms][<?= $room->id ?>]" id="jform_tag_<?= $room->id ?>" value="1">
									<?= $room->title ?>
								</label>
							</div>
						<?php endforeach ?>
					<?php endif ?>
                </fieldset>
            </div>

        </div>

        <!-- sidebar -->
        <div class="span3">

            <!-- image -->
            <div class="well">
                <h3><?= JText::_('COM_CHPANEL_ANY_FIELDSET_IMAGE') ?></h3>
                <div class="control-group muted">
					<?= JText::sprintf('COM_CHPANEL_ANY_IMAGE_DESC', $this->params->get('img_upload', 1500)) ?>
                </div>
				<?php if ($this->image): ?>
					<div class="control-group">
						<?= Jhtml::image(JURI::root() . 'images/chpanel/images/' . $this->item->id . '-small.jpg?rand=' . rand(1, 999), $this->item->title, 'class="thumbnail"') ?>
					</div>
					<div class="control-group">
						<label class="checkbox hasTooltip" title="<?= JText::_('COM_CHPANEL_ANY_IMAGE_DELETE') ?>::<?= JText::_('COM_CHPANEL_ANY_IMAGE_DELETE_DESC') ?>">
							<input type="checkbox" name="image_delete" value="1"><?= JText::_('COM_CHPANEL_ANY_IMAGE_DELETE') ?>
						</label>
					</div>
				<?php endif ?>
                <div class="control-group">
                    <input type="file" name="image" id="chpanel-image" value="" class="">
                </div>
            </div>

            <!-- publishing -->
            <div class="well">
                <h3><?= JText::_('COM_CHPANEL_ANY_FIELDSET_PUBLISH') ?></h3>
				<?php foreach (array('state', 'hotel', 'hotel_id', 'created', 'id') as $field): ?>
					<?= $this->form->getControlGroup($field) ?>
				<?php endforeach ?>
            </div>

        </div>
        <!-- /sidebar -->

    </div>
    <!-- /main -->

</form>
<!-- /adminForm -->
