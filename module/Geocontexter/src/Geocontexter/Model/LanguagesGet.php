<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get all table related attribute groups
 *
   USAGE:
   <pre>

   $languages_get = $this->CoreModel('LanguagesGet');

    // optional
   $params = array('enable' => string ('true' or 'false') );

   $result  = $languages_get->run( $params );

    if ($result instanceof \Core\Library\Exception) {
        return $this->error( $result->getMessage(), __file__, __line__);
    } else {
       $this->view->result = $result;
    }
   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class LanguagesGet extends    AbstractModel
                   implements InterfaceModel
{
    private $sql_where = '';

    /**
     * get all item attribute groups
     *
     *
     * @param array $params
     */
    public function run( $params)
    {
        try {

            $this->validate_params($params);

            $sql = 'SELECT  *
                    FROM  geocontexter.gc_language
                    '.$this->sql_where.'
                    ORDER BY description';

            return $this->query($sql);

        } catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * set and validate parameters
     *
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        if (isset($params['enable'])) {
            if (false === in_array($params['enable'], array('true','false'))) {
                throw new \Exception('enable value isnt "true" or "false"');
            } else {
                $this->sql_where = 'WHERE enable='.$params['enable'];
            }
        }
        
    }
}