<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 *
 *
   USAGE:
   <pre>

   $ItemInsertRule = $this->CoreModel('ItemInsertRule');

   $ItemInsertRule->create( $params );

   $ItemInsertRule->delete();

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;

class ItemInsertRule extends AbstractModel
{

    /**
     * add List
     *
     *
     * @param array $params
     */
    public function create()
    {
        try {

            $this->beginTransaction();

            $sql = 'CREATE OR REPLACE RULE insert_update_item AS
                   ON INSERT TO geocontexter.gc_item
                   WHERE
                   EXISTS(SELECT 1 FROM geocontexter.gc_item WHERE id_item=NEW.id_item)
                   DO INSTEAD
                    (UPDATE geocontexter.gc_item
                         SET title       = NEW.title,
                             id_owner    = NEW.id_owner,
                             id_attribute_group = NEW.id_attribute_group,
                             lang      = NEW.lang,
                             value_varchar126   = NEW.value_varchar126
                     WHERE id_item=NEW.id_item) ';

            $sql2 = 'CREATE OR REPLACE RULE insert_update_item_list AS
                   ON INSERT TO geocontexter.gc_list_item
                   WHERE
                   EXISTS(SELECT 1 FROM geocontexter.gc_list_item WHERE id_item=NEW.id_item AND id_list=NEW.id_list)
                   DO NOTHING';


            $this->query($sql);
            $this->query($sql2);

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
    public function delete()
    {
        try {
            $this->beginTransaction();

            $sql  = 'DELETE RULE insert_update_item';
            $sql2 = 'DELETE RULE insert_update_item_list';

            $this->query($sql);
            $this->query($sql2);

            $this->commit();

        } catch(\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
