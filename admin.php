<?php
defined('IMG2WEBP_PATH') or die('Hacking attempt!');

check_status(ACCESS_ADMINISTRATOR);

// add_uploaded_file() isn't autoloaded the way core query helpers are -
// explicit include needed (same lesson learned the hard way with pwg_mail()
// in the cart plugin)
include_once(PHPWG_ROOT_PATH.'admin/include/functions_upload.inc.php');

$conf_i2w = img2webp_conf();
$imagick_command = img2webp_find_imagick_command();
$imagick_available = ($imagick_command !== false);

$results = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['img2webp_action']))
{
  $action = $_POST['img2webp_action'];

  if ($action == 'save_settings')
  {
    $conf_i2w['quality'] = max(1, min(100, (int)$_POST['quality']));
    conf_update_param('img2webp', $conf_i2w);
    $page['infos'][] = l10n('Information data registered in database');
  }
  elseif ($action == 'upload' && $imagick_available)
  {
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $results = img2webp_process_upload($_FILES['photos'] ?? array(), $category_id, $conf_i2w['quality'], $imagick_command);
  }
}

// Piwigo's own pwg_image::is_ext_imagick()/get_ext_imagick_command() detect
// the CLI via `exec('command -v magick', ...)` - a POSIX shell builtin that
// doesn't exist on Windows, so detection always falls through to `convert`
// there, which then hits Windows' own unrelated convert.exe instead of
// ImageMagick. Doing our own detection here avoids relying on that (broken
// on Windows, fine on the Linux hosts this actually ships to - but no
// reason not to be robust on both).
function img2webp_find_imagick_command()
{
  global $conf;

  foreach (array('magick', 'convert') as $cmd)
  {
    $full = $conf['ext_imagick_dir'].$cmd.' -version';
    @exec($full, $out, $ret);
    if ($ret === 0 && !empty($out[0]) && stripos($out[0], 'ImageMagick') !== false)
    {
      return $cmd;
    }
  }

  return false;
}

$query = '
SELECT id, name, uppercats
  FROM '.CATEGORIES_TABLE.'
  ORDER BY name ASC
;';
$categories = query2array($query);

$template->assign(
  array(
    'IMG2WEBP_IMAGICK_AVAILABLE' => $imagick_available,
    'IMG2WEBP_QUALITY' => $conf_i2w['quality'],
    'IMG2WEBP_CATEGORIES' => $categories,
    'IMG2WEBP_RESULTS' => $results,
    'IMG2WEBP_ADMIN_URL' => get_root_url().'admin.php?page=plugin-'.IMG2WEBP_ID,
    )
  );

$template->set_filename('img2webp_admin_content', realpath(IMG2WEBP_PATH.'template/admin.tpl'));
$template->assign_var_from_handle('ADMIN_CONTENT', 'img2webp_admin_content');

// Converts one uploaded file to WebP via ImageMagick, then hands the
// converted file to Piwigo's own add_uploaded_file() - from that point on
// this is a completely normal Piwigo photo, registered and processed the
// same as any native upload (derivatives, metadata, everything).
function img2webp_process_upload($files, $category_id, $quality, $imagick_command)
{
  global $conf;

  $results = array();

  if (empty($files['name']) || !is_array($files['name']))
  {
    return $results;
  }

  $count = count($files['name']);
  for ($i = 0; $i < $count; $i++)
  {
    $original_name = $files['name'][$i];
    $tmp_path = $files['tmp_name'][$i];
    $error = $files['error'][$i];

    if ($error !== UPLOAD_ERR_OK || empty($tmp_path))
    {
      $results[] = array('name' => $original_name, 'ok' => false, 'message' => l10n('Upload error'));
      continue;
    }

    $image_info = @getimagesize($tmp_path);
    if ($image_info === false || !in_array($image_info[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP)))
    {
      $results[] = array('name' => $original_name, 'ok' => false, 'message' => l10n('Not a supported image file'));
      continue;
    }

    $webp_tmp_path = $tmp_path.'-converted.webp';

    $exec = $conf['ext_imagick_dir'].$imagick_command;
    $exec .= ' '.escapeshellarg(realpath($tmp_path));
    $exec .= ' -auto-orient -quality '.(int)$quality;
    $exec .= ' '.escapeshellarg($webp_tmp_path);
    $exec .= ' 2>&1';
    @exec($exec, $exec_output, $return_var);

    if ($return_var !== 0 || !file_exists($webp_tmp_path))
    {
      $results[] = array('name' => $original_name, 'ok' => false, 'message' => l10n('WebP conversion failed'));
      continue;
    }

    $new_filename = pathinfo($original_name, PATHINFO_FILENAME).'.webp';
    $categories = !is_null($category_id) ? array($category_id) : null;

    $image_id = add_uploaded_file($webp_tmp_path, $new_filename, $categories);

    if (file_exists($webp_tmp_path))
    {
      @unlink($webp_tmp_path);
    }

    $results[] = array(
      'name' => $original_name,
      'ok' => !empty($image_id),
      'message' => !empty($image_id) ? l10n('Uploaded as WebP').' ('.$new_filename.')' : l10n('Registration failed'),
      );
  }

  return $results;
}
