#!/usr/bin/env php
<?php
date_default_timezone_set("America/Santiago");
// config
$config = json_decode(file_get_contents("config.json"));
$canales = array();

foreach (glob($config->channelsFolder . "*.json") as $archivo) {
  foreach (json_decode(file_get_contents($archivo)) as $item) {
    $canales[] = $item;
  }
}

// m3u header
$playlist = "#EXTM3U" . PHP_EOL;

// channels loop
foreach ($canales as $canal) {
  // channel type
  $channel_type = explode(".", $canal->id);
  $channel_type = $channel_type[1];

  // url type
  $url_type = explode(":", $canal->url->address);
  $url_type = $url_type[0];

  // info line
  $playlist .= "#EXTINF:-1 ";
  $playlist .= ($channel_type == "radio") ? "radio=\"true\" " : null;
  $playlist .= "tvg-logo=\"" . str_replace(".", "-", $canal->id) . ".png" . "\" ";
  $playlist .= "tvg-id=\"" . $canal->id . "\" ";
  $playlist .= "group-title=\"" . $canal->group . "\"";
  $playlist .= "," . $canal->name;
  $playlist .= PHP_EOL;

  // url line
  if ($canal->status == "active") {
    switch ($url_type) {
      case 'rtmp':
        $playlist .= "rtmp://\$OPT:rtmp-raw=" . $canal->url->address . PHP_EOL;
        break;
      
      default:
        $playlist .= $canal->url->address . PHP_EOL;
        break;
    }
  } else {
    $playlist .= $config->offlineSignal . PHP_EOL;
  }
}

// generate file
file_put_contents("list.m3u", $playlist);

// output for cron
echo "[", date("d-m-y H:i:s"), "] ", "Playlist generated successfully. " . count($canales) . " channels generated.", PHP_EOL;
