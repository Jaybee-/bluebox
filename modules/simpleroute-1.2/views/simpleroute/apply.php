<style>
td, th{
        border: 1px solid black;
        padding: 5px;
}
</style>

<?php echo form::open_section('Route Outbound Calls'); ?>

<table id=routelisttable border=1>
<tr><th>Destination</th><th>Trunk</th><th>Dial String</th><th>CLID Name</th><th>CLID Number</th><th>Controls</th></tr>
</table><br>
		<?php echo html::anchor('/numbermanager/create/FeatureCodeNumber','<span>' .__('Test link add featurecode') .'</span>', array('class' => 'qtipAjaxForm')); ?>
		<?php echo html::anchor('/simpleroute/route_editor/','<span>' .__('Add New Route') .'</span>', array('class' => 'qtipAjaxForm')); ?>
<script>

function erase(row) {
	while (!(row instanceof HTMLTableRowElement)) {
		row=row.parentNode;
	}
	row.parentNode.deleteRow(row.rowIndex);
	renumber_fields();
}

// Row is row to move. up=true to move up, false to move down.
function moverow(row,up) {
	while (!(row instanceof HTMLTableRowElement)) {
		row=row.parentNode;
	}
	table=row.parentNode;
	if (up) {
		goingup=row;
		goingdown=table.rows.item(row.rowIndex-1);
		row=goingdown;
	} else {
		goingdown=row;
		goingup=table.rows.item(row.rowIndex+1);
		row=goingup;
	}
	if (goingdown.rowIndex>0) {
		row.style.visibility="hidden";
		setTimeout(function() {row.style.visibility="visible"},500);
		setTimeout(function() {table.insertBefore(goingup,goingdown)},250);
	}
}



function update_row(row,rowdata) {
	var rlt=document.getElementById("routelisttable");
	var doinsertlater;
	if ((row==null) || (row==0)) {
		row=document.createElement("tr");
		doinsertlater=1;
	} else {
		doinsertlater=0;
		row=document.getElementById("routelisttable").getElementsByTagName("tr")[row+1];
	}
	row.innerHTML="";

	setcell(row,0,"destination",rowdata["destination"],destinations);
	setcell(row,1,"trunk",rowdata["trunk"],trunks);
	setcell(row,2,"dialstring",rowdata["dialstring"]);
	setcell(row,3,"clid_name",rowdata["clid_name"]);
	setcell(row,4,"clid_number",rowdata["clid_number"]);
	args="magicrownumber=0";
	for (var i in rowdata) {
		args=args+"&route["+i+"]="+escape(rowdata[i]);
	}
	var cell=row.cells[5];
	if (!cell) {
		cell=document.createElement("td");
		row.appendChild(cell);
	}
	html="<span onclick='moverow(this,true);'><a href=#>Up</a></span> <span onclick='moverow(this,false);'><a href=#>Down</a></span> ";
	html=html+'<?php echo html::anchor('/simpleroute/route_editor/?%%%','<span><img src=edit.png>' .__('Edit') .'</span>', array('class' => 'qtipAjaxForm')); ?>';
	html=html+" <span onclick='erase(this);'><a href=#>Delete</a></span> ";
	cell.innerHTML=html.replace("%%%",args);

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
	inputs=document.getElementById("routelisttable").getElementsByTagName("a");
	for (var input=0; input<inputs.length; input++) {
		var href=inputs[input].href;
		href=href.split("magicrownumber=");
		if (href.length>1) {
			href[1]=href[1].split("&");
			href[1][0]=(inputs[input].parentNode.parentNode.rowIndex).toString();
			href[1]=href[1].join("&");
			href=href.join("magicrownumber=");
			inputs[input].href=href;
		}
	}
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

