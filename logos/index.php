<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Listado de iconos</title>
  <style>
  body {
    font-family: "Roboto", "Segoe UI", sans-serif;
    max-width: 960px;
    margin: 0 auto;
    padding: 0 1em;
    background: #bbb;
  }

  ul {
    margin: 0;
    padding: 0;
    list-style-type: none;
  }

  ul li {
    margin: 0;
    padding: 0;
    display: inline-block;
  }
  </style>
</head>
<body>
  <h1>Listado de iconos</h1>

  <h2>Nacionales</h2>
  <ul>
    <?php foreach (glob("cl-nac-*.png") as $logo) : ?>
    <li><img src="<?= $logo ?>" width="96px"></li>
    <?php endforeach; ?>
  </ul>

  <h2>Regionales</h2>
  <ul>
    <?php foreach (glob("cl-reg-*.png") as $logo) : ?>
    <li><img src="<?= $logo ?>" width="96px"></li>
    <?php endforeach; ?>
  </ul>

  <h2>Comunitarios</h2>
  <ul>
    <?php foreach (glob("cl-comunitarios-*.png") as $logo) : ?>
    <li><img src="<?= $logo ?>" width="96px"></li>
    <?php endforeach; ?>
  </ul>

  <h2>Otros</h2>
  <ul>
    <?php foreach (glob("cl-ext-*.png") as $logo) : ?>
    <li><img src="<?= $logo ?>" width="96px"></li>
    <?php endforeach; ?>
  </ul>

  <h2>Radios</h2>
  <ul>
    <?php foreach (glob("cl-radio-*.png") as $logo) : ?>
    <li><img src="<?= $logo ?>" width="96px"></li>
    <?php endforeach; ?>
  </ul>
</body>
</html>