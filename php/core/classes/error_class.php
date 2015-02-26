<?php
  // follow: https://phpbestpractices.org/

  /* ======================================================================== */
  /*                               Error Class                                */
  /* ======================================================================== */

  /**
    * @title       | Error Class
    * @module      | core
    * @file        | error_class.php
    * @description | Global error handler handling error logging and reporting
    * @description | of errors
    **/

  // --------------------------- Force UTF-8 -----------------------------------
  mb_internal_encoding("UTF-8");

  // ---------------------- Set global configuration ---------------------------
  /**
    * @class         | error_class
    * @type          | object
    * @description   | global applciation error handler
    **/
  class error_class {

    /**
      * @property     | $error_name_dict
      * @type         | Object
      * @description  | Error switch done on demand
      **/
    public static $error_name_dict;

    /**
      * @method       | initiateErrorDictIfMissing
      * @param        | {Boolean}   my_return   Whether to return the dict
      * @returns      | nothing
      * @description  | Match Error Types to their corresponding integers. 
      * @description  | Has to be declared on demand, otherwise it does not
      * @description  | exist "early" errors are caught be error_handler or
      * @description  | shutdown_handler
      **/
    public function iniateErrorDict($my_return) {
      $error_list = array(
        1 => "E_ERROR",
        2 => "E_WARNING",
        4 => "E_PARSE",
        8 => "E_NOTICE",
        16 => "E_CORE_ERROR",
        32 => "E_CORE_WARNING",
        64 => "E_COMPILE_ERROR",
        128 => "E_COMPILE_WARNING",
        264 => "E_USER_ERROR",
        512 => "E_USER_WARNING",
        1024 => "E_USER_NOTICE",
        2048 => "E_STRICT",
        4096 => "E_RECOVERABLE_ERROR",
        8192 => "E_DEPRECATED",
        16384 => "E_USER_DEPRECATED",
        32768 => "E_ALL",
      );
      if (isset(self::$error_name_dict) === false) {
        self::$error_name_dict = $error_list;
      }
      if (isset($my_return)) {
        return $error_list;
      }
    }

    /**
      * @method      | overrideExceptionHandler
      * @param       | {Object}   $exception_dict   Exception object
      * @returns     | nothing
      * @description | custom handler for exceptions. Note the message passed
      * @description | should be passed in the form "500, message", so that it
      * @description | can be properly reported. This will be used for all
      * @description | custom exceptions
      **/
    public function overrideExceptionHandler($exception_dict) {

      $message_list = explode(",", $exception_dict::getMessage());

      if (isset($message_list[1])) {
        $error_message = $message_list[1];
        $error_http_code = $message_list[0];
      } else {
        $error_message = $message_list[0];
        $error_http_code = "500";
      }
      
      $this::handleErrorAndDie(
        $exception_dict::getType(),
        $error_message,
        $exception_dict::getFile(),
        $exception_dict::getLine(),
        $exception_dict::getTraceAsString(),
        "exception",
        $error_http_code,
        true
      );
    }

    /**
      * @method      | overrideErrorHandler
      * @param       | {Integer}  $error_code     PHP Error Code
      * @param       | {String}   $error_message  Error Message
      * @param       | {String}   $error_file     File error was thrown
      * @param       | {Integer}  $error_line     Line error was thrown
      * @param       | {Array}    $error_context  Stack trace as array
      * @returns     | nothing
      * @description | custom handler for errors, should only trigger if 
      * @description | we miss throwing an exception. Note the shutdown
      * @description | function override to try and catch everything. Note we
      * @description | catch and throw all, even if errors were recoverable. 
      **/
    public function overrideErrorHandler($error_code, $error_message,
      $error_file, $error_line, $error_context) {
      
      if (isset(self::$error_name_dict[$error_code]) === false) {
        self::iniateErrorDict();
      }

      switch (self::$error_name_dict[$error_code]) {
        case "E_ERROR":
        case "E_CORE_ERROR":
        case "E_COMPILE_ERROR":
        case "E_PARSE":
            $status = "fatal";
            break;
        case "E_USER_ERROR":
        case "E_RECOVERABLE_ERROR":
            $status = "error";
            break;
        case "E_WARNING":
        case "E_CORE_WARNING":
        case "E_COMPILE_WARNING":
        case "E_USER_WARNING":
            $status = "warn";
            break;
        case "E_NOTICE":
        case "E_USER_NOTICE":
            $status = "info";
            break;
        case "E_STRICT":
            $status = "debug";
            break;
        default:
            $status = "warn";
      }

      $this::handleErrorAndDie(
        $error_code,
        $error_message,
        $error_file,
        $error_line,
        implode(",", $error_context),
        $status,
        "500",
        false
      );
    }

    /**
      * @method      | puntForFatalAndParseErrors
      * @returns     | nothing
      * @thanks      | http://markonphp.com/handling-fatal-errors-php/
      * @description | called when php ends because of a fatal or parse error
      * @description | and should allow to also log the error and send an
      * @description | answer back to the client. This will run on all requests,
      * @description | so keep it light!
      **/
    public function puntForFatalAndParseErrors() {

      $last_error_dict = error_get_last();
      $type = $last_error_dict["type"];

      if (isset($type)) {
        if (isset(self::$error_name_dict)) {
          $local_error_dict = self::$error_name_dict;
        } else {
          $local_error_dict = self::iniateErrorDict(true);
        }

        switch ($local_error_dict[$type]) {
          case "E_ERROR":
          case "E_CORE_ERROR":
          case "E_COMPILE_ERROR":
          case "E_USER_ERROR":
          case "E_RECOVERABLE_ERROR":
          case "E_CORE_WARNING":
          case "E_COMPILE_WARNING":
          case "E_PARSE":
            $this->handleErrorAndDie(
              $type,
              $last_error_dict['message'],
              $last_error_dict['file'],
              $last_error_dict['line'],
              null,
              "fatal",
              "500",
              false
            );
        }
      }
    }

    /**
      * @method       | setErrorHandling
      * @param        | {String}  $my_log_path
      * @returns      | nothing
      * @thanks       | http://alanstorm.com/php_error_reporting
      * @description  | Override default settings (instead of php.ini) and
      * @description  | set custom error handling in order to return something
      * @description  | to the user and decide whether to make or break
      **/
    public function setErrorHandling($my_log_path) {

      error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

      // set log and display
      ini_set('log_errors', '1');
      ini_set('display_errors', '0');

      // if specified, report to custom log
      if (isset($my_log_path)) {
        ini_set('error_log', $my_log_path);
      }
      
      // route errors
      // TODO: reset really necessary? What about the others?
      // set_error_handler('var_dump', 0);
      // restore_error_handler();
      set_error_handler(array($this, "overrideErrorHandler"));
      
      // route exceptions
      set_exception_handler(array($this, "overrideExceptionHandler"));
      
      // try catching fatal/parse errors
      register_shutdown_function(array($this, "puntForFatalAndParseErrors"));
    }

    /**
      * @method      | handleErrorAndDie
      * @param       | {Integer}  $error_code       Error Code
      * @param       | {String}   $my_message       Error Message
      * @param       | {String}   $error_file       File error was thrown
      * @param       | {Integer}  $error_line       Line error was thrown
      * @param       | {String}   $my_trace         Stack trace as string
      * @param       | {String}   $my_type          Error Type
      * @param       | {Integer}  $my_http_code     Http error, thrown manually      
      * @param       | {Boolean}  $my_is_exception  Flag error or exception
      * @returns     | {String}   JSON+HAL Error object
      * @thanks      | https://github.com/blongden/vnd.error
      * @api         | vnd.error (discussed error format for JSON+HAL)
      * @description | Log errors to the custom error log, generate a JSON
      * @description | object to return to the client. Tries to follow vnd
      * @description | (JSON+HAL compatible) error specification.
      **/
    public function handleErrorAndDie($error_code, $my_message, $error_file,
      $error_line, $my_trace, $my_type, $my_http_code, $my_is_exception) {

      // make a message
      $log_message  =  'Error: '.$error_code.' :'.$my_message;
      $log_message .= ', file:'.$error_file.' line: '.$error_line;
      
      // log to PHP
      error_log($log_message, 0);

      // log to own
      
      // mask response to user
      if ($my_is_exception === true) {
        $message = $my_message;
      } else {
        $message = "Internal Server Error";
      }

      $response_dict = json_encode(array(
        "logref" => "",
        "path" => "",
        "message" => $message,
        "_links" => array(
          "describes" => ""
        )
      ));
      
      header('HTTP/1.1 '.$my_http_code.' Internal Error');
      header('Content-type: application/vnd.error+json');
      echo $response_dict;
      //echo $log_message;
      die();
    }
  }
?>