<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();
?>

<h4><?= JText::_('COM_CHPANEL_MANAGE_UPDATE') ?></h4>

<div class="row-fluid chpanel-manage-form">

    <div class="span3"> 

        <table class="table table-condensed chpanel-manage-update-table">

            <thead>
                <tr>
                    <th class="chpanel-manage-update-field" colspan="2">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="chpanel-manage-room">
						<?= JText::_('COM_CHPANEL_MANAGE_START') ?>
                    </td>
                    <td>
                        <div class="input-prepend">
                            <span class="add-on"><i class="icon-calendar"></i></span>
                            <input class="input-small chpanel-manage-datepicker" name="start" id="chpanel-manage-start" type="text" value="<?= $this->start ?>" autocomplete="off" readonly="readonly">
                        </div>
                    </td> 
                </tr>
                <tr>
                    <td class="chpanel-manage-room">
						<?= JText::_('COM_CHPANEL_MANAGE_END') ?>
                    </td>
                    <td>
                        <div class="input-prepend">
                            <span class="add-on"><i class="icon-calendar"></i></span>
                            <input class="input-small chpanel-manage-datepicker" name="end" id="chpanel-manage-end" type="text" value="<?= $this->end ?>" autocomplete="off" readonly="readonly">
                        </div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button id="chpanel-manage-apply" class="btn btn-success"><i class="icon-cogs"></i> <?= JText::_('COM_CHPANEL_MANAGE_APPLY') ?></button>
                    </td>
                </tr>
            </tbody>

        </table>

    </div>

    <div class="span4"> 

        <table class="table table-condensed chpanel-manage-update-table">

            <thead>
                <tr>
                    <th></th>
                    <th class="chpanel-manage-update-field"><?= JText::_('COM_CHPANEL_MANAGE_AVAILABILITY') ?></th>
                    <th class="chpanel-manage-update-field"><?= JText::_('COM_CHPANEL_MANAGE_RATE') ?></th>
                </tr>
            </thead>

            <tbody>
				<?php foreach ($this->hotel->rooms as $i => $room): ?>
					<tr>
						<td class="chpanel-manage-room">
							<?= $room->title ?>
						</td>
						<td class="chpanel-manage-update-field">
							<input class="input-mini" type="text" name="availability_<?= $room->id ?>" value="" autocomplete="off" tabindex="<?= 1000 + $i ?>" />
						</td>
						<td class="chpanel-manage-update-field">
							<input class="input-mini" type="text" name="rate_<?= $room->id ?>" value="" autocomplete="off" tabindex="<?= 2000 + $i ?>" />
						</td>
					</tr>
				<?php endforeach ?>
            </tbody>

        </table>

    </div>

</div>




