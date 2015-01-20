<?php
include 'includes/simple_html_dom.php';

date_default_timezone_set("America/Santiago");

// config
$config = json_decode(file_get_contents("config.json"));
$canales = json_decode(file_get_contents($config->channelsFile));
$dias = array(
  strtotime("today"),
  strtotime("tomorrow") // TODO: control this using config file
  // strtotime("2 days"),
  // strtotime("3 days"),
  // strtotime("4 days"),
  // strtotime("5 days")
);

$tv = new SimpleXMLElement("<tv></tv>");
$tv->addAttribute("date", date("YmdHis O"));
$tv->addAttribute("generator-info-name", $config->generatorInfoName);
$tv->addAttribute("generator-info-url", $config->generatorInfoURL);

// channel listing generator
foreach ($canales as $canal) {
  $channel = $tv->addChild("channel");
  $channel->addAttribute("id", $canal->id);
  $channel->addChild("display-name", $canal->name);
}

// channel programming generator
foreach ($dias as $dia) {
  foreach ($canales as $canal) {
    if ($canal->code) {
      // channel code exists
      $data_url = $config->scraperURL;
      $data_url = str_replace("%CODIGO%", $canal->code, $data_url);
      $data_url = str_replace("%FECHA%", date("Y-m-d", $dia), $data_url);
      $data_json = json_decode(file_get_contents($data_url));
      $parrilla = str_get_html($data_json->div_parrilla);
      $programas = $parrilla->find("p.descripcion");

      // programmes loop
      foreach ($programas as $programa) {
        // programme title, start and end
        $descripcion = explode("</span>", $programa->innertext);
        $descripcion[1] = explode(" - ", str_replace(":", "", trim($descripcion[1])));

        $nombre_show = str_replace("<span>", "", $descripcion[0]);
        $inicio_show = strtotime(date("Y-m-d", $dia) . " " . $descripcion[1][0]);
        $fin_show = strtotime(date("Y-m-d", $dia) . " " . $descripcion[1][1]);

        // programme description
        $detalles_url = str_replace(
          array(
            $config->detailsFields->dataID,
            $config->detailsFields->idProg,
            $config->detailsFields->fechaProg,
            $config->detailsFields->codCanal
          ),
          array(
            $programa->parent->{"data-id"},
            $programa->parent->{"data-idprog"},
            date("Y-m-d", $dia),
            $canal->code
          ),
          $config->detailsURL
        );

        // TODO: optimize this code
        $detalles_data = file_get_contents($detalles_url);
        $detalles_programa = str_get_html($detalles_data);
        $sinopsis = explode("<span>", $detalles_programa->find("p", -1)->innertext);
        $sinopsis = str_replace("</span>", "", $sinopsis[1]);
        $sinopsis = (empty($sinopsis)) ? "No hay descripción disponible." : $sinopsis; // detect empty sinopsis

        // add xml child with compiled data
        $programme = $tv->addChild("programme");
        $programme->addAttribute("channel", $canal->id);
        $programme->addAttribute("start", date("YmdHis O", $inicio_show));
        $programme->addAttribute("stop", date("YmdHis O", $fin_show));
        $programme->addChild("title", $nombre_show);
        $programme->addChild("desc", $sinopsis);
      }
    } else {
      // no channel code
      // create a programme that lasts the whole day
      $inicio_show = strtotime(date("Y-m-d", $dia) . " 00:00:00");
      $fin_show = strtotime(date("Y-m-d", $dia) . " 23:59:59");

      $programme = $tv->addChild("programme");
      $programme->addAttribute("channel", $canal->id);
      $programme->addAttribute("start", date("YmdHis O", $inicio_show));
      $programme->addAttribute("stop", date("YmdHis O", $fin_show));
      $programme->addChild("title", "Programación " . $canal->name . " " . date("d-m-Y", $inicio_show));
    }
  }
}

echo $tv->asXML();