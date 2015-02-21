<?php
  // follow: https://phpbestpractices.org/

  /* ======================================================================== */
  /*                         Authentication Class                             */
  /* ======================================================================== */

  /**
    * @title       | Authentication Class
    * @module      | core
    * @file        | authentication_class.php
    * @description | Class handling authentication of incoming requests. This
    * @description | includes redirect, forcing login page
    **/

  // --------------------------- Force UTF-8 -----------------------------------
  mb_internal_encoding("UTF-8");

  // ---------------------- Set authentication class ---------------------------
  /**
    * @class        | $authentication_class
    * @type         | object
    * @description  | application level authentication access
    **/
  class authentication_class {
  
    // ------------------------ Redirect http to https -------------------------
    /**
      * @method       | redirectHttp
      * @param        | $request_dict {Object} $_SERVER object
      * @returns      | true (1) to exit
      * @description  | Establish a persistent database connection
      **/
    // TODO: nobody redirects, but request, Change this!
    public function redirectHttp($request_dict) {

      if(!isset($request_dict['HTTPS']) || $request_dict['HTTPS'] == ""||
        $request_dict['HTTPS'] == 'off') {
        $redirect =
          "https://".$request_dict['HTTP_HOST'].$request_dict['REQUEST_URI'];
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: $redirect");
        return 1;
      }
    }

    // ---------------------- Redirect if missing token ------------------------
    /**
      * @method       | redirectMissingToken
      * @param        | $request_dict {Object} $_SERVER object
      * @description  | Redirect to login page if token is missing. Note this
      * @description  | will not make a redirect but provide the login page as
      * @description  | only parameter in the response being sent back
      **/
    public function redirectMissingToken($request_dict) {
      
    }
    

  }
?>
