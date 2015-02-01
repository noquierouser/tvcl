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

// tv root node
$tv = new SimpleXMLElement("<tv></tv>");
$tv->addAttribute("date", date("YmdHis O"));
$tv->addAttribute("generator-info-name", $config->generatorInfoName);
$tv->addAttribute("generator-info-url", $config->generatorInfoURL);

// channel listing loop
foreach ($canales as $canal) {
  $channel = $tv->addChild("channel");
  $channel->addAttribute("id", $canal->id);
  $channel->addChild("display-name", xml_entity_transform($canal->name));
  $icon = $channel->addChild("icon");
  $icon->addAttribute("src", $config->logosFolder . str_replace(".", "-", $canal->id) . ".png");
}

// programming listing loop
foreach ($canales as $canal) {
  if ($canal->scraper) {
    $filepath = "data/" . $canal->scraper . "-" . $canal->id . ".json";
    $programacion = json_decode(file_get_contents($filepath));

    foreach ($programacion as $item) {
      $programme = $tv->addChild("programme");
      $programme->addAttribute("channel", $item->channel_id);
      $programme->addAttribute("start", $item->start);
      $programme->addAttribute("stop", $item->stop);
      $programme->addChild("title", xml_entity_transform($item->title));
      $programme->addChild("desc", xml_entity_transform($item->desc));

      if (!empty($item->rating)) {
        $rating = $programme->addChild("rating");
        $rating->addAttribute("system", "VCHIP");
        $rating->addChild("value", $item->rating);
      }
    }
  } else {
    // generic programming for seven days
    $fechas = array(
      "today",
      "today + 1 day",
      "today + 2 day",
      "today + 3 day",
      "today + 4 day",
      "today + 5 day",
      "today + 6 day",
      "today + 7 day"
    );

    foreach ($fechas as $dia) {
      $programme = $tv->addChild("programme");
      $programme->addAttribute("channel", $canal->id);
      $programme->addAttribute("start", date("YmdHis O", strtotime($dia . " 00:00")));
      $programme->addAttribute("stop", date("YmdHis O", strtotime($dia . " 00:00 + 1 day")));
      $programme->addChild("title", "Programación " . $canal->name);
      $programme->addChild("desc", "Programación " . $canal->name . " - " . date("d-m-Y", strtotime($dia)));
    }
  }
}


// save to xml file
$dom = dom_import_simplexml($tv)->ownerDocument;
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
file_put_contents("prog.xml", $dom->saveXML());

// output for cron
echo "[", date("d-m-y H:i:s"), "] ", "EPG generated successfully.", PHP_EOL;
