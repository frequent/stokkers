<?php
  // follow: https://phpbestpractices.org/

  /* ======================================================================== */
  /*                             Defaults Class                               */
  /* ======================================================================== */

  /**
    * @title       | Defaults Class
    * @module      | core
    * @file        | defaults_class.php
    * @description | Global defaults to be used in case no custom defaults are
    * @description | provided. Only place for hardcoded parameters.
    **/

  // --------------------------- Force UTF-8 -----------------------------------
  mb_internal_encoding("UTF-8");

  // ---------------------- Set global configuration ---------------------------
  /**
    * class containing global configuration parameters
    * @class defaults_class
    */
  class core_defaults_class {

    /**
      * @property     | $database_user
      * @type         | String
      * @description  | Username for accessing database
      **/
    public $database_user = "root";

    /**
      * @property     | $database_password
      * @type         | String
      * @description  | Password for accessing database
      **/
    public $database_password = "";

    /**
      * @property     | $database_host
      * @type         | String
      * @description  | Host for establishing database connection
      **/
    public $database_host = "localhost";

    /**
      * @property     | $database_name
      * @type         | String
      * @description  | Database name for establishing databse connection
      **/
    public $database_name = "stokkers";
    
    /**
      * @propery      | $dependency_list
      * @type         | Array
      * @description  | Dependencies necessary to run application
      **/
    public $dependency_list = array(
      "mbstring",
      "openssl"
    );
    
    /**
      * @property     | $error_log_path
      * @type         | String
      * @description  | Custom error log destination
      **/
    public $error_log_path = "";

  }
?>