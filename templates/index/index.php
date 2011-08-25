<div class="navbar">
	<span style="font-size: 10px;">Navigation:</span><br />
	<a href="?action=showbugs"><img src="<?=IMG_PATH?>buglist.svg" title="Show Buglist" /> Buglist</a> 
	<?php
		if (in_array('w', $params['config']['anon_access']) || ($params['loggedIn'] && in_array('w', $params['config']['auth_access']))) {
	?>
	| <a href="#" id="new_bug_toggler"><img src="<?=IMG_PATH?>newbug.svg" title="File new Bug" /> File new Bug</a>
	<?php
		}
	?>
</div>

<br clear="all" />
<br clear="all" />

<?php
	echo '
		<h4>Open Bugs</h4>
	';
	echo '
		<table id="buglist">
			<thead>
				<tr style="text-align: left;">
					<th style="width: 50px; text-align: center;">bugID</th>
					<th>Name</th>
					<th style="text-align: center;">State</th>
					<th style="text-align: center;">Priority</th>
					<th>Filed Date</th>
	';
	if (in_array('w', $params['config']['anon_access']) || ($params['loggedIn'] && in_array('w', $params['config']['auth_access']))) echo '	<th style="width: 60px; text-align: center;">Actions</th>';
	echo '
				</tr>
			</thead>
			<tbody id="buglist_body">
	';
	foreach ($params['bugs'] as $bug) {
		$bug['priorityCSS'] = strtolower(str_replace(" ", "_", $bug['priority']));
		echo '
				<tr id="bug_' . $bug['bugID'] . '">
					<td class="bugID" onclick="viewBug(' . $bug['bugID'] . ');">' . $bug['bugID'] . '</td>
					<td onclick="viewBug(' . $bug['bugID'] . ');">' . (strlen($bug['name']) > 64 ? substr(stripslashes($bug['name']), 0, 61) . '...' : stripslashes($bug['name'])) . '</td>
					<td class="state" onclick="viewBug(' . $bug['bugID'] . ');">' . ucwords(str_replace("_", " ", $bug['state'])) . '</td>
					<td class="priority ' . $bug['priorityCSS'] . '" onclick="viewBug(' . $bug['bugID'] . ');">' . ucwords(str_replace("_", " ", $bug['priority'])) . '</td>
					<td onclick="viewBug(' . $bug['bugID'] . ');">' . date("H:i:s, j/n/Y", $bug['fileDate']) . '</td>
		';
		if (in_array('w', $params['config']['anon_access']) || ($params['loggedIn'] && in_array('w', $params['config']['auth_access']))) {
			echo '
					<td class="actions">
						<a onclick="editBug(' . $bug['bugID'] . ');"><img src="' . IMG_PATH . 'edit.svg" title="Edit Bug" /></a> &nbsp; <a href="?action=closebug&bugID=' . $bug['bugID'] . '"><img src="' . IMG_PATH . 'close.svg" title="Close Bug" /></a>
					</td>
			';
		}
		echo '
				</tr>
		';
	}
	echo '
			</tbody>
		</table>

		<h4>Closed Bugs</h4>
		<table id="closed_bugs">
			<thead>
				<tr style="text-align: left;">
					<th style="width: 50px; text-align: center;">bugID</th>
					<th>Name</th>
					<th style="text-align: center;">Priority</th>
					<th>Filed Date</th>
	';
	if (in_array('w', $params['config']['anon_access']) || ($params['loggedIn'] && in_array('w', $params['config']['auth_access']))) echo '<th>Actions</th>';
	echo '
				</tr>
			</thead>
			<tbody>
	';
	foreach ($params['closed_bugs'] as $bug) {
		echo '
				<tr id="bug_' . $bug['bugID'] . '">
					<td onclick="viewBug(' . $bug['bugID'] . ');">' . $bug['bugID'] . '</td>
					<td onclick="viewBug(' . $bug['bugID'] . ');">' . (strlen($bug['name']) > 64 ? substr(stripslashes($bug['name']), 0, 61) . '...' : stripslashes($bug['name'])) . '</td>
					<td class="priority" onclick="viewBug(' . $bug['bugID'] . ');">' . ucwords(str_replace("_", " ", $bug['priority'])) . '</td>
					<td onclick="viewBug(' . $bug['bugID'] . ');">' . date("H:i:s, j/n/Y", $bug['fileDate']) . '</td>
		';
		if (in_array('w', $params['config']['anon_access']) || ($params['loggedIn'] && in_array('w', $params['config']['auth_access']))) {
			echo '
					<td class="actions">
						<a href="?action=reopenbug&bugID=' . $bug['bugID'] . '"><img src="' . IMG_PATH . 'reopen.svg" title="Re-Open Bug" /></a>
					</td>
			';
		}
		echo '
				</tr>
			</tbody>
		';
	}
	echo '
		</table>
	';

	// Pre-defined HTML for inclusion in bug viewer javascript
	$view_bug_html = '
		<div style="padding: 0 10px; font-size: 12px; max-height: 450px; width: 600px; overflow: auto;">
			<p id="view_bug_desc"></p>
			<hr />
			<table>
				<tr>
					<td><b>Priority</b></td>
					<td id="view_bug_priority"></td>
				</tr>
				<tr>
					<td><b>State</b></td>
					<td id="view_bug_state"></td>
				</tr>
				<tr>
					<td><b>Filed Date</b></td>
					<td id="view_bug_fileDate"></td>
				</tr>
			</table>
		</div>
	';

	$settings_modal_html = '
		Username: <span class="immutable" id="settings_username"></span><br /><br />
		Password: <button type="button" class="mux-button mux-button-rectangle" id="change_password" onclick="password_change_modal.open();">Change Password</button>
	';

	$password_change_html = '
		Current Password: <input class="borderless" type="password" id="current_password" /><br /><br />
		New Password: <input class="borderless" type="password" id="new_password" /><br /><br />
		... and again: <input class="borderless" type="password" id="repeat_password" />
	';
?>

<script type="text/javascript">
	var add_form = null;
	var selectedBugID = 0;
	window.addEvent('domready', function() {
		add_form = new MUX.Dialog({
			modal: true,
			autoOpen: false,
			resizable: false,
			size: {x: 760},
			loader: 'none',
			title: 'File New Bug',
			content: new Element('div', {
				html: '<input type="text" id="name" title="Name"><br>' +
				'<textarea id="description" title="Description"></textarea><br>' +
				'<label id="priority_label" for="priority">Priority</label>'+
				'<select id="priority">'+
				'<?php foreach($params['priorities'] as $priority) {echo '<option style="background: #' . $priority[1] . ';" value="' . strtolower(str_replace(" ", "_", $priority[0])) . '"' . ($priority[0] == "Medium" ? ' selected' : '') . '>' . $priority[0] . '</option>';}?>' +
				'</select><br>'+
				'<small>Don&apos;t forget to include reproduction steps!</small>',
				'class': 'bug_form',
				id: 'add_bug_form'
			}),
			buttons: [{
				title: 'Cancel',
				style: 'link',
				click: 'close'
			},{
				title: 'File New Bug',
				click: 'submit'
			}],
			onSubmit: function() {
				$('name').disabled = 1;
				$('description').disabled = 1;
				$('priority').disabled = 1;
				var payload = {};
				payload.name = encodeURIComponent(htmlEntities($('name').value));
				payload.description = encodeURIComponent(htmlEntities($('description').value));
				payload.priority = $('priority').value;
				payload = JSON.encode(payload);
				new Request.JSON({
					url: '<?=APPLICATION_LINK?>ajax/bugs.php',
					onSuccess: function(data) {
						if (data['status'] == 1) {
							$('name').disabled = 0;
							$('description').disabled = 0;
							$('priority').disabled = 0;
							new Element('tr', {
								html:   '<td onclick="viewBug('+data['bugID']+');">'+data['bugID']+'</td>'+
									'<td onclick="viewBug('+data['bugID']+');">' + (data['name'].length > 64 ? data['name'].substr(0, 61) + '...' : data['name']) + '</td>'+
									'<td onclick="viewBug('+data['bugID']+');">Open</td>'+
									'<td onclick="viewBug('+data['bugID']+');">'+data['priority']+'</td>'+
									'<td onclick="viewBug('+data['bugID']+');">'+data['date']+'</td>'+
									'<td class="actions"><a onclick="editBug(' + data['bugID'] + ');"><img src="<?=IMG_PATH?>edit.svg" title="Edit Bug"></a> &nbsp; <a href="?action=closebug&bugID=' + data['bugID'] + '"><img src="<?=IMG_PATH?>close.svg" title="Close Bug"></a></td>',
								'class': data['priority'].replace(" ", "_").toLowerCase()
							}).inject('buglist_body', 'top');
							$('name').value = '';
							$('description').value = '';
							$('priority').selectedIndex = 2;
							$('new_bug_toggler').focus();
							add_form.close();
						}
					}
				}).send('action=newbug&payload='+payload);
			},
			onOpen: function() {
				var addFormName = new OverText('name', {
					positionOptions: {
						offset: {
							'y': 2
						}
					},
					'poll': true,
					'wrap': true
				});
				var addFormDesc = new OverText('description', {
					positionOptions: {
						offset: {
							'y': 2
						}
					},
					'poll': true,
					'wrap': true
				});
				addFormName.reposition();
				addFormDesc.reposition();
			}
		});
		edit_form = new MUX.Dialog({
			modal: true,
			autoOpen: false,
			resizable: false,
			size: {x: 760},
			loader: 'none',
			title: 'Modify Bug',
			content: new Element('div', {
				html: '<input type="text" id="name" title="Name"><br>' +
				'<textarea id="description" title="Description"></textarea><br>' +
				'<label id="priority_label" for="priority">Priority</label>'+
				'<select id="priority">'+
				'<?php foreach($params['priorities'] as $priority) {echo '<option style="background: #' . $priority[1] . ';" value="' . strtolower(str_replace(" ", "_", $priority[0])) . '"' . ($priority[0] == "Medium" ? ' selected' : '') . '>' . $priority[0] . '</option>';}?>' +
				'</select>'+
				' &nbsp; | &nbsp; <label id="state_label" for="state">State</label>'+
				'<select id="state">'+
				'<?php foreach($params['states'] as $state) {echo '<option value="' . strtolower(str_replace(" ", "_", $state)) . '">' . $state . '</option>';}?>' +
				'</select>'+
				'<br>',
				'class': 'bug_form',
				id: 'edit_bug_form'
			}),
			buttons: [{
				title: 'Cancel',
				style: 'link',
				click: 'close'
			},{
				title: 'Modify Bug',
				click: 'submit'
			}],
			onSubmit: function() {
				$('name').disabled = 1;
				$('description').disabled = 1;
				$('priority').disabled = 1;
				var payload = {};
				payload.name = encodeURIComponent(htmlEntities($('name').value));
				payload.description = encodeURIComponent(htmlEntities($('description').value));
				payload.priority = $('priority').value;
				payload.bugID = selectedBugID;
				payload.state = $('state').value;
				payload = JSON.encode(payload);
				new Request.JSON({
					url: '<?=APPLICATION_LINK?>ajax/bugs.php',
					onSuccess: function(data) {
						if (data['status'] == 1) {
							// Re-enable form
							$('name').disabled = 0;
							$('description').disabled = 0;
							$('priority').disabled = 0;

							// Update the bug list with the new values
							var cells = $('bug_'+selectedBugID).getElements('td');
							cells[1].innerHTML = ($('name').value.length > 64 ? $('name').value.substr(0,61) + '...' : $('name').value);
							if ($('bug_'+selectedBugID).get('class') != $('priority').value) $('bug_'+selectedBugID).set('class', $('priority').value);

							edit_form.close();
						}
					}
				}).send('action=modifybug&payload='+payload);
			},
			onOpen: function() {
				new Request.JSON({
					url: '<?=APPLICATION_LINK?>ajax/bugs.php',
					onSuccess: function(data) {
						if (data['status'] == 1) {
							$('name').value = htmlEntityDecode(data['data']['name']);
							$('description').value = htmlEntityDecode(data['data']['description']);
							for(i=0;i < $('priority').length;i++) {
								if ($('priority').options[i].value == data['data']['priority']) $('priority').selectedIndex = i;
							};
							for(i=0;i < $('state').length;i++) {
								if ($('state').options[i].value == data['data']['state']) $('state').selectedIndex = i;
							};
						}
					}
				}).send('action=getbug&bugID='+selectedBugID+'&markdown=0');
				var editFormName = new OverText('name', {
					positionOptions: {
						offset: {
							'y': 2
						}
					},
					'poll': true,
					'wrap': true
				});
				var editFormDesc = new OverText('description', {
					positionOptions: {
						offset: {
							'y': 2
						}
					},
					'poll': true,
					'wrap': true
				});
				editFormName.reposition();
				editFormDesc.reposition();
			}
		});

		if ($('new_bug_toggler')) $('new_bug_toggler').addEvent('click', function() {
			add_form.open();
		});

		view_bug = new MUX.Dialog({
			loader: 'none',
			title: 'Title',
			autoOpen: false,
			content: '<?=APPLICATION_LINK?>ajax/bugs.php?action=render_bug_viewer',
			onOpen: function() {
				view_bug.header.getElement('.mux-dialog-header-title').innerHTML = '';
				new Request.JSON({
					url: '<?=APPLICATION_LINK?>ajax/bugs.php',
					onSuccess: function(data) {
						if (data['status'] == 1) {
							view_bug.content.innerHTML = <?=json_encode($view_bug_html)?>;
							$('view_bug_desc').innerHTML = data['data']['description'].replace(/\n/g, '<br>').replace(/%0A/g, '<br>');
							$('view_bug_priority').innerHTML = data['data']['priority_nice'];
							$('view_bug_state').innerHTML = data['data']['state_nice'];
							var fileDate = new Date(data['data']['fileDate'] * 1000);
							$('view_bug_fileDate').innerHTML = fileDate;
							view_bug.header.getElement('.mux-dialog-header-title').innerHTML = data['data']['name'];
							view_bug.position();
						}
					}
				}).send('action=getbug&bugID='+selectedBugID);
			},
			onClose: function() {
				view_bug.content.innerHTML = '<div style="margin-top: 4.5em; font-weight: bold; text-align: center; font-size: 12px;">Loading &nbsp; <img style="position: relative; top: 4px;" src="<?=IMG_PATH?>loader.gif">';
			}
	<?php
	if (in_array('w', $params['config']['anon_access']) || ($params['loggedIn'] && in_array('w', $params['config']['auth_access']))) {
		echo ',
			buttons: [{
				title: \'Modify this bug\',
				style: \'link\',
				click: \'submit\'
			}],
			onSubmit: function() {
				editBug(selectedBugID);
				view_bug.close();
			}
		';
	}
	?>
		});

		settings_modal = new MUX.Dialog({
			loader: 'none',
			title: 'Account Settings',
			autoOpen: false,
			content: new Element('div', {
				html: <?=json_encode($settings_modal_html)?>,
				style: 'padding: 10px; font-size: 12px;'
			}),
			onOpen: function() {
				new Request.JSON({
					url: '<?=APPLICATION_LINK?>ajax/user.php',
					onSuccess: function(data) {
						if (data['status'] == 1) {
							$('settings_username').innerHTML = data['data']['username'];
						}
					}
				}).send('action=getSettings');
			}
		});

		password_change_modal = new MUX.Dialog({
			loader: 'none',
			title: 'Change Password',
			autoOpen: false,
			content: new Element('div', {
				html: <?=json_encode($password_change_html)?>,
				style: 'padding: 10px; font-size: 12px;'
			}),
			onOpen: function() {
				settings_modal.close();
			},
			onSubmit: function() {
				if ($('new_password').value == $('repeat_password').value) {
					payload = {};
					payload.current = $('current_password').value;
					payload.new = $('new_password').value;
					new Request.JSON({
						url: '<?=APPLICATION_LINK?>ajax/user.php',
						onSuccess: function(data) {
							if (data['status'] == 1) {
								password_change_modal.close();
							}
						}
					}).send('action=changePassword&payload='+JSON.encode(payload));
				}
			},
			onClose: function() {
				settings_modal.open();
			},
			buttons: [{
				title: 'Change Password',
				click: 'submit'
			}]
		});
	});
</script>
