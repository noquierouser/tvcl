#!/usr/bin/env php
<?php
// includes + requires
include 'includes/helper_functions.php';

date_default_timezone_set("America/Santiago");

// config
$config = json_decode(file_get_contents("config.json"));
$canales = array();

foreach (glob($config->channelsFolder . "*.json") as $archivo) {
  foreach (json_decode(file_get_contents($archivo)) as $item) {
    $canales[] = $item;
  }
}

// chlist root mode
$chlist = new SimpleXMLElement("<chlist></chlist>");

// channel listing
foreach ($canales as $canal) {
  // channel type
  $channel_type = explode(".", $canal->id);
  $channel_type = $channel_type[1];

  if ($channel_type != "radio") {
    // url type
    $url_canal = ($canal->url->low) ? $canal->url->low : $canal->url->address;
    $url_type = explode(":", $canal->url->address);
    $url_type = $url_type[0];

    // channel data
    $channel = $chlist->addChild("channel");
    $channel->addChild("name", xml_entity_transform($canal->name));
    $channel->addChild("logo", "http://noquierouser.com/tvcl/logos/" . str_replace(".", "-", $canal->id) . ".png");

    // url line
    if ($url_type != "rtmp") {
      $channel->addChild("link", $url_canal);
    }
  }
}

// generate file
$dom = dom_import_simplexml($chlist)->ownerDocument;
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
file_put_contents("tvcl-list.tkl", $dom->saveXML());

// output for cron
echo "[", date("d-m-y H:i:s"), "] ", "TKL playlist generated successfully.", PHP_EOL;
