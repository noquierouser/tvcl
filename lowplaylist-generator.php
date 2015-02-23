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

  // url low and type
  $url_canal = ($canal->url->low) ? $canal->url->low : $canal->url->address;
  $url_type = explode(":", $url_canal);
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
  switch ($url_type) {
    case 'rtmp':
      $playlist .= "rtmp://\$OPT:rtmp-raw=" . $url_canal . PHP_EOL;
      break;
    
    default:
      $playlist .= $url_canal . PHP_EOL;
      break;
  }
}

// generate file
file_put_contents("listlow.m3u", $playlist);

// output for cron
echo "[", date("d-m-y H:i:s"), "] ", "Playlist generated successfully. " . count($canales) . " channels generated.", PHP_EOL;
