<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * add new list item
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $Author: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class ItemAddNewController extends AbstractController
{
    public function init()
    {
        $this->initView( 'geocontexter/item-add-new/index.phtml' );

        $this->view->id_list = $this->id_list = $this->params()->fromRoute('id_list',false);

        if (false === $this->id_list) {
            $this->id_list = 0;
            $this->view->id_list = 0;
            $this->renderer->headTitle('Add new item in no list');
        } else {
            $this->renderer->headTitle('Add new item');
        }

        $this->view->partialData = array('id_list'     => $this->id_list,
                                         'active_page' => 'add_item_new');

        $this->view->list_branch_result         = array();
        $this->view->attribute_groups           = array();
        $this->view->attribute_id_group         = 0;
        $this->view->item_name                  = '';
        $this->view->item_description           = '';
        $this->view->item_id_status             = 100;
        $this->view->item_lang                  = 'en';
        $this->view->error                      = array();
    }

    public function indexAction()
    {
        $this->fetch_data();

        return $this->view;
    }

    /**
     * add new item
     */
    public function addAction()
    {
        // cancel action ?
        //
        $cancel = $this->request->getPost()->cancel;

        if ($cancel !== null) {
            return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/list/index/id_list/' . $this->sessionOffsetGet('ListController_id_list'));
        }

        $_error = array();

        // this array will contains data for the new list
        $params  = array();

        $attribute_id_group = $this->request->getPost()->attribute_id_group;
        $this->view->id_list = $this->id_list = $this->request->getPost()->id_list;

        $this->fetch_data();

        // fetch addditional attributes values if set
        //
        if (!empty($attribute_id_group)) {

            $this->view->attribute_id_group = $params['id_attribute_group'] = $attribute_id_group;

            $AttributeJsonEncode = $this->CoreModel('AttributeJsonEncode');

            $serialized_attributes = $AttributeJsonEncode->encode( $attribute_id_group, $this->request->getPost() );

            $params['data']['attribute_value'] = $serialized_attributes;

            // get tabindex after the additional attribute fields
            //
            $this->view->tabindex_after_attributes += $AttributeJsonEncode->numAttributes - 1;

        }

        $this->view->item_name =
            $params['data']['title'] = $this->request->getPost()->item_name;

        $this->view->item_description =
            $params['data']['description'] = $this->request->getPost()->item_description;

        if ($this->id_list != 0) {
            $params['id_list']        = $this->id_list;
            $params['preferred_list'] = true;
        }

        $this->view->item_id_status =
            $params['data']['id_status'] = $this->request->getPost()->item_id_status;

        $this->view->item_lang =
            $params['data']['lang'] = $this->request->getPost()->item_lang;

        if (empty($params['data']['title'])) {
            $_error[] = 'Item name is empty';
        }

        if (count($_error) > 0) {

            $this->view->error = $_error;
            $this->renderer->headTitle(implode('|', $_error));

            if (isset($serialized_attributes)) {

                $AttributeJsonDecode = $this->CoreModel('AttributeJsonDecode');

                $this->view->additionalAttributes = array('attributes' =>  $AttributeJsonDecode->decode( $serialized_attributes,
                                                                                                         $attribute_id_group ),
                                                          'id_attribute_group' => $attribute_id_group,
                                                          'tabindex'           => 7,
                                                          'escape'             => $this->view->escape);
            }

            return $this->view;
        }

        // add item attribute
        //
        $ItemAdd = $this->CoreModel('ItemAdd');

        $ItemAdd->run( $params );

        return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/list/index/id_list/' . $this->sessionOffsetGet('ListController_id_list'));
    }

    private function fetch_data()
    {
        // get list branch branch
        //
        $ListGetFromParentBranch = $this->CoreModel('ListGetFromParentBranch');

        $params  = array('id_list' => $this->id_list) ;

        $this->view->list_branch_result =  $ListGetFromParentBranch->run( $params );

        // get only languages that are flagged enabled=true
        //
        $LanguagesGet = $this->CoreModel('LanguagesGet');
        $params = array('enable' => 'true');

        $this->view->languages = $LanguagesGet->run( $params );

        // get all attribute groups related to lists (id_table = 2)
        //
        $AttributeGroupsGet = $this->CoreModel('AttributeGroupsGet');

        $params = array('id_table' => 3);

        $this->view->attribute_groups = $AttributeGroupsGet->run( $params );
    }
}

