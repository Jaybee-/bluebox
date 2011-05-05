<style>
.selected {
        color:blue;
	cursor: move;
}
td, th{
        border: 1px solid black;
        padding: 5px;
}
td.noborder {
	border: 0px;
	vertical-align: top;
}
.dragme {
	cursor: move;
}
</style>

<?php echo form::open_section('Route Outbound Calls'); ?>

<table width="100%">
<tr><td class="noborder" width="50%">
<table id=routelisttable border=1>
<tr><th>Destination</th><th>Trunk</th><th>Dial String</th><th>CLID Name</th><th>CLID Number</th></tr>
</table><br>
Drag and drop to re-order routes
</td><td class="noborder">
	<table>
		<tr><td>Destination</td><td><select id=routeselect><option></option><?php echo $destinationoptions?></select></td></tr>
		<tr><td>Trunk</td><td><select id=trunkselect><option></option><?php echo $trunkoptions?></select></td></tr>
		<tr><td>Dialstring</td><td><input id=dialstringselect value='$1'></td></tr>
		<tr><td>Default Caller-ID Name</td><td><input id=clid_name_select value=''></td></tr>
		<tr><td>Default Caller-ID Number</td><td><input id=clid_number_select value=''></td></tr>
		<tr><td colspan=2>
		<span onclick="update_entry(null);">Add</span> 
		<span onclick="clearform();">Clear</span> 
		<span id=selected_route style="display:none">
			<span onclick="update_entry(editing);">Update</span>
			<span onclick="erase(editing);">Delete</span> 
		</span>
		</td></tr>
	</table>
</td></tr></table>
<script>
editing=null;
dragging=null;
oldclass=null;
moved=0;

function erase(row) {
	clearform();
	row.parentNode.deleteRow(row.rowIndex);
}

function update_entry(row) {
	var routesel=document.getElementById("routeselect");
	var newentry={};
	newentry["destination"]=document.getElementById("routeselect").value;
	newentry["trunk"]=document.getElementById("trunkselect").value;
	newentry["dialstring"]=document.getElementById("dialstringselect").value;
	newentry["clid_name"]=document.getElementById("clid_name_select").value;
	newentry["clid_number"]=document.getElementById("clid_number_select").value;
	if (newentry["destination"]=="") { alert("Please select a destination"); return; }
	if (newentry["trunk"]=="") { alert("Please select a trunk for the calls to go to"); return; }
	update_row(row,newentry);
	renumber_fields();
	clearform();
}

function clearform() {
	if (editing!=null) {
		editing.className=oldclass;
	}
	editing=null;
	document.getElementById("routeselect").selectedIndex=0;
	document.getElementById("trunkselect").selectedIndex=0;
	document.getElementById("dialstringselect").value='$1';
	document.getElementById("clid_name_select").value='';
	document.getElementById("clid_number_select").value='';
	document.getElementById("selected_route").style.display="none";
}

function update_row(row,rowdata) {
	var rlt=document.getElementById("routelisttable");
	var doinsertlater;
	if (row==null) {
		row=document.createElement("tr");
		doinsertlater=1;
	} else {
		doinsertlater=0;
	}
	row.innerHTML="";
	row.className="dragme";
	row.onmousedown=function () {
		if (editing!=null) {
			editing.className=oldclass;
		}
		dragging=row;
		oldclass=dragging.className;
		dragging.className="selected";
		moved=0;
		return false;
	}

	row.onmousemove=function () {
		if ((dragging==row) || (dragging==null)) {
			return;
		}
		document.getElementById("selected_route").style.display="none";
		moved=1;
                if (dragging.rowIndex > row.rowIndex) {
                        row.parentNode.insertBefore(dragging,row);
                } else {
                        row.parentNode.insertBefore(dragging,row);
                        row.parentNode.insertBefore(row,dragging);
                }
		return false;
	}

	row.onclick=function () {
		if (moved>0) {
			return;
		}
		editing=row;
		oldclass=editing.className;
	    	editing.className="selected";
		document.getElementById("selected_route").style.display="inline";
		var inputs=row.getElementsByTagName("input");
		document.getElementById("routeselect").value=inputs[0].value;
		document.getElementById("trunkselect").value=inputs[1].value;
		document.getElementById("dialstringselect").value=inputs[2].value;
		document.getElementById("clid_name_select").value=inputs[3].value;
		document.getElementById("clid_number_select").value=inputs[4].value;
	}
	setcell(row,0,"destination",rowdata["destination"],destinations);
	setcell(row,1,"trunk",rowdata["trunk"],trunks);
	setcell(row,2,"dialstring",rowdata["dialstring"]);
	setcell(row,3,"clid_name",rowdata["clid_name"]);
	setcell(row,4,"clid_number",rowdata["clid_number"]);

	if (doinsertlater==1) {
		rlt.appendChild(row);
	}
	return row;
}


function setcell(row,position,name,value,lookup) {
	var cell=row.cells[position];
	var oVal=value;
	if (!cell) {
		cell=document.createElement("td");
		row.appendChild(cell);
	}
	if (lookup) {
		value=lookup[value]["name"];
	}
	cell.innerHTML="<input type=hidden name='simpleroute[0]["+name+"]' value="+oVal+">"+value;
}

// this takes all the inputs in the routelisttable, and replaces whatever is between the first [] with
// the number of the row - 1 (-1 because the header is row 0, so we want the first line of data, row 1,
// to be index 0)
function renumber_fields () {
	var inputs=document.getElementById("routelisttable").getElementsByTagName("input");;
	for (var input=0; input<inputs.length; input++) {
		var n=inputs[input].name;
		n=n.substr(0,n.indexOf('[')+1)+
			(inputs[input].parentNode.parentNode.rowIndex-1).toString()+
			n.substr(n.indexOf(']'));
		inputs[input].name=n;
	}
}

document.onmouseup = function () {
	dragging.className=oldclass;
	dragging=null;
}


var routes=<?php print json_encode($routes); ?>;
var destinations=<?php print json_encode($destinations); ?>;
var trunks=<?php print json_encode($trunks); ?>;
for (var route in <?php print json_encode($routes); ?>) {
	update_row(null,routes[route]);
}
renumber_fields();
</script>

<?php echo form::close_section(); ?>

