<?php

/**
 * navElementSection
 *
 * @author ts
 */
class navElementSection {
    
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
    trigger_error(
      'Undefined property via __get(): ' . $name .
      ' in ' . $trace[0]['file'] .
      ' on line ' . $trace[0]['line'], E_USER_NOTICE);
    return null;
    }

    public static function currentUser($currentUser) {
    navElementSection::$currentUser = $currentUser;
    }  
    
  public static function helpPath($helpPath) {
    navElementSection::$helpPath = $helpPath;
    }
    
  public static function allowAdmin($allowAdmin) {
    navElementSection::$allowAdmin = $allowAdmin;
    }  
    
  public static function homePage($homePage) {
    navElementSection::$homePage = $homePage;
    }
    
  public static function maintenanceState($isOn = false, $message = '') {
    navElementSection::$maintenanceIsOn = $isOn;
    navElementSection::$maintenanceMessage = $message;
    }
}