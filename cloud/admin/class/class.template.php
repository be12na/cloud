<?php
/**
 * Control the templating system
 *
 *
 */
if (!class_exists('Template', false)) {
    /**
     * Template class
     *
 *
     */
    class Template
    {
        /**
         * Check if all the parts exist
         *
         * @param string $file - the template part to search
         *
         * @return include file
         */
        public function includeTpl( $file )
        {
            if (file_exists(dirname(dirname(__FILE__)).'/_content/template/'.$file.'.php')) {
                $path = '/_content/template/'.$file.'.php';
            } else {
                $path = '/template/'.$file.'.php';
            }
            return $path;
        }
    }
}
