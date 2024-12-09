<?php
/* Copyright (C) 2010-2013	Regis Houssin		<regis.houssin@inodbox.com>
 * Copyright (C) 2010-2011	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2012-2013	Christophe Battarel	<christophe.battarel@altairis.fr>
 * Copyright (C) 2012       Cédric Salvador     <csalvador@gpcsolutions.fr>
 * Copyright (C) 2012-2014  Raphaël Doursenaud  <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2013		Florian Henry		<florian.henry@open-concept.pro>
 * Copyright (C) 2017		Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2022		OpenDSI				<support@open-dsi.fr>
 * Copyright (C) 2024		MDW					<mdeweerd@users.noreply.github.com>
 * Copyright (C) 2024       Alexandre Spangaro  <alexandre@inovea-conseil.com>
 * Copyright (C) 2024       Frédéric France		  <frederic.france@free.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * Need to have the following variables defined:
 * $object (invoice, order, ...)
 * $conf
 * $langs
 * $dateSelector
 * $forceall (0 by default, 1 for supplier invoices/orders)
 * $element     (used to test $user->rights->$element->creer)
 * $permtoedit  (used to replace test $user->rights->$element->creer)
 * $senderissupplier (0 by default, 1 for supplier invoices/orders)
 * $inputalsopricewithtax (0 by default, 1 to also show column with unit price including tax)
 * $outputalsopricetotalwithtax
 * $usemargins (0 to disable all margins columns, 1 to show according to margin setup)
 * $disableedit, $disablemove, $disableremove
 *
 * $text, $description, $line
 */
/**
 * @var CommonObject $object
 * @var CommonObject $this
 * @var CommonObjectLine $line
 * @var Conf $conf
 * @var Form $form
 * @var HookManager $hookmanager
 * @var ?Product $product_static
 * @var Societe $mysoc
 * @var Translate $langs
 * @var User $user
 *
 * @var 0|1 $forceall
 * @var int $num
 * @var 0|1 $senderissupplier
 * @var string $text
 * @var string $description
 */

print "<!-- BEGIN PHP TEMPLATE objectline_edit.tpl.php -->\n";

?>
<?php if ($line->qty > 0) { ?>
	<tr id="row-<?php print $line->id?>" class="drag drop oddeven">
		<td class="linecollabel" colspan="7"><?=str_repeat('&nbsp;', ($line->qty-1)*2);?><?= $line->desc ?></td>
<?php } elseif ($line->qty < 0) { ?>
	<tr id="row-<?php print $line->id?>" class="drag drop oddeven">
		<td class="linecollabel nowrap right" colspan="6"><?=str_repeat('&nbsp;', (-$line->qty-1)*2);?><?= $line->desc ?></td>
		<td class="linecolamount nowrap right">
			<?php
			print price($this->getSubtotalLineAmount($line));
			?>
		</td>
<?php }

// Edit picto
print '<td class="linecoledit center">';
print '<a class="editfielda reposition" href="'.$_SERVER["PHP_SELF"].'?id='.$this->id.'&action=editline&token='.newToken().'&lineid='.$line->id.'">'.img_edit().'</a>';
print '</td>';

// Delete picto
print '<td class="linecoldelete center">';
print '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?id='.$this->id.'&action=ask_deleteline&token='.newToken().'&lineid='.$line->id.'">'.img_delete().'</a>';
print '</td>';

// Move up-down picto
if ($num > 1 && $conf->browser->layout != 'phone' && ((property_exists($this, 'situation_counter') && $this->situation_counter == 1) || empty($this->situation_cycle_ref)) && empty($disablemove)) {
	print '<td class="linecolmove tdlineupdown center">';
	if ($i > 0) {
		print '<a class="lineupdown" href="'.$_SERVER["PHP_SELF"].'?id='.$this->id.'&action=up&token='.newToken().'&rowid='.$line->id.'">';
		print img_up('default', 0, 'imgupforline');
		print '</a>';
	}
	if ($i < $num - 1) {
		print '<a class="lineupdown" href="'.$_SERVER["PHP_SELF"].'?id='.$this->id.'&action=down&token='.newToken().'&rowid='.$line->id.'">';
		print img_down('default', 0, 'imgdownforline');
		print '</a>';
	}
	print '</td>';
} else {
	print '<td '.(($conf->browser->layout != 'phone' && empty($disablemove)) ? ' class="linecolmove tdlineupdown center"' : ' class="linecolmove center"').'></td>';
}
?>
	</tr>
<!-- END PHP TEMPLATE subtotal_view.tpl.php -->
