<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Delete expired files
 *
 * Folders:
 * /public/data/export
 * /application/modules/geocontexter/tmp
 *
   USAGE:
   <pre>
   $_files = $this->CoreModel('FolderFilesDeleteExpired');

   $result = $_files->deleteExpired();

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;

class FolderFilesDeleteExpired extends AbstractModel;
{
    /**
     * main methode
     *
     */
    public function deleteExpired()
    {
        clearstatcache();

        $this->now    = time();

        $_dir  = realpath(APPLICATION_PATH . '/../public/data/export');
        $this->__delete( $_dir );

        $_dir  = realpath(APPLICATION_PATH . '/modules/geocontexter/tmp');
        $this->__delete( $_dir );
    }

    /**
     * walk through the directory and delete files olde than 2 hours
     *
     *
     * @param string $_dir Path
     */
    private function __delete( $_dir )
    {
        if ( (($handle = opendir( $_dir ))) != FALSE ) {
            while ( (( $file = readdir( $handle ) )) != false ) {
                if ( ( $file == "." ) || ( $file == ".." ) ) {
                    continue;
                }

                if (is_file($_dir.'/'.$file)) {
                    // delete files after 2 hours
                    //
                    if (($this->now - filemtime(  $_dir.'/'.$file )) > 7200) {
                        if (false === unlink($_dir.'/'.$file)) {
                            $this->service->get('CoreErrorLogger')->info('Error delete file: ' . $_dir . '/' . $file);
                        }
                    }
                }
            }
            @closedir( $handle );
        } else {
            $this->service->get('CoreErrorLogger')->info('Couldnt open dir: ' . $_dir);
        }
    }
}