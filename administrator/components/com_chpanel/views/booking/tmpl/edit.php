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
?>

<!-- adminForm -->
<form action="<?= JRoute::_('index.php?option=com_chpanel&view=booking&layout=edit&id=' . (int) $this->item->id) ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

    <!-- form fields -->
    <input type="hidden" name="task" value="" />
	<?= JHtml::_('form.token') ?>

    <!-- main -->
    <div class="row-fluid">

        <div class="span8">

            <!-- room info --> 
            <div class="form-horizontal">
                <fieldset>
                    <legend><?= JText::_('COM_CHPANEL_BOOKING_FIELDSET_ROOM') ?></legend>
					<?php foreach (array('hotel', 'room_id', 'board', 'adult', 'child', 'baby') as $field): ?>
						<?= $this->form->getControlGroup($field) ?>
					<?php endforeach ?>
                </fieldset>
            </div>  

            <!-- guest info --> 
            <div class="form-horizontal">
                <fieldset>
                    <legend><?= JText::_('COM_CHPANEL_BOOKING_FIELDSET_GUEST') ?></legend>
					<?php foreach (array('first_name', 'last_name', 'email', 'phone', 'street', 'zip', 'city', 'country', 'comments') as $field): ?>
						<?= $this->form->getControlGroup($field) ?>
					<?php endforeach ?>
                </fieldset>
            </div>    

            <!-- tracking info --> 
            <div class="form-horizontal">
                <fieldset>
                    <legend><?= JText::_('COM_CHPANEL_BOOKING_FIELDSET_TARCKING') ?></legend>
					<?php foreach (array('find_us') as $field): ?>
						<?= $this->form->getControlGroup($field) ?>
					<?php endforeach ?>
                </fieldset>
            </div>  

        </div>

        <!-- sidebar -->
        <div class="span4">

            <!-- publishing -->
            <div class="well">
                <h3><?= JText::_('COM_CHPANEL_BOOKING_FIELDSET_DETAILS') ?></h3>
				<?php foreach (array('id', 'voucher', 'created', 'status', 'checkin', 'checkout', 'amount', 'payment', 'payment_status', 'hotel_id') as $field): ?>
					<?= $this->form->getControlGroup($field) ?>
				<?php endforeach ?>
            </div>

        </div>
        <!-- /sidebar -->

    </div>
    <!-- /main -->

</form>
<!-- /adminForm -->
