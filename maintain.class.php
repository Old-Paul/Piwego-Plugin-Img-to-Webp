<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

class img2webp_maintain extends PluginMaintain
{
  private $default_conf = array(
    'quality' => 82,
    );

  function install($plugin_version, &$errors=array())
  {
    global $conf;

    if (empty($conf['img2webp']))
    {
      conf_update_param('img2webp', $this->default_conf, true);
    }
    else
    {
      $existing = safe_unserialize($conf['img2webp']);
      $merged = array_merge($this->default_conf, is_array($existing) ? $existing : array());
      conf_update_param('img2webp', $merged);
    }
  }

  function activate($plugin_version, &$errors=array())
  {
    $this->install($plugin_version, $errors);
  }

  function update($old_version, $new_version, &$errors=array())
  {
    $this->install($new_version, $errors);
  }

  function deactivate()
  {
  }

  function uninstall()
  {
    pwg_query('DELETE FROM `'.CONFIG_TABLE.'` WHERE param = "img2webp";');
  }
}
