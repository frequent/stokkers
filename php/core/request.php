<?php
  // follow: https://phpbestpractices.org/

  /* ======================================================================== */
  /*                                  Request                                 */
  /* ======================================================================== */
  /**
    * @title       | Request
    * @module      | core
    * @file        | request.php
    * @description | Run on every request similar to Coldfusions application.cfc
    * @description | File will setup database connection and load all classes
    * @description | necessary to run the application. This is the core file.
    **/

  // --------------------------- Force UTF-8 -----------------------------------
  mb_internal_encoding("UTF-8");

  /* ======================================================================== */
  /*                         Dependeny Manager Class                          */
  /* ======================================================================== */

  /**
    * @class        | $dependency_manager_class
    * @type         | object
    * @description  | class handling autoloading of other classes. Should be in
    * @description  | in its own file, but cannot be in classes/ folder, until
    * @description  | its instantiated and autoload is set, so leave the class
    * @description  | here for now.
    **/
  class dependency_manager_class {

    /**
      * @method       | autoLoadSet
      * @description  | register a method for autoloading dependencies
      **/
    public function autoLoadSet() {
      function autoLoad($class_name) {
        include(__DIR__ . "/classes/" . $class_name . ".php");
      }

      spl_autoload_register('autoLoad');
    }

    /**
      * @method       | autoLoadReset
      * @description  | unregister all spl_autoload methods
      **/
    public function autoLoadReset() {
      spl_autoload_register(null, false);
    }
  }

  // --------------------  Define database variable  ---------------------------
  /**
    * @property     | $database
    * @type         | Object
    * @description  | Database connection object
    **/
  $database;

  // ---------------------  Define response error  -----------------------------
  /**
    * @property     | $error
    * @type         | Object
    * @description  | Response object to be returned to the user
    **/
  $error = null;  

  // ---------------------  Define response success  ---------------------------
  /**
    * @property     | $success
    * @type         | Object
    * @description  | Response object to be returned to the user
    **/
  $success = null;
  
  // -------------------  Instantiate autoloading  -----------------------------
  // TODO: make lighter, move to global configuration if possible.
  $dependency_manager_class = new dependency_manager_class();
  $dependency_manager_class->autoLoadReset();
  $dependency_manager_class->autoLoadSet();

  // -------------------  Instantiate error handling  --------------------------
  $error_class = new error_class();

  // --------------------  Instantiate defaults  -------------------------------
  // TODO: defaults should eventually be customizable and loaded ad hoc.
  $defaults_class = new defaults_class();
  
  // --------------------  Instantiate configuration  --------------------------
  $global_configuration_class = new global_configuration_class();

  // ------------------  Set custom error/exception handler --------------------
  //$customErrorHandler = $error_class->customErrorHandler;
  //$customExceptionHandler = $error_class->customExceptionHandler;
  //$new_error_handler = set_error_handler("customErrorHandler");
  //$new_exception_handler = set_exception_handler("customExceptionHandler");
  
  // ------------------   Verify dependencies are available --------------------
  // TODO: really necessary?
  $dependeny_list = $defaults_class->dependency_list;
  $error = $global_configuration_class->verifyDependencies($dependeny_list);

  // --------------------  Set database connection  ----------------------------
  $database = $global_configuration_class->connectToDatabase($defaults_class);
  $error = $global_configuration_class->database_connection_status;

  // ----------------------  (only) echo to user  ------------------------------
  // TODO: how to a || b || c?
  if ($error !== null) {
    echo $error;
  } elseif ($success !== null) {
    echo $success;
  } else {
    echo "ok";
  }
?>