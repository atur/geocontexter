<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Check if context related projects exsist
 *
   USAGE:
   <pre>
   $context_related_project_check = $this->CoreModel('ContextRelatedProjectCheck');

   $result  = $context_related_project_check->run( array('id_context' => bigint );

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

class ContextRelatedProjectCheck extends    AbstractModel
                                 implements InterfaceModel
{
    /**
     * update context
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        $this->validate_params($params);

        $_sql   = 'SELECT id_context FROM geocontexter.gc_context_get_all_childs(?)';
        $result = $this->query($_sql, array($params['id_context']));

        $in = "{$params['id_context']}";

        foreach ($result as $row) {
            $in .= "," . $row['id_context'];
        }

        $sql = 'SELECT * FROM geocontexter.gc_project WHERE id_context IN(?)';

        return $this->query($sql, array($in));
    }

    /**
     * set and validate parameters
     *
     *
     * @param array $params
     */
    private function validate_params( & $params )
    {
        if (!isset($params['id_context'])) {
            throw new \Exception('id_context isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_context'])) {
            throw new \Exception('id_context isnt from type bigint');
        }
    }
}