<?php

/**
 * gct.php
 * provide tags for drawing charts the easy way using google chart tools (javascript).
 * written by kisow
 * https://github.com/kisow/gct.git
 * To activate the functionality of this extension include the following in your
 * LocalSettings.php file:
 * require_once( "$IP/extensions/gct/gct.php" );
 */

error_reporting (E_ALL);

if(! defined( 'MEDIAWIKI' ) ) {
  echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
  die( -1 );
} else {
  $wgExtensionCredits['parserhook'][] = array(
    'name' => 'google chart tools (javascript)',
    'author' =>'kisow',
    'url' => 'https://github.com/kisow/gct.git',
    'description' => 'this is an extension to use google chart tools in your wiki easily.'
    );
}

$wgExtensionFunctions[] = 'gctSetup';

// -----------------------------------------------------------------------------
function gctSetup() {
  global $wgParser;
  $wgParser->setHook( 'gct', 'gctParser' );
}
          
function gctInit() {
  global $size;
  global $title;
  global $div_id;
  global $type;
  global $haxis;
  global $vaxis;
  global $options;
  
  $size = array("900", "500");
  $title = "";
  $div_id = uniqid();
  $type = "LineChart";
  $haxis = "";
  $vaxis = "";
  $options = "";
}

function gctArgsParseCommon ( $args ) {
	global $size;
	global $title;
	global $type;
	global $haxis;
	global $vaxis;
	global $options;

	if (is_null($args)) return;

	foreach( $args as $name => $value ) {
		switch ($name) {
			case "size":
				$size = explode("x", $value);
			break;
			case "title":
				$title = $value;
			break;
			case "type":
				$type = $value;
			break;
			case "haxis":
				$haxis = $value;
			break;
			case "vaxis":
				$vaxis = $value;
			break;
			case "options":
				$options = $value;
			break;
		}
	}
}
// -----------------------------------------------------------------------------
function gctParser( $input, $args, $parser ) {
	global $size;
	global $title;
	global $div_id;
	global $type;
	global $haxis;
	global $vaxis;
	global $options;

	gctInit();
	gctArgsParseCommon($args);

	$data_str = "\n";
	$fieldsep = ",";
	$lines = explode ("\n",$input); 
	foreach($lines as $line) {
		if ($line != "") {
			if (strlen($data_str) > 1) {
				$data_str = $data_str . ",";
			}
			$data_str = $data_str . "[" . $line . "]\n";
		}
	}

	$options_str = "title: '$title'";
	if ($haxis != "") {
		$options_str = $options_str . ", hAxis: {title: '$haxis', titleTextStyle: {color:'red'}}";
	}
	if ($vaxis != "") {
		$options_str = $options_str . ", vAxis: {title: '$vaxis', titleTextStyle: {color:'red'}}";
	}
	if ($options != "") {
		$options_str = "$options";
	}

	switch ($type) {
		case "Gauge":
			$package = "gauge";
			break;
		case "GeoChart":
			$package = "geochart";
			break;
		case "Table":
			$package = "table";
			break;
		case "TreeMap":
			$package = "treemap";
			break;
		default:
			$package = "corechart";
			break;
	}

	$script = <<<EOT
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript">
		google.load("visualization", "1", {packages:['$package']});
		google.setOnLoadCallback(drawChart);
		function drawChart() {
			var data = google.visualization.arrayToDataTable([$data_str]);
			var options = { $options_str };
			var chart = new google.visualization.$type(document.getElementById('$div_id'));
			chart.draw(data, options);
		}
	</script>
EOT;

	$retval = <<<EOT
		$script
		<div id="$div_id" style="width: $size[0]px; height: $size[1]px;"></div> 
EOT;

	return $retval;
}

// vim: ts=4 sw=4
