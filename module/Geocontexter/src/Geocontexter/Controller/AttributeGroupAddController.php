<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * add new item group controller
 *
 * We use the same view for "index" and "add" action
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class AttributeGroupAddController extends AbstractController
{
    /**
     * set some view variables we use in index view
     */
    public function init()
    {
        $this->initView( 'geocontexter/attribute-group-add/index.phtml' );

        // init view variables
        //
        $this->view->attribute_group_name        = '';
        $this->view->attribute_group_description = '';
        $this->view->attribute_group_id_table    = '';
        $this->view->error                 = array();

        $this->view->partialData = array('active_page' => 'add');

        // prepend to page title
        //
        $this->renderer->headTitle('Add new attribute group');
    }

    /**
     * no action. just render the view
     */
    public function indexAction()
    {
        return $this->view;
    }

    /**
     * add new group
     */
    public function addAction()
    {
        // check on cancel action
        //
        $cancel = $this->request->getPost('cancel');

        if ($cancel !== null) {
            return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/attribute');
        }

        // new instance to add group
        //
        $AttributeAddGroup = $this->CoreModel('AttributeAddGroup');

        $this->view->attribute_group_name =
            $attribute_group_name = $this->request->getPost('attribute_group_name');

        $this->view->attribute_group_description =
            $attribute_group_description = $this->request->getPost('attribute_group_description');

        $this->view->attribute_group_id_table =
            $attribute_group_id_table = $this->request->getPost('attribute_group_id_table');

        if (empty($attribute_group_name)) {
            $this->view->error = array('Attribute group name is empty');
            $this->renderer->headTitle('Error: Attribute group name is empty', 'PREPEND');
            return $this->view;
        }

        $params  = array('title'       => $attribute_group_name,
                         'description' => $attribute_group_description,
                         'id_table'    => (int)$attribute_group_id_table);

        $AttributeAddGroup->run( $params );

        return $this->redirect()->toRoute('admin', array('controller' => 'attribute'));
    }
}

