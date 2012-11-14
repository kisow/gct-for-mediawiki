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
    'url' => 'https://github.com/kisow/gct.git'
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
  global $haxis_title;
  global $vaxis_title;
  global $options;
  
  $size = array("900", "500");
  $title = "";
  $div_id = uniqid();
  $type = "LineChart";
  $haxis_title = "";
  $vaxis_title = "";
  $options = "";
}

function gctArgsParseCommon ( $args ) {
	global $size;
	global $title;
	global $type;
	global $haxis_title;
	global $vaxis_title;
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
			case "haxis_title":
				$haxis_title = $value;
			break;
			case "vaxis_title":
				$vaxis_title = $value;
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
	global $haxis_title;
	global $vaxis_title;
	global $options;

	gctInit();
	gctArgsParseCommon($args);

	$fieldsep = ",";
	$lines = explode ("\n",$input); 
	foreach($lines as $line) {
		if ($line != "") {
			$data[] = explode($fieldsep,$line);
		}
	}

	$data_str = "";
	$startcol = 0;
	$startrow = 0;
	for ($i = $startrow; $i < count($data); $i++) {
		if ($i != $startrow) $data_str = $data_str . ",";
		$data_str = $data_str . "[";
		for ($j = $startcol; $j < count($data[0]); $j++) {
			if ($j != $startrow) $data_str = $data_str . ",";
			if ($i == $startcol) {
				$data_str = $data_str . "'" . $data[$i][$j] . "'"; 
			} else {
				$data_str = $data_str . $data[$i][$j]; 
			}
		} 
		$data_str = $data_str . "]\n";
	}
	$hAxisTitle = $data[0][0];

	$options_str = "title: '$title'";
	if ($haxis_title != "") {
		$options_str = $options_str . ", hAxis: {title: '$haxis_title', titleTextStyle: {color:'red'}}";
	}
	if ($vaxis_title != "") {
		$options_str = $options_str . ", vAxis: {title: '$vaxis_title', titleTextStyle: {color:'red'}}";
	}
	if ($options != "") {
		$options_str = "$options";
	}

	$script = <<<EOT
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript">
		google.load("visualization", "1", {packages:["corechart"]});
		google.setOnLoadCallback(drawChart);
		function drawChart() {
			var data = google.visualization.arrayToDataTable([$data_str]);
			var options = { $options_str };
			var chart = new google.visualization.$type(document.getElementById('$div_id'));
			chart.draw(data, options);
		}
	</script>
EOT;

	// 미리보기 화면에서 출력되도록 
	print "$script";

	$retval = <<<EOT
		$script
		<div id="$div_id" style="width: $size[0]px; height: $size[1]px;"></div> 
EOT;

	return $retval;
}

// vim: ts=4 sw=4
