<?php

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
	die("Direct initialization of this file is not allowed.");
}

// Hooks

//Newthread Hooks
$plugins->add_hook("newthread_start", "messager_newchat");
$plugins->add_hook("newthread_do_newthread_end", "messager_newchat_do");

//bei Antwort
$plugins->add_hook("newreply_start", "messager_replychat");
$plugins->add_hook("newreply_do_newreply_end", "messager_replychat_do");
$plugins->add_hook("newreply_threadreview_post", "messager_threadreview");

// Editieren
$plugins->add_hook("editpost_end", "messager_editscene");
$plugins->add_hook("editpost_do_editpost_end", "messager_editscene_do");

// Global
$plugins->add_hook("global_start", "messager_globalchats");
// Showthread
$plugins->add_hook("showthread_start", "messager_showthread");

// Postbit
$plugins->add_hook("postbit", "messager_postbit");

// forumdisplay
$plugins->add_hook("forumdisplay_thread_end", "messager_threads");


// Misc
$plugins->add_hook('misc_start', 'messager_chats');

// Alerts
if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
	$plugins->add_hook("global_start", "messager_alerts");
}

function messager_info()
{
	return array(
		"name" => "Messagersystem",
		"description" => "Hier kannst du deinen Usern ermöglichen, das sie Kurznachrichten schreiben können.",
		"website" => "",
		"author" => "Ales",
		"authorsite" => "",
		"version" => "1.0",
		"guid" => "",
		"codename" => "",
		"compatibility" => "*"
	);
}

function messager_install()
{
	global $db, $mybb;

	// Datenbank
	$db->query("ALTER TABLE `" . TABLE_PREFIX . "threads` ADD `messager_partner` varchar(400) CHARACTER SET utf8 NOT NULL;");
	$db->query("ALTER TABLE `" . TABLE_PREFIX . "threads` ADD `messager_groupchattitle` varchar(400) CHARACTER SET utf8 NOT NULL;");
	$db->query("ALTER TABLE `" . TABLE_PREFIX . "threads` ADD `messager_grouppic` varchar(400) CHARACTER SET utf8 NOT NULL;");
	$db->query("ALTER TABLE `" . TABLE_PREFIX . "threads` ADD `messager_kind` int(100)  NOT NULL;");
	$db->query("ALTER TABLE `" . TABLE_PREFIX . "posts` ADD `message_date` varchar(400) CHARACTER SET utf8 NOT NULL;");
	$db->query("ALTER TABLE `" . TABLE_PREFIX . "posts` ADD `message_time` varchar(100)  NOT NULL;");

	$setting_group = array(
		'name' => 'messager',
		'title' => 'Einstellungen für messager',
		'description' => 'This is my plugin and it does some things',
		'disporder' => 5,
		// The order your setting group will display
		'isdefault' => 0
	);

	$gid = $db->insert_query("settinggroups", $setting_group);

	$setting_array = array(
		// A text setting
		'messager_fid' => array(
			'title' => 'FID für Messagericon',
			'description' => 'Gebe hier die FID des Profilfelds an, in welches das Profilbild für den Messager gespeichert wird:',
			'optionscode' => 'numeric',
			'value' => '3',
			// Default
			'disporder' => 1
		),
		// A select box
		'messager_forum' => array(
			'title' => 'Forum für Chats',
			'description' => 'Wähle hier das Forum aus, in welchen die Chats passiert.',
			'optionscode' => 'forumselectsingle',
			'value' => 2,
			'disporder' => 2
		),

	);

	foreach ($setting_array as $name => $setting) {
		$setting['name'] = $name;
		$setting['gid'] = $gid;

		$db->insert_query('settings', $setting);
	}

	// Don't forget this!
	rebuild_settings();

	// templates

	$insert_array = array(
		'title' => 'messager_editmessage',
		'template' => $db->escape_string('<tr>
		<td class="trow1"><strong>{$lang->messager_chatdatetime}</strong></td>
<td class="trow2">
	<span class="smalltext"><input type="date" class="textbox" name="message_date" id="message_date" size="40" maxlength="1155" value="{$message_date}" /> </span> 	<span class="smalltext"><input type="time" class="textbox" name="message_time" id="message_time" size="40" maxlength="1155" value="{$message_time}" /> </span>
</td>
</tr>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	$insert_array = array(
		'title' => 'messager_editmessage_firstpost',
		'template' => $db->escape_string('<tr>
				<td class="trow1"><strong>{$lang->messager_chatkind}</strong></td>
				<td class="trow2"><select name="messager_kind">
				{$select_kind}
					</select>
				</td></tr>
			<tr>
					<td class="trow1"><strong>{$lang->messager_groupchattitle}</strong></td>
				<td class="trow2">
					<span class="smalltext"><input type="text" class="textbox" name="messager_groupchattitle" id="messager_groupchattitle" size="40" maxlength="1155" value="{$message_groupchattitle}" /> </span>
				</td></tr>
			<tr>
				<tr>
					<td class="trow1"><strong>{$lang->messager_grouppic}</strong></td>
				<td class="trow2">
					<span class="smalltext"><input type="text" class="textbox" name="messager_grouppic" id="messager_grouppic" size="40" maxlength="1155" value="{$messager_grouppic}" /> </span>
				</td></tr>
			<tr>
			<tr>
					<td class="trow1"><strong>{$lang->messager_chatpartner}</strong></td>
				<td class="trow2">
					<span class="smalltext"><input type="text" class="textbox" name="messager_partner" id="messager_partner" size="40" maxlength="1155" value="{$messager_partner}" style="min-width: 347px; max-width: 100%;" /> </span>
				</td></tr>
			<tr>
						<td class="trow1"><strong>{$lang->messager_chatdatetime}</strong></td>
				<td class="trow2">
					<span class="smalltext"><input type="date" class="textbox" name="message_date" id="message_date" size="40" maxlength="1155" value="{$message_date}" /> </span> 	<span class="smalltext"><input type="time" class="textbox" name="message_time" id="message_time" size="40" maxlength="1155" value="{$message_time}" /> </span>
				</td>
			</tr>
			<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/select2/select2.css?ver=1807">
			<script type="text/javascript" src="{$mybb->asset_url}/jscripts/select2/select2.min.js?ver=1806"></script>
			<script type="text/javascript">
			<!--
			if(use_xmlhttprequest == "1")
			{
				MyBB.select2();
				$("#messager_partner").select2({
					placeholder: "{$lang->search_user}",
					minimumInputLength: 2,
					maximumSelectionSize: \'\',
					multiple: true,
					ajax: { // instead of writing the function to execute the request we use Select2\'s convenient helper
						url: "xmlhttp.php?action=get_users",
						dataType: \'json\',
						data: function (term, page) {
							return {
								query: term, // search term
							};
						},
						results: function (data, page) { // parse the results into the format expected by Select2.
							// since we are using custom formatting functions we do not need to alter remote JSON data
							return {results: data};
						}
					},
					initSelection: function(element, callback) {
						var query = $(element).val();
						if (query !== "") {
							var newqueries = [];
							exp_queries = query.split(",");
							$.each(exp_queries, function(index, value ){
								if(value.replace(/\s/g, \'\') != "")
								{
									var newquery = {
										id: value.replace(/,\s?/g, ","),
										text: value.replace(/,\s?/g, ",")
									};
									newqueries.push(newquery);
								}
							});
							callback(newqueries);
						}
					}
				})
			}
				// --></script>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	$insert_array = array(
		'title' => 'messager_forumdisplay_icon',
		'template' => $db->escape_string('<div class="messager_icon" style="top: -10px;">
		<img src="{$icon_user1}">
	</div>
	<div class="messager_icon" style="left: -2px;top: 10px;">
		<img src="{$icon_user2}">
	</div>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	$insert_array = array(
		'title' => 'messager_forumdisplay_thread',
		'template' => $db->escape_string('<tr class="inline_row">
		<td class="{$bgcolor}{$thread_type_class}"><div class="messager_forumdisplay">
			<div class="messager_forumdisplay_icons">
				{$messager_icon}
			</div>
			<div class="messager_forumdisplay_messager">
				<div class="messager_forumdisplay_chat"><a href="{$thread[\'threadlink\']}">{$thread[\'subject\']}</a></div>
				<div class="messager_forumdisplay_lastpost">letzte Nachricht von {$lastposterlink}, {$lastpostdate}</div>
			</div>
			</div>
		</td>
	{$modbit}
	</tr>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	$insert_array = array(
		'title' => 'messager_forumdisplay_thread_modbit',
		'template' => $db->escape_string('<td class="{$bgcolor}{$thread_type_class}" align="center" style="white-space: nowrap"><input type="checkbox" class="checkbox" name="inlinemod_{$multitid}" id="inlinemod_{$multitid}" value="1" {$inlinecheck}  /></td>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	$insert_array = array(
		'title' => 'messager_forumdisplay_threadlist',
		'template' => $db->escape_string('<div class="float_left">
		{$multipage}
	</div>
	<div class="float_right">
		{$newthread}
	</div>
	<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder clear">
		<tr>
			<td class="thead" colspan="{$colspan}">
				<div class="float_right">
					<span class="smalltext"><strong><a href="misc.php?action=markread&amp;fid={$fid}{$post_code_string}">{$lang->markforum_read}</a>{$addremovesubscription}{$clearstoredpass}</strong></span>
				</div>
				<div>
					<strong>{$foruminfo[\'name\']}</strong>
				</div>
			</td>
		</tr>
		{$selectall}
		{$announcementlist}
		{$threads}{$nullthreads}
		{$forumsort}
	</table>
	<div class="float_left">
		{$multipage}
	</div>
	<div class="float_right" style="margin-top: 4px;">
		{$newthread}
	</div>
	<br class="clear" />
	<br />
	<div class="float_left">
		<div class="float_left">
			<dl class="thread_legend smalltext">
				<dd><span class="thread_status newfolder" title="{$lang->new_thread}">&nbsp;</span> {$lang->new_thread}</dd>
				<dd><span class="thread_status newhotfolder" title="{$lang->new_hot_thread}">&nbsp;</span> {$lang->new_hot_thread}</dd>
				<dd><span class="thread_status hotfolder" title="{$lang->hot_thread}">&nbsp;</span> {$lang->hot_thread}</dd>
			</dl>
		</div>
		<div class="float_left">
			<dl class="thread_legend smalltext">
				<dd><span class="thread_status folder" title="{$lang->no_new_thread}">&nbsp;</span> {$lang->no_new_thread}</dd>
				<dd><span class="thread_status dot_folder" title="{$lang->posts_by_you}">&nbsp;</span> {$lang->posts_by_you}</dd>
				<dd><span class="thread_status closefolder" title="{$lang->closed_thread}">&nbsp;</span> {$lang->closed_thread}</dd>
			</dl>
		</div>
		<br class="clear" />
	</div>
	<div class="float_right" style="text-align: right;">
		{$inlinemod}
		{$searchforum}
		{$forumjump}
	</div>
	<br class="clear" />
	{$inline_edit_js}'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	$insert_array = array(
		'title' => 'messager_globalchats',
		'template' => $db->escape_string('{$chats} <a href="misc.php?action=messager">{$lang->messager_global}</a>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
	$insert_array = array(
		'title' => 'messager_messagedate',
		'template' => $db->escape_string('<div>{$post[\'message_date\']} {$post[\'message_time\']}</div>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
	$insert_array = array(
		'title' => 'messager_misc',
		'template' => $db->escape_string('<html>
		<head>
		<title>{$mybb->settings[\'bbname\']} - {$lang->messager_misc}</title>
		{$headerinclude}
		</head>
		<body>
		{$header}
		<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
		<tr>
			<td class="thead"> <div class="float_right messager_icons"><i class="fa-regular fa-camera"></i> <i class="fa-regular fa-magnifying-glass"></i> <i class="fa-solid fa-ellipsis-vertical"></i></div>
				<strong>{$lang->messager_misc}</strong></td>
		</tr>
		<tr>
		<td class="trow1">
		{$your_chats}
		</td>
		</tr>
		</table>
		{$footer}
		</body>
		</html>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);

	$db->insert_query("templates", $insert_array);
	$insert_array = array(
		'title' => 'messager_misc_chats',
		'template' => $db->escape_string('<div class="messager_chat">
		<div class="messager_pic"><img src="{$pic}"></div>
		<div class="messager_lastmessagebox"><div class="messager_messagedate">{$date}</div>
			<div class="messager_messagename">{$chatname}</div>
			<div class="messager_lastmessage">{$lastmessage}</div>
		</div>
	</div>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	$insert_array = array(
		'title' => 'messager_newchat_facts',
		'template' => $db->escape_string('<tr>
		<td class="trow1"><strong>{$lang->messager_chatkind}</strong></td>
		<td class="trow2"><select name="messager_kind">
			<option value="1">{$lang->messager_chatkind_single}</option>
			<option value="2">{$lang->messager_chatkind_group}</option>
			</select>
		</td></tr
	<tr>
			<td class="trow1"><strong>{$lang->messager_groupchattitle}</strong></td>
		<td class="trow2">
			<span class="smalltext"><input type="text" class="textbox" name="messager_groupchattitle" id="messager_groupchattitle" size="40" maxlength="1155" value="{$messager_groupchattitle}" /> </span>
		</td></tr>
	<tr>
		<tr>
				<td class="trow1"><strong>{$lang->messager_grouppic}</strong></td>
		<td class="trow2">
			<span class="smalltext"><input type="text" class="textbox" name="messager_grouppic" id="messager_grouppic" size="40" maxlength="1155" value="{$messager_grouppic}" /> </span>
		</td>
	</tr>
	<tr>
			<td class="trow1"><strong>{$lang->messager_chatpartner}</strong></td>
		<td class="trow2">
			<span class="smalltext"><input type="text" class="textbox" name="messager_partner" id="messager_partner" size="40" maxlength="1155" value="{$messager_partner}" style="min-width: 347px; max-width: 100%;" /> </span>
		</td></tr>
	<tr>
				<td class="trow1"><strong>{$lang->messager_chatdatetime}</strong></td>
		<td class="trow2">
			<span class="smalltext"><input type="date" class="textbox" name="message_date" id="message_date" size="40" maxlength="1155" value="{$message_date}" /> </span> 	<span class="smalltext"><input type="time" class="textbox" name="message_time" id="message_time" size="40" maxlength="1155" value="{$message_time}" /> </span>
		</td>
	</tr>
		
		<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/select2/select2.css?ver=1807">
	<script type="text/javascript" src="{$mybb->asset_url}/jscripts/select2/select2.min.js?ver=1806"></script>
	<script type="text/javascript">
	<!--
	if(use_xmlhttprequest == "1")
	{
		MyBB.select2();
		$("#messager_partner").select2({
			placeholder: "{$lang->search_user}",
			minimumInputLength: 2,
			maximumSelectionSize: \'\',
			multiple: true,
			ajax: { // instead of writing the function to execute the request we use Select2\'s convenient helper
				url: "xmlhttp.php?action=get_users",
				dataType: \'json\',
				data: function (term, page) {
					return {
						query: term, // search term
					};
				},
				results: function (data, page) { // parse the results into the format expected by Select2.
					// since we are using custom formatting functions we do not need to alter remote JSON data
					return {results: data};
				}
			},
			initSelection: function(element, callback) {
				var query = $(element).val();
				if (query !== "") {
					var newqueries = [];
					exp_queries = query.split(",");
					$.each(exp_queries, function(index, value ){
						if(value.replace(/\s/g, \'\') != "")
						{
							var newquery = {
								id: value.replace(/,\s?/g, ","),
								text: value.replace(/,\s?/g, ",")
							};
							newqueries.push(newquery);
						}
					});
					callback(newqueries);
				}
			}
		})
	}
		// --></script>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
	$insert_array = array(
		'title' => 'messager_postbit_classic',
		'template' => $db->escape_string('{$ignore_bit}{$deleted_bit}
		<a name="pid{$post[\'pid\']}" id="pid{$post[\'pid\']}"></a>
		<div class="messager {$unapproved_shade}" style="{$post_visibility}" id="post_{$post[\'pid\']}">
			<div class="{$post[\'messager_css\']}">	<div class="post_head">
				{$post[\'posturl\']}
				{$post[\'icon\']}
				<span class="post_date">{$post[\'postdate\']} <span class="post_edit" id="edited_by_{$post[\'pid\']}">{$post[\'editedmsg\']}</span></span>
			{$post[\'subject_extra\']}
			</div>
		{$post[\'account_pic\']}
			<div class="message_post_body scaleimages" id="pid_{$post[\'pid\']}">
				<div class="message_profillink">{$post[\'profillink\']}</div>
				<div class="message">{$post[\'message\']}</div>
				<div class="messager_datetime">	{$post[\'messager_messagedate\']}</div>
			</div>
			<div class="message_bottom">
				{$post[\'button_edit\']}{$post[\'button_quickdelete\']}
			</div>
			</div>
		</div>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
	$insert_array = array(
		'title' => 'messager_replaychat',
		'template' => $db->escape_string('<tr>
		<td class="trow1"><strong>{$lang->messager_chatdatetime}</strong></td>
<td class="trow2">
	<span class="smalltext"><input type="date" class="textbox" name="message_date" id="message_date" size="40" maxlength="1155" value="{$message_date}" /> </span> 	<span class="smalltext"><input type="time" class="textbox" name="message_time" id="message_time" size="40" maxlength="1155" value="{$message_time}" /> </span>
</td>
</tr>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
	$insert_array = array(
		'title' => 'messager_showthread',
		'template' => $db->escape_string('<html>
		<head>
		<title>{$thread[\'subject\']}</title>
		{$headerinclude}
		<script type="text/javascript">
		<!--
			var quickdelete_confirm = "{$lang->quickdelete_confirm}";
			var quickrestore_confirm = "{$lang->quickrestore_confirm}";
			var allowEditReason = "{$mybb->settings[\'alloweditreason\']}";
			lang.save_changes = "{$lang->save_changes}";
			lang.cancel_edit = "{$lang->cancel_edit}";
			lang.quick_edit_update_error = "{$lang->quick_edit_update_error}";
			lang.quick_reply_post_error = "{$lang->quick_reply_post_error}";
			lang.quick_delete_error = "{$lang->quick_delete_error}";
			lang.quick_delete_success = "{$lang->quick_delete_success}";
			lang.quick_delete_thread_success = "{$lang->quick_delete_thread_success}";
			lang.quick_restore_error = "{$lang->quick_restore_error}";
			lang.quick_restore_success = "{$lang->quick_restore_success}";
			lang.editreason = "{$lang->postbit_editreason}";
			lang.post_deleted_error = "{$lang->post_deleted_error}";
			lang.softdelete_thread = "{$lang->soft_delete_thread}";
			lang.restore_thread = "{$lang->restore_thread}";
		// -->
		</script>
		<!-- jeditable (jquery) -->
		<script type="text/javascript" src="{$mybb->asset_url}/jscripts/report.js?ver=1820"></script>
		<script src="{$mybb->asset_url}/jscripts/jeditable/jeditable.min.js"></script>
		<script type="text/javascript" src="{$mybb->asset_url}/jscripts/thread.js?ver=1827"></script>
		</head>
		<body>
			{$header}
			{$threadnotesbox}
			{$pollbox}
			<div class="float_left">
				{$multipage}
			</div>
			<div class="float_right">
				{$newreply}
			</div>
			{$ratethread}
			<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder tfixed clear">
				<tr>
					<td class="thead">
						<div class="float_right">
							<span class="smalltext"><strong><a href="showthread.php?mode={$thread_toggle}&amp;tid={$tid}&amp;pid={$pid}#pid{$pid}">{$lang->thread_toggle}</a>{$threadnoteslink}</strong></span>
						</div>
						<div>
							
						{$messager_showthread}
						</div>
					</td>
				</tr>		
			
			<tr><td id="posts_container">
			<div id="posts">
				{$posts}
			</div>
		</td></tr>
				<tr>
					<td class="tfoot">
						{$search_thread}
						<div>
							<strong>&laquo; <a href="{$next_oldest_link}">{$lang->next_oldest}</a> | <a href="{$next_newest_link}">{$lang->next_newest}</a> &raquo;</strong>
						</div>
					</td>
				</tr>
			</table>
			<div class="float_left">
				{$multipage}
			</div>
			<div style="padding-top: 4px;" class="float_right">
				{$newreply}
			</div>
			<br class="clear" />
			<a name="switch" id="switch"></a>
			{$threadexbox}
			{$similarthreads}
			<br />
			<div class="float_left">
				<ul class="thread_tools">
					{$printthread}
					{$sendthread}
					{$addremovesubscription}
					{$addpoll}
				</ul>
			</div>
		
			<div class="float_right" style="text-align: right;">
				{$moderationoptions}
				{$forumjump}
			</div>
			<br class="clear" />
			{$usersbrowsing}
			{$footer}
			<script type="text/javascript">
				var thread_deleted = "{$thread_deleted}";
				if(thread_deleted == "1")
				{
					$("#quick_reply_form, .new_reply_button, .thread_tools, .inline_rating").hide();
					$("#moderator_options_selector option.option_mirage").attr("disabled","disabled");
				}
			</script>
		</body>
		</html>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
	$insert_array = array(
		'title' => 'messager_showthread_icons_both',
		'template' => $db->escape_string('<div class="showthread_icon" style="top: -10px;">
		<img src="{$icon_user1}">
	</div>
	<div class="showthread_icon" style="left: -2px;top: 10px;">
		<img src="{$icon_user2}">
	</div>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
	$insert_array = array(
		'title' => 'messager_showthread_icons_single',
		'template' => $db->escape_string('<div class="showthread_icon_single">
		<img src="{$chat_icon}">
		</div>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
	$insert_array = array(
		'title' => 'messager_showthread_infos',
		'template' => $db->escape_string('<div class="showthread_head">
		<div class="showthread_icons">
			{$messager_icons}
		</div>
		<div class="showthread_chatinfos">
			<div class="showthread_name">{$chat_title}</div>
			<div class="showthread_infos">{$chat_infos}</div>
		</div>
	</div>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
	$insert_array = array(
		'title' => 'messager_threadreview',
		'template' => $db->escape_string('<div><b>{$lang->messager_threadreview}</b> {$post[\'message_date\']} {$post[\'message_time\']}</div>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
	//CSS einfügen
	$css = array(
		'name' => 'messager.css',
		'tid' => 1,
		'attachedto' => '',
		"stylesheet" => '.messager_icons{
			display: flex;
			align-items: center;
		}
		
		.messager_icons i{
			font-size:16px;
			padding: 0 10px;
		}
		
		.messager_chat{
			display: flex;
			align-items: center;
			width: 80%;
			margin: 5px auto;
			gap: 5px 10px;
		}
		
		.messager_chat > div{
			margin: 3px;	
		}
		
		.messager_pic{
					height:50px;
			width:50px;
			border-radius: 100%;
			margin-right: 10px;
		}
		
		.messager_pic img{
					height:50px;
			width:50px;
			border-radius: 100%;
		}
		
		.messager_lastmessagebox{
			padding: 2px;	
			width: 100%;
		}
		
		.messager_messagename{
			font-weight: bold;
			font-size: 15px;
		}
		
		.messager_messagedate{
			float: right;
			font-size:9px;
		}
		
		
.messager_lastmessage{
	font-size: 11px;	
}

/* Showthread */

.showthread_head{
	display: flex;
	gap: 0 30px;
	align-items: center;
	padding:10px 20px;
}

.showthread_icon{
	display: flex;
	justify-content: center;
	align-items: center;
}

.showthread_icon_single{
	height: 60px;
	width: 60px;
	border-radius: 100%;
}

.showthread_icon_single img{
	height: 60px;
	width: 60px;
	border-radius: 100%;
}

.showthread_name{
	font-size: 20px;
	font-weight: bold;
}

.showthread_icons{
	display: flex;
	justify-content: center;
	align-items: center;
}


.showthread_icon{
	height: 60px;
	width: 60px;
	border-radius: 100%;
	position: relative;
	left: 10px;
}

.showthread_icon img{
	height: 60px;
	width: 60px;
	border-radius: 100%;
}

/* Postbit */
.messager {
	padding: 4px 20px;
}

.message_own {
	display: flex;
	align-items: center;
	flex-direction:row-reverse;
	padding: 20px 30px 20px 20px;
		flex-wrap: wrap;
}

.message_other {
	display: flex;
	align-items: center;
		padding: 20px;
	flex-wrap: wrap;
	
}


.message_account_pic{
	width: 120px;
	height: 120px;
	margin: 20px 10px;
}


.message_account_pic img{
	width: 100px;
	height: 100px;
	border-radius: 100%;
}

.message_own .message_post_body{
	width: 80%;
	border-radius: 20px 0 20px 20px;
	background: #ddd;
	padding: 10px 20px;
  box-sizing: border-box;

}


.message_other .message_post_body{
	width: 80%;
	border-radius: 0 20px 20px 20px ;
	background: #ddd;
	padding: 10px 20px;
  box-sizing: border-box;

}


.message_own .post_head{
	text-align: right;
	font-size: 10px;
	width: 100%;
	display: flex;
	gap: 5px 10px;
	flex-direction: row-reverse;
  margin-bottom: 10px;
}

.message_other .post_head{
	font-size: 10px;
		width: 100%;
	display: flex;
		gap: 5px 10px;
	margin-bottom: 10px;
}

.message_own .message_post_body::before{
	content: "";
	width: 0;
  height: 0;
border-top: 20px solid #ddd;
  border-right: 20px solid transparent;
  position: relative;
top: -10px;
  float: right;
  left: 39px;
}

.message_other .message_post_body::before{
	content: "";
	width: 0;
  height: 0;
border-top: 20px solid #ddd;
  border-left: 20px solid transparent;
  position: relative;
  top: 9px;
  left: -39px;
}

.message_own .message_bottom{
	width: 100%;	
	text-align: right;
	padding: 2px 10px 2px 0;
}
.message_other .message_bottom{
	width: 100%;	
	padding: 2px 0 2px 10px;
}


.message_profile{
	font-size: 14px;
	padding: 2px 5px;
}

.messager_datetime{
	font-size: 8px;
	text-align: right;
}

.message{
	padding: 2px;	
}
/*Forumdisplay*/
.messager_forumdisplay{
	display: flex;
	align-items: center;
	gap: 5px 20px;
}

.messager_forumdisplay_icons{
	width: 120px;
	height: 120px;
	display: flex;
	justify-content: center;
	align-items: center;
}

.messager_icon{
	height: 60px;
	width: 60px;
	border-radius: 100%;
	position: relative;
	left: 10px;
}

.messager_icon img{
	height: 60px;
	width: 60px;
	border-radius: 100%;
}

.messager_groupicon img{
		height: 80px;
	width:80px;
	border-radius: 100%;
}


.messager_forumdisplay_messager{
	width: 100%;
	padding: 10px;
	box-sizing: border-box;
}

.messager_forumdisplay_chat{
	font-size: 20px;
	font-weight: bold;
}

.messager_forumdisplay_lastpost{
	font-size: 10px;
	padding-left: 10px;
	text-transform: uppercase;
}
',
		'cachefile' => $db->escape_string(str_replace('/', '', 'messager.css')),
		'lastmodified' => time()
	);

	require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";

	$sid = $db->insert_query("themestylesheets", $css);
	$db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=" . $sid), "sid = '" . $sid . "'", 1);

	$tids = $db->simple_select("themes", "tid");
	while ($theme = $db->fetch_array($tids)) {
		update_theme_stylesheet_list($theme['tid']);
	}

}

function messager_is_installed()
{
	global $db;
	if ($db->field_exists("messager_partner", "threads")) {
		return true;
	}
	return false;
}

function messager_uninstall()
{
	global $db, $mybb, $templates;

	$db->query("DELETE FROM " . TABLE_PREFIX . "settinggroups WHERE name='messager'");
	$db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='messager_fid'");
	$db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='messager_forum'");

	if ($db->field_exists("messager_partner", "threads")) {
		$db->drop_column("threads", "messager_partner");
	}

	if ($db->field_exists("messager_kind", "threads")) {
		$db->drop_column("threads", "messager_kind");
	}


	if ($db->field_exists("messager_groupchattitle", "threads")) {
		$db->drop_column("threads", "messager_groupchattitle");
	}

	if ($db->field_exists("messager_grouppic", "threads")) {
		$db->drop_column("threads", "messager_grouppic");
	}


	if ($db->field_exists("message_date", "posts")) {
		$db->drop_column("posts", "message_date");
	}

	if ($db->field_exists("message_time", "posts")) {
		$db->drop_column("posts", "message_time");
	}

	$db->delete_query("templates", "title LIKE '%messager%'");

	require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";
	$db->delete_query("themestylesheets", "name = 'messager.css'");
	$query = $db->simple_select("themes", "tid");
	while ($theme = $db->fetch_array($query)) {
		update_theme_stylesheet_list($theme['tid']);
	}


	// Don't forget this!
	rebuild_settings();

}

function messager_activate()
{
	global $db, $cache;

	// Alerts
	if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
		$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

		if (!$alertTypeManager) {
			$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
		}

		$alertType = new MybbStuff_MyAlerts_Entity_AlertType();
		$alertType->setCode('messager_newmessagerchat'); // The codename for your alert type. Can be any unique string.
		$alertType->setEnabled(true);
		$alertType->setCanBeUserDisabled(true);

		$alertTypeManager->add($alertType);

		$alertType = new MybbStuff_MyAlerts_Entity_AlertType();
		$alertType->setCode('messager_newmessage'); // The codename for your alert type. Can be any unique string.
		$alertType->setEnabled(true);
		$alertType->setCanBeUserDisabled(true);

		$alertTypeManager->add($alertType);
	}

	// neue variabeln in die templates einfügen
	require MYBB_ROOT . "/inc/adminfunctions_templates.php";

}

function messager_deactivate()
{
	global $db, $cache;

	// Alerts wieder löschen
	if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
		$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

		if (!$alertTypeManager) {
			$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
		}

		$alertTypeManager->deleteByCode('messager_newmessagerchat');
		$alertTypeManager->deleteByCode('messager_newmessage');
	}

	// variabeln rausnehmen
	require MYBB_ROOT . "/inc/adminfunctions_templates.php";


}


// new Thread

function messager_newchat()
{
	global $mybb, $db, $templates, $forum, $lang, $post_errors, $thread, $messager_newchat_facts, $chat_partner, $chat_date, $chat_time;
	//Die Sprachchat_datei
	$lang->load('messager');
	//Zieht sich erstmal die Einstellung
	$messager_forum = $mybb->settings['messager_forum'];

	$thread['messager_partner'] = $mybb->user['username'] . $mybb->get_input('messager_partner');
	$forum['parentlist'] = "," . $forum['parentlist'] . ",";
	$thread['message_date'] = "2024-01-01";
	if (preg_match("/,$messager_forum,/i", $forum['parentlist'])) {
		if ($mybb->input['previewpost'] || $post_errors) {
			$messager_partner = htmlspecialchars($mybb->get_input('messager_partner'));
			$messager_kind = (int) $mybb->input['messager_kind'];
			$mesager_groupchattitle = htmlspecialchars($mybb->get_input('messager_groupchattitle'));
			$messager_grouppic = $mybb->input['messager_grouppic'];
			$message_date = $mybb->input['message_date'];
			$message_time = $mybb->input['message_time'];

		} else {
			$messager_partner = htmlspecialchars($thread['messager_partner']);
			$messager_groupchattitle = htmlspecialchars($thread['messager_groupchattitle']);
			$message_time = $thread['messager_grouppic'];
			$message_date = $thread['message_date'];
			$message_time = $thread['message_time'];
			$messager_kind = $thread['messager_kind'];
		}

		eval ("\$messager_newchat_facts = \"" . $templates->get("messager_newchat_facts") . "\";");

	}


}


function messager_newchat_do()
{
	global $db, $mybb, $templates, $tid, $forum, $pid;


	$messager_forum = $mybb->settings['messager_forum'];

	$forum['parentlist'] = "," . $forum['parentlist'] . ",";
	if (preg_match("/,$messager_forum,/i", $forum['parentlist'])) {

		// get info

		$messager_kind = $_POST['messager_kind'];
		$messager_partner = $_POST['messager_partner'];
		$message_time = $_POST['message_time'];
		$message_date = $_POST['message_date'];
		$messager_groupchattitle = $_POST['messager_groupchattitle'];
		$messager_grouppic = $_POST['messager_grouppic'];

		// Einmal Charaktere informieren �ber neue SMS
		$usernames = explode(',', $messager_partner);
		$usernames = array_map("trim", $usernames);
		$charas = array();

		foreach ($usernames as $username) {

			$username = $db->escape_string($username);
			$user = $db->query("SELECT username
		  FROM " . TABLE_PREFIX . "users
		  WHERE username = '" . $username . "'
		   ");
			$chara = $db->fetch_field($user, "username");
			$charas[] = $chara;


			$uid_query = $db->query("SELECT uid, username
		  FROM " . TABLE_PREFIX . "users
		  WHERE username = '" . $username . "'
		   ");
			$row = $db->fetch_array($uid_query);

			$charaname = $row['username'];
			$uid = $row['uid'];
			$from_uid = $mybb->user['uid'];

			if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
				$alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('messager_newmessagerchat');
				if ($alertType != NULL && $alertType->getEnabled() && $from_uid != $uid) {
					$alert = new MybbStuff_MyAlerts_Entity_Alert((int) $uid, $alertType, (int) $tid);
					MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
				}
			}
		}

		$messager_partner = implode(",", $charas);


		// Informationen in die Posttabelle eintragen
		$new_message = array(
			"message_date" => $db->escape_string($message_date),
			"message_time" => $db->escape_string($message_time)
		);

		$db->update_query("posts", $new_message, "tid='{$tid}'");



		// Informationen in die Threadtabelle eintragen

		$new_messager = array(
			"messager_partner" => $db->escape_string($messager_partner),
			"messager_groupchattitle" => $db->escape_string($messager_groupchattitle),
			"messager_grouppic" => $db->escape_string($messager_grouppic),
			"messager_kind" => $db->escape_string($messager_kind)
		);


		$db->update_query("threads", $new_messager, "tid='{$tid}'");
	}
}

function messager_replychat()
{
	global $mybb, $forum, $templates, $db, $tid, $thread, $lang, $messager_replaychat, $post_errors;
	//Die Sprachdatei
	$lang->load('messager');
	$messager_forum = $mybb->settings['messager_forum'];
	$thread['message_date'] = "2024-01-01";

	$forum['parentlist'] = "," . $forum['parentlist'] . ",";
	if (preg_match("/,$messager_forum,/i", $forum['parentlist'])) {

		if ($mybb->input['previewpost'] || $post_errors) {
			$message_date = $mybb->input['message_date'];
			$message_time = $mybb->input['message_time'];
		} else {
			$message_date = $thread['message_date'];
			$message_time = $thread['message_time'];
		}

		eval ("\$messager_replaychat = \"" . $templates->get("messager_replaychat") . "\";");

	}
}


function messager_replychat_do()
{
	global $db, $mybb, $templates, $tid, $forum, $pid, $thread, $subject;
	$messager_partner = $thread['messager_partner'];
	$message_time = $_POST['message_time'];
	$message_date = $_POST['message_date'];
	$subject = $thread['subject'];

	// Einmal Charaktere informieren �ber neue SMS
	$usernames = explode(',', $messager_partner);
	$usernames = array_map("trim", $usernames);
	$charas = array();

	foreach ($usernames as $username) {

		$username = $db->escape_string($username);
		$user = $db->query("SELECT username
		  FROM " . TABLE_PREFIX . "users
		  WHERE username = '" . $username . "'
		   ");
		$chara = $db->fetch_field($user, "username");
		$charas[] = $chara;


		$uid_query = $db->query("SELECT uid, username
		  FROM " . TABLE_PREFIX . "users
		  WHERE username = '" . $username . "'
		   ");
		$row = $db->fetch_array($uid_query);

		$charaname = $row['username'];
		$uid = $row['uid'];
		$from_uid = $mybb->user['uid'];

		if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
			$last_post = $db->fetch_field($db->query("SELECT pid FROM " . TABLE_PREFIX . "posts WHERE tid = '$thread[tid]' ORDER BY pid DESC LIMIT 1"), "pid");


			$alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('messager_newmessage');
			if ($alertType != NULL && $alertType->getEnabled() && $from_uid != $uid) {
				$alert = new MybbStuff_MyAlerts_Entity_Alert((int) $uid, $alertType, (int) $thread['tid']);
				$alert->setExtraDetails([
					'subject' => $subject,
					'lastpost' => $last_post
				]);
				MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);

			}
		}
	}


	// Informationen in die Posttabelle eintragen
	$new_message = array(
		"message_date" => $db->escape_string($message_date),
		"message_time" => $db->escape_string($message_time)
	);

	$db->update_query("posts", $new_message, "pid='{$pid}'");
}

function messager_editscene()
{
	global $mybb, $forum, $templates, $db, $post_errors, $thread, $lang, $select_kind, $messager_editmessage;
	//Die Sprachdatei
	$lang->load('messager');
	$messager_forum = $mybb->settings['messager_forum'];


	$forum['parentlist'] = "," . $forum['parentlist'] . ",";
	if (preg_match("/,$messager_forum,/i", $forum['parentlist'])) {

		$pid = $mybb->get_input('pid', MyBB::INPUT_INT);

		$post_query = $db->query("SELECT message_date, message_time
		FROM " . TABLE_PREFIX . "posts
		where pid = '" . $pid . "'
		");

		$pc = $db->fetch_array($post_query);

		// Wenn es sich um den ersten Post handelt
		if ($thread['firstpost'] == $pid) {
			$kind = array(
				"1" => "Einzelchat",
				"2" => "Gruppenchat"
			);


			if ($mybb->input['previewpost'] || $post_errors) {

				$messager_partner = htmlspecialchars($mybb->get_input('messager_partner'));
				foreach ($kind as $key => $value) {
					$checked = "";
					if ($key == $mybb->get_input('messager_kind')) {
						$checked = "selected";
					}
					$select_kind .= "<option value='{$key}' {$checked}>{$value}</option>";
				}
				$message_groupchattitle = htmlspecialchars($mybb->get_input('messager_groupchattitle'));
				$messager_grouppic = $mybb->get_input('messager_grouppic');
				$message_date = $mybb->get_input('message_date');
				$message_time = $mybb->get_input('message_time');
			} else {
				$messager_partner = htmlspecialchars($thread['messager_partner']);

				foreach ($kind as $key => $value) {
					$checked = "";
					if ($key == $thread['messager_kind']) {
						$checked = "selected";
					}
					$select_kind .= "<option value='{$key}' {$checked}>{$value}</option>";
				}
				$message_groupchattitle = $thread['messager_groupchattitle'];
				$messager_grouppic = $thread['messager_grouppic'];
				$message_date = htmlspecialchars($pc['message_date']);
				$message_time = htmlspecialchars($pc['message_time']);
			}


			eval ("\$messager_editmessage = \"" . $templates->get("messager_editmessage_firstpost") . "\";");
		} else {
			// Wenn es sich nur um einen Post handelt
			if ($mybb->input['previewpost'] || $post_errors) {
				$message_date = htmlspecialchars($mybb->get_input('message_date'));
				$message_time = htmlspecialchars($mybb->get_input('message_time'));
			} else {
				$message_date = htmlspecialchars($pc['message_date']);
				$message_time = htmlspecialchars($pc['message_time']);
			}


			eval ("\$messager_editmessage = \"" . $templates->get("messager_editmessage") . "\";");
		}


	}
}

// Datum auch anzeigen, wenn man Antwortet



function messager_editscene_do()
{
	global $db, $template, $mybb, $tid, $pid, $forum, $thread;

	$messager_kind = $mybb->input['messager_kind'];
	$messager_partner = $mybb->input['messager_partner'];
	$message_time = $mybb->input['message_time'];
	$message_date = $mybb->input['message_date'];
	$messager_groupchattitle = $mybb->input['messager_groupchattitle'];
	$messager_grouppic = $mybb->input['messager_grouppic'];

	if ($thread['firstpost'] == $pid) {
		// Informationen in die Posttabelle eintragen
		$edit_message = array(
			"message_date" => $db->escape_string($message_date),
			"message_time" => $db->escape_string($message_time)
		);

		$db->update_query("posts", $edit_message, "pid='{$pid}'");



		// Informationen in die Threadtabelle eintragen

		$edit_message = array(
			"messager_partner" => $db->escape_string($messager_partner),
			"messager_groupchattitle" => $db->escape_string($messager_groupchattitle),
			"messager_grouppic" => $db->escape_string($messager_grouppic),
			"messager_kind" => $db->escape_string($messager_kind)
		);


		$db->update_query("threads", $edit_message, "tid='{$tid}'");
	} else {
		// Informationen in die Posttabelle eintragen
		$edit_message = array(
			"message_date" => $db->escape_string($message_date),
			"message_time" => $db->escape_string($message_time)
		);

		$db->update_query("posts", $edit_message, "pid='{$pid}'");

	}
}


// Showthread auslesen

function messager_showthread()
{
	global $db, $mybb, $templates, $thread, $fid, $forum, $threads, $lang, $messager_showthread, $pid, $chat_icon, $messager_icons, $chat_title, $chat_infos;
	$lang->load('messager');

	$messager_forum = $mybb->settings['messager_forum'];
	$picfid = "fid" . $mybb->settings['messager_fid'];
	$messager_archive = 77;

	//einfach mal alles leeren
	$user1 = "";
	$icon_user1 = "";
	$user2 = "";
	$icon_user2 = "";
	$uid = 0;
	$chat_icon = "";

	$forum['parentlist'] = "," . $forum['parentlist'] . ",";
	if (preg_match("/,$messager_forum,/i", $forum['parentlist']) or preg_match("/,$messager_archive,/i", $forum['parentlist'])) {
		$charas = explode(",", $thread['messager_partner']);
		if ($thread['messager_kind'] == 1) {
			if (in_array($mybb->user['username'], $charas)) {
				foreach ($charas as $chara) {

					if ($mybb->user['username'] != $chara) {
						$get_user = get_user_by_username($chara);
						$uid = $get_user['uid'];


						if (!empty($uid)) {
							$chat_icon = $db->fetch_field($db->simple_select("userfields", "{$picfid}", "ufid = {$uid}"), $picfid);
						}
						if (empty($chat_icon)) {
							$chat_icon = "images/messager/nopic.png";
						}

						eval ("\$messager_icons  = \"" . $templates->get("messager_showthread_icons_single") . "\";");
						$charaname = $db->escape_string($chara);
						$charaname_explode = explode(" ", $charaname);
						$charaname = $charaname_explode[0];
						$chat_title = $charaname;
						$last_seen = max(array($get_user['lastactive'], $get_user['lastvisit']));
						$chat_infos = "zuletzt online " . my_date('relative', $last_seen);

					}
				}
			} else {
				$charalist = array();
				foreach ($charas as $charaname) {
					$charaname = $db->escape_string($charaname);
					$charaname_explode = explode(" ", $charaname);
					$charaname = $charaname_explode[0];

					$chara = $charaname;

					array_push($charalist, $chara);

				}

				//lasst uns die Charas wieder zusammenkleben :D
				$chat_title = implode(" & ", $charalist);


				// Informationen zu Account 1
				$get_user1 = get_user_by_username($charas[0]);
				$user1 = $get_user1['uid'];


				if (!empty($user1)) {
					$icon_user1 = $db->fetch_field($db->simple_select("userfields", "{$picfid}", "ufid = {$user1}"), $picfid);
				}
				if (empty($icon_user1)) {
					$icon_user1 = "images/messager/nopic.png";
				}

				// Informationen zu Account 2
				$get_user2 = get_user_by_username($charas[1]);
				$user2 = $get_user2['uid'];
				if (!empty($user2)) {
					$icon_user2 = $db->fetch_field($db->simple_select("userfields", "{$picfid}", "ufid = {$user2}"), $picfid);
				}
				if (empty($icon_user2)) {
					$icon_user2 = "images/messager/nopic.png";
				}

				eval ("\$messager_icons  = \"" . $templates->get("messager_showthread_icons_both") . "\";");





			}
			// Wenn es sich um einen Gruppenchat handelt
		} elseif ($thread['messager_kind'] == 2) {
			// alle Charas wieder auseinander nehmen

			$charalist = array();
			foreach ($charas as $charaname) {
				$charaname = $db->escape_string($charaname);
				$charaname_explode = explode(" ", $charaname);
				$charaname = $charaname_explode[0];

				$chara = $charaname;

				array_push($charalist, $chara);
			}

			//lasst uns die Charas wieder zusammenkleben :D
			$chat_infos = implode(", ", $charalist);

			$chat_title = $thread['messager_groupchattitle'];

			if (!empty($thread['messager_grouppic'])) {
				$chat_icon = "{$thread['messager_grouppic']}";
			} else {
				$chat_icon = "images/messager/nogrouppic.png";
			}
			eval ("\$messager_icons  = \"" . $templates->get("messager_showthread_icons_single") . "\";");

		}
		eval ("\$messager_showthread  = \"" . $templates->get("messager_showthread_infos") . "\";");
	}
}




// Antwort Review, wird mit angezeigt

function messager_threadreview()
{
	global $db, $mybb, $templates, $post, $messager_threadreview, $lang, $forum;
	$lang->load('messager');
	$messager_forum = $mybb->settings['messager_forum'];

	$forum['parentlist'] = "," . $forum['parentlist'] . ",";
	if (preg_match("/,$messager_forum,/i", $forum['parentlist'])) {




		$post['message_date'] = strtotime($post['message_date']);
		$post['message_date'] = date("d.m.Y", $post['message_date']);


		eval ("\$messager_threadreview = \"" . $templates->get("messager_threadreview") . "\";");
	}

}

// Postbit infos
// Postbit auslesen vom Datum und Uhrzeit
function messager_postbit(&$post)
{
	global $db, $mybb, $templates, $pid, $forum, $tid, $forum, $thread, $fid;

	$messager_forum = $mybb->settings['messager_forum'];
	$picfid = "fid" . $mybb->settings['messager_fid'];
	$onlineuser = $mybb->user['uid'];
	$chatmembers = explode(",", $thread['messager_partner']);

	$forum['parentlist'] = "," . $forum['parentlist'] . ",";
	if (preg_match("/,$messager_forum,/i", $forum['parentlist'])) {


		$post['message_date'] = strtotime($post['message_date']);
		$post['message_date'] = date("d.m.Y", $post['message_date']);

		if ($thread['messager_kind'] == 2) {
			if (!empty($post[$picfid])) {
				$fid = $post[$picfid];
			} else {
				$fid = "images/messager/nopic.png";
			}
			$post['account_pic'] = "<div class='message_account_pic'><img src='{$fid}'></div>";

			$post['profillink'] = "<div class='message_profile'>{$post['profilelink']}</div>";
		} else {
			$post['profillink'] = "";
			if (!empty($post[$picfid])) {
				$fid = $post[$picfid];
			} else {
				$fid = "images/messager/nopic.png";
			}
			$post['account_pic'] = "<div class='message_account_pic'><img src='{$fid}'></div>";
		}
		eval ("\$post['messager_messagedate'] = \"" . $templates->get("messager_messagedate") . "\";");
		$post['messager_css'] = "";
		$post['profilelink'] = "";
		$post['profillink'] = "";
		$status = "false";
		foreach ($chatmembers as $chatmember) {
			$get_user = get_user_by_username($chatmember, array('fields' => '*'));
			$get_user = $get_user['uid'];

			if ($onlineuser == $get_user) {
				
				$status = "true";
			}
		}

		if ($status == "true") {
			if ($post['username'] == $mybb->user['username']) {
				$post['messager_css'] = "message_own";
				$post['profilelink'] = "<a href='member.php?action=profile&uid={$post['uid']}'>{$post['username']}</a>";
				$post['profillink'] = "<div class='message_profile'></div>";
			} else {
				$post['messager_css'] = "message_other";
				$user = explode(" ", $post['username']);
				$post['username'] = $user[0];
				$post['profilelink'] = "<a href='member.php?action=profile&uid={$post['uid']}'>{$post['username']}</a>";
				$post['profillink'] = "<div class='message_profile'>{$post['profilelink']}</div>";
			}
		} else{
			if ($post['username'] == $thread['username']) {
				$user = explode(" ", $post['username']);
				$post['username'] = $user[0];
				$post['messager_css'] = "message_own";
				$post['profilelink'] = "<a href='member.php?action=profile&uid={$post['uid']}'>{$post['username']}</a>";
				$post['profillink'] = "<div class='message_profile'>{$post['profilelink']}</div>";
			} else {
				$post['messager_css'] = "message_other";
				$user = explode(" ", $post['username']);
				$post['username'] = $user[0];
				$post['profilelink'] = "<a href='member.php?action=profile&uid={$post['uid']}'>{$post['username']}</a>";
				$post['profillink'] = "<div class='message_profile'>{$post['profilelink']}</div>";
			}

		}

	}

}


//forumdisplay anzeige :D das auch alle Chats hübsch angezeigt werden

function messager_threads(&$thread)
{
	global $db, $mybb, $templates, $lang, $thread, $icon_user1, $icon_user2, $messager_icon, $message_partner;
	$lang->load('messager');

	$field = $mybb->settings['messager_fid'];
	$messager_icon = "";
	$all_charas = explode(",", $thread['messager_partner']);
	// Wenn es sich um einen Einzelchat handelt
	if ($thread['messager_kind'] == 1) {

		// Informationen zu Account 1
		$get_user1 = get_user_by_username($all_charas[0]);
		$user1 = $get_user1['uid'];
		if ($user1 != 0) {
			$icon_user1 = $db->fetch_field($db->simple_select("userfields", "fid" . $field, "ufid = {$user1}"), "fid" . $field);
		}
		if (empty($icon_user1)) {
			$icon_user1 = "images/messager/nopic.png";
		}

		// Informationen zu Account 2
		$get_user2 = get_user_by_username($all_charas[1]);
		$user2 = $get_user2['uid'];
		if ($user2 != 0) {
			$icon_user2 = $db->fetch_field($db->simple_select("userfields", "fid" . $field, "ufid = {$user2}"), "fid" . $field);
		}
		if (empty($icon_user2)) {
			$icon_user2 = "images/messager/nopic.png";
		}



		eval ("\$messager_icon = \"" . $templates->get("messager_forumdisplay_icon") . "\";");
	} else {

		if (!empty($thread['messager_grouppic'])) {
			$messager_icon = "<div class='messager_groupicon'><img src='{$thread['messager_grouppic']}'></div>";
		} else {
			$messager_icon = "<div class='messager_groupicon'><img src='images/messager/nogrouppic.png'></div>";
		}
	}

	$message_partner = implode(" & ", $all_charas);
}

// Globale Anzeige
function messager_globalchats()
{
	global $templates, $mybb, $db, $lang, $messager_chats, $chats;
	$lang->load('messager');

	$messager_forum = $mybb->settings['messager_forum'];
	$character = $db->escape_string($mybb->user['username']);



	$chats = 0;


	$select_chats = $db->query("SELECT t.lastposter, t.lastpost, p.message_date, t.messager_partner, t.subject, t.lastposteruid
	FROM " . TABLE_PREFIX . "threads t
	LEFT JOIN " . TABLE_PREFIX . "posts p
	ON (t.lastpost = p.dateline)
	LEFT JOIN " . TABLE_PREFIX . "forums f
	ON (t.fid = f.fid)
	WHERE f.fid LIKE '" . $messager_forum . "'
	AND t.messager_partner LIKE '%" . $character . "%'
	");

	while ($row = $db->fetch_array($select_chats)) {
		// Zähle, wie viele Chats vorhanden sind
		$chats++;
	}
	eval ("\$messager_chats = \"" . $templates->get("messager_globalchats") . "\";");

}

// zeige alle Chats

function messager_chats()
{
	global $mybb, $templates, $lang, $header, $headerinclude, $footer, $db, $pic, $chatname, $date, $lastmesage, $lastpost;
	$lang->load('messager');

	if ($mybb->get_input('action') == 'messager') {
		add_breadcrumb($lang->messager_misc, "misc.php?action=messager");

		$messager_forum = $mybb->settings['messager_forum'];
		$character = $db->escape_string($mybb->user['username']);
		$picfid = "fid" . $mybb->settings['messager_fid'];


		$select_chats = $db->query("SELECT *, t.lastposter, t.lastpost, t.messager_kind, t.messager_partner, t.subject, t.messager_groupchattitle, t.lastposteruid, p.pid
	FROM " . TABLE_PREFIX . "threads t
	LEFT JOIN " . TABLE_PREFIX . "posts p
	ON (t.lastpost = p.dateline)
	LEFT JOIN " . TABLE_PREFIX . "forums f
	ON (t.fid = f.fid)
	WHERE f.fid LIKE '" . $messager_forum . "'
	AND t.messager_partner LIKE '%" . $character . "%'
	");

		while ($message = $db->fetch_array($select_chats)) {
			$date = "";
			$chatname = "";
			$lastmessage = "";
			$pic = "";
			$tid = 0;

			$tid = $message['tid'];


			$all_charas = explode(",", $message['messager_partner']);


			if ($message['messager_kind'] == 1) {

				foreach ($all_charas as $chara) {
					if ($chara != $character) {
						$charainfos = get_user_by_username($chara);
						$uid = $charainfos['uid'];
						$charaquery = $db->simple_select("userfields", "*", "ufid ='$uid'");
						$charafid = $db->fetch_array($charaquery);

						if (!empty($charafid[$picfid])) {
							$pic = $charafid[$picfid];
						} else {
							$pic = "images/messager/nopic.png";
						}

						$chara = explode(" ", $chara);
						$chara = $chara[0];

						$chatname = "<a href='showthread.php?tid={$tid}&action=lastpost'>{$chara}</a>";
					}

					$messagequery = $db->simple_select(
						"posts",
						"*",
						"tid='{$tid}'",
						array(
							"order_by" => 'pid',
							"order_dir" => 'DESC',
							"limit" => 1
						)
					);

					$last_message = $db->fetch_array($messagequery);

					if ($last_message['username'] == $mybb->user['username']) {
						$check = "<i class='fa-solid fa-check-double'></i> ";
					}

					$lastmessage = $check . my_substr($last_message['message'], 0, 100) . "...";
				}
			} else {
				$chatname = "<a href='showthread.php?tid={$tid}&action=lastpost'>{$message['messager_groupchattitle']}</a>";
				if (!empty($message['messager_grouppic'])) {
					$pic = $message['messager_grouppic'];
				} else {
					$pic = "images/messager/nogrouppic.png";
				}


				$messagequery = $db->simple_select(
					"posts",
					"*",
					"tid='{$tid}'",
					array(
						"order_by" => 'pid',
						"order_dir" => 'DESC',
						"limit" => 1
					)
				);

				$last_message = $db->fetch_array($messagequery);


				$chara = explode(" ", $last_message['username']);
				$chara = $chara[0];
				$lastmessage = "{$chara}: " . my_substr($last_message['message'], 0, 100) . "...";
			}


			$date = strtotime($message['message_date']);
			$date = date("d.m.Y", $date) . ", " . $message['message_time'];


			eval ("\$your_chats.= \"" . $templates->get("messager_misc_chats") . "\";");
		}




		eval ("\$page = \"" . $templates->get("messager_misc") . "\";");
		output_page($page);
	}
}


// Benachrichtigung, wenn neue Nachricht
function messager_alerts()
{
	global $mybb, $lang;
	$lang->load('messager');
	/**
	 * Alert formatter for my custom alert type.
	 */
	class MybbStuff_MyAlerts_Formatter_NewMessagerchatFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
	{
		/**
		 * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
		 *
		 * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
		 *
		 * @return string The formatted alert string.
		 */
		public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
		{
			return $this->lang->sprintf(
				$this->lang->messager_newmessagerchat,
				$outputAlert['from_user'],
				$outputAlert['dateline']
			);
		}

		/**
		 * Init function called before running formatAlert(). Used to load language files and initialize other required
		 * resources.
		 *
		 * @return void
		 */
		public function init()
		{
		}

		/**
		 * Build a link to an alert's content so that the system can redirect to it.
		 *
		 * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
		 *
		 * @return string The built alert, preferably an absolute link.
		 */
		public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
		{
			return $this->mybb->settings['bburl'] . '/' . get_thread_link($alert->getObjectId());
		}
	}

	if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
		$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

		if (!$formatterManager) {
			$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
		}

		$formatterManager->registerFormatter(
			new MybbStuff_MyAlerts_Formatter_NewMessagerchatFormatter($mybb, $lang, 'messager_newmessagerchat')
		);
	}

	/**
	 * Alert formatter for my custom alert type.
	 */
	class MybbStuff_MyAlerts_Formatter_NewMessageFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
	{
		/**
		 * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
		 *
		 * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
		 *
		 * @return string The formatted alert string.
		 */
		public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
		{
			$alertContent = $alert->getExtraDetails();
			return $this->lang->sprintf(
				$this->lang->messager_newmessage,
				$outputAlert['from_user'],
				$alertContent['subject'],
				$outputAlert['dateline']
			);
		}


		/**
		 * Init function called before running formatAlert(). Used to load language files and initialize other required
		 * resources.
		 *
		 * @return void
		 */
		public function init()
		{
		}

		/**
		 * Build a link to an alert's content so that the system can redirect to it.
		 *
		 * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
		 *
		 * @return string The built alert, preferably an absolute link.
		 */
		public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
		{
			$alertContent = $alert->getExtraDetails();
			return $this->mybb->settings['bburl'] . '/' . get_post_link((int) $alertContent['lastpost'], (int) $alert->getObjectId()) . '#pid' . $alertContent['lastpost'];
		}
	}

	if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
		$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

		if (!$formatterManager) {
			$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
		}

		$formatterManager->registerFormatter(
			new MybbStuff_MyAlerts_Formatter_NewMessageFormatter($mybb, $lang, 'messager_newmessage')
		);
	}

}


?>
