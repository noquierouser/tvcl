#!/usr/bin/env php
<?php
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
  // info line
  $playlist .= "#EXTINF:-1 ";
  $playlist .= ($canal->group == "Radios") ? "radio=\"true\" " : null;
  $playlist .= "tvg-logo=\"" . str_replace(".", "-", $canal->id) . ".png" . "\" ";
  $playlist .= "tvg-id=\"" . $canal->id . "\" ";
  $playlist .= "group-title=\"" . $canal->group . "\"";
  $playlist .= "," . $canal->name;
  $playlist .= PHP_EOL;

  // url type
  $url_type = explode(":", $canal->url->address);
  $url_type = $url_type[0];

  // url line
  switch ($url_type) {
    case 'rtmp':
      $playlist .= "rtmp://\$OPT:rtmp-raw=" . $canal->url->address . PHP_EOL;
      break;
    
    default:
      $playlist .= $canal->url->address . PHP_EOL;
      break;
  }
}

// generate file
file_put_contents("list.m3u", $playlist);

// output for cron
echo "[", date("d-m-y H:i:s"), "] ", "Playlist generated successfully. " . count($canales) . " channels generated.", PHP_EOL;
