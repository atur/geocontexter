<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * handles: show attribute groups, delete attribute group, import attribute groups from file upload
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class AttributeController extends AbstractController
{
    public function init()
    {
        $this->initView( 'geocontexter/attribute/index.phtml' );

        // prepend to page title
        //
        $this->renderer->headTitle('Areas of attribute groups');

        // partial view variables for layout/context_navigation.phtml
        //
        $this->view->partialData = array('active_page' => 'main');

        $this->view->error = array();
    }

    /**
     * load attribute groups
     *
     */
    public function indexAction()
    {
        $params                  = array();
        $params['system_serial'] = true;
        $params['has_relation']  = true;

        $AttributeGroupsGet = $this->CoreModel('AttributeGroupsGet');

        // -------------------------
        // groups of table gc_record
        //

        $params['id_table'] = 1;

        $this->view->gc_record_value_atttribute_groups = $AttributeGroupsGet->run($params);

        // ----------------------------
        // groups of table gc_list_item
        //

        $params['id_table']      = 2;

        $this->view->gc_list_item_atttribute_groups = $AttributeGroupsGet->run($params);

        // -----------------------
        // groups of table gc_item
        //

        $params['id_table']      = 3;

        $this->view->gc_item_atttribute_groups = $AttributeGroupsGet->run($params);

        // --------------------------
        // groups of table gc_keyword
        //
        $params['id_table']      = 4;

        $this->view->gc_keyword_atttribute_groups = $AttributeGroupsGet->run($params);

        return $this->view;
    }

    /**
     * delete attribute group
     */
    public function deleteAction()
    {
        $id_group   = $this->params()->fromRoute('id_group',false);

        if ($id_group !== null) {

            $AttributeGroupDelete = $this->CoreModel('AttributeGroupDelete');

            $params = array('id_group' => $id_group);

            $AttributeGroupDelete->run( $params );
        }

        return $this->indexAction();
    }

    /**
     * upload attribute groups backup file for import
     */
    public function uploadAction()
    {

        $upload = $this->getServiceLocator()->get('CoreUploadForm');
        $upload->init('upload-form');

        $request = $this->getRequest();
        if ($request->isPost()) {
            // Make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $upload->form->setData($post);
            if ($upload->form->isValid()) {
                $data = $upload->form->getData();

                $AttributeImport = $this->CoreModel('AttributeImport');

                $result  = $AttributeImport->run(array('file' => $data['upload_file']));
            }
        }

        return $this->indexAction();
    }
}

