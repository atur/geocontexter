<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Add new Item
 *
   USAGE:
   <pre>

   $ListInsertRule = $this->CoreModel('ListInsertRule');

   $result = $ListInsertRule->run( $params );

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   }

   $result = $ListInsertRule->delete();

   if ($result instanceof \Core\Library\Exception) {
       return $this->error( $result->getMessage(), __file__, __line__);
   }

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
\*

namespace Geocontexter\Model;

use Core\Model\AbstractModel;
use Core\Model\InterfaceModel;

class ListInsertRule extends AbstractModel
                     implements InterfaceModel
{

    /**
     * add List
     *
     *
     * @param array $params
     */
    public function run( $params = array() )
    {
        $sql = 'CREATE OR REPLACE RULE insert_update_list AS
                   ON INSERT TO geocontexter.gc_list
                   WHERE
                     EXISTS(SELECT 1 FROM geocontexter.gc_list WHERE id_list=NEW.id_list)
                   DO INSTEAD
                      (UPDATE geocontexter.gc_list
                           SET title       = NEW.title,
                               description = NEW.description,
                               id_parent   = NEW.id_parent,
                               id_owner    = NEW.id_owner,
                               id_status   = NEW.id_status,
                               id_attribute_group = NEW.id_attribute_group,
                               lang               = NEW.lang,
                               value_integer      = NEW.value_integer,
                               value_varchar126   = NEW.value_varchar126
                       WHERE id_list=NEW.id_list) ';

        try {

            $this->query($sql);

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
    public function delete()
    {
        $sql = 'DELETE RULE insert_update_list';

        try {

            $this->query($sql);

        } catch(\Exception $e) {
            throw $e;
        }
    }
}
