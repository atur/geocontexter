<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * show attributes of a group
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class AttributeGroupAttributesController extends AbstractController
{
    public function init()
    {
        $this->initView( 'geocontexter/attribute-group-attributes/index.phtml' );

        $this->view->id_group = $this->id_group = $this->params()->fromRoute('id_group',false);

        if ($this->id_group === null) {
            return $this->error( 'no id_group request parameter defined.', __file__, __line__ );
        }

        $this->view->partialData = array('active_page' => 'attrgroupmain',
                                         'id_group'    => $this->id_group);

        // get info if there is content associated to the attribute group (false if not, else true)
        //
        $AttributeDelete = $this->CoreModel('AttributeDelete');
        $this->view->disableDeleteLink = $AttributeDelete->isContent( $this->id_group );
    }

    public function indexAction()
    {
        $AttributeGroupGet = $this->CoreModel('AttributeGroupGet');

        $this->model_params = array('id_group'      => $this->id_group,
                                    'system_serial' => true) ;

        $result  = $AttributeGroupGet->run( $this->model_params );

        $this->view->attribute_group_name        = $result['title'];
        $this->view->attribute_group_description = $result['description'];
        $this->view->attribute_group_id_table    = $result['id_table'];
        // prepend to page title
        //
        $this->renderer->headTitle('Attributes of group: ' . $result['title']);

        // fetch vars to move attributes in order up or down
        //
        $move_attribute_up   = $this->params()->fromRoute('moveUp',false);
        $move_attribute_down = $this->params()->fromRoute('moveDown',false);

        if (false !== $move_attribute_up) {
            $id_attribute       = $this->params()->fromRoute('id_attribute',false);
            $AttributeMoveOrder = $this->CoreModel('AttributeMoveOrder');

            $AttributeMoveOrder->moveUp(array('id_attribute' => $id_attribute, 'id_group' => $this->id_group));

            $this->renderer->headTitle('Moved attribute up of group: ' . $this->view->attribute_group_name);
        }

        if (false !== $move_attribute_down) {
            $id_attribute       = $this->params()->fromRoute('id_attribute',false);
            $AttributeMoveOrder = $this->CoreModel('AttributeMoveOrder');

            $AttributeMoveOrder->moveDown(array('id_attribute' => $id_attribute, 'id_group' => $this->id_group));

            $this->renderer->headTitle('Moved attribute down of group: ' . $this->view->attribute_group_name);
        }

        // get the group attributes
        //
        $this->view->attribute_result = array();

        $AttributeGetGroupAttributes = $this->CoreModel('AttributeGetGroupAttributes');

        $this->view->attribute_result = $AttributeGetGroupAttributes->run( $this->model_params );

        return $this->view;
    }

    public function deleteAction()
    {
      // fetch vars to move attributes order up or down
      //
      $id_attribute   = $this->request->getParam('id_attribute');

      if(null !== $id_attribute)
      {
          $attribute = new Geocontexter_Model_AttributeDelete;

          $params    = array('id_attribute' => $id_attribute);

          $result = $attribute->deleteAttribute( $params );

          if($result instanceof Mozend_ModelError)
          {
             return $this->error( $result->getErrorString(), __file__, __line__ );
          }
          else if(true !== $result)
          {
            // delete of attribute failed because there are some table enries
            // which make use of this attribute
            // $result contains the related entries
          }
      }

      $this->indexAction();
    }
}
