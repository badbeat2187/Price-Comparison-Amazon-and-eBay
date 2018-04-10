<?php
require_once('amazon_product_api_class.php');
$public = //amazon public key here
$private = //amazon private/secret key here
$site = 'com'; //amazon region
$affiliate_id =  //amazon affiliate id

$searchkey=$_POST['search'];
$category=$_POST['category'];
echo $searchkey;
echo $category;



//error_reporting(E_ALL);  // Turn on all errors, warnings and notices for easier debugging

// API request variables
$endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';  // URL to call
$version = '1.0.0';  // API version supported by your application
$appid =  // Replace with your own AppID
$globalid = 'EBAY-US';  // Global ID of the eBay site you want to search (e.g., EBAY-DE)
$query = $searchkey;  // You may want to supply your own query
$safequery = urlencode($query);  // Make the query URL-friendly
$i = '0';  // Initialize the item filter index to 0

$filterarray =
  array(
    array(
    'name' => '',
    'value' => '',
    'paramName' => 'Currency',
    'paramValue' => 'USD'),
    array(
    'name' => '',
    'value' => '',
    'paramName' => '',
    'paramValue' => ''),
    array(
    'name' => 'ListingType',
    'value' => array('AuctionWithBIN','FixedPrice','StoreInventory'),
    'paramName' => '',
    'paramValue' => ''),
  );
  
  // Generates an indexed URL snippet from the array of item filters
function buildURLArray ($filterarray) {
  global $urlfilter;
  global $i;
  // Iterate through each filter in the array
  foreach($filterarray as $itemfilter) {
    // Iterate through each key in the filter
    foreach ($itemfilter as $key =>$value) {
      if(is_array($value)) {
        foreach($value as $j => $content) { // Index the key for each value
          $urlfilter .= "&itemFilter($i).$key($j)=$content";
        }
      }
      else {
        if($value != "") {
          $urlfilter .= "&itemFilter($i).$key=$value";
        }
      }
    }
    $i++;
  }
  return "$urlfilter";
} // End of buildURLArray function

// Build the indexed item filter URL snippet
buildURLArray($filterarray);

// Construct the findItemsByKeywords HTTP GET call 
$apicall = "$endpoint?";
$apicall .= "OPERATION-NAME=findItemsByKeywords";
$apicall .= "&SERVICE-VERSION=$version";
$apicall .= "&SECURITY-APPNAME=$appid";
$apicall .= "&GLOBAL-ID=$globalid";
$apicall .= "&keywords=$safequery";
$apicall .= "&paginationInput.entriesPerPage=5";
$apicall .= "$urlfilter";

// Load the call and capture the document returned by eBay API
$resp = simplexml_load_file($apicall);

// Check to see if the request was successful, else print an error
if ($resp->ack == "Success") {
  $results = '';
  // If the response was loaded, parse it and build links  
  foreach($resp->searchResult->item as $item) {
    $pic   = $item->galleryURL;
    $link  = $item->viewItemURL;
    $title = $item->title;
  
    // For each SearchResultItem node, build a link and append it to $results
    $results .= "<div class=\"col-lg-2 col-md-2 col-sm-4 col-xs-4\"><section id=\"item\"><img src=\"$pic\"><a href=\"$link\">$title</a></section></div>";
  }
}
// If the response does not indicate 'Success,' print an error
else {
  $results  = "<h3>Oops! The request was not successful. Make sure you are using a valid ";
  $results .= "AppID for the Production environment.</h3>";
}


$amazon = $amazon = new AmazonProductAPI($public, $private, $site, $affiliate_id);

$similar = array(
	'Operation' => 'ItemSearch',
	//'ItemId' => 'B00CMQTTZ2',
	//'Condition' => 'All',
	'SearchIndex'=>$category,
	'Keywords'=>$searchkey,
	'ResponseGroup' => 'Medium'
	);

$result =	$amazon->queryAmazon($similar);

$similar_products = $result->Items->Item;
foreach($similar_products as $si){

	$item_url = $si->DetailPageURL; //get its amazon url
	$img = $si->MediumImage->URL; //get the image url

  echo "reached here";

	// echo "<li>";
	// echo "<img src='$img'/>";
	// echo "<a href='$item_url'>". $si->ASIN . "</a>";
	// echo $si->ItemAttributes->ListPrice->FormattedPrice; //item price
	// echo "</li>";
}
	
?>
