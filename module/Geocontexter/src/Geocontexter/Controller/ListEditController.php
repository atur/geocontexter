<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Edit list
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $Author: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class ListEditController extends AbstractController
{
    public function init()
    {
        $this->initView( 'geocontexter/list-edit/index.phtml' );

        $this->view->id_list = $this->id_list = $this->params()->fromRoute('id_list',false);

        if (false === $this->id_list) {
            return $this->error( '0 or no id_list request parameter defined.', __file__, __line__ );
        }

        $this->view->partialData = array('id_list'     => $this->id_list,
                                         'active_page' => 'edit');

        $this->view->id_list                    = $this->id_list;
        $this->view->list_branch_result         = array();
        $this->view->attribute_groups           = array();
        $this->view->attribute_id_group         = '';
        $this->view->list_name                  = '';
        $this->view->list_preferred             = 1;
        $this->view->list_description           = '';
        $this->view->list_id_status             = 100;
        $this->view->keywords_result            = array();
        $this->view->list_lang                  = 'en';
        $this->view->tabindex_after_attributes  = 8;
    }

    public function indexAction()
    {
        $this->init_data();

        return $this->view;
    }

    /**
     * add new list group
     */
    public function updateAction()
    {
        // check on cancel action
        //
        $cancel = $this->request->getPost()->cancel;
        $this->view->id_list = $id_list = $this->request->getPost()->id_list;

        $_error = array();

        if ($cancel !== null) {
            return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/list/index/id_list/' . $this->sessionOffsetGet('ListController_id_list'));
        }

        $params  = array();

        $attribute_id_group = $this->request->getPost()->attribute_group;

        // fetch addditional attributes values if set
        //
        if (!empty($attribute_id_group)) {

            $this->view->attribute_id_group = $attribute_id_group;

            $AttributeJsonEncode = $this->CoreModel('AttributeJsonEncode');

            $serialized_attributes = $AttributeJsonEncode->encode( $attribute_id_group, $this->request->getPost() );

            $params['data']['attribute_value']    = $serialized_attributes;
            $params['data']['id_attribute_group'] = $attribute_id_group;

            // get tabindex after the additional attribute fields
            //
            $this->view->tabindex_after_attributes += $AttributeJsonEncode->numAttributes - 1;

            $AttributeJsonDecode = $this->CoreModel('AttributeJsonDecode');

            $this->view->additionalAttributes = array('attributes' =>  $AttributeJsonDecode->decode( $serialized_attributes,
                                                                                                     $attribute_id_group ),
                                                      'id_attribute_group' => $attribute_id_group,
                                                      'tabindex'           => 7,
                                                      'escape'             => $this->view->escape);
        }

        $params['id_list'] = $id_list;

        $params['data']['title'] = $this->request->getPost()->list_name;

        $params['data']['description'] = $this->request->getPost()->list_description;

        $params['data']['id_parent'] = $this->request->getPost()->list_id_parent;

        $params['data']['id_status'] = $this->request->getPost()->list_id_status;

        $_preferred = $this->request->getPost()->list_preferred;

        if ($_preferred == 1) {
            $this->view->list_preferred  = 1;
            $params['data']['preferred'] = true;
        } else {
            $this->view->list_preferred  = 0;
            $params['data']['preferred'] = false;
        }

        $this->view->result['lang'] =
            $params['data']['lang'] = $this->request->getPost()->list_lang;

        $delete_id_keyword = $this->request->getPost()->delete_id_keyword;

        if (($delete_id_keyword) !== null && (count($delete_id_keyword) > 0)) {
            $params['remove_id_keyword'] = $delete_id_keyword;
        }

        if (empty($params['data']['title'])) {
            $_error[] = 'List name is empty';
        }

        if (count($_error) > 0) {
            $this->renderer->headTitle(implode('|', $_error));
            $this->view->result = $params['data'];
            $this->view->error = $_error;
            $this->init_data();
            return;
        }

        $ListUpdate = $this->CoreModel('ListUpdate');

        $ListUpdate->run( $params );

        return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/list/index/id_list/' . $this->sessionOffsetGet('ListController_id_list'));

    }

    /**
     * Register of model callback classes
     *
     *
     *
     * @param object $session
     */
    private function register_model_callbacks()
    {
        $this->ModelCallback = $this->CoreModel('ModelCallback');
        $this->ModelCallback->session = $this->sessionGet();

        // url to reload after a model callback was done
        //
        $this->opener_url = $this->getAdminBaseUrl() . '/list-edit/index/id_list/'.$this->id_list;

        //
        // list_keywords
        //
        $params_list_keyword =
                  array('model_class'         => 'ListAddKeyword',
                        'model_class_methode' => 'run',
                        'model_field'         => 'id_keyword',
                        'id_name'             => 'id_list',
                        'id_value'            => $this->id_list,
                        'input_type'          => 'checkbox',
                        'opener_url'          => $this->opener_url);

        $callback_number  = $this->ModelCallback->register( $params_list_keyword );

        $this->view->list_keyword_callback_number = $callback_number;

        //
        // list id_parent
        //
        $params_list =
                  array('model_class'         => 'ListUpdate',
                        'model_class_methode' => 'run',
                        'data_array'          => true,
                        'model_field'         => 'id_parent',
                        'id_name'             => 'id_list',
                        'id_value'            => $this->id_list,
                        'input_type'          => 'radio',
                        'root_allowed'        => true,
                        'opener_url'          => $this->opener_url);

        $callback_number  = $this->ModelCallback->register( $params_list );

        $this->view->list_parent_callback_number = $callback_number;
    }

    private function init_data()
    {
        // init of model callbacks. used for new window model calls.
        //
        $this->register_model_callbacks();

        // get list parent branch
        //
        $ListGetFromParentBranch = $this->CoreModel('ListGetFromParentBranch');

        $params  = array('id_list' => $this->id_list) ;

        $this->view->list_branch_result = $ListGetFromParentBranch->run( $params );

        $ListGetChilds = $this->CoreModel('ListGetChilds');

        $params  = array('id_parent'     => 0) ;

        $this->view->list_result = $ListGetChilds->run( $params );

        // get availaible languages
        //
        $LanguagesGet = $this->CoreModel('LanguagesGet');

        // optional
        $params = array('enable' => 'true');

        $this->view->languages = $LanguagesGet->run( $params );

        // get all list related attribute groups
        //
        $AttributeGroupsGet = $this->CoreModel('AttributeGroupsGet');

        $params = array('id_table' => 2);

        $this->view->attribute_groups = $AttributeGroupsGet->run( $params );

        // get all list
        //
        $ListGet = $this->CoreModel('ListGet');
        $params  = array('id_list' => $this->id_list);

        $this->view->result = $result_list  = $ListGet->run( $params );

        $this->renderer->headTitle('Edit list ' . $result_list['title']);

        $ListGetKeywordBranches = $this->CoreModel('ListGetKeywordBranches');

        $params  = array('id_list' => $this->id_list);

        $this->view->keywords_result = $ListGetKeywordBranches->run( $params );

        // Assign additional attributes with values
        //
        if ($result_list['id_attribute_group'] != null) {

            $AttributeJsonDecode = $this->CoreModel('AttributeJsonDecode');

            // we pass this array to the partial view helper "_additional_attributes.phtml"
            //
            $this->view->additionalAttributes = array('attributes' =>  $AttributeJsonDecode->decode( $result_list['attribute_value'],
                                                                                                     $result_list['id_attribute_group'] ),
                                                      'id_attribute_group' => $result_list['id_attribute_group'],
                                                      'tabindex'           => 7,
                                                      'escape'             => $this->view->escape);

            // get tabindex after the aaditional attribute fields
            //
            $this->view->tabindex_after_attributes += count($this->view->additionalAttributes['attributes']) - 1;
        }
    }
}

