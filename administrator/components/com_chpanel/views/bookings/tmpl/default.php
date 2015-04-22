<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

JHtml::_('formbehavior.chosen', 'select');

$context = 'bookings';
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$archived = $this->state->get('filter.state') == 2 ? true : false;
$trashed = $this->state->get('filter.state') == -2 ? true : false;

$status = CHPanelHelper::getBookingStatus();

// js options
$lang = substr(JFactory::getLanguage()->getTag(), 0, 2);
$checkfields = JText::_('COM_CHPANEL_ANY_ERROR_CHECKFIELDS');
$weekstart = JText::_('COM_CHPANEL_LOCALE_WEEKSTART');
$dateformat = str_replace('Y', 'yyyy', str_replace('m', 'mm', (str_replace('d', 'dd', CHPanelHelper::getDateFormat(JText::_('COM_CHPANEL_LOCALE'))))));
$options = array();
$options[] = "checkfields: '$checkfields'";
$options[] = "datepicker_format: '$dateformat'";
$options[] = "datepicker_lang: '$lang'";
$options[] = "datepicker_weekstart: '$weekstart'";
JFactory::getDocument()->addScriptDeclaration("window.chpanel_options = {" . implode(",\n", $options) . "};");

// load datepicker
JHtml::stylesheet(JUri::base() . 'components/com_chpanel/assets/datepicker/css/datepicker.css');
JHtml::script(JUri::base() . 'components/com_chpanel/assets/datepicker/js/bootstrap-datepicker.js');
if ($lang != 'en')
{
	JHtml::script(JUri::base() . "components/com_chpanel/assets/datepicker/js/locales/bootstrap-datepicker.$lang.js");
}
?>

<!-- adminForm -->
<form action="<?= JRoute::_('index.php?option=com_chpanel&view=' . $context) ?>" method="post" name="adminForm" id="adminForm">

    <!-- sidebar -->
    <div id="j-sidebar-container" class="span2">

		<?= $this->sidebar ?>

        <hr>

        <h4><?= JText::_('COM_CHPANEL_BOOKINGS_FILTER_DATES') ?></h4>

        <div class="chpanel-bookings-datefilters">

            <div class="chpanel-bookings-datefilter">
                <label for="chpanel-bookings-date"><?= JText::_('COM_CHPANEL_BOOKINGS_FILTER_DATE') ?></label>
                <select name="date"  id="chpanel-bookings-date" class="input input-medium">
					<?= JHtml::_('select.options', CHPanelHelper::getBookingFilterDates(), null, null, $this->date, true) ?>
                </select>
            </div>

            <label for="chpanel-bookings-start"><?= JText::_('COM_CHPANEL_BOOKINGS_FILTER_START') ?></label>
            <div class="input-prepend">
                <span class="add-on"><i class="icon-calendar"></i></span>
                <input class="input-small chpanel-bookings-datepicker" name="start" id="chpanel-bookings-start" type="text" value="<?= $this->start ?>" autocomplete="off" readonly="readonly">
            </div>
            <br>
            <label for="chpanel-bookings-end"><?= JText::_('COM_CHPANEL_BOOKINGS_FILTER_END') ?></label>
            <div class="input-prepend">
                <span class="add-on"><i class="icon-calendar"></i></span>
                <input class="input-small chpanel-bookings-datepicker" name="end" id="chpanel-bookings-end" type="text" value="<?= $this->end ?>" autocomplete="off" readonly="readonly">
            </div>

            <br>
            <button id="chpanel-bookings-clear" class="btn"><?= JText::_('COM_CHPANEL_BOOKINGS_FILTER_CLEAR') ?></button>
            <button id="chpanel-bookings-apply" class="btn btn-success"><i class="icon-filter"></i> <?= JText::_('COM_CHPANEL_BOOKINGS_FILTER_APPLY') ?></button>

        </div>

    </div>
    <!-- /sidebar -->

    <!-- main -->
    <div id="j-main-container" class="span10">

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
						<th width="1%" class="nowrap hidden-phone">
							<?= JHtml::_('grid.checkall') ?>
						</th> 
						<th class="nowrap center" width="8%">
							<?= JHtml::_('searchtools.sort', 'COM_CHPANEL_ANY_DATE', 'a.created', $listDirn, $listOrder) ?>
						</th>
						<th class="nowrap center" width="8%">
							<?= JHtml::_('searchtools.sort', 'COM_CHPANEL_BOOKING_VOUCHER', 'a.voucher', $listDirn, $listOrder) ?>
						</th>                        
						<th class="nowrap">
							<?= JHtml::_('searchtools.sort', 'COM_CHPANEL_BOOKINGS_GUEST', 'guest', $listDirn, $listOrder) ?>
						</th>
						<th class="nowrap">
							<?= JHtml::_('searchtools.sort', 'COM_CHPANEL_ANY_HOTEL', 'hotel', $listDirn, $listOrder) ?>
						</th>                          
						<th class="nowrap">
							<?= JHtml::_('searchtools.sort', 'COM_CHPANEL_ANY_ROOM', 'room', $listDirn, $listOrder) ?>
						</th>    
						<th class="nowrap center" width="8%">
							<?= JHtml::_('searchtools.sort', 'COM_CHPANEL_BOOKING_CHECKIN', 'a.checkin', $listDirn, $listOrder) ?>
						</th>     
						<th class="nowrap center" width="8%">
							<?= JHtml::_('searchtools.sort', 'COM_CHPANEL_BOOKING_CHECKOUT', 'a.checkout', $listDirn, $listOrder) ?>
						</th>  
						<th class="nowrap center">
							<?= JHtml::_('searchtools.sort', 'COM_CHPANEL_BOOKING_AMOUNT', 'a.amount', $listDirn, $listOrder) ?>
						</th>                         
						<th class="nowrap" width="10%">
							<?= JHtml::_('searchtools.sort', 'COM_CHPANEL_BOOKING_STATUS', 'a.status', $listDirn, $listOrder) ?>
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
						?>

						<tr class="row<?= $i % 2 ?>" sortable-group-id="<?= $item->item_id ?>">

							<td class="nowrap center hidden-phone">
								<?= JHtml::_('grid.id', $i, $item->id) ?>
							</td>

							<td class="nowrap center">
								<?= JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4') . ' H:i:s') ?>
							</td>

							<td class="nowrap center">
								<?php if ($item->checked_out) : ?>
									<?= JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $context . '.', $canCheckin) ?>
								<?php endif ?>
								<a href="<?= JRoute::_('index.php?option=com_chpanel&task=booking.edit&id=' . $item->id) ?>">
									<?= $this->escape($item->voucher) ?>
								</a>
							</td>

							<td class="nowrap">
								<a href="<?= JRoute::_('index.php?option=com_chpanel&task=booking.edit&id=' . $item->id) ?>">
									<?= $item->last_name . ', ' . $item->first_name ?>
								</a>
							</td>

							<td class="nowrap">
								<?= $item->hotel ?>
							</td>                            
							<td class="nowrap">
								<?= $item->reference ?>
							</td>
							<td class="nowrap center">
								<?= JHtml::_('date', $item->checkin, JText::_('DATE_FORMAT_LC4')) ?>
							</td>
							<td class="nowrap center">
								<?= JHtml::_('date', $item->checkout, JText::_('DATE_FORMAT_LC4')) ?>
							</td>
							<td class="nowrap center">
								<?= $item->amount ?>
							</td>                            
							<td class="nowrap">
								<?= str_replace(' ', '&nbsp;', $status[$item->status]) ?>
							</td>
							<td class="nowrap center">
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
    <input type="hidden" name="boxchecked" value="0" />
	<?= JHtml::_('form.token') ?>
    <!-- /form fields -->

</form>
<!-- /adminForm -->