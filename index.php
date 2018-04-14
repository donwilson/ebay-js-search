<?php
	// http://svcs.ebay.com/services/search/FindingService/v1?OPERATION-NAME=findItemsByKeywords&SERVICE-VERSION=1.0.0&SECURITY-APPNAME=__________&RESPONSE-DATA-FORMAT=JSON&REST-PAYLOAD&keywords=ty%20beanie
	
	define('EBAY_APP_ID', "");
	define('EBAY_DEV_ID', "");
	define('EBAY_CERT_ID', "");   // client secret
	
	function die_json($status="success", $cargo=[]) {
		die(json_encode([
			'status' => strtolower(trim($status)),
			'cargo' => $cargo,
		]));
	}
	
	function get_url($url, $params=[]) {
		if(!empty($params) && (false === strpos($url, "?"))) {
			$url .= "?";
		}
		
		$url .= "&". http_build_query($params);
		$url = str_replace("?&", "?", $url);
		
		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 20,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 40,
			CURLOPT_URL => $url,
		]);
		$data = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		
		return $data;
	}
	
	function search_ebay($query) {
		$url = "http://svcs.ebay.com/services/search/FindingService/v1";
		$params = [
			'OPERATION-NAME' => "findItemsByKeywords",
			'SERVICE-VERSION' => "1.0.0",
			'SECURITY-APPNAME' => EBAY_APP_ID,
			'RESPONSE-DATA-FORMAT' => "JSON",
			"REST-PAYLOAD",
			'keywords' => trim(strtolower($query)),
			'sortOrder' => "StartTimeNewest",
		];
		
		return json_decode(get_url($url, $params), true);
	}
	
	if(isset($_REQUEST['q']) && ("" !== $query = trim($_REQUEST['q']))) {
		header("Content-Type: application/json");
		
		$data = search_ebay($query);
		
		$rows = [];
		
		if(!empty($data['findItemsByKeywordsResponse'][0]['searchResult'][0]['item'])) {
			$items = $data['findItemsByKeywordsResponse'][0]['searchResult'][0]['item'];
			
			foreach($items as $item) {
				$item['startTime_timestamp'] = strtotime($item['listingInfo'][0]['startTime'][0]);
				
				$rows[] = $item;
			}
			
			usort($rows, function($a, $b) {
				if($a['listingInfo'][0]['startTime'][0] == $b['listingInfo'][0]['startTime'][0]) {
					return 0;
				}
				
				$a_time = strtotime($a['listingInfo'][0]['startTime'][0]);
				$b_time = strtotime($b['listingInfo'][0]['startTime'][0]);
				
				if($a_time < $b_time) {
					return -1;
				} else {
					return 1;
				}
			});
		}
		
		die_json('success', $rows);
	}
?>
<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<title>eBay Auto Searcher</title>
	
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	
	<style type="text/css">
		#flipkart-navbar {
			/*background-color: #2874f0;*/
			color: #FFFFFF;
			margin-bottom: 20px;
		}
		
		.row1 {
			padding-top: 20px;
		}
		
		.row2 {
			padding-bottom: 20px;
		}
		
		.flipkart-navbar-input {
			padding: 11px 16px;
			color: #333;
			font-weight: bold;
			border-radius: 2px 0 0 2px;
			border: 0 none;
			outline: 0 none;
			font-size: 15px;
		}
		
		.flipkart-navbar-button {
			background-color: #ffe11b;
			border: 1px solid #ffe11b;
			border-radius: 0 2px 2px 0;
			color: #565656;
			padding: 10px 0;
			height: 43px;
			cursor: pointer;
		}
		
		.largenav {
			display: none;
		}
		
		.smallnav{
			display: block;
		}
		
		.smallsearch{
			margin-left: 15px;
			margin-top: 15px;
		}
		
		.menu{
			cursor: pointer;
		}
		
		@media screen and (min-width: 768px) {
			.largenav {
				display: block;
			}
			.smallnav{
				display: none;
			}
			.smallsearch{
				margin: 0px;
			}
		}
		
		/*Sidenav*/
		.sidenav {
			height: 100%;
			width: 0;
			position: fixed;
			z-index: 1;
			top: 0;
			left: 0;
			background-color: #fff;
			overflow-x: hidden;
			transition: 0.5s;
			box-shadow: 0 4px 8px -3px #555454;
			padding-top: 0px;
		}
		
		.sidenav a {
			padding: 8px 8px 8px 32px;
			text-decoration: none;
			font-size: 25px;
			color: #818181;
			display: block;
			transition: 0.3s
		}
		
		.sidenav .closebtn {
			position: absolute;
			top: 0;
			right: 25px;
			font-size: 36px;
			margin-left: 50px;
			color: #fff;		
		}
		
		@media screen and (max-height: 450px) {
		  .sidenav a {font-size: 18px;}
		}
		
		.sidenav-heading{
			font-size: 36px;
			color: #fff;
		}
	</style>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<script src="jquery.timeago.js"></script>
</head>
<body>
	
	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
	<div id="flipkart-navbar" class="bg-primary">
		<div class="container">
			<div class="row row1 row2">
				<div class="col-sm-2 text-center">
					<h1 style="margin:0px;"><span class="largenav">eBay</span></h1>
				</div>
				<form method="get" action="index.php" class="flipkart-navbar-search smallsearch col-sm-9" id="search_form">
					<div class="row">
						<input class="flipkart-navbar-input col-xs-11" type="text" placeholder="Search for Products, Brands and more" id="search_input">
						<button class="flipkart-navbar-button col-xs-1"><i class="glyphicon glyphicon-search"></i></button>
					</div>
				</form>
				<div class="col-sm-1 text-center">
					<span id="search_status"></span>
				</div>
			</div>
		</div>
	</div>
	
	<div class="container">
		<div id="results">
			<div class="alert alert-info">Use the search form above</div>
		</div>
	</div>
	
	<script type="text/javascript">
		;jQuery(document).ready(function($) {
			var query_input_el = $("#search_input"),
				search_status_el = $("#search_status"),
				search_status = "off",
				search_interval = 60,
				search_interval_at = search_interval;
			
			function add_item(item) {
				var html = [];
				
				var itemId = item['itemId'][0];
				
				if($("#results #item__"+ itemId).length) {
					// already added
					return;
				}
				
				var title = item['title'][0];
				var galleryURL = item['galleryURL'][0] || false;
				var viewItemURL = item['viewItemURL'][0];
				var isBuyItNow = ("true" === item['listingInfo'][0]['buyItNowAvailable'][0]);
				var startTime = item['listingInfo'][0]['startTime'][0];
				var endTime = item['listingInfo'][0]['endTime'][0];
				var itemLocation = item['location'][0] || "Unknown";
				
				var currentPrice;
				
				if(item['sellingStatus'][0]['currentPrice'][0]['@currencyId']) {
					if("USD" === item['sellingStatus'][0]['currentPrice'][0]['@currencyId']) {
						currentPrice = "$"+ item['sellingStatus'][0]['currentPrice'][0]['__value__'];
					} else {
						currentPrice = item['sellingStatus'][0]['currentPrice'][0]['__value__'] +" "+ item['sellingStatus'][0]['currentPrice'][0]['@currencyId'];
					}
				}
				
				
				var shippingPrice = false;
				
				if(item['shippingInfo'][0]['shippingServiceCost'] && item['shippingInfo'][0]['shippingServiceCost'][0] && item['shippingInfo'][0]['shippingServiceCost'][0]['__value__']) {
					shippingPrice = "$"+ item['shippingInfo'][0]['shippingServiceCost'][0]['__value__'];
					
					if(item['shippingInfo'][0]['shippingServiceCost'][0]['@currencyId'] && ("USD" !== item['shippingInfo'][0]['shippingServiceCost'][0]['@currencyId'])) {
						shippingPrice = item['shippingInfo'][0]['shippingServiceCost'][0]['__value__'] +" "+ item['shippingInfo'][0]['shippingServiceCost'][0]['@currencyId'];
					}
				}
				
				html.push("<tr id=\"item__"+ itemId +"\">");
				
				html.push("<td class=\"text-center\">");
				
				if(galleryURL) {
					html.push("<a href=\""+ viewItemURL +"\" target=\"_blank\"><img src=\""+ galleryURL +"\" /></a>");
				}
				
				html.push("</td>");
				
				// item
				html.push("<td>");
				html.push("<a href=\""+ viewItemURL +"\" target=\"_blank\"><strong>"+ title +"</strong></a><br />");
				
				var meta = [];
				
				if(isBuyItNow) {
					meta.push("<span class=\"label label-danger\">Buy it Now</span>");
				}
				
				meta.push(itemLocation);
				
				html.push( meta.join("&nbsp; &nbsp;") );
				
				html.push("</td>");
				
				// date
				html.push("<td class=\"text-center\"><time class=\"timeago\" datetime=\""+ startTime +"\">a few seconds ago</time></td>");
				
				// price
				html.push("<td class=\"text-right\">");
				html.push("<strong>"+ currentPrice +"</strong>");
				
				if(shippingPrice) {
					html.push("<br />");
					html.push("Ship: <em>"+ shippingPrice +"</em>");
				}
				
				html.push("</td>");
				html.push("</tr>");
				
				$("#results table tbody").prepend( html.join("") );
			}
			
			function search_ebay() {
				var query = $(query_input_el).val() || "";
				
				if(!query) {
					search_status = "off";
					
					$("#results").empty();
					
					return;
				}
				
				$.ajax({
					'url': "index.php",
					'data': {
						'q': query
					},
					'cache': false,
					'beforeSend': function() {
						search_status = "searching";
					},
					'success': function(data) {
						if(!data || !data.status || ("error" === data.status) || !data.cargo || !data.cargo.length) {
							search_status = "off";
							
							$("#results").empty().html("<div class='alert alert-danger'>Search failed or is empty</div>");
							
							return;
						}
						
						if(!$("#results table").length) {
							var html = [];
							
							html.push("<table class='table table-hover'>");
							html.push("<thead>");
							html.push("<tr>");
							html.push("<th class=\"text-center\">Image</th>");
							html.push("<th>Item</th>");
							html.push("<th class=\"text-center\">Created</th>");
							html.push("<th class=\"text-right\">Price</th>");
							html.push("</tr>");
							html.push("</thead>");
							html.push("<tbody>");
							html.push("</tbody>");
							html.push("</table>");
							
							$("#results").html( html.join("") );
						}
						
						$.each(data.cargo, function(index, value) {
							add_item(value);
						});
						
						$("time.timeago").timeago();
						
						search_status = "searched";
						search_interval_at = search_interval;
					}
				});
			}
			
			$("#search_form").on('submit', function(e) {
				e.preventDefault();
				
				$("#results").empty();
				
				search_ebay();
			});
			
			window.setInterval(function() {
				if("off" === search_status) {
					$(search_status_el).empty();
				} else if("searching" === search_status) {
					$(search_status_el).empty().html("Searching...");
				} else if("searched" === search_status) {
					search_interval_at -= 1;
					
					if(search_interval_at <= 0) {
						search_interval_at = 0;
						
						$(search_status_el).html("Refreshing...");
						
						search_ebay();
					} else {
						$(search_status_el).html(search_interval_at);
					}
				}
			}, 1000);
		});
	</script>
	
</body>
</html>