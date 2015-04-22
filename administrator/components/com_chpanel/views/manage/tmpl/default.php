<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

JHtml::_('formbehavior.chosen', 'select');

// options
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

// tooltip
JHtml::_('bootstrap.tooltip');
?>

<!-- adminForm -->
<form action="<?= JRoute::_('index.php?option=com_chpanel&view=manage') ?>" method="post" name="adminForm" id="chpanel-manage-form">

    <!-- sidebar -->
    <div id="j-sidebar-container" class="span2">

		<?= $this->sidebar ?>

    </div>
    <!-- /sidebar -->

    <!-- main -->
    <div id="j-main-container" class="span10">

        <!--
        <div class="pull-right chpanel-manage-legend">
            <h4><?= JText::_('COM_CHPANEL_MANAGE_LEGEND') ?></h4>
            <span class="alert alert-success"><span class="label label-success">&nbsp;</span> <?= JText::_('COM_CHPANEL_MANAGE_LEGEND_YES') ?></span>
            <span class="alert alert-error"><span class="label label-important">&nbsp;</span> <?= JText::_('COM_CHPANEL_MANAGE_LEGEND_NO') ?></span>
            <span class="alert alert-warning"><span class="label label-warning">&nbsp;</span> <?= JText::_('COM_CHPANEL_MANAGE_LEGEND_LOW') ?></span>
        </div>
        -->

        <div class="chpanel-manage-filter">

            <h4><?= JText::_('COM_CHPANEL_MANAGE_FILTER') ?></h4>

            <select name="filter_hotel" class="input input-medium" onchange="this.form.submit()">
                <option value=""><?= JText::_('COM_CHPANEL_ANY_SELECT_HOTEL') ?></option>
				<?= JHtml::_('select.options', $this->hotels, 'id', 'title', $this->state->get('filter.hotel'), true) ?>
            </select>

            <select name="filter_month" class="input input-medium" onchange="this.form.submit()">
				<?= JHtml::_('select.options', $this->months, 'id', 'title', $this->state->get('filter.month'), true) ?>
            </select>

            <hr>
        </div>


		<?php if (!$this->state->get('filter.hotel')): ?>
			<p class="alert alert-info"><?= JText::_('COM_CHPANEL_ANY_SELECT_HOTEL') ?></p>
		<?php endif ?>

		<?php if ($this->state->get('filter.hotel') && !$this->hotel): ?>
			<p class="alert alert-error"><?= JText::_('COM_CHPANEL_MANAGE_ERROR_HOTEL') ?></p>
		<?php endif ?>

		<?php if ($this->hotel) echo $this->loadTemplate('form') ?>

		<?php if ($this->hotel) echo $this->loadTemplate('calendar') ?>

    </div>
    <!-- /main -->

    <!-- form fields -->
    <input type="hidden" id="chpanel-manage-task" name="task" value="" />
	<?= JHtml::_('form.token') ?>
    <!-- /form fields -->

</form>
<!-- /adminForm -->

<div id="chpanel-tips"> </div>