<?php
include '../includes/simple_html_dom.php';
date_default_timezone_set("America/Santiago");

echo "[" . date("d-m-y H:i:s") . "] ", "VTR CNNCL scraper start!", PHP_EOL;

// scraper vars
$config = json_decode(file_get_contents("vtr-cl.ext.CNNCL.json"));
$dict_fechas = array(
  strtotime("today"),
  strtotime("tomorrow"),
  strtotime("2 days")
);

$dict_canales = array(
  "519007859" => "cl.ext.cnncl"
);

$grilla_url = "http://televisionvtr.cl/index.php?obt=grillacat&canal_tipo=noticias&fecha=%FECHA%&comuna=Santiago&canal_cantidad=20&_=" . time();
$grilla_fields = "%FECHA%";

$detalles_url = "http://televisionvtr.cl/index.php?obt=minificha&channels=%CHANNELID%&programs=%PROGRAMID%&starttime=%STARTTIME%&startdate=%STARTDATE%&canal_tipo=Noticias&_=" . time();
$detalles_fields = array(
  "%CHANNELID%",
  "%PROGRAMID%",
  "%STARTDATE%",
  "%STARTTIME%"
);

// programming array
$programming = array();

// scraper day loop
foreach ($dict_fechas as $dia) {

  // day and general programming
  $dia = date("mdy", $dia);
  $grilla_data = json_decode(file_get_contents(str_replace($grilla_fields, $dia, $grilla_url)));
  $grilla = str_get_html($grilla_data->grilla);
  $grilla = $grilla->find("a.verficha");
  
  // scraper programming loop
  foreach ($grilla as $programa) {
    if (array_key_exists($programa->parent->{"data-chn"}, $dict_canales)) {
      // program data
      $start_time = $programa->parent->{"data-starttime"} . "00";
      $start_date = str_split($programa->parent->{"data-startdate"}, 2);
      $start_date = "20" . $start_date[2] . $start_date[0] . $start_date[1];
      $stop_time = $start_date . $start_time . " +0000" . " + " . substr_replace($programa->parent->{"data-duration"}, "hours ", 2, 0) . "minutes";

      $programme_start = date("YmdHis O", strtotime($start_date . $start_time . " +0000"));
      $programme_stop = date("YmdHis O", strtotime($stop_time));

      $programme_title = $programa->innertext;
      $programme_rating = $programa->next_sibling()->innertext;

      $programme_channel_id = $dict_canales[$programa->parent->{"data-chn"}];

      // url detalles building
      if ($config->detalles) {
        $build_fields = array(
          $programa->parent->{"data-chn"},
          $programa->parent->{"data-prog"},
          $programa->parent->{"data-startdate"},
          $programa->parent->{"data-starttime"}
        );
        $detalles_data = file_get_contents(str_replace($detalles_fields, $build_fields, $detalles_url));

        // get details
        $detalles_data = str_get_html($detalles_data);
        $detalles_data = $detalles_data->find("p", 0)->innertext;
        $programme_desc = $detalles_data ? $detalles_data : "No hay descripción disponible.";
      } else {
        $programme_desc = "No hay descripción disponible para este programa.";
      }


      // store into programming array
      $detalles_compilado = array(
        "channel_id" => $programme_channel_id,
        "start" => $programme_start,
        "stop" => $programme_stop,
        "title" => $programme_title,
        "desc" => $programme_desc,
        "rating" => $programme_rating
      );

      // save into a per-channel array
      $programming[$programme_channel_id][] = $detalles_compilado;
    }
  }
}

// save programming array to per-channel json files
foreach ($programming as $chan => $items) {
  file_put_contents("../data/vtr-" . $chan . ".json", json_encode($items));
}

echo "[" . date("d-m-y H:i:s") . "] ", "VTR CNNCL scraper done!", PHP_EOL;
