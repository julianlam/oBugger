<div class="navbar">
	<span style="font-size: 10px;">Navigation:</span><br />
	<a onclick="obugger.loadBugs();"><img src="<?=IMG_PATH?>buglist.svg" title="Show Buglist" /> Buglist</a> 
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
					<th data-sort="bugID" style="width: 50px; text-align: center;">bugID <span style="font-size: 9px; position: relative; top: -1px"></span></th>
					<th data-sort="name" style="max-width: 600px;">Name <span style="font-size: 9px; position: relative; top: -1px"></span></th>
					<th data-sort="state" style="text-align: center;">State <span style="font-size: 9px; position: relative; top: -1px"></span></th>
					<th data-sort="priority" style="text-align: center;">Priority <span style="font-size: 9px; position: relative; top: -1px"></span></th>
					<th data-sort="assignee" style="text-align: center;">Assigned To <span style="font-size: 9px; position: relative; top: -1px"></span></th>
					<th data-sort="fileDate" style="width: 135px; text-align: center;">Filed Date <span style="font-size: 9px; position: relative; top: -1px"></span></th>
	';
	if (in_array('w', $params['config']['anon_access']) || ($params['loggedIn'] && in_array('w', $params['config']['auth_access']))) echo '	<th style="width: 60px; text-align: center;">Actions</th>';
	echo '
				</tr>
			</thead>
			<tbody id="buglist_body"></tbody>
		</table>

		<h4>Closed Bugs</h4>
		<table id="closed_bugs">
			<thead>
				<tr style="text-align: left;">
					<th data-sort="bugID" style="width: 50px; text-align: center;">bugID <span style="font-size: 9px; position: relative; top: -1px"></span></th>
					<th data-sort="name" style="max-width: 600px;">Name <span style="font-size: 9px; position: relative; top: -1px"></span></th>
					<th data-sort="priority" style="text-align: center;">Priority <span style="font-size: 9px; position: relative; top: -1px"></span></th>
					<th data-sort="assignee" style="text-align: center;">Assigned To <span style="font-size: 9px; position: relative; top: -1px"></span></th>
					<th data-sort="fileDate" style="width: 135px; text-align: center;">Filed Date <span style="font-size: 9px; position: relative; top: -1px"></span></th>
	';
	if (in_array('w', $params['config']['anon_access']) || ($params['loggedIn'] && in_array('w', $params['config']['auth_access']))) echo '<th style="width: 60px; text-align: center;">Actions</th>';
	echo '
				</tr>
			</thead>
			<tbody id="closed_bugs_body"></tbody>
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
					<td><b>Filed By</b></td>
					<td id="view_bug_filer"></td>
				</tr>
				<tr>
					<td><b>Assigned To</b></td>
					<td id="view_bug_assignee"></td>
				</td>
				<tr>
					<td><b>Last Updated</b></td>
					<td id="view_bug_lastUpdate"></td>
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
	var accountID = 0;

	// Bug list rendering functions
	var obugger = {
		loggedIn: <?=$params['loggedIn']?>,
		account: <?=(isset($GLOBALS['obugger']['accountID']) ? '{ username: "' . $GLOBALS['obugger']['username'] . '", accountID: ' . $GLOBALS['obugger']['accountID'] . ' }' : '{}')?>,
		bugList: {
			open: <?=(htmlspecialchars(json_encode($params['bugs']), ENT_NOQUOTES) ? htmlspecialchars(json_encode($params['bugs']), ENT_NOQUOTES) : '{}')?>,
			closed: <?=(htmlspecialchars(json_encode($params['closed_bugs']), ENT_NOQUOTES) ? htmlspecialchars(json_encode($params['closed_bugs']), ENT_NOQUOTES) : '{}')?>
		},
		renderBug: function(bugID, location) {
			// Given a bug ID, renders its row entry into the appropriate list
			if (obugger.bugList.open[bugID]) { var container = $('buglist_body'); var state = 'open'; }
			else if (obugger.bugList.closed[bugID]) { var container = $('closed_bugs_body'); var state = 'closed'; }
			else return;	// Do nothing
			if (!location) location = 'bottom';
                        if (obugger.account.accountID && parseInt(obugger.bugList[state][bugID].assignedTo) == obugger.account.accountID) var myBug = true;
                        else myBug = false;

			if (state == 'open') {
				var fileDate = new Date(obugger.bugList[state][bugID].fileDate * 1000).format('%x, %X');
				var pretty_priority = '';
				var priority_split = obugger.bugList[state][bugID].priority.split('_').each(function(word, index) {
					pretty_priority += word.charAt(0).toUpperCase()+word.slice(1);
					if (index == 0) pretty_priority += ' ';
				});
				var pretty_state = '';
				var priority_state = obugger.bugList[state][bugID].state.split('_').each(function(word, index) {
					pretty_state += word.charAt(0).toUpperCase()+word.slice(1);
					if (index == 0) pretty_state += ' ';
				});
				new Element('tr', {
					id: 'bug_'+bugID,
					'data-bug-id': bugID,
					html: 
						'<td class="bugID">'+bugID+'</td>'+
						'<td>'+obugger.bugList[state][bugID]['name']+'</td>'+
						'<td class="state">'+pretty_state+'</td>'+
						'<td class="priority '+obugger.bugList[state][bugID].priority+'">'+pretty_priority+'</td>'+
						'<td class="assignee">'+(obugger.bugList[state][bugID].assignedTo > 0 ? (myBug ? '<b>' : '') + obugger.bugList[state][bugID].assignee + (myBug ? '</b>' : '') : '<span style="color: #ccc;">Unassigned</span>')+'</td>'+
						'<td class="filedDate">'+fileDate+'</td>'+
						((obugger.loggedIn) ?
							'<td class="actions">'+
								'<a data-action="edit"><img src="<?=IMG_PATH?>edit.svg" title="Edit Bug"></a> &nbsp; <a href="?action=closebug&bugID='+bugID+'"><img src="<?=IMG_PATH?>close.svg" title="Close Bug"></a>'+
							'</td>'
						:'')
				}).inject(container, location);
			}
			else {
				var fileDate = new Date(obugger.bugList[state][bugID].fileDate * 1000).format('%x, %X');
				var pretty_priority = '';
				var priority_split = obugger.bugList[state][bugID].priority.split('_').each(function(word, index) {
					pretty_priority += word.charAt(0).toUpperCase()+word.slice(1);
					if (index == 0) pretty_priority += ' ';
				});
				new Element('tr', {
					id: 'bug_'+bugID,
					'data-bug-id': bugID,
					html: 
						'<td class="bugID">'+bugID+'</td>'+
						'<td>'+obugger.bugList[state][bugID]['name']+'</td>'+
						'<td class="priority '+obugger.bugList[state][bugID].priority+'">'+pretty_priority+'</td>'+
						'<td class="assignee">'+(obugger.bugList[state][bugID].assignedTo > 0 ? (myBug ? '<b>' : '') + obugger.bugList[state][bugID].assignee + (myBug ? '</b>' : '') : '<span style="color: #ccc;">Unassigned</span>')+'</td>'+
						'<td class="filedDate">'+fileDate+'</td>'+
						((obugger.loggedIn) ?
							'<td class="actions">'+
								'<a data-action="edit"><img src="<?=IMG_PATH?>edit.svg" title="Edit Bug"></a> &nbsp; <a href="?action=closebug&bugID='+bugID+'"><img src="<?=IMG_PATH?>close.svg" title="Close Bug"></a>'+
							'</td>'
						:'')
				}).inject(container, 'bottom');
			}
		},
		clearBugs: function(list) {
			if (!list || list == 'open') $('buglist_body').empty();
			if (!list || list == 'closed') $('closed_bugs_body').empty();
		},
		loadBugs: function(list) {
			this.clearBugs();
			if (!list || list == 'open') Array.each(Object.keys(obugger.bugList.open), function(bugID) { obugger.renderBug(bugID); });
			if (!list || list == 'closed') Array.each(Object.keys(obugger.bugList.closed), function(bugID) { obugger.renderBug(bugID); });
			obugger.sortBugs('open', 'bugID', 'desc');
			obugger.sortBugs('closed', 'bugID', 'desc');
		},
		sortBugs: function(list, column, dir) {
			if (list == 'open') listTable = '#buglist';
			else listTable = '#closed_bugs';

			var glyph = document.body.getElement(listTable+' th[data-sort="'+column+'"] span');
			if (dir == 'asc') {
				// Clear existing glyphs (if present)
				$$(listTable+' th span').each(function(el) {
					el.empty();
				});

				glyph.innerHTML = '\u25b2';
				dir = 'asc';
			}
			else if (dir == 'desc') {
				// Clear existing glyphs (if present)
				$$(listTable+' th span').each(function(el) {
					el.empty();
				});

				glyph.innerHTML = '\u25bc';
				dir = 'desc';
			}
			else if (glyph.innerHTML == '' || glyph.innerHTML == '\u25bc') {
				// Clear existing glyphs (if present)
				$$(listTable+' th span').each(function(el) {
					el.empty();
				});

				glyph.innerHTML = '\u25b2';
				dir = 'asc';
			}
			else {
				// Clear existing glyphs (if present)
				$$(listTable+' th span').each(function(el) {
					el.empty();
				});

				glyph.innerHTML = '\u25bc';
				dir = 'desc';
			}

			var bugIDs = Object.keys(obugger.bugList[list]);
			switch(column) {
				case 'name':
					if (dir == 'asc') {
						bugIDs.sort(function(a, b) {
							if (obugger.bugList[list][a]['name'] < obugger.bugList[list][b]['name']) return -1;
							else return 1;
						});
					}
					if (dir == 'desc') {
						bugIDs.sort(function(a, b) {
							if (obugger.bugList[list][a]['name'] < obugger.bugList[list][b]['name']) return 1;
							else return -1;
						});
					}
				break;
				case 'bugID':
					if (dir == 'asc') {
						bugIDs.sort(function(a, b) {
							return obugger.bugList[list][a].bugID - obugger.bugList[list][b].bugID;
						});
					}
					if (dir == 'desc') {
						bugIDs.sort(function(a, b) {
							return obugger.bugList[list][b].bugID - obugger.bugList[list][a].bugID;
						});
					}
				break;
				case 'assignee':
					if (dir == 'asc') {
						bugIDs.sort(function(a, b) {
							if (obugger.bugList[list][a].assignee < obugger.bugList[list][b].assignee || !obugger.bugList[list][a].assignee) return -1;
							if (obugger.bugList[list][a].assignee > obugger.bugList[list][b].assignee || !obugger.bugList[list][b].assignee) return 1;
							else return 0;
						});
					}
					if (dir == 'desc') {
						bugIDs.sort(function(a, b) {
							if (obugger.bugList[list][a].assignee > obugger.bugList[list][b].assignee || !obugger.bugList[list][b].assignee) return -1;
							if (obugger.bugList[list][a].assignee < obugger.bugList[list][b].assignee || !obugger.bugList[list][a].assignee) return 1;
							else return -1;
						});
					}
				break;
				case 'priority':
					var states = {
						'very_low': 0,
						'low': 1,
						'medium': 2,
						'high': 3,
						'critical': 4
					}
					if (dir == 'asc') {
						bugIDs.sort(function(a, b) {
							return states[obugger.bugList[list][a].priority] - states[obugger.bugList[list][b].priority];
						});
					}
					if (dir == 'desc') {
						bugIDs.sort(function(a, b) {
							return states[obugger.bugList[list][b].priority] - states[obugger.bugList[list][a].priority];
						});
					}
				break;
				case 'fileDate':
					if (dir == 'asc') {
						bugIDs.sort(function(a, b) {
							return obugger.bugList[list][a].fileDate - obugger.bugList[list][b].fileDate;
						});
					}
					if (dir == 'desc') {
						bugIDs.sort(function(a, b) {
							return obugger.bugList[list][b].fileDate - obugger.bugList[list][a].fileDate;
						});
					}
				break;
				case 'state':
					var states = {
						open: 0,
						confirmed: 1,
						in_progress: 2,
						resolved: 3
					}
					if (dir == 'asc') {
						bugIDs.sort(function(a, b) {
							return states[obugger.bugList[list][a].state] - states[obugger.bugList[list][b].state];
						});
					}
					if (dir == 'desc') {
						bugIDs.sort(function(a, b) {
							return states[obugger.bugList[list][b].state] - states[obugger.bugList[list][a].state];
						});
					}
				break;
			}

			obugger.clearBugs(list);
			Array.each(bugIDs, function(bugID) { obugger.renderBug(bugID); });
		}
	}

	// Modals
	window.addEvent('domready', function() {
		// Load the retrieved bugs into the list
		obugger.loadBugs();

		// Initiate sorting events
		$$($('buglist'), $('closed_bugs')).addEvent('click:relay(th[data-sort])', function() {
			var column = this.get('data-sort');
			var table = this.getParent('table').get('id');
			if (table == 'buglist') var list = 'open';
			else if (table == 'closed_bugs') var list = 'closed';
			else return;

			obugger.sortBugs(list, column);
		});

		// Make bugs clickable
		$$($('buglist_body'), $('closed_bugs_body')).addEvent('click:relay(a[data-action="edit"])', function(event) {
			editBug(this.getParent('tr').getProperty('data-bug-id'));
		});
		$$($('buglist_body'), $('closed_bugs_body')).addEvent('click:relay(tr[data-bug-id])', function(event) {
			if (!event.target.match('img')) viewBug(this.getProperty('data-bug-id'));
		});

		accountID = <?=$params['loggedIn']?>;
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
				payload.filer = accountID;
				payload = JSON.encode(payload);
				new Request.JSON({
					url: '<?=APPLICATION_LINK?>ajax/bugs.php',
					onSuccess: function(data) {
						if (data['status'] == 1) {
							$('name').disabled = 0;
							$('description').disabled = 0;
							$('priority').disabled = 0;

							// Add bug to the local bug list
							var fileDate = new Date().format('%s');
							obugger.bugList.open[data['bugID']] = {
								assignedTo: "0",
								assignee: null,
								bugID: data['bugID'],
								description: data['description'],
								fileDate: fileDate,
								filedB: obugger.account.username,
								filedBy: obugger.account.accountID,
								lastUpdated: fileDate,
								name: data['name'],
								priority: $('priority').value,
								state: 'open'
							}
							obugger.renderBug(data['bugID'], 'top');

							$('name').value = '';
							$('description').value = '';
							$('priority').selectedIndex = 2;
							$('new_bug_toggler').focus();
							add_form.close();
						}
					}
				}).post({action: 'newbug', payload: payload});
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
				'<label for="priority">Priority</label>'+
				'<select id="priority">'+
				'<?php foreach($params['priorities'] as $priority) {echo '<option style="background: #' . $priority[1] . ';" value="' . strtolower(str_replace(" ", "_", $priority[0])) . '"' . ($priority[0] == "Medium" ? ' selected' : '') . ' id="priority_' . strtolower(str_replace(" ", "_", $priority[0])) . '">' . $priority[0] . '</option>';}?>' +
				'</select>'+
				' &nbsp; | &nbsp; <label for="state">State</label>'+
				'<select id="state">'+
				'<?php foreach($params['states'] as $state) {echo '<option id="state_' . strtolower(str_replace(" ", "_", $state)) . '" value="' . strtolower(str_replace(" ", "_", $state)) . '">' . $state . '</option>';}?>' +
				'</select>'+
				'<br><label for="assignee">Assigned To</label>'+
				'<select id="assignee"></select>'+
				' &nbsp; <input style="width: 150px; position: relative; top: 2px; font-weight: normal;" type="text" id="assignee_search" onkeyup="searchUsersByName($(\'assignee_search\').value, \'assignee\');"></input>'+
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
				$('state').disabled = 1;
				$('assignee').disabled = 1;
				$('assignee_search').disabled = 1;
				var payload = {};
				payload.name = $('name').value;
				payload.description = $('description').value;
				payload.priority = $('priority').value;
				payload.bugID = selectedBugID;
				payload.state = $('state').value;
				payload.assignee = ($('assignee').value > 0 ? $('assignee').value : 0);
				payload = JSON.encode(payload);
				new Request.JSON({
					url: '<?=APPLICATION_LINK?>ajax/bugs.php',
					onSuccess: function(data) {
						if (data['status'] == 1) {
							// Parse payload again
							payload = JSON.decode(payload);

							// Determine bug ownership
							var myBug = (obugger.account.accountID == payload.assignee ? true : false);

							// Re-enable form
							$('name').disabled = 0;
							$('description').disabled = 0;
							$('priority').disabled = 0;
							$('state').disabled = 0;
							$('assignee').disabled = 0;
							$('assignee_search').disabled = 0;

							// Update the bug list with the new values
							var cells = $('bug_'+selectedBugID).getElements('td');
							cells[1].innerHTML = $('name').value;
							cells[2].innerHTML = $('state_' + $('state').value).innerHTML;
							cells[3].set('class', 'priority ' + $('priority').value);
							cells[3].innerHTML = $('priority_'+$('priority').value).innerHTML;
							cells[4].innerHTML = ($('assignee').value > 0 ? (myBug ? '<b>' : '') + $('assignee_'+$('assignee').value).innerHTML + (myBug ? '</b>' : '') : '<span style="color: #ccc;">Unassigned</span>');

							// Also update the bug as stored locally
							if (payload.state != 'closed') {
								var bugType = 'open';
								var otherType = 'closed';
							}
							else {
								var bugType = 'closed';
								var otherType = 'open';
							}
							if (!obugger.bugList[bugType][payload.bugID]) {	// If bug is not where I am expecting it...
								// Bug was closed or re-opened, move to proper object
								obugger.bugList[otherType][payload.bugID] = Object.clone(obugger.bugList[bugType][payload.bugID]);
								delete obugger.bugList[bugType][payload.bugID];
								bugType = otherType;
							}
							var bugInfo = obugger.bugList[bugType][payload.bugID];
							bugInfo.name = payload.name;
							bugInfo.description = payload.description;
							bugInfo.priority = payload.priority;
							bugInfo.state = payload.state;
							bugInfo.assignedTo = payload.assignee;
							bugInfo.assignee = ($('assignee').value > 0 ? $('assignee_'+$('assignee').value).innerHTML : null);

							// Reset values in edit modal
							$('name').value = '';
							$('description').value = '';
							$('priority').selectedIndex = 2;
							$('assignee_search').value = '';
							window.location.hash = '';
							edit_form.close();
						}
					}
				}).post({action: 'modifybug', payload: payload, accountID: obugger.account.accountID});
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
							new Element('option', {
								value: data['data']['assignedTo'],
								id: 'assignee_'+data['data']['assignedTo'],
								html: data['data']['assignee'],
								selected: 1
							}).inject('assignee');
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
							$('view_bug_filer').innerHTML = (data['data']['filedBy'] && data['data']['filedBy'].length > 0 ? data['data']['filedBy'] : 'Unknown');
							$('view_bug_assignee').innerHTML = (data['data']['assignee'] && data['data']['assignee'].length > 0 ? data['data']['assignee'] : 'Unassigned');
							var fileDate = new Date(data['data']['fileDate'] * 1000).format('%x, %T');
							$('view_bug_fileDate').innerHTML = fileDate;
							var lastUpdate = new Date(data['data']['lastUpdated'] * 1000).format('%x, %T');
							$('view_bug_lastUpdate').innerHTML = lastUpdate;
							view_bug.header.getElement('.mux-dialog-header-title').innerHTML = data['data']['name'];
							view_bug.position();
						}
					}
				}).send('action=getbug&bugID='+selectedBugID);
			},
			onClose: function() {
				window.location.hash = '';
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

		// Load a bug up if there is already one selected in the URL
		if ($get('bugID')) viewBug($get('bugID'));
	});
</script>
