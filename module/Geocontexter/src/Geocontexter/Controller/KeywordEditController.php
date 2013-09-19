<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Edit keyword
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $Author: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class KeywordEditController extends AbstractController
{
   public function init()
    {
        $this->initView( 'geocontexter/keyword-edit/index.phtml' );

        $this->id_keyword = $this->params()->fromRoute('id_keyword',false);

        if(false === $this->id_keyword)
        {
            return $this->error( '0 or no id_keyword request parameter defined.', __file__, __line__ );
        }

        $this->view->partialData = array('id_keyword'     => $this->id_keyword,
                                         'active_page' => 'edit');

        $this->view->id_keyword                 = $this->id_keyword;
        $this->view->keyword_branch_result      = array();
        $this->view->attribute_groups           = array();
        $this->view->attribute_id_group         = '';
        $this->view->keyword_name               = '';
        $this->view->keyword_description        = '';
        $this->view->keyword_id_status          = 100;
        $this->view->keyword_lang               = 'en';
        $this->view->tabindex_after_attributes  = 8;
        $this->view->error                      = array();
    }

    public function indexAction()
    {
        $this->init_data();
        return $this->view;
    }

    private function init_data()
    {
        // get keyword
        //
        $KeywordGet = $this->CoreModel('KeywordGet');

        $params  = array('id_keyword' => $this->id_keyword);

        $result  = $KeywordGet->run( $params );

        if ($result === false) {
            throw new \Exception ('Keyword id dosent exists: ' . $this->id_keyword);
        }

        $this->view->result = $result;

        // init of model callbacks. used for new window model calls.
        //
        $this->register_model_callbacks();

        // get keyword branch
        //
        $KeywordGetBranch = $this->CoreModel('KeywordGetBranch');

        $params  = array('id_keyword' => $this->id_keyword) ;

        $this->view->keyword_branch_result = $KeywordGetBranch->run( $params );


        // get availaible languages
        //
        $LanguagesGet = $this->CoreModel('LanguagesGet');

        // optional
        $params = array('enable' => 'true');

        $this->view->languages = $LanguagesGet->run( $params );

        // get all keyword related attribute groups
        //
        $AttributeGroupsGet = $this->CoreModel('AttributeGroupsGet');

        $params = array('id_table' => 4);

        $this->view->attribute_groups = $AttributeGroupsGet->run( $params );

        // assign html head title
        //
        $this->renderer->headTitle('Edit keyword ' . $result['title']);

        // Assign additional attributes with values
        //
        if($result['id_attribute_group'] != null)
        {
            $attributes_values = new Geocontexter_Model_AttributeAssignKeyValue;

            // we pass this array to the partial view helper "_additional_attributes.phtml"
            //
            $this->view->additionalAttributes               = array();
            $this->view->additionalAttributes['attributes'] = $attributes_values->get( $result );

            $this->view->additionalAttributes['id_attribute_group'] = $result['id_attribute_group'];
            $this->view->additionalAttributes['tabindex']           = 7;

            // get tabindex after the aaditional attribute fields
            //
            $this->view->tabindex_after_attributes += count($this->view->additionalAttributes['attributes']) - 1;
        }
    }

    /**
     * update keyword
     */
    public function updateAction()
    {
        // check on cancel action
        //
        $cancel = $this->request->getPost('cancel');

        if($cancel !== null)
        {
            $this->_redirect($this->view->adminAreaToken .
                             '/geocontexter/keyword/index/id_keyword/' . $this->request->getPost('keyword_id_parent'));
        }

        $params  = array();

        $attribute_id_group = $this->request->getPost('attribute_group');

        // fetch addditional attributes values if set
        //
        if(!empty($attribute_id_group))
        {
            $attributes_values = new Geocontexter_Model_AttributeAssignKeyValue;

            $this->view->attribute_id_group = $attribute_id_group;

            // we pass this array to the partial view helper "_additional_attributes.phtml"
            //
            $this->view->additionalAttributes = array();
            $_attr_result = $this->view->additionalAttributes['attributes']
                          = $attributes_values->getAttributesWithValuesByOrder( $attribute_id_group, $this->request->getPost() );

            if($attr_result instanceof Mozend_ModelError)
            {
                return $this->error( $attr_result->getErrorString(), __file__, __line__ );
            }
            else
            {
                $this->view->additionalAttributes['id_attribute_group'] = $attribute_id_group;
                $this->view->additionalAttributes['tabindex']           = 7;

                // get tabindex after the additional attribute fields
                //
                $this->view->tabindex_after_attributes += count($this->view->additionalAttributes['attributes']) - 1;
            }

            // fetch attribute values and prepare/convert it in postgresql array format for insert as new keyword
            //
            $assign_result = $attributes_values->assign( $attribute_id_group, $this->request->getPost() );

            if($assign_result instanceof Mozend_ModelError)
            {
                return $this->error( $assign_result->getErrorString(), __file__, __line__ );
            }
            else
            {
                $params['data'] = $assign_result;
                $params['data']['id_attribute_group'] = $attribute_id_group;
            }
        }
        else
        {
            // remove reference to attribute group
            // and delete any additional attribute values
            //
            $params['data']['attribute_value'] = null;
        }

        $this->view->error  = array();
        $this->view->result = array();

        $this->id_keyword = $this->request->getPost('id_keyword');

        $params['id_keyword'] = $this->id_keyword;

        $this->view->result['title'] =
            $params['data']['title'] = $this->request->getPost('keyword_title');

        $this->view->result['description'] =
            $params['data']['description'] = $this->request->getPost('keyword_description');

        $this->view->result['id_parent'] =
            $params['data']['id_parent'] = $this->request->getPost('keyword_id_parent');

        $this->view->result['id_status'] =
            $params['data']['id_status'] = $this->request->getPost('keyword_id_status');

        $this->view->result['lang'] =
            $params['data']['lang'] = $this->request->getPost('keyword_lang');

        if(empty($params['data']['title']))
        {
            $this->view->error[] = 'Keyword title is empty';
            $this->view->headTitle('Error: keyword title field is empty', 'PREPEND');
        }

        if(count($this->view->error) > 0)
        {
            $this->init_data();
            return;
        }

        // add item attribute
        //

        $KeywordUpdate = $this->CoreModel('KeywordUpdate');

        $KeywordUpdate->run( $params );

        return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/keyword/index/id_keyword/' . $this->view->result['id_parent']);
    }

    /**
     * Register of model callback classes
     *
     * @param object $session
     */
    private function register_model_callbacks()
    {
        $this->ModelCallback = $this->CoreModel('ModelCallback');
        $this->ModelCallback->session = $this->sessionGet();

        // url to reload after a model callback was done
        //
        $this->opener_url = $this->getAdminBaseUrl() . '/keyword-edit/index/id_keyword/'.$this->id_keyword;

        //
        // item_keywords
        //
        $params_keyword =
                  array('model_class'         => 'KeywordUpdate',
                        'model_class_methode' => 'updateKeywordParent',
                        'model_field'         => 'id_parent',
                        'check_circular'      => true,
                        'id_name'             => 'id_keyword',
                        'id_value'            => $this->id_keyword,
                        'input_type'          => 'radio',
                        'opener_url'          => $this->opener_url);

        $callback_number  = $this->ModelCallback->register( $params_keyword );

        $this->view->keyword_callback_number = $callback_number;
    }
}

