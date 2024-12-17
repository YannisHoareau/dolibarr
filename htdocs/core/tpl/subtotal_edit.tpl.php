<?php
/* Copyright (C) 2010-2012	Regis Houssin       <regis.houssin@inodbox.com>
 * Copyright (C) 2010-2022	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2012		Christophe Battarel	<christophe.battarel@altairis.fr>
 * Copyright (C) 2012       Cédric Salvador     <csalvador@gpcsolutions.fr>
 * Copyright (C) 2012-2014  Raphaël Doursenaud  <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2013		Florian Henry		<florian.henry@open-concept.pro>
 * Copyright (C) 2018-2024	Frédéric France     <frederic.france@free.fr>
 * Copyright (C) 2022		OpenDSI				<support@open-dsi.fr>
 * Copyright (C) 2024		MDW					<mdeweerd@users.noreply.github.com>
 * Copyright (C) 2024       Alexandre Spangaro  <alexandre@inovea-conseil.com>
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
 * $seller, $buyer
 * $dateSelector
 * $forceall (0 by default, 1 for supplier invoices/orders)
 * $senderissupplier (0 by default, 1 for supplier invoices/orders)
 * $inputalsopricewithtax (0 by default, 1 to also show column with unit price including tax)
 * $canchangeproduct (0 by default, 1 to allow to change the product if it is a predefined product)
 */

/**
 * @var CommonObject $this
 * @var CommonObject $object
 * @var CommonObjectLine $line
 * @var Form $form
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

// Options for subtotal //TODO : check if 'checked' is true or false
$line_options = array(
	'showuponpdf' => array('type' => array('title'), 'checked' => true, 'trans_key' => 'ShowUPOnPDF'),
	'showtotalexludingvatonpdf' => array('type' => array('title', 'subtotal'), 'checked' => true, 'trans_key' => 'ShowTotalExludingVATOnPDF'),
	'forcepagebreak' => array('type' => array('title'), 'checked' => true, 'trans_key' => 'ForcePageBreak'),
);

// Line type
$line_type = $line->qty > 0 ? 'title' : 'subtotal';

print "<!-- BEGIN PHP TEMPLATE subtotal_edit.tpl.php -->\n";

echo '<tr class="oddeven tredited">';

if (getDolGlobalString('MAIN_VIEW_LINE_NUMBER')) {
	echo '<td class="linecolnum center">'.($i + 1).'</td>';
}

// Base colspan if there is no module activated to display line correctly
$colspan = 7;

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

?>

	<td class="linecoldesc minwidth250onall" colspan="<?= $colspan; ?>">
	<div id="line_<?php echo $line->id; ?>"></div>

	<input type="hidden" name="lineid" value="<?php echo $line->id; ?>">
	<input type="hidden" id="product_type" name="type" value="<?php echo $line->product_type; ?>">
	<input type="hidden" id="special_code" name="special_code" value="<?php echo $line->special_code; ?>">
	<input type="hidden" id="fk_parent_line" name="fk_parent_line" value="<?php echo $line->fk_parent_line; ?>">

	<?php

	$line_edit_mode = $line->qty < 0 ? 'subtotal' : 'title';

	$level = abs($line->qty);

	print '<input type="hidden" name="line_edit_mode" value="'.$line_edit_mode.'">';

	$situationinvoicelinewithparent = 0;
	if ($line->fk_prev_id != null && in_array($object->element, array('facture', 'facturedet'))) {
		/** @var CommonInvoice $object */
		// @phan-suppress-next-line PhanUndeclaredConstantOfClass
		if ($object->type == $object::TYPE_SITUATION) {	// The constant TYPE_SITUATION exists only for object invoice
			// Set constant to disallow editing during a situation cycle
			$situationinvoicelinewithparent = 1;
		}
	}

	// Do not allow editing during a situation cycle
	// but in some situations that is required (update legal information for example)
	if (getDolGlobalString('INVOICE_SITUATION_CAN_FORCE_UPDATE_DESCRIPTION')) {
		$situationinvoicelinewithparent = 0;
	}

	$langs->load('subtotals');
	$depth_array = array();
	for ($i = 0; $i < getDolGlobalString('SUBTOTAL_'.strtoupper($this->element).'_MAX_DEPTH', 2); $i++) {
		$depth_array[$i + 1] = $langs->trans("Level", $i + 1);
	}

	if (!$situationinvoicelinewithparent) {
		print '<input type="text" name="line_desc" class="marginrightonly" id="line_desc" value="';
		print GETPOSTISSET('product_desc') ? GETPOST('product_desc', 'restricthtml') : $line->description;
		print '">';
		print $form->selectarray('line_depth', $depth_array, $level);
		$selected = 0;
		print '<div><ul class="ecmjqft">';
		foreach ($line_options as $key => $value) {
			if (in_array($line_type, $value['type'])) {
				print '<li><input id="'.$key.'" type="checkbox" name="'.$key.'" value="" checked="'.$value['checked'].'">'.$langs->trans($value['trans_key']).'</input></li>';
			}
		}
		print '</ul></div>';
	} else {
		print '<input type="text" readonly name="line_desc" id="line_desc" value="';
		print GETPOSTISSET('product_desc') ? GETPOST('product_desc', 'restricthtml') : $line->description;
		print '">';
	}
	?>
	</td>

	<td class="center valignmiddle" colspan="3">
		<input type="submit" class="reposition button buttongen button-save" id="savelinebutton marginbottomonly" name="saveSubtotal" value="<?php echo $langs->trans("Save"); ?>"><br>
		<input type="submit" class="reposition button buttongen button-cancel" id="cancellinebutton" name="cancel" value="<?php echo $langs->trans("Cancel"); ?>">
	</td>
</tr>

<!-- END PHP TEMPLATE objectline_edit.tpl.php -->
