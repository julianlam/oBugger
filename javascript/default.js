function viewBug(bugID) {
	selectedBugID = bugID;
	view_bug.open();
}

function editBug(bugID) {
	selectedBugID = bugID;
	edit_form.open();
}

function sortColumnByPriority(table) {
	var rows = $(table).getElements('tr');
	
	Object.each(rows, function(row, key) {
		var cells = row.getElements('td');
		alert(cells[0].innerHTML);
		alert(cells[4].innerHTML);
	});
}

function htmlEntities(str) {
	return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/\n/g, '%0A');
}

function htmlEntityDecode(str) {
	return String(str).replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/%0A/g, '\n');
}

