<?php
error_reporting(0);  // Turn on all errors, warnings and notices for easier debugging

if(isset($_POST['submit']))
{
  require_once('amazon_product_api_class.php');
  $public = 'AKIAJTGOY62FTKSI2DDQ'; //amazon public key here
  $private = 'q0kVO3pKMJg1I2haRZkBnLawidlG1dMl3nvQz05v'; //amazon private/secret key here
  $site = 'com'; //amazon region
  $affiliate_id = 'nikhilswami-20'; //amazon affiliate id
  
  $searchkey=$_POST['search'];
  $category=$_POST['category'];
 // $range=$_POST['range'];
  //echo $range;
  //echo $searchkey;
  //echo $category;
$ebay_results=null;
$amazon_results=null;
	/* if($range==null)
	{
	$range='1000';
	echo $range;
	}
   */

  // API request variables
  $endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';  // URL to call
  $version = '1.0.0';  // API version supported by your application
  $appid = 'student-products-PRD-038ccaf50-b2ee97ab';  // Replace with your own AppID
  $globalid = 'EBAY-US';  // Global ID of the eBay site you want to search (e.g., EBAY-DE)
  $query = $searchkey;  // You may want to supply your own query
  $safequery = urlencode($query);  // Make the query URL-friendly
  $i = '0';  // Initialize the item filter index to 0
  //$rangefilter = $range;
  
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
  $apicall .= "&paginationInput.entriesPerPage=10";
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
      //$title = $item->title;
      $price = $item->sellingStatus->currentPrice;
    
      // For each SearchResultItem node, build a link and append it to $results
		
      $ebay_results .= "<div class=\"col-lg-3 col-md-3 col-sm-6 col-xs-6\">
                        <section id=\"item\"><img src=\"$pic\"><a href=\"$link\">$price</a></section>
                                </div>";
    }
  }
  // If the response does not indicate 'Success,' print an error
  else {
    $results  = "<h3>Oops! The request was not successful. Make sure you are using a valid ";
    $results .= "AppID for the Production environment.</h3>";
  }


  $amazon = $amazon = new AmazonProductAPI($public, $private, $site, $affiliate_id);
//echo $range;
  $similar = array(
    'Operation' => 'ItemSearch',
    //'ItemId' => 'B00CMQTTZ2',
    //'Condition' => 'All',
    'SearchIndex'=>$category,
    'Keywords'=>$searchkey,
	//'MaximumPrice'=>$range,
    'ResponseGroup' => 'Medium'
    );

  $result = $amazon->queryAmazon($similar);

  $similar_products = $result->Items->Item;
  foreach($similar_products as $si){

    $item_url = $si->DetailPageURL; //get its amazon url
    $img = $si->MediumImage->URL;
    $aprice = $si->ItemAttributes->ListPrice->FormattedPrice ;//get the image url

	
    $amazon_results .= "<div class=\"col-lg-3 col-md-3 col-sm-6 col-xs-6\">
                        <section id=\"item\"><a href=\"$item_url\"><img src=\"$img\">$aprice</a></section>
                                </div>";



  }
}   
?>
<html>
<head>
    <title> Price Comparison application</title>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.9/angular.min.js"></script>
    <script src="http://crypto-js.googlecode.com/svn/tags/3.0.2/build/rollups/hmac-sha256.js"></script>
    <script src="http://crypto-js.googlecode.com/svn/tags/3.0.2/build/components/enc-base64.js"></script>
    <script src="js/price.js"></script>

    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/price.css">
</head>

<body>
    <div class="container-fluid">
        <section id="header">
            <h1> Price Comparison Application</h1>
            <h3> The right price for the Right product</h3>
        </section>
    </div>

    <section id="mainBody">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <section id="searchSection">
                        <form id="searchForm" method="post" action="<?=$_SERVER['PHP_SELF']?>">
                            <h4> Enter the item you want to search</h4>
                            <input type="text" name="search" placeholder="enter the item">
                            <input type="submit" name="submit" value="Search">
                            <a id="searchCategory"><h4>Enter the search category:</h4></a>
                             <section id="showCategory">
                                 <input type="radio" name="category" value="Books">Books<br/>
                                 <input type="radio" name="category" value="DVD">DVD<br/>
                                 <input type="radio" name="category" value="Electronics">Electronics<br/>
                                 <input type="radio" name="category" value="Music">Music<br/>
                                 <input type="radio" name="category" value="Apparel">Apparel<br/>
                                 <input type="radio" name="category" value="Video">Video<br/>
                                 <input type="radio" name="category" value="Jewelry">Jewelry<br/>
                                 <input type="radio" name="category" value="Watch">Watch<br/>
                                 <input type="radio" name="category" value="Automotive">Automotive<br/>
                             </section>
                        </form>
                    </section>
                </div>



                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-9">
                    <section id="responseList">
                        <section id="ebayResults">
                            <h3> Results form Ebay</h3>
                            <div class="row">
                                <?php echo $ebay_results; ?>
                            </div>
                        </section>

                        <section id="amazonResults">
                            <h3> Results form Amazon</h3>
                            <div class="row">
                                <?php echo $amazon_results; ?>
                            </div>
                        </section>
                    </section>
                </div>

            </div>
        </div>

    </section>
</body>
</html>