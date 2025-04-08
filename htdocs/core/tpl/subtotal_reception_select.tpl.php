<?php

/**
 * @var CommonObject $object
 * @var CommonObject $this
 * @var CommonObjectLine $line
 * @var int $indiceAsked
 */
$line_color = $object->getSubtotalColors($line['level']);

print '<!-- line for order line '.$line['id'].' -->'."\n";
print '<tr style="background:#' . $line_color . '" id="row-'.$line['id'].'">'."\n";

$colspan = 10;

$selected = 1;
if (!empty($selectedLines) && !in_array($this->tpl['id'], $selectedLines)) {
	$selected = 0;
}
print '<td colspan="'.$colspan.'">';
print '<input id="cb'.$line['id'].'" class="flat checkforselect" type="checkbox" name="subtotal_toselect[]" value="'.$line['id'].'" ' . ($selected ? ' checked="checked"' : '') . ' >';
print '<input name="idl'.$indiceAsked.'" type="hidden" value="'.$line['id'].'">';
//print '<input name="subtotal'.$indiceAsked.'" id="subtotal'.$indiceAsked.'" type="hidden" value="0">';
print '<input name="qtyasked'.$indiceAsked.'" id="qtyasked'.$indiceAsked.'" type="hidden" value="0">';
print $line['description'] . "</td>\n";


print '</tr>';
