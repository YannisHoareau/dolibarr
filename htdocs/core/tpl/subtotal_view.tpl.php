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

echo "<!-- BEGIN PHP TEMPLATE objectline_edit.tpl.php -->\n";
switch (abs($line->qty)) {
	case 1:
		$color = '#6495ED';
		break;
	case 2:
		$color = '#87CEFA';
		break;
	case 3:
		$color = '#87CEEB';
		break;
	case 4:
		$color = '#B0E0E6';
		break;
	case 5:
		$color = '#ADD8E6';
		break;
}

// Base colspan if there is no module activated to display line correctly
$colspan = 5;

// Handling colspan if margin module is enabled
if (!empty($object->element) && in_array($object->element, array('facture', 'facturerec', 'propal', 'commande')) && isModEnabled('margin') && empty($user->socid)) {
	if ($user->hasRight('margins', 'creer')) {
		$colspan +=1;
	}
	if (getDolGlobalString('DISPLAY_MARGIN_RATES') && $user->hasRight('margins', 'liretous')) {
		$colspan +=1;
	}
	if (getDolGlobalString('DISPLAY_MARK_RATES') && $user->hasRight('margins', 'liretous')) {
		$colspan +=1;
	}
}

// Handling colspan if multicurrency module is enabled
if (isModEnabled('multicurrency') && $object->multicurrency_code != $conf->currency) {
	$colspan +=1;
}

// Handling colspan if MAIN_NO_INPUT_PRICE_WITH_TAX conf is enabled
if (!getDolGlobalInt('MAIN_NO_INPUT_PRICE_WITH_TAX')) {
	$colspan +=1;
}

// Handling colspan if PRODUCT_USE_UNITS conf is enabled
if (getDolGlobalString('PRODUCT_USE_UNITS')) {
	$colspan +=1;
}
echo '<tr id="row-<?php echo $line->id?>" class="drag drop" style="background:'.$color.'">';

// Showing line number if conf is enabled
if (getDolGlobalString('MAIN_VIEW_LINE_NUMBER')) {
	echo '<td class="linecolnum center"><span class="opacitymedium">'.($i + 1).'</span></td>';
}

if ($line->qty > 0) { ?>
		<?php $colspan = isModEnabled('multicurrency') && $this->multicurrency_code != $conf->currency ? $colspan+2 : $colspan+1 ?>
		<td class="linecollabel" colspan="<?= $colspan ?>"><?=str_repeat('&nbsp;', ($line->qty-1)*4);?><?= $line->desc ?></td>
<?php } elseif ($line->qty < 0) { ?>
		<td class="linecollabel nowrap right" colspan="<?= $colspan ?>"><?=str_repeat('&nbsp;', (-$line->qty-1)*2);?><?= $line->desc ?></td>
		<td class="linecolamount nowrap right">
			<?php
			echo price($this->getSubtotalLineAmount($line));
			?>
		</td>
		<?php
		if (isModEnabled('multicurrency') && $object->multicurrency_code != $conf->currency) {
			echo '<td class="linecolamount nowrap right">';
			echo price($this->getSubtotalLineMulticurrencyAmount($line));
			echo '</td>';
		}
		?>
<?php }

// Edit picto
echo '<td class="linecoledit center">';
echo '<a class="editfielda reposition" href="'.$_SERVER["PHP_SELF"].'?id='.$this->id.'&action=editline&token='.newToken().'&lineid='.$line->id.'">'.img_edit().'</a>';
echo '</td>';

// Delete picto
echo '<td class="linecoldelete center">';
echo '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?id='.$this->id.'&action=ask_deleteline&token='.newToken().'&lineid='.$line->id.'">'.img_delete().'</a>';
echo '</td>';

// Move up-down picto
if ($num > 1 && $conf->browser->layout != 'phone' && ((property_exists($this, 'situation_counter') && $this->situation_counter == 1) || empty($this->situation_cycle_ref)) && empty($disablemove)) {
	echo '<td class="linecolmove tdlineupdown center">';
	if ($i > 0) {
		echo '<a class="lineupdown" href="'.$_SERVER["PHP_SELF"].'?id='.$this->id.'&action=up&token='.newToken().'&rowid='.$line->id.'">';
		echo img_up('default', 0, 'imgupforline');
		echo '</a>';
	}
	if ($i < $num - 1) {
		echo '<a class="lineupdown" href="'.$_SERVER["PHP_SELF"].'?id='.$this->id.'&action=down&token='.newToken().'&rowid='.$line->id.'">';
		echo img_down('default', 0, 'imgdownforline');
		echo '</a>';
	}
	echo '</td>';
} else {
	echo '<td '.(($conf->browser->layout != 'phone' && empty($disablemove)) ? ' class="linecolmove tdlineupdown center"' : ' class="linecolmove center"').'></td>';
}
echo '</tr>';
echo '<!-- END PHP TEMPLATE subtotal_view.tpl.php -->';
?>
