<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>List Deleted Kaltura Entries</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<style type="text/css" media="screen">
			@import "js/datatables/media/css/jquery.dataTables_themeroller.css";
			@import "js/datatables/media/css/pepper-grinder/jquery-ui-1.8.21.custom.css";
	</style>
	<script type="text/javascript" language="javascript" src="js/datatables/media/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function() {
			$('#dataTable').dataTable( {
				"bJQueryUI": true,
				"bProcessing": true,
				"bServerSide": true,
				"sAjaxSource": "./getlist.php",
				"aoColumnDefs": [ 
				  { "bSortable": false, "aTargets": [ 1 ] },
				  { "bSortable": false, "aTargets": [ 3 ] },
				  { "bSortable": false, "aTargets": [ 5 ] },
				  { "bSortable": false, "aTargets": [ 6 ] }
				],
				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "statusIn", "value": $.map($("input[name='filterstatus[braket]']:checked"),function(a){return a.value;}) } );
				},
				"fnServerData": function ( sSource, aoData, fnCallback ) {
					$.getJSON( sSource, aoData, function (json) { 
						/* Do whatever additional processing you want on the callback, then tell DataTables */
						$("#sourcecode").html(json.codesample);
						console.log(json.codesample);
						fnCallback(json);
					} );
				}
			} );
		} );
	</script>
	<script src="js/prettycheckboxes/js/prettyCheckboxes.js" charset="utf-8" ></script>
	<link rel="stylesheet" href="js/prettycheckboxes/css/prettyCheckboxes.css" type="text/css" media="screen" title="prettyComment main stylesheet" charset="utf-8" />
	<script type="text/javascript" charset="utf-8">
		$(document).ready(function(){
			$('#boxes input[type=checkbox]').prettyCheckboxes({'display':'list'});
			$("input[type='checkbox']").change(function () {
				var oTable = $('#dataTable').dataTable();
				oTable.fnDraw();
			});
		});
	</script>
	<style>
		.type {
			padding: 5px 30px 5px 0;
		}
		.type-1 {
			background: transparent url(icons/icon_film.gif) no-repeat center right;
		}
		.type-2 {
			background: transparent url(icons/icon_pic.gif) no-repeat center right;
		}
		.type-5 {
			background: transparent url(icons/icon_music.gif) no-repeat center right;
		}
		.downloadlink {
			padding: 5px 42px 5px 0;
			background: transparent url(icons/icon_download.png) no-repeat center right;
		}
	</style>
	<!-- Icons from: http://pooliestudios.com/projects/iconize/ -->
</head>
<body>
<article style="display:block;width:90%;margin: 0px auto;">
	<h1>Litsing Kaltura <span style="font-style:italic;">Media</span> Entries...</h1>
	<p>This sample shows how to use jQuery datatables and Kaltura's PHP API Client Library to create a searchable and sortable list of Kaltura Media Entries.</p>
	<p style="font-size: 14px; background: #F2F4D5 url(http://cdnknowledge.kaltura.com//sites/all/themes/kaltura_theme/tinymce_styles/images/note_icons.png) no-repeat left center; padding-left: 80px; min-height: 56px; display: block; padding-top: 5px; margin-top: 15px; margin-bottom: 15px;">NOTE: Make sure to set your partner id and admin secret in getlist.php</p>
	<div id="boxes" style="width:20%;float:left;">
		<p>Filter by Kaltura Entry Status:</p>
		<label for="chk-13" style="text-decoration: underline;">Un/Check All</label>
		<input type="checkbox" name="filterstatus[braket]" id="chk-13" value="all" onclick="checkAllPrettyCheckboxes(this, $('#boxes'));" />
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
	</div>
	<div style="width:80%;float:left;">
		<table cellpadding="0" cellspacing="0" border="0" class="dataTable" id="dataTable">
			<thead>
				<tr>
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
	<div style="clear:both;"></div>
	<div style="margin-top:40px;">
		<h2>The source code used to list the entries:</h2>
		<div id="sourcecode" class="brush: js;">
		</div>
	</div>
	<p style="margin-top:80px;font-style:italic;">Kaltura PHP API Client Library - <a href="http://knowledge.kaltura.com/introduction-kaltura-client-libraries" target="_blank">http://knowledge.kaltura.com/introduction-kaltura-client-libraries</a></p>
	<p style="font-style:italic;">jQuery DataTables - <a href="http://datatables.net" target="_blank">http://datatables.net/</a></p>
</article>
</body>
</html>