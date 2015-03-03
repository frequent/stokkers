<?php
  // follow: https://phpbestpractices.org/
  // $this->member for non-static members, use self::$member for static members
  
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
        $position = strpos($class_name, "_");
        if ($position !== false) {
          $path = substr_replace($class_name, "/" , $position, 1);
          include(__DIR__ . '/'.$path.".php");
        }
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

  // -------------------  Instantiate autoloading  -----------------------------
  // TODO: necessary as a class?.
  $dependency_manager_class = new dependency_manager_class();
  $dependency_manager_class->autoLoadReset();
  $dependency_manager_class->autoLoadSet();

  // --------------------  Instantiate defaults  -------------------------------
  // TODO: fetch, from where? It's a "recipe" to setup db, so it can't be in db
  $defaults_class = new core_defaults_class();

  // -------------------  Instantiate error handling  --------------------------
  $error_class = new core_error_class();
  $error_class->setErrorHandling($defaults_class->error_log_path);
  
  // -------------------  FROM HERE ERRORS ARE CAUGHT  -------------------------

  // ------------------  Instantiate authentication  ---------------------------
  // $authentication_class = new core_authentication_class();

  // -------------------  Throw on non secure requests -------------------------
  // TODO: necessary? This is a REST accesspoint, so...
  //if ($authentication_class->redirectHttp($_SERVER)) {
    //throw new Exception("403, Access denied over http.");
    //exit();
  //}

  // --------------------  Instantiate configuration  --------------------------
  $global_configuration_class = new core_global_configuration_class();

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
  
  // ------------------   Verify dependencies are available --------------------
  // TODO: really necessary?
  $dependeny_list = $defaults_class->dependency_list;
  $error = $global_configuration_class->verifyDependencies($dependeny_list);

  // --------------------  Set database connection  ----------------------------
  $database = $global_configuration_class->connectToDatabase($defaults_class);
  $error = $global_configuration_class->database_connection_status;

  // ----------------------  (only) echo to user  ------------------------------
  /* TODO: how to a || b || c?
  if ($error !== null) {
    echo $error;
  } elseif ($success !== null) {
    echo $success;
  } else {
    foreach (getallheaders() as $name => $value) {
      $dict[$name] = $value;
    }
    echo json_encode($dict);
  }
  */
  echo "hello";



?>