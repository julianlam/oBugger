<div class="navbar">
	<span style="font-size: 10px;">Navigation:</span><br />
	<a href="?action=showbugs"><img src="<?=IMG_PATH?>buglist.svg" title="Show Buglist" /> Buglist</a> 
	<?php
		if (isLoggedIn()) {
	?>
	| <a href="?action=newbug"><img src="<?=IMG_PATH?>newbug.svg" title="File new Bug" /> File new Bug</a>
	<?php
		}
	?>
</div>

<br clear="all" />
<br clear="all" />

<?php
	if ($_GET['action'] == 'showbugs') {
		echo '
			<h4>Open Bugs</h4>
			<table id="buglist">
				<tr style="text-align: left;">
					<th>bugID</th>
					<th>Name</th>
					<th>Description</th>
					<th>State</th>
					<th>Priority</th>
					<th>Filed Date</th>
		';
		if (isLoggedIn()) echo '<th>Actions</th>';
		echo '
				</tr>
		';
		foreach ($params['bugs'] as $bug) {
			$bug['priorityCSS'] = strtolower(str_replace(" ", "_", $bug['priority']));
			echo '
				<tr class="' . $bug['priorityCSS'] . '">
					<td>' . $bug['bugID'] . '</td>
					<td>' . stripslashes($bug['name']) . '</td>
					<td>' . nl2br(stripslashes($bug['description'])) . '</td>
					<td>' . ucwords($bug['state']) . '</td>
					<td>' . str_replace("_", " ", ucwords($bug['priority'])) . '</td>
					<td>' . date("r", $bug['fileDate']) . '</td>
			';
			if (isLoggedIn()) {
				echo '
						<td class="actions">
							<a href="?action=editbug&bugID=' . $bug['bugID'] . '"><img src="' . IMG_PATH . 'edit.svg" title="Edit Bug" /></a> &nbsp; <a href="?action=closebug&bugID=' . $bug['bugID'] . '"><img src="' . IMG_PATH . 'close.svg" title="Close Bug" /></a>
						</td>
				';
			}
			echo '
				</tr>
			';
		}
		echo '
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
		if (isLoggedIn()) echo '<th>Actions</th>';
		echo '
				</tr>
		';
		foreach ($params['closed_bugs'] as $bug) {
			echo '
				<tr style="background: #e0e0e0;">
					<td>' . $bug['bugID'] . '</td>
					<td>' . stripslashes($bug['name']) . '</td>
					<td>' . nl2br(stripslashes($bug['description'])) . '</td>
					<td>' . ucwords($bug['priority']) . '</td>
					<td>' . date("r", $bug['fileDate']) . '</td>
			';
			if (isLoggedIn()) {
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
	}
	else if ($_GET['action'] == 'newbug') {
?>
		<h4>File New Bug</h4>
		<form action="?action=filebug" method="POST">
			Name: <input type="text" name="name" /><br />
			Description:<br />
			<textarea style="width: 90%;" name="description"></textarea><br />
			Priority:
			<select name="priority">
<?php
		foreach($params['priorities'] as $priority) {
			echo '
				<option style="background: #' . $priority[1] . ';" value="' . strtolower(str_replace(" ", "_", $priority[0])) . '">' . $priority[0] . '</option>
			';
		}
?>
			</select><br />
			<small>Don't forget to include reproduction steps!</small>
			<input type="submit" value="File bug" />
		</form>
<?php
	}
	else if ($_GET['action'] == 'filebug') {
		if ($params['status']) {
			echo 'Bug filed successfully!<br /><br /><a href="?action=showbugs">Return to buglist</a>';
		}
		else {
			echo 'Something went wrong, bug not filed!';
		}
	}
	else if ($_GET['action'] == 'editbug') {
		if ($params['status']) {
			echo 'Changed saved!';
		}
		elseif ($params['bug']['bugID']) {
?>
			<h4>Edit Bug</h4>
			<form action="?action=editbug" method="POST">
				Name: <input type="text" name="name" value="<?=htmlentities(stripslashes($params['bug']['name']))?>" /><br />
				Description:<br />
				<textarea style="width: 90%;" name="description"><?=stripslashes($params['bug']['description'])?></textarea><br />
				Priority:
				<select name="priority">
<?php
		foreach($params['priorities'] as $priority) {
			echo '
				<option style="background: #' . $priority[1] . ';" value="' . strtolower(str_replace(" ", "_", $priority[0])) . '"' . ($params['bug']['priority'] == strtolower(str_replace(" ", "_", $priority[0])) ? ' selected' : '') . '>' . $priority[0] . '</option>
			';
		}
?>
				</select><br />
				State:
				<select name="state">
<?php
		foreach($params['states'] as $state) {
			echo '
				<option value="' . strtolower(str_replace(" ", "_", $state)) . '"' . ($params['bug']['state'] == strtolower(str_replace(" ", "_", $state)) ? ' selected' : '') . '>' . $state . '</option>
			';
		}
?>
				</select><br />
				<small>Don't forget to include reproduction steps!</small>
				<input type="hidden" name="bugID" value="<?=$params['bug']['bugID']?>" />
				<input type="hidden" name="save" value="1" />
				<input type="submit" value="Save changes" />
			</form>
<?php
		}
	}
	else if ($_GET['action'] == 'login') {
		if ($params['status']) echo 'Successfully logged in!';
		else echo 'Incorrect username and/or password';
	}
	else if ($_GET['action'] == 'logout') {
		if ($params['status']) echo 'Successfully logged out!';
	}
?>
