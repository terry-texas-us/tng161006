<?php

require_once 'UploadHandler.php';

class tsUploadHandler extends UploadHandler {

  // [ts] were in the parent constructor
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
  protected $mediatypes_table = ''; // [ts] used in handleFileUpload
  protected $subfolder = ''; // [ts] used in handleFileUpload
  protected $added = 'added'; // [ts] used in handleFileUpload (ui snippet)

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

  protected function handleFileUpload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null) {
    // [ts] parent::handleFileUpload($uploaded_file, $name, $size, $type, $error, $index, $content_range);

    $file = new \stdClass();
    $file->name = $this->getFileName($uploaded_file, $name, $size, $type, $error, $index, $content_range);
    $file->size = $this->fixIntegerOverflow((int) $size);
    $file->type = $type;
    if ($this->validate($uploaded_file, $file, $error, $index)) {
      $this->handleFormData($file, $index);
      $upload_dir = $this->getUploadPath();
      if (!is_dir($upload_dir)) {
        mkdir($upload_dir, $this->options['mkdir_mode'], true);
      }
      $file_path = $this->getUploadPath($file->name);
      $append_file = $content_range && is_file($file_path) &&
           $file->size > $this->getFileSize($file_path);
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
      $file_size = $this->getFileSize($file_path, $append_file);
      if ($file_size === $file->size) {
        $file->url = $this->getDownloadUrl($file->name);
        if ($this->isValidImageFile($file_path)) {
          $this->handleImageFile($file_path, $file);
        }
      } else {
        $file->size = $file_size;
        if (!$content_range && $this->options['discard_aborted_uploads']) {
          unlink($file_path);
          $file->error = $this->getErrorMessage('abort');
        }
      }
      $this->setAdditionalFileProperties($file);
    }
    return $file;
  }

  // [ts] mediaId returned as a property of the $file argument

  private function handleDbTableEntries(&$file) {
    $fileparts = pathinfo($file->name);
    $form = strtoupper($fileparts['extension']);
    $newdate = date('Y-m-d H:i:s', time() + (3600 * $this->time_offset));
    $mediakey = $this->media_folder . '/' . $file->name;

    if ($this->options['subfolder']) {
      $filepath = $this->options['subfolder'] . '/' . $file->name;
      $thumbpath = $this->options['subfolder'] . '/' . $thumbpath;
    } else {
      $filepath = $file->name;
    }
    $query = "INSERT IGNORE INTO {$this->media_table} (mediatypeID, mediakey, path, thumbpath, description, notes, width, height, datetaken, placetaken, owner, changedate, changedby, form, alwayson, map, abspath, status, cemeteryID, plot, showmap, linktocem, latitude, longitude, zoom, bodytext, usenl, newwindow, usecollfolder) 
      VALUES ('{$this->mediatypeID}', '$mediakey', '$filepath', '$thumbpath', '{$file->name}', '', '0', '0', '', '', '', '$newdate', '{$this->currentuser}', '$form', '0', '', '0', '', '0', '', '0', '0', '', '', '0', '', '0', '0', '1')";
    $result = tng_query($query);
    $success = tng_affected_rows();
    if ($result && $success) {
      $file->mediaID = tng_insert_id();
      adminwritelog("<a href=\"mediaEdit.php?mediaID={$file->mediaID}\">{$this->options['added']}: {$file->mediaID}</a>");

      $query = "UPDATE {$this->options['mediatypes_table']} SET disabled=\"0\" WHERE mediatypeID=\"{$this->mediatypeID}\"";
      tng_query($query);
    } else {
      $file->mediaID = '';
    }
  }
 
  // [ts] function was renamed from set_delete_file_properties

  protected function setAdditionalFileProperties($file) {
    // [ts] parent::setAdditionalFileProperties($file);

    $file->deleteUrl = $this->options['script_url']
         . $this->getQuerySeparator($this->options['script_url'])
         . $this->getSingularParamName()
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

    $file_names = $this->getFileNamesParams();
    if (empty($file_names)) {
      $file_names = [$this->getFileNameParam()];
    }
    $response = [];
    foreach ($file_names as $file_name) {
      $file_path = $this->getUploadPath($file_name);
      $success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
      if ($success) {
        foreach ($this->options['image_versions'] as $version => $options) {
          if (!empty($version)) {
            $file = $this->getUploadPath($file_name, $version);
            if (is_file($file)) {
              unlink($file);
            }
          }
        }
      }
      $response[$file_name] = $success;
    }
    return $this->generateResponse($response, $print_response);
  }

  protected function getDownloadUrl($file_name, $version = null, $direct = false) {
    // [ts] parent::getDownloadUrl($file_name, $version, $direct);

    if (!$direct && $this->options['download_via_php']) {
      $url = $this->options['script_url']
           . $this->getQuerySeparator($this->options['script_url'])
           . $this->getSingularParamName() . '=' . rawurlencode($file_name);
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
        return $version_url . $this->getUserPath() . rawurlencode($file_name);
      }
      $version_path = rawurlencode($version) . '/';
    }
    return $this->options['upload_url'] . $this->getUserPath() . $version_path . rawurlencode($file_name);
  }

}
