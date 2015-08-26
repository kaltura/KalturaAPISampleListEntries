<?php require_once('kalturaconf.php') ?>
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
	<link rel="stylesheet" href="js/fancyBox/jquery.fancybox.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="js/prettycheckboxes/css/prettyCheckboxes.css" type="text/css" media="screen" title="prettyComment main stylesheet" charset="utf-8" />
	<link href="js/loadmask/jquery.loadmask.css" rel="stylesheet" type="text/css" />
	<link href="css/style.css" rel="stylesheet" type="text/css" />
	<link href="css/social-buttons.css" rel="stylesheet" type="text/css" />
	
	<!-- Script Includes -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script type="text/javascript" language="javascript" src="js/datatables/media/js/jquery.dataTables.min.js"></script>
	<script src="js/prettycheckboxes/js/prettyCheckboxes.js" charset="utf-8" ></script>
	<script type="text/javascript" src="js/loadmask/jquery.loadmask.min.js"></script>
	<script type="text/javascript" src="js/fancyBox/jquery.fancybox.js"></script>
	<script type="text/javascript" src="js/jquery.popupWindow.js"></script>
	<script src="https://cdnapisec.kaltura.com/html5/html5lib/v2.34/mwEmbedLoader.php"></script>
	<!-- Page Scripts -->
	<script type="text/javascript" charset="utf-8">

		partnerId=<?php echo $partnerId ?>;
		uiConfId=<?php echo $uiConfId ?>;
		
		mw.setConfig( 'KalturaSupport.LeadWithHTML5', true );
		
		function toHHMMSS (inseconds) {
			sec_numb    = parseInt(inseconds);
			var hours   = Math.floor(sec_numb / 3600);
			var minutes = Math.floor((sec_numb - (hours * 3600)) / 60);
			var seconds = sec_numb - (hours * 3600) - (minutes * 60);

			if (hours   < 10) {hours   = "0"+hours;}
			if (minutes < 10) {minutes = "0"+minutes;}
			if (seconds < 10) {seconds = "0"+seconds;}
			var time    = (hours == "00" ? "" : hours + ':') + minutes+':'+seconds;
			return time;
		}

		var urlParams = {};
		(function () {
			var match,
				pl     = /\+/g,  // Regex for replacing addition symbol with a space
				search = /([^&=]+)=?([^&]*)/g,
				decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
				query  = window.location.search.substring(1);

			while (match = search.exec(query))
			   urlParams[decode(match[1])] = decode(match[2]);
		})();
				
		function showPlayer() {
			
			var embedCode='<div id="kdpwrapper" style="width:400px;height:333px;"></div>' +
			'<div style="display:none" id="sharingcontrols">Share Start at: <span style="margin-right: 10px;" id="videotime"></span>' +
			'<a id="tweetbtn" class="sb small glossy gradient light-blue twitter">Twitter</a>' +
			'<a id="facebookbtn" class="sb small glossy gradient blue facebook">Facebook</a></div>';
			
			jQuery.fancybox(embedCode, { 
				'height':333+10,
				'width':400,
				'scrolling':'no',
				'autoDimensions':'false',
				'afterShow': function (){
					var uniqid = +new Date();
					var kdpId = 'kdptarget'+uniqid;
					$('#kdpwrapper').html('<div id="'+kdpId+'" style="width:400px;height:333px;"></div>');
					kWidget.embed({
						'targetId': kdpId,
						'wid': '_'+partnerId,
						'uiconf_id' : uiConfId,
						'entry_id' : urlParams['entry_id'],
						'flashvars':{
							'externalInterfaceDisabled' : false,
							'autoPlay' : true
						},
						'readyCallback': function( playerId ){
							//console.log('readycalled');
							window.kdp = $('#'+playerId).get(0);
							kdp.addJsListener("playerUpdatePlayhead", 'startSeekPlayer');
						}
					});
				},
				'beforeClose': function () {
							//console.log('deleting...');
							kWidget.destroy(window.kdp);
							delete(window.kdp);
						}
			});
		}
		
		function startSeekPlayer() {
			if (parseInt(urlParams['vid_sec']) > 0) {
				kdp.sendNotification('doSeek', urlParams['vid_sec']);
			}
			kdp.removeJsListener("playerUpdatePlayhead", 'startSeekPlayer');
			kdp.addJsListener("playerUpdatePlayhead", 'updateTwitterFBSeekTime');
			$('#sharingcontrols').show();
		}
		
		function updateTwitterFBSeekTime() {
			var cuePoint = Math.floor(kdp.evaluate("{video.player.currentTime}"));
			$('#videotime').text(toHHMMSS(cuePoint));
			var winPath = window.location.origin+window.location.pathname;
			var cuepointPath = encodeURIComponent(winPath + "?entry_id=" + urlParams['entry_id'] + "&vid_sec=" + cuePoint);
			var text2share = encodeURIComponent("Check out this awesom moment!");
			var tweetUrl = 'https://twitter.com/share?url='+cuepointPath+'&text='+text2share+'&via=kaltura';
			var fbShareUrl = 'https://www.facebook.com/sharer.php?u='+cuepointPath+'&t='+text2share;
			$('#tweetbtn').popupWindow({windowURL:tweetUrl, 
										windowName:'tweeter window',
										height:300, 
										width:600, 
										resizable:false,
										scrollbars:false,
										centerBrowser:true
									});
			$('#facebookbtn').popupWindow({windowURL:fbShareUrl, 
										windowName:'facebook window',
										height:300, 
										width:600, 
										resizable:false,
										scrollbars:false,
										centerBrowser:true
									});
		}

		$(document).ready(function() {
	
			
			var configset = <?php echo ($adminSecret != 'your-api-admin-secret' ? 1 : 0); ?>;
			if (configset) $('.notep').hide();
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
						string=''
						for(prop in json){
							string+=prop+':'+json[prop]
						}
						$("#sourcecode").html(json.codesample); //print the source code to the page before printing the table
						fnCallback(json); //finalize dataTables run
					} );
				},
				
				"fnRowCallback":function( nRow, aData, iDisplayIndex, iDisplayIndexFull ){
					var thing = 0;
					var times = new Array();	
					var i = 0;
					var timer = null;
					//Perhaps a call back function should be set here to be performed at the very end
					$(nRow).children('td:first').children('img').mouseover(function(){	
						var that = this;
						if (aData[8] > 10) {
							timer = setInterval( function () {
								if (i < 10 && times.length == 10 ) {
									that.src = times[i++].src;
								} else {
									i=0;
								}	
							},250);
							
							if (times.length < 10) {
								var timeUnit = aData[8] / 10;
								var timeUnits = 0;
								for (i; i < 10; i++) {
									timeUnits=Math.floor(timeUnit * i);
									times.push(new Image());
									times[i].setAttribute('src', 'https://cdnapisec.kaltura.com/p/'+partnerId+'/thumbnail/entry_id/'+aData[2]+'/width/50/height/50/type/1/quality/100/vid_sec/'+timeUnits);
								}
								i=0;
							}
						}
					});
					$(nRow).children('td:first').children('img').mouseout(function() {
						clearTimeout(timer);
					});
					$(nRow).children('td:first').children('img').click(function() {
						urlParams['entry_id'] = aData[2];
						urlParams['vid_sec'] = 0;
						showPlayer();
					});
				}
			} );
			$('#dataTable').dataTable().bind('processing', function (e, oSettings, bShow) {
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
			
			if (urlParams['entry_id']) {
				showPlayer();
			}
		});
		
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
			<li>Kaltura PHP API Client Library - <a href="https://knowledge.kaltura.com/introduction-kaltura-client-libraries" target="_blank">https://knowledge.kaltura.com/introduction-kaltura-client-libraries</a></li>
			<li>jQuery DataTables - <a href="https://datatables.net" target="_blank">https://datatables.net/</a></li>
			<li>jQuery plugin to mask DOM elements during loading - <a href="https://datatables.net" target="_blank">https://code.google.com/p/jquery-loadmask/</a></li>
			<li>Icons - <a href="http://pooliestudios.com/projects/iconize/" target="_blank">http://pooliestudios.com/projects/iconize/</a></li>
			<li>prettyCheckboxes - <a href="http://www.no-margin-for-errors.com/projects/prettycheckboxes/" target="_blank">http://www.no-margin-for-errors.com/projects/prettycheckboxes/</a></li>
			<li>JQuery Accordion Menu - <a href="http://www.lateralcode.com/jquery-accordion-menu/" target="_blank">http://www.lateralcode.com/jquery-accordion-menu/</a></li>
		</ul>
	</div>
</article>
<script type="text/javascript" src="js/accordion-menu/jMenu.js"></script>
</body>
</html>
