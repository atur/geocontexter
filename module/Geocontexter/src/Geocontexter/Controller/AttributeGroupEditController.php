<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * edit attribute group
 *
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class AttributeGroupEditController extends AbstractController
{
    public function init()
    {
        $this->initView( 'geocontexter/attribute-group-edit/index.phtml' );

        // init view variables
        //
        $this->view->attribute_name        = '';
        $this->view->attribute_description = '';
        $this->view->attribute_id_table    = '';
        $this->view->attribute_id_status   = 0;
        $this->view->error                 = array();

        $this->view->partialData = array('active_page' => 'update');
    }

    /**
     * no action. just render the view
     */
    public function indexAction()
    {
        $this->view->id_group = $this->id_group = $this->params()->fromRoute('id_group',false);

        if ($this->id_group === null) {
            return $this->error( 'no id_group request parameter defined.', __file__, __line__ );
        }

        $AttributeGroupGet = $this->CoreModel('AttributeGroupGet');

        $params = array('id_group' => $this->id_group );

        $result  = $AttributeGroupGet->run( $params );

        // prepend to page title
        //
        $this->renderer->headTitle('Update attribute group: ' . $result['title']);

        // get info if there is content associated to the attribute group (false if not, else true)
        //
        $AttributeDelete = $this->CoreModel('AttributeDelete');
        $this->view->showAreaList     =
        $this->view->showDeleteButton = $AttributeDelete->isContent( $this->id_group );

        $this->view->attribute_group_name        = $result['title'];
        $this->view->attribute_group_description = $result['description'];
        $this->view->attribute_group_id_status   = $result['id_status'];
        $this->view->attribute_group_id_table    = $result['id_table'];

        return $this->view;
    }

    /**
     * update group action
     */
    public function updateAction()
    {
        // check on cancel action
        //
        $cancel = $this->request->getPost()->cancel;

        if ($cancel !== null) {
            return $this->redirect()->toRoute('admin', array('controller' => 'attribute',
                                                             'action'     => 'index'));
        }

        $this->view->id_group =
            $id_group = $this->request->getPost()->id_group;

        // check on delete action
        //
        $delete = $this->request->getPost()->delete;

        if ($delete !== null){
            $this->delete($id_group);
            return $this->redirect()->toRoute('admin', array('controller' => 'attribute',
                                                             'action'     => 'index'));
        }

        // new instance to update group
        //
        $AttributeGroupUpdate = $this->CoreModel('AttributeGroupUpdate');

        $this->view->attribute_group_name =
            $attribute_group_name = $this->request->getPost()->attribute_group_name;

        $this->view->attribute_group_description =
            $attribute_group_description = $this->request->getPost()->attribute_group_description;

        $this->view->attribute_group_id_status =
            $attribute_group_id_status = $this->request->getPost()->attribute_group_id_status;

        if (empty($attribute_group_name)){
            $this->view->error = array('Attribute group name is empty');
            $this->renderer->headTitle('Error: Attribute group name is empty');
            return;
        }

        $params  = array('id_group' => $id_group,
                         'data'     => array('title'       => $attribute_group_name,
                                             'id_status'   => $attribute_group_id_status,
                                             'description' => $attribute_group_description));

        $AttributeGroupUpdate->run( $params );

        return $this->redirect()->toRoute('admin', array('controller' => 'attribute',
                                                         'action'     => 'index'));
    }

    /**
     * delete attribute group
     */
    private function delete($id_group)
    {
        // get info if there is content associated to the attribute group (false if not, else true)
        //
        $AttributeGroupDelete = $this->CoreModel('AttributeGroupDelete');
        $result               = $AttributeGroupDelete->run( array('id_group' => $id_group) );
    }
}

