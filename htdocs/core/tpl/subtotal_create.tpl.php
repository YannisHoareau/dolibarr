<?php
$langs->load('subtotals');

$depth_array = array();
for ($i = 0; $i < getDolGlobalString('SUBTOTAL_'.strtoupper($this->element).'_MAX_DEPTH', 2); $i++) {
$depth_array[$i + 1] = $langs->trans("Level", $i + 1);
}

// Create an array for form
$formquestion = array(
array('type' => 'hidden', 'name' => 'subtotallinetype', 'value' => $type),
array('type' => 'text', 'name' => 'subtotallinedesc', 'label' => $langs->trans("SubtotalLineDesc"), 'moreattr' => 'placeholder="'.$langs->trans("Description").'"'),
array('type' => 'select', 'name' => 'subtotallinelevel', 'label' => $langs->trans("SubtotalLineLevel"), 'values' => $depth_array, 'default' => 1, 'select_show_empty' => 0),
);

$page = $_SERVER["PHP_SELF"];
if ($this->element == 'facture') {
$page .= '?facid='.$this->id;
} elseif (in_array($this->element, array('propal', 'commande'))) {
$page .= '?id='.$this->id;
}

$form_title = $type == 'title' ? $langs->trans('AddTitleLine') : $langs->trans('AddSubtotalLine');

return $form->formconfirm($page, $form_title, '', 'confirm_add_line', $formquestion, 'yes', 1);
