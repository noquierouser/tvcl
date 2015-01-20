<?php
$config = json_decode(file_get_contents("config.json"));
$canales = json_decode(file_get_contents($config->channelsFile));

// m3u header
$playlist = "#EXTM3U" . PHP_EOL;

// channels loop
foreach ($canales as $canal) {
  // info line
  $playlist .= "#EXTINF:-1 "
            . "tvg-id=\"" . $canal->id . "\" "
            . "group-title=\"" . $canal->group . "\""
            . "," . $canal->name
            . PHP_EOL;

  // url line
  // rtmp has a different structure
  switch ($canal->url->type) {
    case 'rtmp':
      $playlist .= "rtmp://\$OPT:rtmp-raw=" . $canal->url->address . PHP_EOL;
      break;
    
    default:
      $playlist .= $canal->url->address . PHP_EOL;
      break;
  }
}

echo $playlist;