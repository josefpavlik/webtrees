<?php
// Displays the details about a source record.  Also shows how many people and families
// reference this source.
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2009 PGV Development Team.  All rights reserved.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id$

define('WT_SCRIPT_NAME', 'source.php');
require './includes/session.php';
require WT_ROOT.'includes/functions/functions_print_lists.php';

$controller=new WT_Controller_Source();
$controller->init();

if ($controller->source && $controller->source->canDisplayName()) {
	print_header($controller->getPageTitle());
	if (WT_USER_CAN_EDIT || WT_USER_CAN_ACCEPT) {
		if ($controller->source->isMarkedDeleted()) {
			echo '<p class="ui-state-highlight">', WT_I18N::translate('This record has been deleted, but the deletion needs to be reviewed by a moderator.');
			if (WT_USER_CAN_ACCEPT) {
				echo ' <a href="', $controller->source->getHtmlUrl(), '&amp;action=accept">', WT_I18N::translate('Accept the changes.'), '</a>';
				echo ' <a href="', $controller->source->getHtmlUrl(), '&amp;action=undo">', WT_I18N::translate('Reject the changes.'), '</a>';
			}
			echo '</p>';
		} elseif (find_updated_record($controller->source->getXref(), WT_GED_ID)!==null) {
			echo '<p class="ui-state-highlight">', WT_I18N::translate('This record has been changed, but the changes need to be reviewed by a moderator.');
			if ($controller->show_changes) {
				echo ' <a href="', $controller->source->getHtmlUrl(), '&amp;show_changes=no">', WT_I18N::translate('Hide the changes.'), '</a>';
				if (WT_USER_CAN_ACCEPT) {
					echo ' <a href="', $controller->source->getHtmlUrl(), '&amp;action=accept">', WT_I18N::translate('Accept the changes.'), '</a>';
					echo ' <a href="', $controller->source->getHtmlUrl(), '&amp;action=undo">', WT_I18N::translate('Reject the changes.'), '</a>';
				}
			} else {
				echo ' <a href="', $controller->source->getHtmlUrl(), '&amp;show_changes=yes">', WT_I18N::translate('Show the changes.'), '</a>';
			}
			echo '</p>';
		} elseif ($controller->accept_success) {
			echo '<p class="ui-state-highlight">', WT_I18N::translate('The changes have been accepted.'), '</p>';
		} elseif ($controller->reject_success) {
			echo '<p class="ui-state-highlight">', WT_I18N::translate('The changes have been rejected.'), '</p>';
		}
	}
} else {
	print_header(WT_I18N::translate('Source'));
	echo '<p class="ui-state-error">', WT_I18N::translate('This record does not exist or you do not have permission to view it.'), '</p>';
	print_footer();
	exit;
}

// We have finished writing session data, so release the lock
Zend_Session::writeClose();

if (WT_USE_LIGHTBOX) {
	require WT_ROOT.WT_MODULES_DIR.'lightbox/lb_defaultconfig.php';
	require WT_ROOT.WT_MODULES_DIR.'lightbox/functions/lb_call_js.php';
}

$linkToID=$controller->sid; // Tell addmedia.php what to link to

echo WT_JS_START;
echo 'function show_gedcom_record() {';
echo ' var recwin=window.open("gedrecord.php?pid=', $controller->sid, '", "_blank", "top=0, left=0, width=600, height=400, scrollbars=1, scrollable=1, resizable=1");';
echo '}';
echo 'function showchanges() {';
echo ' window.location="source.php?sid=', $controller->sid, '&show_changes=yes"';
echo '}';
echo WT_JS_END;

echo '<table width="70%" class="list_table"><tr><td>';
echo '<span class="name_head">', PrintReady(htmlspecialchars($controller->source->getFullName()));
echo '</span><br />';
echo '<table class="facts_table">';

$sourcefacts=$controller->source->getFacts();
foreach ($sourcefacts as $fact) {
	print_fact($fact);
}

// Print media
print_main_media($controller->sid);

// new fact link
if ($controller->source->canEdit()) {
	print_add_new_fact($controller->sid, $sourcefacts, 'SOUR');
	// new media
	echo '<tr><td class="descriptionbox">';
	echo WT_I18N::translate('Add media'), help_link('add_media');
	echo '</td><td class="optionbox">';
	echo '<a href="javascript: ', WT_I18N::translate('Add media'), '" onclick="window.open(\'addmedia.php?action=showmediaform&linktoid=', $controller->sid, '\', \'_blank\', \'top=50, left=50, width=600, height=500, resizable=1, scrollbars=1\'); return false;">', WT_I18N::translate('Add a new media item'), '</a>';
	echo '<br />';
	echo '<a href="javascript:;" onclick="window.open(\'inverselink.php?linktoid=', $controller->sid, '&linkto=source\', \'_blank\', \'top=50, left=50, width=600, height=500, resizable=1, scrollbars=1\'); return false;">', WT_I18N::translate('Link to an existing Media item'), '</a>';
	echo '</td></tr>';
}
echo '</table><br /><br /></td></tr><tr class="center"><td colspan="2">';

// Individuals linked to this source
if ($controller->source->countLinkedIndividuals()) {
	print_indi_table($controller->source->fetchLinkedIndividuals(), $controller->source->getFullName());
}

// Families linked to this source
if ($controller->source->countLinkedFamilies()) {
	print_fam_table($controller->source->fetchLinkedFamilies(), $controller->source->getFullName());
}

// Media Items linked to this source
if ($controller->source->countLinkedMedia()) {
	print_media_table($controller->source->fetchLinkedMedia(), $controller->source->getFullName());
}

// Shared Notes linked to this source
if ($controller->source->countLinkedNotes()) {
	print_note_table($controller->source->fetchLinkedNotes(), $controller->source->getFullName());
}

echo '</td></tr></table>';

print_footer();
