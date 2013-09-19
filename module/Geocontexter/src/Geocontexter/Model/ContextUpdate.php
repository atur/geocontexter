<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Update context
 *
   USAGE:
   <pre>

   $context_update = $this->CoreModel('ContextUpdate');

   $params  = array('id_context' => bigint id_context,
                    'data'       => array('title'       => string,
                                          'description' => string,
                                          'id_parent'   => bigint,
                                          'id_status'   => smallint));

   $context_update->run( $params );

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

class ContextUpdate extends    AbstractModel
                    implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('title'       => true,
                                    'description' => true,
                                    'id_parent'   => true,
                                    'id_status'   => true,
                                    'lang'        => true
                                    );

    /**
     * update context
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params($params);

            $params['data']['update_time'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");

            $this->update('gc_context','geocontexter', $params['data'], array('id_context' => $params['id_context']));

            if (isset($params['data']['id_status'])) {
                $trash = $this->CoreModel('Trash');

                if ($params['data']['id_status'] == 0) {
                    $trash->toTrash( $params['id_context'], 5 );
                } else {
                    // remove id from trash if present
                    $trash->undoTrash( $params['id_context'], 5 );
                }
            }

            $this->commit();

        } catch(\Exception $e) {
            $this->rollback();
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
        if (!isset($params['data'])) {
            throw new \Exception('data array isnt defined');
        }

        foreach ($params['data'] as $key => $val) {
            if (!isset($this->allowed_fields[$key])) {
                throw new \Exception('Field isnt allowed: ' . $key);
            }
        }

        if (isset($params['data']['title'])) {
            if (empty($params['data']['title'])) {
                throw new \Exception('Context title is empty');
            }
        }

        if (!isset($params['id_context'])) {
            throw new \Exception('id_context field isnt defined');
        }

        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($params['id_context'])) {
            throw new \Exception('id_context isnt from type bigint');
        }
    }
}