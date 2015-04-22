<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

JHtml::_('formbehavior.chosen', 'select');

$context = 'items';
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder)
{
	JHtml::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), 'index.php?option=com_chpanel&task=' . $context . '.saveOrderAjax&tmpl=component');
}

$archived = $this->state->get('filter.state') == 2 ? true : false;
$trashed = $this->state->get('filter.state') == -2 ? true : false;

$imageHelper = new CHPanelHelperImage(JComponentHelper::getParams('com_chpanel'));
$image_path = JURI::root() . "/images/chpanel/$context/";
$types = CHPanelHelper::defaultTypes();
?>

<!-- adminForm -->
<form action="<?= JRoute::_('index.php?option=com_chpanel&view=' . $context) ?>" method="post" name="adminForm" id="adminForm">

    <!-- main -->
    <div id="j-main-container" class="span12">

        <!-- search tools -->
		<?= JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)) ?>

        <!-- no items -->
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?= JText::_('JGLOBAL_NO_MATCHING_RESULTS') ?>
			</div>
		<?php endif ?>
        <!-- /no items -->

        <!-- items table -->
		<?php if (!empty($this->items)) : ?>

			<table class="table table-striped" id="articleList">

				<thead>
					<tr>
						<th class="nowrap center" width="8%">
							<?= JHtml::_('searchtools.sort', 'COM_CHPANEL_ANY_CREATED', 'a.created', $listDirn, $listOrder) ?>
						</th>
						<th class="nowrap">
							<?= JHtml::_('searchtools.sort', 'COM_CHPANEL_ITEM_TITLE', 'a.title', $listDirn, $listOrder) ?>
						</th>
						<th class="nowrap center" width="8%">
							<?= JHtml::_('searchtools.sort', 'COM_CHPANEL_ANY_LANGUAGE', 'l.title', $listDirn, $listOrder) ?>
						</th> 
						<th class="nowrap center" width="8%">
							<?= JHtml::_('searchtools.sort', 'COM_CHPANEL_ANY_EDITED', 'a.modified', $listDirn, $listOrder) ?>
						</th>
						<th class="nowrap center" width="1%">
							<?= JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder) ?>
						</th>
					</tr>
				</thead>

				<tfoot>
					<tr>
						<td colspan="30">
							<?= $this->pagination->getListFooter() ?>
						</td>
					</tr>
				</tfoot>

				<tbody>

					<?php foreach ($this->items as $i => $item) : ?>

						<?php
						$canChange = true;
						$canCheckin = true;
						$images = $item->images > 0 ? '1' : '0';
						$link_images = JRoute::_('index.php?option=com_chpanel&view=images&filter[item]=' . $item->id . '&filter[catid]=' . $item->catid . '&filter[user]=&filter[state]=');
						?>

						<tr class="row<?= $i % 2 ?>" sortable-group-id="<?= $item->catid ?>">

							<td class="nowrap center small">
								<?= JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')) ?>
							</td>

							<td class="nowrap has-context">
								<div class="pull-left">
									<?php if ($item->checked_out) : ?>
										<?= JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $context . '.', $canCheckin) ?>
									<?php endif ?>
									<a href="javascript:;" onclick="window.parent.chpanelSelectItem('<?= $item->id ?>', '<?= $this->escape(addslashes($item->title)) ?>');">
										<?php if ($imageHelper->getImage($item->id, $context)) : ?>
											<?= '<img class="thumbnail pull-left chpanel-images-thumb" src="' . $image_path . $item->id . '-tiny.jpg?' . rand(1, 999) . '" />' ?>
										<?php endif ?>
										<?= $this->escape($item->title) ?>
									</a>
									<span class="small"><?= JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)) ?></span>
									<div class="small">
										<?= JText::_('JCATEGORY') . ": " . $this->escape($item->category) ?>
										\ <?= JText::_('COM_CHPANEL_TYPE') . ": " . ($item->type_id > 100 ? $item->type : JText::_('COM_CHPANEL_TYPE_' . $types[$item->type_id])) ?>
									</div>
								</div>
							</td>

							<td class="nowrap center small">
								<?php if ($item->language == '*'): ?>
									<?= JText::alt('JALL', 'language') ?>
								<?php else: ?>
									<?= $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED') ?>
								<?php endif ?>
							</td>

							<td class="nowrap center small">
								<?= JHtml::_('date', $item->modified, JText::_('DATE_FORMAT_LC4') . ' H:m') ?>
							</td>

							<td class="nowrap center small">
								<?= $item->id ?>
							</td>

						</tr>

					<?php endforeach ?>

				</tbody>


			</table>
		<?php endif ?>
        <!-- /items table -->

    </div>
    <!-- /main -->

    <!-- form fields -->
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="tmpl" value="component" />
    <input type="hidden" name="layout" value="modal" />
    <input type="hidden" name="boxchecked" value="0" />
	<?= JHtml::_('form.token') ?>
    <!-- /form fields -->

</form>
<!-- /adminForm -->