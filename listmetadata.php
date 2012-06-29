<?php
require_once('php5/KalturaClient.php');
$adminSecret = 'b47cd50fad6d869b8d9ec0b706d2c07a';
$partnerId = 224962;
$userId = 'listentriestool';
$config = new KalturaConfiguration($partnerId);
$config->serviceUrl = 'http://www.kaltura.com';
$client = new KalturaClient($config);
$ks = $client->generateSession($adminSecret, $userId, KalturaSessionType::ADMIN, $partnerId);
$client->setKs($ks);

$filter = new KalturaMediaEntryFilter();

$filterAdvancedSearchItems = array();
$filterAdvancedSearchItems3 = new KalturaSearchCondition();
$filterAdvancedSearchItems3->field = "/*[local-name()='metadata']/*[local-name()='Tester']"; // Obtained by calling metadataProfile service and showing defined fields
$filterAdvancedSearchItems3->value = 'value2';

$filterAdvancedSearch = new KalturaMetadataSearchItem();
$filterAdvancedSearch->type = KalturaSearchOperatorType::SEARCH_OR;
$filterAdvancedSearch->metadataProfileId = 31; // Obtained by calling metadataProfile service and getting the profile ID
$filterAdvancedSearch->items = array($filterAdvancedSearchItems3);


$filter->advancedSearch = $filterAdvancedSearch;

$results = $client-> media ->listAction($filter);

print '<pre>'.print_r($results, true).'</pre>';