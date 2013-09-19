<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * add new list
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $Author: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class ListAddController extends AbstractController
{
    public function init()
    {
        $this->initView( 'geocontexter/list-add/index.phtml' );

        $this->view->id_list = $this->id_list = $this->params()->fromRoute('id_list',false);

        if (false === $this->id_list) {
            $this->id_list = 0;
            $this->renderer->headTitle('Add new list to parent root');
        }

        $this->view->partialData = array('id_list'     => $this->id_list,
                                         'active_page' => 'add');

        $this->view->list_branch_result         = array();
        $this->view->attribute_groups           = array();
        $this->view->list_tree_result           = array();
        $this->view->attribute_id_group         = '';
        $this->view->list_name                  = '';
        $this->view->list_preferred             = 0;
        $this->view->list_description           = '';
        $this->view->list_id_status             = 100;
        $this->view->max_num_lists              = 500;
        $this->view->num_lists                  = 0;
        $this->view->list_lang                  = 'en';
        $this->view->error                      = array();
    }

    public function indexAction()
    {
        $this->init_data();

        return $this->view;
    }

    /**
     * add new list
     */
    public function addAction()
    {
        // cancel action ?
        //
        $cancel = $this->request->getPost()->cancel;

        $this->view->id_list = $id_list = $this->request->getPost()->id_list;

        if ($cancel !== null) {
            return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/list/index/id_list/' . $this->sessionOffsetGet('ListController_id_list'));
        }

        // this array will contains data for the new list
        $params  = array();
        $_error = array();

        $attribute_id_group = $this->request->getPost()->attribute_id_group;

        // fetch addditional attributes values if set
        //
        if ($attribute_id_group !== null) {

            $this->view->attribute_id_group = $params['id_attribute_group'] = $attribute_id_group;

            $AttributeJsonEncode = $this->CoreModel('AttributeJsonEncode');

            $serialized_attributes = $AttributeJsonEncode->encode( $attribute_id_group, $this->request->getPost() );

            $params['attribute_value'] = $serialized_attributes;

            $this->view->attribute_value = $serialized_attributes;

            // get tabindex after the additional attribute fields
            //
            $this->view->tabindex_after_attributes += $AttributeJsonEncode->numAttributes - 1;
        }

        $this->view->list_name =
            $params['title'] = $this->request->getPost()->list_name;

        $this->view->list_description =
            $params['description'] = $this->request->getPost()->list_description;

        $params['id_parent'] = $id_list;

        $this->view->list_id_status =
            $params['id_status'] = $this->request->getPost()->list_id_status;


        $_preferred = $this->request->getPost()->list_preferred;

        if ($_preferred == 1) {
            $this->view->list_preferred = 1;
            $params['preferred']        = true;
        } else {
            $this->view->list_preferred = 0;
            $params['preferred']        = false;
        }

        $this->view->list_lang =
            $params['lang'] = $this->request->getPost()->list_lang;

        if (empty($params['title'])) {
            $_error[] = 'List name is empty';
        }

        if (count($_error) > 0) {
            $this->init_data();
            $this->view->error = $_error;
            $this->renderer->headTitle(implode('|', $_error));

            $AttributeJsonDecode = $this->CoreModel('AttributeJsonDecode');

            $this->view->additionalAttributes = array('attributes' =>  $AttributeJsonDecode->decode( $serialized_attributes,
                                                                                                     $attribute_id_group ),
                                                      'id_attribute_group' => $attribute_id_group,
                                                      'tabindex'           => 7,
                                                      'escape'             => $this->view->escape);

            return $this->view;
        }

        // add item attribute
        //
        $ListAdd = $this->CoreModel('ListAdd');

        $ListAdd->run( $params );

        return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/list/index/id_list/' . $id_list);
    }

    private function init_data()
    {
        if (0 != $this->id_list) {
            // get current list as parent of the new list
            //
            $ListGet = $this->CoreModel('ListGet');
            $params  = array('id_list' => $this->id_list);
            $result  = $ListGet->run( $params );

            $this->renderer->headTitle('Add new list to parent ' . $result['title']);
        }

        // get list branch branch
        //
        $ListGetFromParentBranch = $this->CoreModel('ListGetFromParentBranch');

        $params  = array('id_list' => $this->id_list) ;

        $this->view->list_branch_result = $ListGetFromParentBranch->run( $params );

        // get only languages that are flagged enabled=true
        //
        $LanguagesGet = $this->CoreModel('LanguagesGet');
        $params = array('enable' => 'true');

        $this->view->languages = $LanguagesGet->run( $params );

        // get all attribute groups related to lists (id_table = 2)
        //
        $AttributeGroupsGet = $this->CoreModel('AttributeGroupsGet');
        $params = array('id_table' => 2);

        $this->view->attribute_groups = $AttributeGroupsGet->run( $params );
    }
}

