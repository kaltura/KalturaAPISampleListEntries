<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>List Kaltura Media Entries, PHP Sample</title>
	<!-- Style Includes -->
	<style type="text/css" media="screen">
		@import "js/datatables/media/css/jquery.dataTables_themeroller.css";
		@import "js/datatables/media/css/pepper-grinder/jquery-ui-1.8.21.custom.css";
	</style>
	<link rel="stylesheet" href="js/prettycheckboxes/css/prettyCheckboxes.css" type="text/css" media="screen" title="prettyComment main stylesheet" charset="utf-8" />
	<link href="js/loadmask/jquery.loadmask.css" rel="stylesheet" type="text/css" />
	<link href="css/style.css" rel="stylesheet" type="text/css" />
	<!-- Script Includes -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script type="text/javascript" language="javascript" src="js/datatables/media/js/jquery.dataTables.min.js"></script>
	<script src="js/prettycheckboxes/js/prettyCheckboxes.js" charset="utf-8" ></script>
	<script type="text/javascript" src="js/loadmask/jquery.loadmask.min.js"></script>
	<!-- Page Scripts -->
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function() {
			var configset = <?php require_once('kalturaconf.php'); echo '"'.$adminSecret.'"'; ?>;
			if (configset != 'your-api-admin-secret') $('.notep').hide();
			$('#dataTable').dataTable( {
				"bJQueryUI": true,
				"bProcessing": true,
				"bServerSide": true,
				"sAjaxSource": "./getlist.php",
				"aoColumnDefs": [ 
				  { "bSortable": false, "aTargets": [ 0 ] },
				  { "bSortable": false, "aTargets": [ 2 ] },
				  { "bSortable": false, "aTargets": [ 4 ] },
				  { "bSortable": false, "aTargets": [ 6 ] },
				  { "bSortable": false, "aTargets": [ 7 ] }
				],
				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "statusIn", "value": $.map($("input[name='filterstatus[braket]']:checked"),function(a){return a.value;}) } );
				  aoData.push( { "name": "mediaTypeIn", "value": $.map($("input[name='filtermediatype[braket]']:checked"),function(a){return a.value;}) } );
				},
				"fnServerData": function ( sSource, aoData, fnCallback ) {
					$.getJSON( sSource, aoData, function (json) { 
						$("#sourcecode").html(json.codesample); //print the source code to the page before printing the table
						fnCallback(json); //finalize dataTables run
					} );
				}
			} );
			$('#dataTable').dataTable().bind('processing', 
				function (e, oSettings, bShow) {
					if (bShow) {
						ajaxIndicator();
					} else {
						ajaxIndicator(true);
					}
				});
			$('#boxes input[type=checkbox]').prettyCheckboxes({'display':'list'});
			$("input[type='checkbox']").change(function () {
				var oTable = $('#dataTable').dataTable();
				oTable.fnDraw();
			});
		} );
		
		function ajaxIndicator(hide) {
			if (!hide)
				$("#tableandfilters").mask("Loading...");
			else
				$("#tableandfilters").unmask();
		}
		
		function toggleCheckboxes(elem){
			var indicator = $(elem).is(":visible") ? '+' : '-';
			$(elem).prev().find('.openindicator').text(indicator);
			$(elem).slideToggle(400, null, function (){
				//make the +/- sign change after the slide is over according to the state of the div
				var indicator = $(this).is(":visible") ? '-' : '+';
				$(this).prev().find('.openindicator').text(indicator);
			});
		}
	</script>
</head>
<body>
<a href="https://github.com/kaltura/KalturaAPISampleListEntries" target="_blank"><img style="position: absolute; top: 0; right: 0; border: 0;" src="https://s3.amazonaws.com/github/ribbons/forkme_right_green_007200.png" alt="Fork me on GitHub"></a>
<article style="display:block;width:90%;margin: 0px auto;">
	<h1>Listing Kaltura Media Entries...</h1>
	<p>This sample shows how to use Kaltura's PHP API Client Library to create a searchable and sortable list of Kaltura Media Entries.
	<br />The <a href="#sourcecodeexample">source code example below</a> the table, will automatically be updated according to the filters and sort conditions you set.</p>
	<p class="notep">NOTE: Make sure to set your partner id and admin secret in getlist.php</p>
	<div id="tableandfilters">
		<div id="boxes" style="width:20%;float:left;display:block;">
			<div class="accordian">
				<ul>
					<li>Filter by Media Type</li>
					<li>
						<div id="media_boxes" class="chknoxwrapper">
							<label for="chk-14">Video</label>
							<input type="checkbox" name="filtermediatype[braket]" id="chk-14" value="1"  />
							<label for="chk-15">Audio</label>
							<input type="checkbox" name="filtermediatype[braket]" id="chk-15" value="5"  />
							<label for="chk-16">Image</label>
							<input type="checkbox" name="filtermediatype[braket]" id="chk-16" value="2"  />
							<label for="chk-all">*Un/Check All</label>
							<input type="checkbox" id="chk-all" value="all" onclick="checkAllPrettyCheckboxes(this, $('#media_boxes'));" />
						</div>
					</li>
					<li>Filter by Kaltura Entry Status:</li>
					<li>
						<div id="status_boxes" class="chknoxwrapper">
							<label for="chk-1">Blocked</label>
							<input type="checkbox" name="filterstatus[braket]" id="chk-1" value="6"  />
							<label for="chk-2">Deleted</label>
							<input type="checkbox" name="filterstatus[braket]" id="chk-2" value="3"  />
							<label for="chk-3">Error Converting</label>
							<input type="checkbox" name="filterstatus[braket]" id="chk-3" value="-1"  />
							<label for="chk-4">Error Importing</label>
							<input type="checkbox" name="filterstatus[braket]" id="chk-4" value="-2"  />
							<label for="chk-5">Importing</label>
							<input type="checkbox" name="filterstatus[braket]" id="chk-5" value="0"  />
							<label for="chk-6">In Moderation</label>
							<input type="checkbox" name="filterstatus[braket]" id="chk-6" value="5"  />
							<label for="chk-7">Entry Without Content</label>
							<input type="checkbox" name="filterstatus[braket]" id="chk-7" value="7"  />
							<label for="chk-8">Pending</label>
							<input type="checkbox" name="filterstatus[braket]" id="chk-8" value="4"  />
							<label for="chk-9">Waiting Conversion</label>
							<input type="checkbox" name="filterstatus[braket]" id="chk-9" value="1"  />
							<label for="chk-10">Ready To Play</label>
							<input type="checkbox" name="filterstatus[braket]" id="chk-10" value="2"  />
							<label for="chk-11">Virus Scan Failed</label>
							<input type="checkbox" name="filterstatus[braket]" id="chk-11" value="virusScan.ScanFailure"  />
							<label for="chk-12">Infected with Virus</label>
							<input type="checkbox" name="filterstatus[braket]" id="chk-12" value="virusScan.Infected"  />
							<label for="chk-13">*Un/Check All</label>
							<input type="checkbox" id="chk-13" value="all" onclick="checkAllPrettyCheckboxes(this, $('#status_boxes'));" />
						</div>
					</li>
				</ul>
			</div>
		</div>
		<div style="width:80%;float:left;">
			<table cellpadding="0" cellspacing="0" border="0" class="dataTable" id="dataTable">
				<thead>
					<tr>
						<th></th>
						<th>Type</th>
						<th>Entry Id</th>
						<th width="40%">Title</th>
						<th width="40%">Description</th>
						<th>Updated</th>
						<th>Owner</th>
						<th>Download</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="5" class="dataTables_empty">Loading data from Kaltura...</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<th></th>
						<th>Type</th>
						<th>Entry Id</th>
						<th>Title</th>
						<th>Description</th>
						<th>Updated</th>
						<th>Owner</th>
						<th>Download</th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
	<div style="clear:both;"></div>
	<div style="margin-top:40px;">
		<a name="sourcecodeexample"></a>
		<h2>The source code used to list the entries:</h2>
		<div id="sourcecode" class="brush: js;">
		</div>
	</div>
	<div style="margin-top:80px;font-style:italic;">
		<h2>Recognitions:</h2>
		<ul>
			<li>Kaltura PHP API Client Library - <a href="http://knowledge.kaltura.com/introduction-kaltura-client-libraries" target="_blank">http://knowledge.kaltura.com/introduction-kaltura-client-libraries</a></li>
			<li>jQuery DataTables - <a href="http://datatables.net" target="_blank">http://datatables.net/</a></li>
			<li>jQuery plugin to mask DOM elements during loading - <a href="http://datatables.net" target="_blank">http://code.google.com/p/jquery-loadmask/</a></li>
			<li>Icons - <a href="http://pooliestudios.com/projects/iconize/" target="_blank">http://pooliestudios.com/projects/iconize/</a></li>
			<li>prettyCheckboxes - <a href="http://www.no-margin-for-errors.com/projects/prettycheckboxes/" target="_blank">http://www.no-margin-for-errors.com/projects/prettycheckboxes/</a></li>
			<li>JQuery Accordion Menu - <a href="http://www.lateralcode.com/jquery-accordion-menu/" target="_blank">http://www.lateralcode.com/jquery-accordion-menu/</a></li>
		</ul>
	</div>
</article>
<script type="text/javascript" src="js/accordion-menu/jMenu.js"></script>
</body>
</html>
