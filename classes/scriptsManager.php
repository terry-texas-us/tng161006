<?php

class scriptsManager {
  private $data = []; // [ts] overloaded data

  private static $showShare = false;
  private static $http = 'http';

  public function __construct($id) {
    $this->id = $id;
  }

  public function __set($name, $value) {
    $this->data[$name] = $value;
  }

  public function __get($name) {
    if (array_key_exists($name, $this->data)) {
      $value = $this->data[$name];
    } else {
      $trace = debug_backtrace();
      trigger_error('Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_NOTICE);
      $value = null;
    }
    return $value;
  }

  public static function setShowShare($showShare, $http = '$http') {
    scriptsManager::$showShare = $showShare;
    scriptsManager::$http = $http;
  }

  public static function getScript($scriptName, $flags = null) {
    $out = "<script> // $scriptName\n";
    switch ($scriptName) {
      case 'litbox-globals':
        $out .= "var tnglitbox;\n";
        $out .= "var share = 0;\n";

        if (isset($flags['error']) && $flags['error']) {
          $out .= "$(document).ready(function(){openLogin('ajx_login.php?p=&message={$flags['error']}');});\n";
        }
        break;

      case 'toggleall':
        $out .= "function toggleAll(flag) {\n";
        $out .= "for (var i = 0; i < document.form2.elements.length; i++) {\n";
        $out .= "if (document.form2.elements[i].type == \"checkbox\") {\n";
        $out .= "if (flag) {\n";
        $out .= "document.form2.elements[i].checked = true;\n";
        $out .= "} else {\n";
        $out .= "document.form2.elements[i].checked = false;\n";
        $out .= "}\n";
        $out .= "}\n";
        $out .= "}\n";
        $out .= "}\n";
        break;
      case 'sharethis':
        $out .= "stLight.options({publisher: \"be4e16ed-3cf4-460b-aaa4-6ac3d0e3004b\",doNotHash:true,doNotCopy:true,hashAddressBar:false});\n";
        break;

      default:
        break;
    }
    $out .= "</script> <!-- $scriptName -->\n";
    return $out;
  }

  public static function buildScriptElements($flags, $id = 'public') {
    $out = "<script src='node_modules/jquery/dist/jquery.min.js'></script>\n";
    $out .= "<script src='js/jquery-ui.min.js'></script>\n";
    $out .= "<script src='node_modules/tether/dist/js/tether.min.js'></script>\n";
    $out .= "<script src='_/js/bootstrap.min.js'></script>\n";
    $out .= "<script src='node_modules/svg-injector/dist/svg-injector.min.js'></script>\n";
    $out .= "<script src='js/net.js'></script>\n";
    $out .= "<script src='js/textSnippets.js'></script>\n";
    $out .= "<script src='js/modalDialog.js'></script>\n";

    if ($id === 'admin') {
      // [ts] $out .= "<script src='js/jquery.ui.touch-punch.min.js'></script>\n";
      $out .= scriptsManager::getScript('toggleall');
    } elseif ($id === 'public') {

      if (isset($flags['scripting'])) {
        $out .= $flags['scripting'];
      }
      if (scriptsManager::$showShare) {
        $w = HeadElementSection::$http === 'https' ? 'ws' : 'w';
        $out .= "<script src='" . HeadElementSection::$http . "://{$w}.sharethis.com/button/buttons.js'></script>\n";
        $out .= scriptsManager::getScript('sharethis');
      }
      $out .= scriptsManager::getScript('litbox-globals', $flags);
    }
    return $out;
  }
  
}
