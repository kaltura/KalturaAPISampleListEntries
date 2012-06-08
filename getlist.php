<?php
	require_once('php5/KalturaClient.php');
	$adminSecret = 'your-api-admin-secret';
	$partnerId = 000; //your partner id
	$userId = 'listentriestool';
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
				'$userId = \'listentriestool\'; ' . PHP_EOL .
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
		- in Kaltura id can't be sorted
		- in Kaltura: NAME_ASC / NAME_DESC
		- in Kaltura description can't be sorted
		- in Kaltura: UPDATED_AT_ASC / UPDATED_AT_DESC
		- in Kaltura user Id can't be sorted
	   iSortCol_  - The column id of the column to order
	   sSortDir_  - Should the column be desc or asc
	*/
	$aColumns = array( 'id', 'name', 'description', 'updatedAt', 'userId' );
	if ( isset( $_GET['iSortCol_0'] ) )
	{
		for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
		{
			if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
			{
				$filter->orderBy = ($_GET['sSortDir_'.$i] == 'asc' ? '+' : '-') . $aColumns[ intval( $_GET['iSortCol_'.$i] ) ];
				$codesample .= PHP_EOL . '$filter->orderBy = '.($_GET['sSortDir_'.$i] == 'asc' ? '+' : '-') . $aColumns[ intval( $_GET['iSortCol_'.$i] ) ] . ';';
				break; //Kaltura can do only order by single field currently
			}
		}
	}
	
	// FILTERING
	if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
	{
		$filter->freeText = $_GET['sSearch'];
		$codesample .= PHP_EOL . '$filter->freeText = ' . $_GET['sSearch'] . ';';
	}
	// Status filter. see KalturaEntryStatus for list of status codes.
	if ( isset($_GET['statusIn']) && $_GET['statusIn'] != "" ) {
		$filter->statusIn = $_GET['statusIn'];
		$codesample .= PHP_EOL . '$filter->statusIn = "' . $_GET['statusIn'] . '";';
	}
	
	// Execute the search (list) action
	$filteredListResult = $client->media->listAction($filter, $pager);
	$codesample .= PHP_EOL . '$filteredListResult = $client->media->listAction($filter, $pager);';
	
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
		$row[] = $entry->id;
		$row[] = $entry->name;
		$row[] = $entry->description;
		$row[] = gmdate("m.d.y", $entry->updatedAt);
		$row[] = $entry->userId;
		$output['aaData'][] = $row;
	}
	
	echo json_encode( $output );
?>