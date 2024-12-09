<?php
/* Copyright (C) 2004-2011	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012	Regis Houssin		<regis.houssin@inodbox.com>
 * Copyright (C) 2012-2013	Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2019		Christophe Battarel <christophe@altairis.fr>
 * Copyright (C) 2024       Frédéric France             <frederic.france@free.fr>
 * Copyright (C) 2024		MDW							<mdeweerd@users.noreply.github.com>
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
 */

/**
 *  \file       htdocs/admin/subtotals.php
 *  \ingroup    subtotals
 *  \brief      Activation page for the subtotals module in the other modules
 */

// Load Dolibarr environment
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/doleditor.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var Form $form
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

// Load translation files required by the page
$langs->loadLangs(array('admin', 'subtotals', 'errors'));
$action = GETPOST('action', 'aZ09');

if (!$user->admin) {
	accessforbidden();
}

// Constant and translation of the module description
$modules = array(
	'PROPAL' => 'Propal',
	'COMMANDE' => 'CustomerOrder',
	'FACTURE' => 'CustomerInvoice',
);
// Conditions for the option to be offered
$conditions = array(
	'PROPAL' => (isModEnabled("propal")),
	'COMMANDE' => (isModEnabled("commande")),
	'FACTURE' => (isModEnabled("facture")),
);

/*
 *  Actions
 */

if (preg_match('/^SUBTOTAL_.*$/', $action)) {
	if (preg_match('/^.*_MAX_DEPTH$/', $action)) {
		dolibarr_set_const($db, $action, GETPOST($action), 'int', 0, '', $conf->entity);
	} else {
		$value = getDolGlobalInt($action, 0);
		$value == 0 ? $value = 1 : $value = 0;
		dolibarr_set_const($db, $action, $value, 'chaine', 0, '', $conf->entity);
	}
}


/*
 * View
 */

llxHeader('', '', '', '', 0, 0, '', '', '', 'mod-admin page-subtotals');
$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans("SubtotalSetup"), $linkback, 'title_setup'); #TODO

if (empty($conf->use_javascript_ajax)) {
	setEventMessages(null, array($langs->trans("NotAvailable"), $langs->trans("JavascriptDisabled")), 'errors');
} else {
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre">';
	#TODO translations
	print '<td width="1100">'.$langs->trans("ActivateSubtotal").'</td>';
	print '<td class="center">'.$langs->trans("Title").'</td>';
	print '<td class="center">'.$langs->trans("Subtotal").'</td>';
	print '<td class="center">'.$langs->trans("MaxDepthLevel").'</td>';
	#print '<td>'.$langs->trans("Comment").'</td>'; #TODO : comment ?
	print "</tr>\n";

	// Modules
	foreach ($modules as $const => $desc) {
		// If this condition is not met, the option is not offered
		if (!$conditions[$const]) {
			continue;
		}

		$constante_title = 'SUBTOTAL_TITLE_'.$const;
		$constante_subtotal = 'SUBTOTAL_'.$const;
		print '<!-- constant = '.$constante_subtotal.' -->'."\n";
		print '<tr class="oddeven">';
		print '<td>'.$langs->trans($desc).'</td>';

		print '<td class="center">';
		$value_title = getDolGlobalInt($constante_title, 0);
		print '<a class="reposition" href="'.$_SERVER['PHP_SELF'].'?action='.$constante_title.'&token='.newToken().'">';
		print $value_title == 0 ? img_picto($langs->trans("Disabled"), 'switch_off') : img_picto($langs->trans("Enabled"), 'switch_on').'</a>';
		print '</td>';

		print '<td class="center">';
		$value_subtotal = getDolGlobalInt($constante_subtotal, 0);
		print '<a class="reposition" href="'.$_SERVER['PHP_SELF'].'?action='.$constante_subtotal.'&token='.newToken().'">';
		print $value_subtotal == 0 ? img_picto($langs->trans("Disabled"), 'switch_off') : img_picto($langs->trans("Enabled"), 'switch_on').'</a>';
		print '</td>';

		print '<td class="center">';
		$can_modify = !($value_subtotal == 0 && $value_title == 0);
		print '<form action="'.$_SERVER["PHP_SELF"].'?action='.'SUBTOTAL_'.$const.'_MAX_DEPTH'.'" method="POST">';
		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="action" value="updateMask">';
		print '<input size="3" type="text"';
		print $can_modify ? '' : ' readonly ';
		print 'name="SUBTOTAL_'.$const.'_MAX_DEPTH" value="'.getDolGlobalString('SUBTOTAL_'.$const.'_MAX_DEPTH', 2).'">';
		print $can_modify ? '<input type="submit" class="button button-edit reposition smallpaddingimp" name="Button"value="'.$langs->trans("Modify").'">' : '';
		print '</form>';
		print '</td>';

		print '</tr>';
	}

	print '</table>';

	// Other options
	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans("Other").'</td>';
	print '<td></td>';
	print "</tr>\n";

	print '<tr class="oddeven">';
	print '<td>';
	print $langs->trans('TitleSubtotalPDFbackColor');
	print '</td>';
	print '<td class="center width100">';
	print 'COLOR PICKER HERE';
	print "</td>";
	print '</tr>';

	print '<tr class="oddeven">';
	print '<td>';
	print $langs->trans('CommentPDFbackColor ?');
	print '</td>';
	print '<td class="center width100">';
	print 'COLOR PICKER HERE';
	print "</td>";
	print '</tr>';

	print '</table>'."\n";

}

// End of page
llxFooter();
$db->close();
