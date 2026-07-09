<?php
/*
Plugin Name: Img2Webp
Version: 1.0.0
Description: Upload photos as WebP to save space and speed up downloads. Adds its own upload page - separate from Piwigo's native "Add Photos", which is left untouched.
Author: Webgoodies
Has Settings: true
License: GPL2
*/

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

define('IMG2WEBP_ID', basename(dirname(__FILE__)));
define('IMG2WEBP_PATH', PHPWG_PLUGINS_PATH.IMG2WEBP_ID.'/');

add_event_handler('init', 'img2webp_init');
function img2webp_init()
{
  load_language('plugin.lang', IMG2WEBP_PATH);
}

function img2webp_conf()
{
  global $conf;
  $c = safe_unserialize($conf['img2webp']);
  return array(
    'quality' => isset($c['quality']) ? (int)$c['quality'] : 82,
    );
}
