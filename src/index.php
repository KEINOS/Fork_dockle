<?php

const SUCCESS = 0; //Exit code on success
const FAILURE = 1; //Exit code on fail
const DIR_SEP = DIRECTORY_SEPARATOR;
const IS_MODE_DEBUG   = true;
const PATH_DIR_DATA   = '/app/data';
const NAME_FILE_USAGE = 'usage.md';

require_once(dirname(__FILE__) . DIR_SEP . 'functions.inc.php');

// Show help if no request found.
if (isRequestEmpty()) {
    echoUsageAndExit();
}

// Show Cache in detail if any
$result = getCacheDetails();
if (! empty($result) && isRequestDetails()) {
    echoArrayAsJsonAndExit($result);
}

// Show Cache if any
$result = getCacheBadge();
if (! empty($result) && ! isRequestUpdate()) {
    echoArrayAsJsonAndExit($result);
}

// Analyze with Dockle the requested image in the URL query
$name_image = getNameImage();
if (! isValidImageName($name_image)) {
    echoErrorAndExit('Invalid Image Name.');
}
$json    = runDockle($name_image);
$array   = json_decode($json, JSON_OBJECT_AS_ARRAY);
$badge   = getBadgeAsArray($array['summary']);
$details = $array['details'];

// Save results and display as JSON for badger.IO badge
if (putCacheBadge($badge) && putCacheDetails($details)) {
    $url = getUrlRedirectCached();
    redirectToUrlAndExit($url);
}
echoErrorAndExit('Fail to save cache.');
exit;
