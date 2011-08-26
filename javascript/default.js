function $get(key,url){
	if(arguments.length < 2) url = location.href;
	if(arguments.length > 0 && key != ""){
		if(key == "#"){
			var regex = new RegExp("[#]([^$]*)");
		} else if(key == "?"){
			var regex = new RegExp("[?]([^#$]*)");
		} else {
			var regex = new RegExp("[?&]"+key+"=([^&#]*)");
		}
		var results = regex.exec(url);
		return (results == null )? "" : results[1];
	} else {
		url = url.split("?");
		var results = {};
			if(url.length > 1){
				url = url[1].split("#");
				if(url.length > 1) results["hash"] = url[1];
				url[0].split("&").each(function(item,index){
					item = item.split("=");
					results[item[0]] = item[1];
				});
			}
		return results;
	}
}

function viewBug(bugID) {
	window.location.hash = '?bugID='+bugID;
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
	return String(str).replace(/"/g, '&quot;').replace(/\n/g, '%0A');
}

function htmlEntityDecode(str) {
	return String(str).replace(/&quot;/g, '"').replace(/%0A/g, '\n');
}

