<?php

require_once 'UploadHandler.php';

class tsUploadHandler extends UploadHandler {

  // [ts] were in the parent constructor
  protected $tree = '';
  protected $media_table = '';
  protected $medialinks_table = '';
  protected $currentuser = '';
  protected $timeOffset = '';
  protected $mediatypeID = '';
  protected $media_folder = '';
  protected $thumbnail = [
         'folder' => '', 'prefix' => '', 'suffix' => '', 'maxwidth' => '', 'maxheight' => ''
  ];
  protected $mediapath = ''; // [ts] used to change upload_dir in constructor
  protected $mediaurl = ''; // [ts] used to change upload_url  in constructor
  protected $mediatypes_table = ''; // [ts] used in handle_file_upload
  protected $subfolder = ''; // [ts] used in handle_file_upload
  protected $added = 'added'; // [ts] used in handle_file_upload (ui snippet)

  function __construct($options = null, $initialize = true) {
    parent::__construct($options, $initialize);

    $this->tree = $options['tree'];
    $this->media_table = $options['media_table'];
    $this->medialinks_table = $options['medialinks_table'];
    $this->currentuser = $options['currentuser'];
    $this->time_offset = $options['time_offset'];
    $this->mediatypeID = $options['mediatypeID'];
    $this->media_folder = $options['media_folder'];
  }

  protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null) {
    // [ts] parent::handle_file_upload($uploaded_file, $name, $size, $type, $error, $index, $content_range);

    $file = new \stdClass();
    $file->name = $this->get_file_name($uploaded_file, $name, $size, $type, $error, $index, $content_range);
    $file->size = $this->fix_integer_overflow((int) $size);
    $file->type = $type;
    if ($this->validate($uploaded_file, $file, $error, $index)) {
      $this->handle_form_data($file, $index);
      $upload_dir = $this->get_upload_path();
      if (!is_dir($upload_dir)) {
        mkdir($upload_dir, $this->options['mkdir_mode'], true);
      }
      $file_path = $this->get_upload_path($file->name);
      $append_file = $content_range && is_file($file_path) &&
           $file->size > $this->get_file_size($file_path);
      if ($uploaded_file && is_uploaded_file($uploaded_file)) {
        // multipart/formdata uploads (POST method uploads)
        if ($append_file) {
          file_put_contents($file_path, fopen($uploaded_file, 'r'), FILE_APPEND);
        } else {
          move_uploaded_file($uploaded_file, $file_path);
        }
      } else {
        // Non-multipart uploads (PUT method support)
        file_put_contents($file_path, fopen('php://input', 'r'), $append_file ? FILE_APPEND : 0);
      }
      $file_size = $this->get_file_size($file_path, $append_file);
      if ($file_size === $file->size) {
        $file->url = $this->get_download_url($file->name);
        if ($this->is_valid_image_file($file_path)) {
          $this->handle_image_file($file_path, $file);
        }
      } else {
        $file->size = $file_size;
        if (!$content_range && $this->options['discard_aborted_uploads']) {
          unlink($file_path);
          $file->error = $this->get_error_message('abort');
        }
      }
      $this->set_additional_file_properties($file);
    }
    return $file;
  }

  // [ts] mediaId returned as a property of the $file argument

  private function handleDbTableEntries(&$file) {
    $fileparts = pathinfo($file->name);
    $form = strtoupper($fileparts['extension']);
    $newdate = date("Y-m-d H:i:s", time() + (3600 * $this->time_offset));
    $mediakey = $this->media_folder . "/" . $file->name;

    if ($this->options['subfolder']) {
      $filepath = $this->options['subfolder'] . "/" . $file->name;
      $thumbpath = $this->options['subfolder'] . "/" . $thumbpath;
    } else {
      $filepath = $file->name;
    }
    $query = "INSERT IGNORE INTO {$this->media_table} (mediatypeID,mediakey,gedcom,path,thumbpath,description,notes,width,height,datetaken,placetaken,owner,changedate,changedby,form,alwayson,map,abspath,status,cemeteryID,plot,showmap,linktocem,latitude,longitude,zoom,bodytext,usenl,newwindow,usecollfolder)
        VALUES (\"{$this->mediatypeID}\",\"$mediakey\",\"{$this->tree}\",\"$filepath\",\"$thumbpath\",\"{$file->name}\",\"\",\"0\",\"0\",\"\",\"\",\"\",\"$newdate\",\"{$this->currentuser}\",\"$form\",\"0\",\"\",\"0\",\"\",\"0\",\"\",\"0\",\"0\",\"\",\"\",\"0\",\"\",\"0\",\"0\",\"1\")";
    $result = tng_query($query);
    $success = tng_affected_rows();
    if ($result && $success) {
      $file->mediaID = tng_insert_id();
      adminwritelog("<a href=\"admin_editmedia.php?mediaID={$file->mediaID}\">{$this->options['added']}: {$file->mediaID}</a>");

      $query = "UPDATE {$this->options['mediatypes_table']} SET disabled=\"0\" where mediatypeID=\"{$this->mediatypeID}\"";
      tng_query($query);
    } else {
      $file->mediaID = "";
    }
  }
 
  // [ts] function was renamed from set_delete_file_properties
  protected function set_additional_file_properties($file) {
    // [ts] parent::set_additional_file_properties($file);

    $file->deleteUrl = $this->options['script_url']
         . $this->get_query_separator($this->options['script_url'])
         . $this->get_singular_param_name()
         . '=' . rawurlencode($file->name);
    $file->deleteType = $this->options['delete_type'];
    if ($file->deleteType !== 'DELETE') {
      $file->deleteUrl .= '&_method=DELETE';
    }
    if ($this->options['access_control_allow_credentials']) {
      $file->deleteWithCredentials = true;
    }
  }

  public function delete($print_response = true) {
    // [ts] parent::delete($print_response);

    $file_names = $this->get_file_names_params();
    if (empty($file_names)) {
      $file_names = array($this->get_file_name_param());
    }
    $response = array();
    foreach ($file_names as $file_name) {
      $file_path = $this->get_upload_path($file_name);
      $success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
      if ($success) {
        foreach ($this->options['image_versions'] as $version => $options) {
          if (!empty($version)) {
            $file = $this->get_upload_path($file_name, $version);
            if (is_file($file)) {
              unlink($file);
            }
          }
        }
      }
      $response[$file_name] = $success;
    }
    return $this->generate_response($response, $print_response);
  }

  protected function get_download_url($file_name, $version = null, $direct = false) {
    // [ts] parent::get_download_url($file_name, $version, $direct);

    if (!$direct && $this->options['download_via_php']) {
      $url = $this->options['script_url']
           . $this->get_query_separator($this->options['script_url'])
           . $this->get_singular_param_name() . '=' . rawurlencode($file_name);
      if ($version) {
        $url .= '&version=' . rawurlencode($version);
      }
      return $url . '&download=1';
    }
    if (empty($version)) {
      $version_path = '';
    } else {
      $version_url = $this->options['image_versions'][$version]['upload_url'];
      if ($version_url) {
        return $version_url . $this->get_user_path() . rawurlencode($file_name);
      }
      $version_path = rawurlencode($version) . '/';
    }
    return $this->options['upload_url'] . $this->get_user_path() . $version_path . rawurlencode($file_name);
  }
}
