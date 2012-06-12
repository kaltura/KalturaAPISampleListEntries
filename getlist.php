<?php
	function truncateWords($input, $numwords, $padding="...")
	{
		$output = strtok($input, " \n");
		while(--$numwords > 0) $output .= " " . strtok(" \n");
		if($output != $input) $output .= $padding;
		$output = trim($output);
		if ($output == $padding) $output = '';
		return $output;
	}
	
	function prepareDescription ($desc) {
		$truncated = truncateWords($desc, 10);
		$output = ($truncated == '') ? '' : '<span title="'.$desc.'">'.$truncated.'</span>';
		return $output;
	}
	
	function tep_rewrite_email($content) {
		$email_patt = '([A-Za-z0-9._%-]+)\@([A-Za-z0-9._%-]+)\.([A-Za-z0-9._%-]+)';
		$mailto_pattern = '#\<a[^>]*?href=\"mailto:\s?' . $email_patt . '[^>]*?\>[^>]*?<\/a\>#';
		$rewrite_result = '<span title="\\1@\\2.\\3">\\1...</span>';
		$content = preg_replace($mailto_pattern, $rewrite_result, $content);
		$content = preg_replace('#' . $email_patt . '#', $rewrite_result, $content);
		return $content;
	}

  
	require_once('php5/KalturaClient.php');
	require_once('kalturaconf.php');
	$config = new KalturaConfiguration($partnerId);
	$config->serviceUrl = 'http://www.kaltura.com';
	$client = new KalturaClient($config);
	$ks = $client->generateSession($adminSecret, $userId, KalturaSessionType::ADMIN, $partnerId);
	$client->setKs($ks);

	$filter = new KalturaMediaEntryFilter();
	$pager = new KalturaFilterPager();
	
	$codesample = 'require_once(\'php5/KalturaClient.php\');' . PHP_EOL .
				'$adminSecret = \'your-api-admin-secret\';' . PHP_EOL .
				'$partnerId = 000; //your partner id' . PHP_EOL .
				'$userId = \'listentriestool\'; //this can be the logged-in admin user for tracking/auditing purposes' . PHP_EOL .
				'$config = new KalturaConfiguration($partnerId);' . PHP_EOL .
				'$config->serviceUrl = \'http://www.kaltura.com\';' . PHP_EOL .
				'$client = new KalturaClient($config);' . PHP_EOL .
				'$ks = $client->generateSession($adminSecret, $userId, KalturaSessionType::ADMIN, $partnerId);' . PHP_EOL .
				'$client->setKs($ks);' . PHP_EOL .
				'$filter = new KalturaMediaEntryFilter();' . PHP_EOL .
				'$pager = new KalturaFilterPager();';
	
	// PAGING
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' ) {
		$pager->pageSize = intval($_GET['iDisplayLength']);
		$pager->pageIndex = floor(intval($_GET['iDisplayStart']) / $pager->pageSize) + 1;
		
		$codesample .= PHP_EOL . '$pager->pageSize = ' . intval($_GET['iDisplayLength']) . ';';
		$codesample .= PHP_EOL . '$pager->pageIndex = ' . (floor(intval($_GET['iDisplayStart']) / $pager->pageSize) + 1) . ';';
	}
	
	/* 
	 ORDERING
		- Check KalturaMediaEntryOrderBy for more info.
		- in Kaltura thumbnailUrl can't be sorted
		- in Kaltura: MEDIATYPE_ASC / MEDIATYPE_DESC
		- in Kaltura id can't be sorted
		- in Kaltura: NAME_ASC / NAME_DESC
		- in Kaltura description can't be sorted
		- in Kaltura: UPDATED_AT_ASC / UPDATED_AT_DESC
		- in Kaltura user Id can't be sorted
		- Download is a generated cell, server can't sort according it cause we're creating it here...
	   iSortCol_  - The column id of the column to order
	   sSortDir_  - Should the column be desc or asc
	*/
	$aColumns = array( 'thumbnailUrl', 'mediaType', 'id', 'name', 'description', 'updatedAt', 'userId', 'download' );
	if ( isset( $_GET['iSortCol_0'] ) )
	{
		for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
		{
			if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
			{
				$filter->orderBy = ($_GET['sSortDir_'.$i] == 'asc' ? '+' : '-') . $aColumns[ intval( $_GET['iSortCol_'.$i] ) ];
				$codesample .= PHP_EOL . '$filter->orderBy = "'.($_GET['sSortDir_'.$i] == 'asc' ? '+' : '-') . $aColumns[ intval( $_GET['iSortCol_'.$i] ) ] . '"; //see KalturaMediaEntryOrderBy for available ordering methods';
				break; //Kaltura can do only order by single field currently
			}
		}
	}
	
	// FILTERING
	if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
	{
		$filter->freeText = $_GET['sSearch'];
		$codesample .= PHP_EOL . '$filter->freeText = "' . $_GET['sSearch'] . '";';
	}
	// Status filter. see KalturaEntryStatus for list of status codes.
	if ( isset($_GET['statusIn']) && $_GET['statusIn'] != "" ) {
		$filter->statusIn = $_GET['statusIn'];
		$codesample .= PHP_EOL . '$filter->statusIn = "' . $_GET['statusIn'] . '";';
	}
	//mediaTypeIn - See http://www.kaltura.com/api_v3/testmeDoc/index.php?object=KalturaMediaType
	if ( isset($_GET['mediaTypeIn']) && $_GET['mediaTypeIn'] != "" ) {
		$filter->mediaTypeIn = $_GET['mediaTypeIn'];
		$codesample .= PHP_EOL . '$filter->mediaTypeIn = "' . $_GET['mediaTypeIn'] . '";';
	}
	// Execute the search (list) action
	$filteredListResult = $client->media->listAction($filter, $pager);
	$codesample .= PHP_EOL . '$filteredListResult = $client->media->listAction($filter, $pager);';
	
	$codesample .= PHP_EOL . '// loop through the list and build the table:';
	$codesample .= PHP_EOL . '$table = array();';
	$codesample .= PHP_EOL . '$table[] = array("mediaType", "id", "name", "description", "updatedAt", "userId", "download");';
	$codesample .= PHP_EOL . 'foreach ($filteredListResult->objects as $entry) {';
	$codesample .= PHP_EOL . '		$row = array();';
	$codesample .= PHP_EOL . '		//to learn more about thumbnail api see: http://knowledge.kaltura.com/kaltura-thumbnail-api';
	$codesample .= PHP_EOL . '		//for the default thumbnail, can also use: $entry->thumbnailUrl;';
	$codesample .= PHP_EOL . '		$row[] = \'<img src="http://cdn.kaltura.com/p/\'.$partnerId.\'/thumbnail/entry_id/\'.$entry->id.\'/width/50/height/50/type/1/quality/100" />\'; ';
	$codesample .= PHP_EOL . '		$row[] = $entry->mediaType;';
	$codesample .= PHP_EOL . '		$row[] = $entry->id;';
	$codesample .= PHP_EOL . '		$row[] = $entry->name;';
	$codesample .= PHP_EOL . '		$row[] = $entry->description;';
	$codesample .= PHP_EOL . '		$row[] = gmdate("m.d.y", $entry->updatedAt);';
	$codesample .= PHP_EOL . '		$row[] = $entry->userId;';
	$codesample .= PHP_EOL . '		if ($entry->mediaType == KalturaMediaType::VIDEO || $entry->mediaType == KalturaMediaType::AUDIO) {';
	$codesample .= PHP_EOL . '			//to learn more about getting download url - http://knowledge.kaltura.com/faq/how-retrieve-download-or-streaming-url-using-api-calls';
	$codesample .= PHP_EOL . '			$downloadUrl = \'http://www.kaltura.com/p/\'. $partnerId .\'/sp/0/playManifest/entryId/\'. $entry->id .\'/format/url/flavorParamId/0\';';
	$codesample .= PHP_EOL . '			$row[] = \'<a href="\'.$downloadUrl.\'" target="_blank">Download</a>\';';
	$codesample .= PHP_EOL . '		} else {';
	$codesample .= PHP_EOL . '			$row[] = \'<a href="\'.$entry->dataUrl.\'" target="_blank" class="downloadlink"></a>\';';
	$codesample .= PHP_EOL . '		}';
	$codesample .= PHP_EOL . '		$table[] = $row;';
	$codesample .= PHP_EOL . '}';
	$codesample .= PHP_EOL . 'return $table;';
	
	
	$output = array(
		"codesample" => highlight_string('<?php' . PHP_EOL . $codesample . PHP_EOL . '?>', true),
		"orderBy" => $filter->orderBy,
		"iTotalRecords" => intval($filteredListResult->totalCount),
		"iTotalDisplayRecords" => intval($filteredListResult->totalCount),
		"aaData" => array()
	);
	
	if (isset($_GET['sEcho'])) {
		$output["sEcho"] = intval($_GET['sEcho']);
	}
	
	foreach ($filteredListResult->objects as $entry) {
		$row = array();
		//to learn more about thumbnail api see: http://knowledge.kaltura.com/kaltura-thumbnail-api
		//for the default thumbnail, can also use: $entry->thumbnailUrl;
		$row[] = '<img src="http://cdn.kaltura.com/p/'.$partnerId.'/thumbnail/entry_id/'.$entry->id.'/width/50/height/50/type/1/quality/100" />'; 
		$row[] = '<span class="type type-'.$entry->mediaType.'"></span>';
		$row[] = $entry->id;
		$row[] = $entry->name;
		$row[] = prepareDescription($entry->description);
		$row[] = gmdate("m.d.y", $entry->updatedAt);
		$row[] = tep_rewrite_email($entry->userId);
		if ($entry->mediaType == KalturaMediaType::VIDEO || $entry->mediaType == KalturaMediaType::AUDIO) {
			//to learn more about getting download url - http://knowledge.kaltura.com/faq/how-retrieve-download-or-streaming-url-using-api-calls
			$row[] = '<a href="http://www.kaltura.com/p/'.$partnerId.'/sp/0/playManifest/entryId/'.$entry->id.'/format/url/flavorParamId/0" target="_blank" class="downloadlink"></a>';
		} else {
			$row[] = '<a href="'.$entry->dataUrl.'" target="_blank" class="downloadlink"></a>';
		}
		$output['aaData'][] = $row;
	}
	
	echo json_encode( $output );
?>
