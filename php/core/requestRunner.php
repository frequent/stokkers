<?php
  // follow: https://phpbestpractices.org/

  /* ======================================================================== */
  /*                              Request Runner                              */
  /* ======================================================================== */
  /**
    * @title       | Request Runner
    * @module      | core
    * @file        | requestRunner.php
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
        include_once(__DIR__ . "/classes/" . $class_name . ".php");
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
          echo "Could not load extension";
          break;
        }
      }
    }
  }

  // -------------------  Initiate autoloading class  --------------------------
  // TODO: make lighter, move to global configuration if possible
  $dependency_manager_class = new dependency_manager_class();
  $dependency_manager_class->autoLoadReset();
  $dependency_manager_class->autoLoadSet();

  // --------------------  Initiate defaults class   ---------------------------
  // TODO: defaults should eventually be customizable.
  $defaults_class = new defaults_class();

  // --------------------  Set global configuration  ---------------------------
  $global_configuration_class = new global_configuration_class();
  $database = $global_configuration_class->connectToDatabase($defaults_class);

  // ------------------   Verify dependencies are available --------------------
  // TODO: really necessary?
  $dependeny_list = $defaults_class->dependency_list;
  $dependency_manager_class->verifyDependencies($dependeny_list);


?>
