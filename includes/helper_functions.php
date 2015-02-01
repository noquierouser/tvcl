<?php

function xml_entity_transform($text = null) {
  if (empty($text)) {
    return "";
  }

  $text = str_replace("<", "&lt;", $text);
  $text = str_replace(">", "&gt;", $text);
  $text = str_replace("&", "&amp;", $text);
  return $text;
}