<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

$langs = CHPanelHelperLangs::getLangs();
$imageHelper = new CHPanelHelperImage(JComponentHelper::getParams('com_chpanel'));
$path_items = JURI::root() . "/images/chpanel/items/";
$path_images = JURI::root() . "/images/chpanel/images/";
?>

<?php if ($this->banner): ?>
	<div class="alert alert-block alert-info">
		<button type="button" class="close" data-dismiss="alert" id="chbanner-close">&times;</button>
		<h4><?= $this->banner->title ?></h4>
		<?= $this->banner->text ?> <a href="<?= $this->banner->link ?>" target="_blank"><i class="icon-out"></i> <?= JText::_('COM_CHPANEL_PANEL_BANNER_MORE') ?></a>
	</div>
<?php endif ?>

<!-- adminForm -->
<form action="<?= JRoute::_('index.php?option=com_chpanel&view=items') ?>" method="post" name="adminForm" id="adminForm">

    <!-- sidebar -->
    <div id="j-sidebar-container" class="span2">

		<?= $this->sidebar ?>

    </div>
    <!-- /sidebar -->

    <!-- main -->
    <div id="j-main-container" class="span10">

        <!-- latest edited & recently added items -->
        <div class="row-fluid">


            <div class="span6">

                <div class="well well-small chpanel-panel">

                    <h2 class="module-title nav-header"><?= JText::_('COM_CHPANEL_PANEL_BOOKINGS') ?></h2>

					<?php if ($this->bookings): ?>

						<?php foreach ($this->bookings as $booking): ?>

							<?php $link = JRoute::_('index.php?option=com_chpanel&task=booking.edit&id=' . $booking->id) ?>

							<div class="row-striped">
								<div class="row-fluid">
									<div class="span6">
										<a href="<?= $link ?>"><?= $booking->voucher ?></a>
										<div class="small"><?= JText::_('COM_CHPANEL_BOOKINGS_GUEST') ?>: <?= $booking->last_name . ', ' . $booking->first_name ?></div>
									</div>
									<div class="span3">
										<span class="small"><?= $booking->hotel ?></span>
										<br><span class="small"><?= JText::_('COM_CHPANEL_BOOKING_AMOUNT') ?>: <?= $booking->amount ?></span>
									</div>
									<div class="span3">
										<span class="small"><?= JText::_('COM_CHPANEL_ANY_DATE') ?>:</span><br>
										<span class="small"><i class="icon-calendar"></i> <?= JHtml::_('date', $booking->created, JText::_('DATE_FORMAT_LC4') . ' H:i') ?></span>
									</div>
								</div>
							</div>

						<?php endforeach ?>

					<?php else: ?>

						<p class="alert alert-warning"><?= JText::_('COM_CHPANEL_PANEL_BOOKINGS_NO') ?></p>

					<?php endif ?>

                    <div class="clearfix">
						<?php $link = JRoute::_('index.php?option=com_chpanel&view=bookings&filter[hotel]=&filter[status]=&filter[state]=&list[fullordering]=a.created%20DESC&date=created&start=&end=') ?>
                        <a class="btn btn-small pull-right" href="<?= $link ?>"><?= JText::_('COM_CHPANEL_PANEL_VIEW_MORE') ?></a>
                    </div>      

                </div>

            </div>                 

            <div class="span6">

                <div class="well well-small chpanel-panel">

                    <h2 class="module-title nav-header"><?= JText::_('COM_CHPANEL_PANEL_CHECKINS') ?></h2>

					<?php if ($this->checkins): ?>

						<?php foreach ($this->checkins as $booking): ?>

							<?php $link = JRoute::_('index.php?option=com_chpanel&task=booking.edit&id=' . $booking->id) ?>

							<div class="row-striped">
								<div class="row-fluid">
									<div class="span6">
										<a href="<?= $link ?>"><?= $booking->voucher ?></a>
										<div class="small"><?= JText::_('COM_CHPANEL_BOOKINGS_GUEST') ?>: <?= $booking->last_name . ', ' . $booking->first_name ?></div>
									</div>
									<div class="span3">
										<span class="small"><?= $booking->hotel ?></span>
										<br><span class="small"><?= JText::_('COM_CHPANEL_BOOKING_AMOUNT') ?>: <?= $booking->amount ?></span>
									</div>
									<div class="span3">
										<span class="small"><?= JText::_('COM_CHPANEL_BOOKING_CHECKIN') ?>: <?= JHtml::_('date', $booking->checkin, JText::_('DATE_FORMAT_LC4')) ?></span>
										<br><span class="small"><?= JText::_('COM_CHPANEL_BOOKING_CHECKOUT') ?>: <?= JHtml::_('date', $booking->checkout, JText::_('DATE_FORMAT_LC4')) ?></span>
									</div>
								</div>
							</div>

						<?php endforeach ?>

					<?php else: ?>

						<p class="alert alert-warning"><?= JText::_('COM_CHPANEL_PANEL_CHECKINS_NO') ?></p>

					<?php endif ?>

                    <div class="clearfix">
						<?php
						$today = JFactory::getDate()->format(CHPanelHelper::getDateFormat(JText::_('COM_CHPANEL_LOCALE')));
						$link = JRoute::_('index.php?option=com_chpanel&view=bookings&filter[hotel]=&filter[status]=&filter[state]=&list[fullordering]=a.checkin%20ASC&date=checkin&end=&start=' . $today);
						?>
                        <a class="btn btn-small pull-right" href="<?= $link ?>"><?= JText::_('COM_CHPANEL_PANEL_VIEW_MORE') ?></a>
                    </div>

                </div>

            </div> 

        </div>     

        <div class="row-fluid">
            <div class="span4">
                <fieldset class="form-horizontal">
                    <legend><?= JText::_('COM_CHPANEL_PANEL_NEWS') ?></legend>
                    <div class="control-group ch-news">
						<?php
						if (count($this->info->news))
						{
							foreach ($this->info->news as $item)
							{
								echo '<h4><a target="_blank" href="' . $item->link . '">' . $item->title . '</a></h4>';
								echo '<p>' . $item->text;
								if ($item->link)
								{
									echo '<br><a class="btn btn-small" target="_blank" href="' . $item->link . '">' . JText::_('COM_CHPANEL_PANEL_NEWS_MORE') . '</a>';
								}
								echo '</p>';
							}
						}
						?>
                    </div>
                </fieldset>
            </div>
            <div class="span4">
                <fieldset class="form-horizontal">
                    <legend><?= JText::_('COM_CHPANEL_PANEL_VERSION') ?></legend>
                    <div class="control-group">
                        <p><?= JText::_('COM_CHPANEL_PANEL_VERSION_DESC') ?></p>
                        <p class="alert alert-info"><?= JText::_('COM_CHPANEL_PANEL_VERSION_LATEST') ?>: <?= $this->info->com_chpanel ?></p>
                        <p class="alert <?= $this->info->com_chpanel_ok ? 'alert-error' : 'alert-info' ?>"><?= JText::_('COM_CHPANEL_PANEL_VERSION_CURRENT') ?>: <?= $this->info->com_chpanel_installed ?></p>
						<?php if ($this->info->com_chpanel_ok): ?>
							<p class="alert alert-warning"><?= JText::_('COM_CHPANEL_PANEL_VERSION_UPDATE_LITE') ?></p>
						<?php else: ?>
							<p class="alert alert-success"><?= JText::_('COM_CHPANEL_PANEL_VERSION_OK') ?></p>
						<?php endif ?>
                    </div>
                </fieldset>
            </div>
            <div class="span4">
                <fieldset class="form-horizontal">
                    <legend><?= JText::_('COM_CHPANEL_PANEL_VERSION_COM_HOTEL') ?></legend>
                    <div class="control-group">
                        <p><?= JText::_('COM_CHPANEL_PANEL_VERSION_DESC') ?></p>
                        <p class="alert alert-info"><?= JText::_('COM_CHPANEL_PANEL_VERSION_LATEST') ?>: <?= $this->info->com_hotel ?></p>
                        <p class="alert <?= $this->info->com_hotel_ok ? 'alert-error' : 'alert-info' ?>"><?= JText::_('COM_CHPANEL_PANEL_VERSION_CURRENT') ?>: <?= $this->info->com_hotel_installed ?></p>
						<?php if ($this->info->com_hotel_ok): ?>
							<p class="alert alert-warning"><?= JText::_('COM_CHPANEL_PANEL_VERSION_UPDATE') ?></p>
						<?php else: ?>
							<p class="alert alert-success"><?= JText::_('COM_CHPANEL_PANEL_VERSION_OK') ?></p>
						<?php endif ?>
                    </div>
                </fieldset>
            </div>

        </div>

    </div>
    <!-- /main -->

    <!-- form fields -->
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
	<?= JHtml::_('form.token') ?>
    <!-- /form fields -->

</form>
<!-- /adminForm -->

<div id="chpanel-tips"> </div>