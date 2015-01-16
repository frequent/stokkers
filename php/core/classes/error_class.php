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
    * @description | of errors to the front end
    **/

  // --------------------------- Force UTF-8 -----------------------------------
  mb_internal_encoding("UTF-8");

  // ---------------------- Set global configuration ---------------------------
  /**
    * @class        | error_class
    * @type         | object
    * @description  | global applciation error handler
    **/
  class error_class {

    /**
      * @property     | $error_list
      * @type         | Array
      * @description  | List of available error messages
      **/
    public static $error_list = array(
      "500"=>"Internal Server Error"
    );

    /**
      * @method       | createErrorObject
      * @param        | {Object}  $error    Error object
      * @param        | {Integer} $code     Error Code called
      * @param        | {String}  $message  Message being sent
      * @returns      | {Object}  The error object
      * @description  | create a JSON error object to return to the front end
      **/
    public function createErrorObject ($code, $message, $error) {

      // TODO: log internal error
      if (isset($error)) {
      
      }

      // return json response
      return json_encode(array(
        "status" => "error",
        "error" => $code,
        "reason" => $message,
        "message" => $this::$error_list[$code]
      ));
    }
  }
?>