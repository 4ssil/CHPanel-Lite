<?php
/**
 * @package     CHPanel
 * @copyright	Copyright (C) CloudHotelier. All rights reserved.
 * @license		GNU GPLv2 <http://www.gnu.org/licenses/gpl.html>
 * @author		Xavier Pallicer <xpallicer@cloudhotelier.com>
 */
defined('_JEXEC') or die();

$days = array(
	0 => 'SUN',
	1 => 'MON',
	2 => 'TUE',
	3 => 'WED',
	4 => 'THU',
	5 => 'FRI',
	6 => 'SAT',
);

$row_1 = new stdClass();
$row_1->day = $this->month_start;
$row_1->start = 1;
$row_1->max = 15;
$row_2 = new stdClass();
$row_2->start = 16;
$row_2->day = $this->month_16;
$row_2->max = $this->month_days;
$rows = array($row_1, $row_2);

$cols = '';
$cols_th = '';
$empty = 31 - $this->month_days;
while ($empty)
{
	$cols .= '<td width="5%">&nbsp;</td>';
	$cols_th .= '<th width="5%">&nbsp;</th>';
	$empty--;
}

$month = JFactory::getDate('01-' . $this->state->get('filter.month'))->format('Y-m');
?>

<div class="chpanel-manage-calendar">

    <h4><?= $this->title ?></h4>

	<?php foreach ($rows as $row): ?>

		<table class="table table-bordered chpanel-manage-table">
			<thead>
				<tr>
					<th></th>
					<?php
					$day = $row->day;
					for ($i = $row->start; $i <= $row->max; $i++)
					{
						$d = $day;
						if ($day == 7)
						{
							$day = 0;
							$d = $day;
						}
						$day++;
						echo '<th width="5%" class="chpanel-manage-weekday-' . $day . '">' . JText::_($days[$d]) . '&nbsp;' . $i . '</th>';
					}
					echo $i == 16 ? '<th width="5%">&nbsp;</th>' : $cols_th;
					?>
				</tr>
			</thead>

			<tbody>

				<?php foreach ($this->hotel->rooms as $room): ?>

					<?php
					if (isset($this->inventory[$room->id]))
					{
						$room_inventory = $this->inventory[$room->id];
					}
					?>

					<tr class="chpanel-manage-row">
						<td class="chpanel-manage-room">
							<?= $room->title ?>
							<br><small class="muted"><?= $room->reference ?></small>
						</td>
						<?php
						$day = $row->day;
						for ($i = $row->start; $i <= $row->max; $i++)
						{

							// availability and rate
							$d = $day;
							if ($day == 7)
							{
								$day = 0;
								$d = $day;
							}
							$day++;
							$availability = 0;
							$rate = $room->rate;
							$day_number = str_pad($i, 2, "0", STR_PAD_LEFT);
							if (isset($room_inventory[$month . '-' . $day_number]))
							{
								$inv = $room_inventory[$month . '-' . $day_number];
								$rate = $inv->rate;
								$availability = $inv->availability;
							}
							$availability_class = $availability ? ($availability > 2 ? 'ok' : 'low') : 'no';

							// bookings in progress alert
							$processing = 0;
							$processing_tip = '';
							$fulldate = (int) str_replace('-', '', $month . $day_number);
							if ($this->bookings_in_progress)
							{
								foreach ($this->bookings_in_progress as $booking)
								{
									if ($booking->room_id == $room->id)
									{
										$checkin = (int) str_replace('-', '', $booking->checkin);
										$checkout = (int) str_replace('-', '', $booking->checkout);
										if ($checkin <= $fulldate && $fulldate < $checkout)
										{
											$processing = 1;
											$processing_tip = ' hasTooltip" title="' . JText::_('COM_CHPANEL_MANAGE_PROCESSING');
										}
									}
								}
							}

							echo '<td width="5%" class="chpanel-manage-date chpanel-manage-weekday-' . $day . '">';
							echo '<span class="chpanel-manage-available-' . $availability_class . '">&nbsp;</span>';
							echo '<span class="chpanel-manage-available"><span class="chpanel-manage-processing-' . $processing . ' ' . $availability_class . $processing_tip . '">' . $availability . '</span></span>';
							echo '<span class="chpanel-manage-rate">' . $rate . '</span>';
							echo '</td>';
						}
						echo $i == 16 ? '<td width="5%">&nbsp;</td>' : $cols;
						?>

					</tr>
				<?php endforeach ?>
			</tbody>
		</table>

	<?php endforeach ?>

</div>