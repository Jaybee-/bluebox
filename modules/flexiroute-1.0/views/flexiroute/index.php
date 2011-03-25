<div id="flexiroute_header" class="update flexiroute module_header">

    <h2>FlexiRoute</h2>

</div>

<div id="simpleroutes_update_form" class="update simpleroute">


<style>
.selected {
        color:blue;
	cursor: move;
}
td, th{
        border: 1px solid black;
        padding: 5px;
}
.dragme {
	cursor: move;
}
td.noborder {
	border: 0px;
}
</style>
<table width="100%" border=0><tr>
<td class=noborder valign=top>
<table id="list" class=list>
<tr><th>Context</th><th>Destination</th><th>Trunk</th><th>Dialstring</th></tr>
<?php foreach ($froutes AS $routeid=>$route){ echo new View('flexiroute/route.mus',$route); } ?>
</table>
</td>
<td class=noborder width="50%" valign="top">
	<table>
		<tr><td>Context</td><td><select id=contextselect name="context"><option></option><?php echo $contextoptions?></select></td></tr>
		<tr><td>Trunk</td><td><select id=trunkselect name="trunk"><option></option><?php echo $trunkoptions?></select></td></tr>
		<tr><td>Destination</td><td><select id=routeselect name="route"><option></option><?php echo $routeoptions?></select></td></tr>
		<tr><td>Dialstring</td><td><input id=dialstringselect name=dialstring value='$1'></td></tr>
		<tr><td colspan=2>
		<a href=# onclick="addentry();">Add</a> 
		<a href=# onclick="clearform();">Clear</a> 
		<span id=selected_route style="display:none">
			<a href=# onclick="update();">Update</a> 
			<a href=# onclick="erase();">Delete</a> 
		</span>
		</td></tr>
	</table>
</td>
</tr></table>
    <?php echo form::open(); ?>
<input type=hidden id="submitdata" name=submitdata value="">
    <?php echo form::close(TRUE); ?>


<script>
document.onmouseup = mouseUp; 
var dragobject = null;
var oldclass = null;
var addedindex = 1;
var nextid = <?php print $nextid?>;
var moved = 0;
var routeinfo = <?php print json_encode($routes_hash); ?>;
var editing = null;

function update_submit() {
	var list=document.getElementById("list").rows;
	for (var index=1; index<list.length; index++) {
		if (routeinfo[list[index].id.substr(6)].priority!=index-1) {
			routeinfo[list[index].id.substr(6)]._newpriority=index-1;
		}
	}
	document.getElementById("submitdata").value=JSON.stringify(routeinfo);
}

function selectline(line) {
	if (moved==1) { // ignore clicks if they are drags
		move=0;
		return;
	}
	lineid=line.id.substr(6);
	document.getElementById('contextselect').selectedIndex=0;
	document.getElementById('dialstringselect').value=routeinfo[lineid].dialstring;
	setdropdown("trunkselect",routeinfo[lineid].trunk_id);
	setdropdown("routeselect",routeinfo[lineid].simple_route_id);
	setdropdown("contextselect",routeinfo[lineid].context_id);
	document.getElementById('selected_route').style.display="inline";
	editing=line;
	oldclass=editing.className;
    	editing.className="selected";
}

function setdropdown(listbox,key) {
	listbox=document.getElementById(listbox);
	for (var i=0; i<listbox.length; i++) {
		if (listbox.options[i].value==key) {
			listbox.selectedIndex = i;
			return;
		}
	}
}

function clearform() {
	document.getElementById('routeselect').selectedIndex=0;
	document.getElementById('contextselect').selectedIndex=0;
	document.getElementById('trunkselect').selectedIndex=0;
	document.getElementById('dialstringselect').value='$1';
	document.getElementById('selected_route').style.display="none";
	if (editing!=null) {
		editing.className=oldclass;
		editing=null;
	}
	return false;
}

function erase() {
	if (routeinfo[editing.id.substr(6)]._state=="new") {
		delete routeinfo[editing.id.substr(6)];
	} else {
		routeinfo[editing.id.substr(6)]._state="deleted";
	}
	editing.parentNode.deleteRow(editing.rowIndex);
	update_submit();
	
	clearform();
}

// This function has a lot in common with addentry - perhaps merge?
function update() {
	var dialstring=document.getElementById("dialstringselect").value;
	var contextselect=seloption("contextselect");
	var routeselect=seloption("routeselect");
	var trunkselect=seloption("trunkselect");
	for (var loop in routeinfo) {
		if ( (routeinfo[loop]["trunk_id"]==trunkselect.value) && (routeinfo[loop]["simple_route_id"]==routeselect.value) && (routeinfo[loop]["context_id"]==contextselect.value) && (loop!=editing.id.substr(6))) {
			alert("There is already a flexiroute with that context, route, and trunk.");
			return false;
		}
	}
	if (routeinfo[editing.id.substr(6)]._state=="unmodified") {
		routeinfo[editing.id.substr(6)]._state="modified";
	}
	
	routeinfo[editing.id.substr(6)].dialstring=dialstring;
	routeinfo[editing.id.substr(6)].trunk_id=trunkselect.value;
	routeinfo[editing.id.substr(6)]._TrunkName=trunkselect.text;
	routeinfo[editing.id.substr(6)].simple_route_id=routeselect.value;
	routeinfo[editing.id.substr(6)]._RouteName=routeselect.text;
	routeinfo[editing.id.substr(6)].context_id=contextselect.value;
	routeinfo[editing.id.substr(6)]._ContextName=contextselect.text;
	editing.cells[0].innerHTML=contextselect.text;
	editing.cells[1].innerHTML=routeselect.text;
	editing.cells[2].innerHTML=trunkselect.text;
	editing.cells[3].innerHTML=dialstring;
	update_submit();
	clearform();
}

function addentry() {
	if (missingfields()) {
		alert("Please make sure all the neccesary fields are filled in");
		return false;
	}

	var dialstring=document.getElementById("dialstringselect").value;
	var contextselect=seloption("contextselect");
	var routeselect=seloption("routeselect");
	var trunkselect=seloption("trunkselect");
	for (var loop in routeinfo) {
		if ( (routeinfo[loop]["trunk_id"]==trunkselect.value) && (routeinfo[loop]["simple_route_id"]==routeselect.value) && (routeinfo[loop]["context_id"]==contextselect.value)) {
			alert("There is already a flexiroute with that context, route, and trunk.");
			return false;
		}
	}
	var newrouteinfo=new Array();
	routeinfo[nextid]=new Object();
	routeinfo[nextid].flexiroute_id=nextid;
	routeinfo[nextid].dialstring=dialstring;
	routeinfo[nextid].trunk_id=trunkselect.value;
	routeinfo[nextid]._TrunkName=trunkselect.text;
	routeinfo[nextid].simple_route_id=routeselect.value;
	routeinfo[nextid]._RouteName=routeselect.text;
	routeinfo[nextid].context_id=contextselect.value;
	routeinfo[nextid]._ContextName=contextselect.text;
	routeinfo[nextid]._state="new";
	
	var list=document.getElementById("list");
	var row=document.createElement("tr");
	row.onclick=function () {selectline(row); }
	row.onmousemove=function () {mouseMove(row); }
	row.onmousedown=function () {mouseDown(row); return false;}
	row.id="route_"+nextid;
	appendtd(row,contextselect.text);
	appendtd(row,routeselect.text);
	appendtd(row,trunkselect.text);
	appendtd(row,dialstring);
	list.appendChild(row);

	nextid++;

	update_submit();
	clearform();
	return false;
}

function appendtd(row,html) {
	var cell=document.createElement("td");
	cell.innerHTML=html;
	row.appendChild(cell);
}

function seloption(listbox) {
	listbox=document.getElementById(listbox);
	return listbox.options[listbox.selectedIndex];
}

function missingfields() {
	if (document.getElementById("routeselect").value=="") { return true; }
	if (document.getElementById("trunkselect").value=="") { return true; }
	if (document.getElementById("contextselect").value=="") { return true; }
	return false;
}

function mouseDown(sender) {
	if (editing!=null) {
		editing.className=oldclass;
		editing=null;
	}
	moved=0;
	dragobject=sender;
	oldclass=dragobject.className;
	dragobject.className="selected";
}

function mouseUp(ev) {
    dragobject.className=oldclass;
    dragobject=null;
}

function mouseMove(sender){ 
        if (sender==dragobject) {
                return;
        }
	if (dragobject==null) {
		return;
	}
	moved=1;
        if ((sender!=dragobject) && (dragobject!=null)) {
                if (dragobject.rowIndex > sender.rowIndex) {
                        sender.parentNode.insertBefore(dragobject,sender);
                } else {
                        sender.parentNode.insertBefore(dragobject,sender);
                        sender.parentNode.insertBefore(sender,dragobject);
                }
        }
	update_submit();
}
</script>

