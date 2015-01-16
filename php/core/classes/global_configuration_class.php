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
        echo "Failed to connect to database";
      }
    }
  }
?>
