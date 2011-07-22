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
					<th>bugID</th>
					<th>Name</th>
					<th>Description</th>
					<th>State</th>
					<th>Priority</th>
					<th>Filed Date</th>
	';
	if (in_array('w', $params['config']['anon_access']) || ($params['loggedIn'] && in_array('w', $params['config']['auth_access']))) echo '	<th>Actions</th>';
	echo '
				</tr>
			</thead>
			<tbody id="buglist_body">
	';
	foreach ($params['bugs'] as $bug) {
		$bug['priorityCSS'] = strtolower(str_replace(" ", "_", $bug['priority']));
		echo '
				<tr id="bug_' . $bug['bugID'] . '" class="' . $bug['priorityCSS'] . '">
					<td onclick="viewBug(' . $bug['bugID'] . ');">' . $bug['bugID'] . '</td>
					<td onclick="viewBug(' . $bug['bugID'] . ');">' . (strlen($bug['name']) > 32 ? substr(stripslashes($bug['name']), 0, 29) . '...' : stripslashes($bug['name'])) . '</td>
					<td onclick="viewBug(' . $bug['bugID'] . ');">' . (strlen($bug['description']) > 65 ? substr(str_replace("\n", " / ", trim(stripslashes($bug['description']))),0,63) . '...' : str_replace("\n", " / ", trim(stripslashes($bug['description'])))) . '</td>
					<td onclick="viewBug(' . $bug['bugID'] . ');">' . ucwords(str_replace("_", " ", $bug['state'])) . '</td>
					<td onclick="viewBug(' . $bug['bugID'] . ');">' . ucwords(str_replace("_", " ", $bug['priority'])) . '</td>
					<td>' . date("r", $bug['fileDate']) . '</td>
		';
		if (in_array('w', $params['config']['anon_access']) || ($params['loggedIn'] && in_array('w', $params['config']['auth_access']))) {
			echo '
					<td class="actions">
						<a href="#" onclick="editBug(' . $bug['bugID'] . ');"><img src="' . IMG_PATH . 'edit.svg" title="Edit Bug" /></a> &nbsp; <a href="?action=closebug&bugID=' . $bug['bugID'] . '"><img src="' . IMG_PATH . 'close.svg" title="Close Bug" /></a>
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
			<tr style="text-align: left;">
				<th>bugID</th>
				<th>Name</th>
				<th>Description</th>
				<th>Priority</th>
				<th>Filed Date</th>
	';
	if (in_array('w', $params['config']['anon_access']) || ($params['loggedIn'] && in_array('w', $params['config']['auth_access']))) echo '<th>Actions</th>';
	echo '
			</tr>
	';
	foreach ($params['closed_bugs'] as $bug) {
		echo '
			<tr id="bug_' . $bug['bugID'] . '" style="background: #e0e0e0;">
				<td onclick="viewBug(' . $bug['bugID'] . ');">' . $bug['bugID'] . '</td>
				<td onclick="viewBug(' . $bug['bugID'] . ');">' . (strlen($bug['name']) > 32 ? substr(stripslashes($bug['name']), 0, 29) . '...' : stripslashes($bug['name'])) . '</td>
				<td onclick="viewBug(' . $bug['bugID'] . ');">' . (strlen($bug['description']) > 65 ? substr(nl2br(stripslashes($bug['description'])),0,63) . '...' : nl2br(stripslashes($bug['description']))) . '</td>
				<td onclick="viewBug(' . $bug['bugID'] . ');">' . ucwords(str_replace("_", " ", $bug['priority'])) . '</td>
				<td onclick="viewBug(' . $bug['bugID'] . ');">' . date("r", $bug['fileDate']) . '</td>
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
				payload.name = $('name').value;
				payload.description = $('description').value;
				payload.priority = $('priority').value;
				payload = JSON.encode(payload);
				new Request.JSON({
					url: '<?=APPLICATION_LINK?>ajax/bugs.php',
					onSuccess: function(data) {
						if (data['status'] == 1) {
							$('name').disabled = 0;
							$('description').disabled = 0;
							$('priority').disabled = 0;
							add_form.close();
							new Element('tr', {
								html:   '<td>'+data['bugID']+'</td>'+
									'<td>'+data['name']+'</td>'+
									'<td>'+data['description']+'</td>'+
									'<td>Open</td>'+
									'<td>'+data['priority']+'</td>'+
									'<td>'+data['date']+'</td>'+
									'<td class="actions"><a href="?action=editbug&bugID=' + data['bugID'] + '"><img src="<?=IMG_PATH?>edit.svg" title="Edit Bug"></a> &nbsp; <a href="?action=closebug&bugID=' + data['bugID'] + '"><img src="<?=IMG_PATH?>close.svg" title="Close Bug"></a></td>',
								'class': data['priority'].replace(" ", "_").toLowerCase()
							}).inject('buglist_body', 'top');
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
				payload.name = $('name').value;
				payload.description = $('description').value;
				payload.priority = $('priority').value;
				payload.bugID = selectedBugID;
				payload.state = $('state').value;
				payload = JSON.encode(payload);
				new Request.JSON({
					url: '<?=APPLICATION_LINK?>ajax/bugs.php',
					onSuccess: function(data) {
						if (data['status'] == 1) {
							$('name').disabled = 0;
							$('description').disabled = 0;
							$('priority').disabled = 0;
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
							$('name').value = data['data']['name'];
							$('description').value = data['data']['description'];
							for(i=0;i < $('priority').length;i++) {
								if ($('priority').options[i].value == data['data']['priority']) $('priority').selectedIndex = i;
							};
							for(i=0;i < $('state').length;i++) {
								if ($('state').options[i].value == data['data']['state']) $('state').selectedIndex = i;
							};
						}
					}
				}).send('action=getbug&bugID='+selectedBugID);
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
							$('view_bug_desc').innerHTML = data['data']['description'].replace(/\n/g, '<br>');
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
			},
			buttons: [{
				title: 'Modify this bug',
				style: 'link',
				click: 'submit'
			}],
			onSubmit: function() {
				editBug(selectedBugID);
			}
		});
	});

	function viewBug(bugID) {
		selectedBugID = bugID;
		view_bug.open();
	}

	function editBug(bugID) {
		selectedBugID = bugID;
		edit_form.open();
	}
</script>
