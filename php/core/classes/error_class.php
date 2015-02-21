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
      * @method      | overrideErrorHandler
      * @param       | {String}   $error_level    PHP Error Level
      * @param       | {String}   $error_message  Error Message
      * @param       | {String}   $error_file     File error was thrown
      * @param       | {Integer}  $error_line     Line error was thrown
      * @param       | {String}   $error_trace    Stack trace as string
      * @returns     | nothing
      * @description | custom handler for errors, should only trigger if 
      * @description | we miss throwing an exception. Note the shutdown
      * @description | function override to try and catch everything
      **/
    private function overrideErrorHandler($error_level, $error_message,
      $error_file, $error_line, $error_context) {

      switch ($error_level) {
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_PARSE:
            $status = "fatal";
            break;
        case E_USER_ERROR:
        case E_RECOVERABLE_ERROR:
            $status = "error";
            break;
        case E_WARNING:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
        case E_USER_WARNING:
            $status = "warn";
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            $status = "info";
            break;
        case E_STRICT:
            $status = "debug";
            break;
        default:
            $status = "warn";
      }

      // $status will always be set, so handle it (context = array, so implode)
      $this::handleAllErrors(
        $status,
        $error_message,
        $error_file,
        $error_line,
        implode(",", $error_context),
        null,
        false
      );
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
    private function overrideExceptionHandler($exception_dict) {

      $message_list = explode(",", $exception_dict::getMessage());
      $this::handleAllErrors(
        "exception",
        $message_list[1] || $message_list[0],
        $exception_dict::getFile(),
        $exception_dict::getLine(),
        $exception_dict::getTraceAsString(),
        $message_list[0],
        true
      );
    }
    
    /**
      * @method      | puntForFatalAndParseErrors
      * @returns     | nothing
      * @thanks      | http://stackoverflow.com/a/7313887/536768
      * @description | called when php ends because of a fatal or parse error
      * @description | and should allow to also log the error and send an
      * @description | answer back to the client.
      **/
    private function puntForFatalAndParseErrors() {

      $last_error_dict = error_get_last();
      switch ($lasterror['type']) {
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
        case E_RECOVERABLE_ERROR:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
        case E_PARSE:
          $this::handleAllErrors(
            "fatal",
            $last_error_dict['message'],
            $last_error_dict['file'],
            $last_error_dict['line'],
            implode(",", $last_error_dict['context']),
            null,
            false
          );
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

      // report all
      // TODO: set -1?
      error_reporting(E_ALL | E_STRICT);

      // set log and display
      ini_set('log_errors', '1');
      ini_set('display_errors', '0');

      // if specified, report to custom log
      if (isset($my_log_path)) {
        ini_set('error_log', $my_log_path);
      }

      // route errors
      set_error_handler(array($this, "overrideErrorHandler"));
      
      // route exceptions
      set_exception_handler(array($this, "overrideExceptionHandler"));
      
      // try catching fatal/parse errors
      register_shutdown_function(array($this, "puntForFatalAndParseErrors"));
    }

    /**
      * @method      | handleAllErrors
      * @param       | {String}   $my_type          Error Type
      * @param       | {String}   $my_message       Error Message
      * @param       | {String}   $error_file       File error was thrown
      * @param       | {Integer}  $error_line       Line error was thrown
      * @param       | {String}   $my_trace         Stack trace as string
      * @param       | {Integer}  $my_http_code     Http error, thrown manually 
      * @param       | {Boolean}  $my_is_exception  Flag error or exception
      * @returns     | {String}   JSON+HAL Error object
      * @thanks      | https://github.com/blongden/vnd.error
      * @api         | vnd.error (discussed error format for JSON+HAL)
      * @description | Log errors to the custom error log, generate a JSON
      * @description | object to return to the client. Tries to follow vnd
      * @description | (JSON+HAL compatible) error specification.
      **/
    // TODO: who does the logging? Storage should not be accessible here... but
    // this is the end of the line on shutdowns, so this needs to log, too...
    public function handleAllErrors($my_type, $my_message, $error_file,
      $error_line, $my_trace, $my_http_code, $my_is_exception) {

      $status_code = $my_http_code || 500;
      if ($my_is_exception === true) {
        $message = $my_message;
      } else {
        $message = "Internal Server Error";
      }

      // log error
      
      // and don't forget the user...
      $response_dict = json_encode(array(
        "logref" => "",
        "path" => "",
        "message" => $message,
        "_links" => json_encode(array(
          "describes" => ""
        ))
      ));

      header('HTTP/1.1 '.$status_code.' Internal Error');
      header('Content-type: application/vnd.error+json');
      echo json_encode($response_dict);
    }
  }
?>