<?php

if ($type == 'subtotal' && empty($titles)) {
	setEventMessages("NoTitleSet", null, 'errors');
	return;
}

if ($type == 'title') {
	$formquestion = array(
		array('type' => 'hidden', 'name' => 'subtotallinetype', 'value' => $type),
		array('type' => 'text', 'name' => 'subtotallinedesc', 'label' => $langs->trans("SubtotalLineDesc"), 'moreattr' => 'placeholder="'.$langs->trans("Description").'"'),
		array('type' => 'select', 'name' => 'subtotallinelevel', 'label' => $langs->trans("SubtotalLineLevel"), 'values' => $depth_array, 'default' => 1, 'select_show_empty' => 0),
		array('type' => 'other', 'name' => 'subtotalvatrate', 'label' => $langs->trans("SelectVATRate"), 'value' => $form->load_tva('subtotalvatrate', '', $seller)),
		array('type' => 'text', 'name' => 'subtotalremisepercent', 'label' => $langs->trans("EnterRemisePercent"), 'morecss' => "width50", 'moreattr' => 'placeholder="%"'),
		array('type' => 'checkbox', 'name' => 'showuponpdf', 'label' => $langs->trans("ShowUPOnPDF")),
		array('type' => 'checkbox', 'name' => 'showtotalexludingvatonpdf', 'label' => $langs->trans("ShowTotalExludingVATOnPDF")),
		array('type' => 'checkbox', 'name' => 'forcepagebreak', 'label' => $langs->trans("ForcePageBreak")),
	);
} elseif ($type == 'subtotal') {
	$formquestion = array(
		array('type' => 'hidden', 'name' => 'subtotallinetype', 'value' => $type),
		array('type' => 'select', 'name' => 'subtotaltitleline', 'label' => $langs->trans("CorrespondingSubtotalLine"), 'values' => $titles, 'select_show_empty' => 0),
		array('type' => 'checkbox', 'name' => 'showtotalexludingvatonpdf', 'label' => $langs->trans("ShowTotalExludingVATOnPDF")),
	);
}

$page = $_SERVER["PHP_SELF"];

if ($this->element == 'facture') {
	$page .= '?facid='.$this->id;
} elseif (in_array($this->element, array('propal', 'commande'))) {
	$page .= '?id='.$this->id;
}

$form_title = $type == 'title' ? $langs->trans('AddTitleLine') : $langs->trans('AddSubtotalLine');

return $form->formconfirm($page, $form_title, '', 'confirm_add_line', $formquestion, 'yes', 1);
