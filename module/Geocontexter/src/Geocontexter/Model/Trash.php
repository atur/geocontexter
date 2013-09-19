<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * delete expired content from trash
 *
   USAGE:
   <pre>

   $trash = $this->CoreModel('Trash');

   $result = $trash->deleteExpiredContent();

    // $id = id_iten - $table_hash > see table codes
    $result = $trash->toTrash( $id, $table_hash );

    $result = $trash->undoTrash( $id, $table_hash );

   </pre>
 * @package GeoContexter
 * @subpackage Module_Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 835 $ / $LastChangedDate: 2011-03-05 12:55:03 +0100 (Sa, 05 Mrz 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Model;

use Core\Model\AbstractModel;

class Trash extends AbstractModel
{
    /**
     * get contextes from id_context
     *
     *
     * @param array $params
     */
    public function deleteExpiredContent()
    {
        try
        {
            $this->timestamp = time() - 0;

            $this->beginTransaction();

            $this->delete_trash();

            $this->commit();
        }
        catch(\Exception $e)
        {
            $this->rollback();

            throw $e;
        }
    }

    /**
     * get contextes from id_context
     *
     *  Table codes
        1 = gc_record
        2 = gc_list
        3 = gc_item
        4 = gc_keyword
        5 = gc_context
        6 = gc_user
        7 = gc_project
     *
     * @param array $params
     */
    private function delete_trash()
    {
        $sql = 'SELECT id_item, table_hash
                FROM  geocontexter.gc_trash
                WHERE trash_time < ?';

        $result = $this->query($sql, array($this->timestamp));

        foreach ($result as $row) {

            switch ($row['table_hash']) {

                case 2;
                    $list = $this->CoreModel('ListDelete');

                    $l_result = $list->run(array('id_list' => $row['id_item']));

                    if ($l_result instanceof \Core\Library\Exception) {
                        throw new \Exception($l_result->getMessage());
                    }

                    $sql = 'DELETE FROM geocontexter.gc_trash
                            WHERE id_item  = '.$row['id_item'].'
                            AND table_hash = 2';

                    $this->query($sql);

                    break;

                case 4;
                    // keyword

                    if (!isset($this->keyword)) {
                        $this->keyword = $this->CoreModel('KeywordDelete');
                    }

                    $k_result  = $this->keyword->run( array('id_keyword' => $row['id_item']) );

                    if ($k_result instanceof \Core\Library\Exception) {
                        throw new \Exception($k_result->getMessage());
                    }

                    $sql = 'DELETE FROM geocontexter.gc_trash
                            WHERE id_item  = '.$row['id_item'].'
                            AND table_hash = 4';

                    $this->query($sql);

                    break;

                case 5;
                    // context
                    $sql = 'DELETE FROM geocontexter.gc_context
                            WHERE id_context = '.$row['id_item'];

                    $this->query($sql);

                    $sql = 'DELETE FROM geocontexter.gc_trash
                            WHERE id_item  = '.$row['id_item'].'
                            AND table_hash = 5';

                    $this->query($sql);

                    break;

                case 6;
                    // user
                    $sql = 'DELETE FROM geocontexter.gc_user
                            WHERE id_user = '.$row['id_item'];

                    $this->query($sql);

                    $sql = 'DELETE FROM geocontexter.gc_project_user
                            WHERE id_user = '.$row['id_item'];

                    $this->query($sql);

                    $sql = 'DELETE FROM geocontexter.gc_trash
                            WHERE id_item  = '.$row['id_item'].'
                            AND table_hash = 6';

                    $this->query($sql);

                    break;
            }
        }
    }

    /**
     * move id in trash
     *
     *
     * @param string $id
     * @param int    $table_hash
                         1 = gc_record
                         2 = gc_list
                         3 = gc_item
                         4 = gc_keyword
                         5 = gc_context
                         6 = gc_user
                         7 = gc_project
     */
    public function toTrash( $id, $table_hash )
    {
        $val_digits = new \Zend\Validator\Digits();

        if (false === $val_digits->isValid($id)) {
            throw new \Exception('id isnt from type bigint');
        }

        if (false === in_array( $table_hash, array(1,2,3,4,5,6,7) )) {
            throw new \Exception('table_hash must be one of values: 1,2,3,4,5,6,7');
        }

        $trash_params = array('id_item'    => $id,
                              'table_hash' => $table_hash,
                              'trash_time' => time());

        $this->insert('gc_trash', 'geocontexter', $trash_params);
    }

    /**
     * remove ids from trash
     *
     *
     * @param mixed $id_keyword string of one id or array of id's
     * @param int    $table_hash
                         1 = gc_record
                         2 = gc_list
                         3 = gc_item
                         4 = gc_keyword
                         5 = gc_context
                         6 = gc_user
                         7 = gc_project
     */
    public function undoTrash( $id, $table_hash )
    {
        if (false === in_array( $table_hash, array(1,2,3,4,5,6,7) )) {
            throw new \Exception('table_hash must be one of values: 1,2,3,4,5,6,7');
        }

        $__in  = "";

        $val_digits = new \Zend\Validator\Digits();

        if (is_array($id) && (count($id) > 0)) {
            $comma = "";

            foreach ($id as $__id) {
                $__in .= $comma . $__id;
                $comma = ",";

                if(false === $val_digits->isValid($__id))
                {
                    throw new \Exception('id isnt from type bigint: ' . $__id);
                }
            }
        } elseif(!empty($id)) {
            $__in = $id;

            if (false === $val_digits->isValid($id)) {
                throw new \Exception('id isnt from type bigint: ' . $id);
            }
        } else {
            throw new \Exception('id isnt from type bigint: ' . $id);
        }



        $sql = 'DELETE FROM geocontexter.gc_trash
                WHERE id_item  IN( ' . $__in . ' )
                AND table_hash = ' . $table_hash;

        return $this->query($sql);
    }
}