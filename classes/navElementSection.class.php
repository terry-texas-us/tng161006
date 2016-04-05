<?php

class NavElementSection {
    
  protected $out = '';
  protected $id = 'admin';

  protected static $currentUser = '';
  public static $allowAdmin = true;
  protected static $homePage = 'index.php';
  protected static $maintenanceIsOn = false;
  protected static $maintenanceMessage = '';
  protected static $helpPath = '';

  protected $data = []; // [ts] overloaded data mainly for vocabulary text

  public function __construct($id) {
    $this->id = $id;
  }
  public function __set($name, $value) {
    $this->data[$name] = $value;
  }

  public function __get($name) {
    if (array_key_exists($name, $this->data)) {
      return $this->data[$name];
    }
    $trace = debug_backtrace();
    trigger_error('Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_NOTICE);
    return null;
  }

  public static function currentUser($currentUser) {
    NavElementSection::$currentUser = $currentUser;
  }  

  public static function helpPath($helpPath) {
    NavElementSection::$helpPath = $helpPath;
  }
    
  public static function allowAdmin($allowAdmin) {
    NavElementSection::$allowAdmin = $allowAdmin;
  }  
    
  public static function homePage($homePage) {
    NavElementSection::$homePage = $homePage;
  }
    
  public static function maintenanceState($isOn = false, $message = '') {
    NavElementSection::$maintenanceIsOn = $isOn;
    NavElementSection::$maintenanceMessage = $message;
  }
}