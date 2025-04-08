<?php

/**
 * @var CommonObject $object
 * @var CommonObjectLine $objp
 * @var CommonObjectLine $lines
 * @var Translate $langs
 *
 * @var int $i
 * @var string $suffix
 */

'
@phan-var-force CommonObjectLine|CommonOrderLine|ExpeditionLigne $line
@phan-var-force Commande|Expedition $object
';

if (!empty($objp)) {
	$id = $objp->rowid;
	print '<!-- subtotal dispatch line id = ' . $id . ' -->';
	$element = "commande";
	$desc = $objp->description;
	$extraparams = (array)json_decode($objp->extraparams, true);
	$line_options = $extraparams["subtotal"] ?? array();
	$qty = $objp->qty;
} else {
	$id = $lines[$i]->id;
	print '<!-- subtotal reception line id = ' . $id . ' -->';
	$element = "commande";
	$desc = $lines[$i]->description;
	$line_options = (array)$lines[$i]->extraparams["subtotal"];
	$qty = $lines[$i]->qty;
	$buttons = true;
}

$langs->load('subtotals');

$line_color = $object->getSubtotalColors((int) $qty);
$colspan = 9;

if (isModEnabled('productbatch')) {
	$colspan++;
}
if (isModEnabled('stock')) {
	$colspan++;
}

print '<tr id="row-' . $id . '" data-id="' . $id . '" data-element="' . $element . '" style="background:#' . $line_color . '" >';
if (!empty($objp)) {
	print '<input name="subtotal' . $suffix . '" type="hidden" value="' . $objp->qty . '">';
	print '<input name="description' . $suffix . '" type="hidden" value="' . $desc . '">';
	print '<input name="id' . $suffix . '" type="hidden" value="' . $id . '">';

	if (array_key_exists('titleshowuponpdf', $line_options)) {
		print '<input name="titleshowuponpdf'.$suffix.'" type="hidden" value="1">';
	}
	if (array_key_exists('titleshowtotalexludingvatonpdf', $line_options)) {
		print '<input name="titleshowtotalexludingvatonpdf'.$suffix.'" type="hidden" value="1">';
	}
	if (array_key_exists('titleforcepagebreak', $line_options)) {
		print '<input name="titleforcepagebreak'.$suffix.'" type="hidden" value="1">';
	}
}

if ($qty > 0) { ?>
	<td class="linecollabel" colspan="<?php echo $colspan ?>" <?php echo !colorIsLight($line_color) ? ' style="color: white"' : ' style="color: black"' ?>><?php echo str_repeat('&nbsp;', (int) ($qty - 1) * 8); ?>
		<?php
		echo $desc;
		if (array_key_exists('titleshowuponpdf', $line_options)) {
			echo '&nbsp;' . img_picto($langs->trans("ShowUPOnPDF"), 'invoicing');
		}
		if (array_key_exists('titleshowtotalexludingvatonpdf', $line_options)) {
			echo '&nbsp; <span title="' . $langs->trans("ShowTotalExludingVATOnPDF") . '">%</span>';
		}
		if (array_key_exists('titleforcepagebreak', $line_options)) {
			echo '&nbsp;' . img_picto($langs->trans("ForcePageBreak"), 'file');
		}
		?>
	</td>
<?php } elseif ($qty < 0) { ?>
<td class="linecollabel nowrap right" <?php echo !colorIsLight($line_color) ? ' style="color: white"' : ' style="color: black"' ?> colspan="<?php echo $colspan ?>">
	<?php
	echo $desc;
	if (array_key_exists('subtotalshowtotalexludingvatonpdf', $line_options)) {
		echo '&nbsp; <span title="' . $langs->trans("ShowTotalExludingVATOnPDF") . '">%</span>';
	}
	?>
</td>
<?php }

if (isset($buttons)) {
	// Delete picto
	echo '<td class="linecoldelete center">';
	echo '<a class="reposition" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=ask_subtotal_deleteline&token=' . newToken() . '&lineid=' . $id;
	if ($qty > 0) {
		echo '&type=title';
	}
	echo '">';
	if (!colorIsLight($line_color)) {
		echo img_delete('default', 'class="pictodelete" style="color: white"');
	} else {
		echo img_delete('default', 'class="pictodelete" style="color: #666"');
	}
	echo '</a> </td>';
}

print "</tr>";
