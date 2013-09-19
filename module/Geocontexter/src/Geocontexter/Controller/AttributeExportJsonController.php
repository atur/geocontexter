<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Get attribute export file
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\JsonModel;
use Core\Controller\AbstractController;

class AttributeExportJsonController extends AbstractController
{
    public function indexAction()
    {
        $id_groups = $this->request->getPost()->id_groups;

        if (($id_groups !== null) && is_array($id_groups)) {

            try {

                $AttributeExport = $this->CoreModel('AttributeExport');

                $params  = array('id_groups' => $id_groups);

                $export_file  = $AttributeExport->run( $params );

                $json_return = array('file' => $export_file);

            } catch(\Exception $e) {
                $json_return = array('error' => "Error\n File: " . __file__ . "\nLine: " . __line__ . "\nError: " . $e->getMessage());
            }

        } else {
            $json_return = array('error' => 'No group selected');
        }

        return new JsonModel($json_return);
    }
}

