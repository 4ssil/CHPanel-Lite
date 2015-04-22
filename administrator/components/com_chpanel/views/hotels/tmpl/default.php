<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

JHtml::_('formbehavior.chosen', 'select');

$context = 'hotels';
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

$archived = $this->state->get('filter.state') == 2 ? true : false;
$trashed = $this->state->get('filter.state') == -2 ? true : false;

$imageHelper = new CHPanelHelperImage(JComponentHelper::getParams('com_chpanel'));
$image_path = JURI::root() . "/images/chpanel/$context/";
?>

<!-- adminForm -->
<form action="<?= JRoute::_('index.php?option=com_chpanel&view=' . $context) ?>" method="post" name="adminForm" id="adminForm">

    <!-- sidebar -->
    <div id="j-sidebar-container" class="span2">
		<?= $this->sidebar ?>
    </div>
    <!-- /sidebar -->

    <!-- main -->
    <div id="j-main-container" class="span10">

        <!-- search tools -->
		<?= JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)) ?>

        <!-- no hotels -->
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-hotels">
				<?= JText::_('JGLOBAL_NO_MATCHING_RESULTS') ?>
			</div>
		<?php endif ?>
        <!-- /no hotels -->

        <!-- hotels table -->
		<?php if (!empty($this->items)) : ?>

			<table class="table table-striped" id="articleList">

				<thead>
					<tr>
						<th width="1%" class="nowrap hidden-phone">
							<?= JHtml::_('grid.checkall') ?>
						</th> 
						<th class="nowrap" width="1%" style="min-width:55px">
							<?= JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder) ?>
						</th>
						<th class="nowrap">
							<?= JHtml::_('searchtools.sort', 'COM_CHPANEL_ANY_TITLE', 'a.title', $listDirn, $listOrder) ?>
						</th>

						<th class="nowrap center" width="8%">
							<?= JText::_('COM_CHPANEL_HOTELS_ROOMS') ?>
						</th> 

						<th class="nowrap center" width="8%">
							<?= JText::_('COM_CHPANEL_HOTELS_IMAGES') ?>
						</th> 

						<th class="nowrap center" width="8%">
							<?= JHtml::_('searchtools.sort', 'COM_CHPANEL_ANY_LANGUAGE', 'l.title', $listDirn, $listOrder) ?>
						</th> 

						<?= CHPanelHelperLangs::listHeader($this->lists->langs) ?>

						<th class="nowrap center" width="8%">
							<?= JHtml::_('searchtools.sort', 'COM_CHPANEL_ANY_CREATED', 'a.created', $listDirn, $listOrder) ?>
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
						$link_images = JRoute::_('index.php?option=com_chpanel&view=images&filter[hotel]=' . $item->id . '&filter[state]=');
						$link_rooms = JRoute::_('index.php?option=com_chpanel&view=rooms&filter[hotel]=' . $item->id . '&filter[state]=');
						?>

						<tr class="row<?= $i % 2 ?>">

							<td class="nowrap center hidden-phone">
								<?= JHtml::_('grid.id', $i, $item->id) ?>
							</td>

							<td class="nowrap center">
								<div class="btn-group">
									<?= JHtml::_('jgrid.published', $item->state, $i, $context . '.', $canChange, 'cb') ?>
									<?php
									// Create dropdown hotels
									$action = $archived ? 'unarchive' : 'archive';
									JHtml::_('actionsdropdown.' . $action, 'cb' . $i, $context);

									$action = $trashed ? 'untrash' : 'trash';
									JHtml::_('actionsdropdown.' . $action, 'cb' . $i, $context);

									// Render dropdown list
									echo JHtml::_('actionsdropdown.render', $this->escape($item->title));
									?>
								</div>
							</td>

							<td class="nowrap has-context">
								<div class="pull-left">
									<?php if ($item->checked_out) : ?>
										<?= JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $context . '.', $canCheckin) ?>
									<?php endif ?>
									<a href="<?= JRoute::_('index.php?option=com_chpanel&task=hotel.edit&id=' . $item->id) ?>">
										<?php if ($imageHelper->getImage($item->id, $context)) : ?>
											<?= '<img class="thumbnail pull-left chpanel-images-thumb" src="' . $image_path . $item->id . '-tiny.jpg?' . rand(1, 999) . '" />' ?>
										<?php endif ?>
										<?= $this->escape($item->title) ?>
									</a>
									<span class="small"><?= JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)) ?></span>
								</div>
							</td>

							<td class="nowrap center small">
								<a class="btn btn-mini chpanel-hotel-gallery" href="<?= $link_rooms ?>">
									<i class="icon-enter"></i> <?= JText::_('COM_CHPANEL_HOTELS_ROOMS') ?>
								</a>
							</td>

							<td class="nowrap center small">
								<a class="btn btn-mini chpanel-hotel-gallery gallery-<?= $images ?>" href="<?= $link_images ?>">
									<i class="icon-image"></i> <?= JText::_('COM_CHPANEL_HOTELS_IMAGES_VIEW') ?>
								</a>
							</td>

							<td class="nowrap center small">
								<?php if ($item->language == '*'): ?>
									<?= JText::alt('JALL', 'language') ?>
								<?php else: ?>
									<?= $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED') ?>
								<?php endif ?>
							</td>

							<!-- languages -->
							<?= CHPanelHelperLangs::listItem($this->lists->langs, $this->lists->translations, $item) ?>

							<td class="nowrap center small">
								<?= JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')) ?>
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
        <!-- /hotels table -->

    </div>
    <!-- /main -->

    <!-- form fields -->
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
	<?= JHtml::_('form.token') ?>
    <!-- /form fields -->

</form>
<!-- /adminForm -->