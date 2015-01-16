<?php
  // follow: https://phpbestpractices.org/

  /* ======================================================================== */
  /*                         Global Configuration Class                       */
  /* ======================================================================== */

  /**
    * @title       | Global Configuration Class
    * @module      | core
    * @file        | globalConfigurationClass.php
    * @description | Global Initialization Parameters and methods. Should be
    * @description | similar to what application.cfc onApplicationStart was
    * @description | doing = set database, dependency loading et al
    **/

  // --------------------------- Force UTF-8 -----------------------------------
  mb_internal_encoding("UTF-8");

  // ---------------------- Set global configuration ---------------------------
  /**
    * @class        | $global_configuration_class
    * @type         | object
    * @description  | set application level configuration
    **/
  class global_configuration_class {

    /**
      * @property     | $database_connection_status
      * @type         | Object
      * @default      | null
      * @description  | Contains status of database connection
      **/
    public $database_connection_status = null;
   
    // ----------------- Set persistent database connection --------------------
    /**
      * @method       | connectToDatabase
      * @param        | $my_configuration_dict {Object} Connection parameters
      * @description  | Establish a persistent database connection.
      **/
    public function connectToDatabase($my_configuration_dict) {

      $host = $my_configuration_dict->database_host;
      $name = $my_configuration_dict->database_name;
      $user = $my_configuration_dict->database_user;
      $pass = $my_configuration_dict->database_password;

      try {
        $db = new \PDO(
          "mysql:host=$host;dbname=$name;charset=utf8", $user, $pass,
          array(
            \PDO::ATTR_PERSISTENT => true,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8mb4'
          )
        );
        return $db;
      }
      catch(\PDOException $e) {
        $error_class = new error_class();
        $this->database_connection_status = $error_class->createErrorObject(
          500, "Failed to connect to database.", $e
        );
      }
    }
    
    // -----------------  Verify dependencies are available --------------------
    /**
      * @method       | verifyDependencies
      * @param        | {Array} $my_dependency_list List of dependencies
      * @description  | Verfiy all required dependencies are available
      **/
    public function verifyDependencies($my_dependency_list) {
      $count;
      $extension;
      $dependency_list_len = count($my_dependency_list);

      for ($count = 0; $count < $dependency_list_len; $count += 1) {
        $extension = $my_dependency_list[$count];
        if (!extension_loaded($extension)) {
          $error_class = new error_class();
          return $error_class->createErrorObject(
            500, "Please enable '". $extension ."' extension.", null
          );
          break;
        }
      }
      return null;
    }
  }
?>
