<?php

/**
 * @author Jason F. Irwin
 * @copyright 2011
 * 
 * This file contains the primary themes interface which will serve as the
 *  basis for all the themes employed by the PhotoStorage Website
 * 
 * Change Log
 * ----------
 * 2011.01.26 - Created File (J2fi)
 * 
 */

interface themes {

    public function _getData();

    /**
     * Function Returns a string specifying whether page construction is overriden
     *      for another function.
     * 
     * Default String: ''
     */
    public function _getOverride();

    /***********************************************************************
     *                          Theme Details
     *
     *   The following code should only be called for Theme Information
     *
     ***********************************************************************/


}

?>