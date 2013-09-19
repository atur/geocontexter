<?php
/**
 * Geokeyworder
 * @link http://code.google.com/p/geocontexter/
 * @package Geocontexter
 */

/**
 * Add new keyword
 *
 *  USAGE:
    <pre>

    $keyword_add = $this->CoreModel('KeywordAdd');

    $params  = array('id_keyword'         => string,
                     'title'              => string,
                     'description'        => string,
                     'id_parent'          => bigint (string),
                     'id_status'          => smallint,
                     'id_attribute_group' => bigint (string),
                     'lang'               => string,
                     'update_time'        => string,
                     'attribute_value'    => string);

    $id_keyword  = $keyword_add->run( $params );

    if ($id_keyword instanceof \Core\Library\Exception) {
        return $this->error( $id_keyword->getMessage(), __file__, __line__);
    }

    // return new created id_keyword or error object

    </pre>
 * @package Geocontexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 838 $ / $LastChangedDate: 2011-03-17 21:16:20 +0100 (Do, 17 Mrz 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class KeywordAdd extends    AbstractModel
                 implements InterfaceModel
{
    /**
     * allowed fields
     *
     *
     * @param array $allowed_fields
     */
    private $allowed_fields = array('id_keyword'         => true,
                                    'title'              => true,
                                    'description'        => true,
                                    'id_parent'          => true,
                                    'id_status'          => true,
                                    'id_attribute_group' => true,
                                    'preferred_order'    => true,
                                    'lang'               => true,
                                    'update_time'        => true,
                                    'attribute_value'    => true
                                    );


    /**
     * add keyword
     *
     *
     * @param array $params
     */
    public function run( $params )
    {
        try {

            $this->beginTransaction();

            $this->validate_params($params);

            if (!isset($params['update_time'])) {
                $params['update_time'] = new \Zend\Db\Sql\Expression("CURRENT_TIMESTAMP(0) AT TIME ZONE 'UTC'");
            }

            if (isset($params['id_parent'])) {
                $params['preferred_order'] = new \Zend\Db\Sql\Expression("geocontexter.gc_get_new_entry_order('gc_keyword',
                                                                                                              'id_parent',
                                                                                                              '{$params['id_parent']}')");
            }

            $this->insert('gc_keyword', 'geocontexter', $params);

            $new_keyword = $this->query("SELECT currval('geocontexter.seq_gc_keyword') AS id_keyword");

            $this->commit();

            return $new_keyword[0]['id_keyword'];

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
        foreach ($params as $key => $val) {
            if (!isset($this->allowed_fields[$key])) {
                throw new \Exception('Field isnt allowed: ' . $key);
            }
        }

        if (!isset($params['title'])) {
            throw new \Exception('keyword title field isnt defined');
        }

        if (empty($params['title'])) {
            throw new \Exception('keyword title is empty');
        }

        if (isset($params['id_parent'])) {

            $val_digits = new \Zend\Validator\Digits();

            if (false === $val_digits->isValid($params['id_parent'])) {
                throw new \Exception('id_parent isnt from type bigint');
            }
        }

        if (isset($params['update_time'])) {

            $val_date = new \Zend\Validator\Date(array('format' => 'yyyy-MM-dd HH:mm:ss'));

            if (true !== $val_date->isValid($params['update_time'])) {
                throw new \Exception('update_time has wrong format: ' . $params['update_time']);
            }
        }

        
    }
}
