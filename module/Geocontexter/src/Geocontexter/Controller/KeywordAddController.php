<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * add new keyword
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $Author: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class KeywordAddController extends AbstractController
{
    public function init()
    {
        $this->initView( 'geocontexter/keyword-add/index.phtml' );

        $this->view->id_keyword = $this->id_keyword = $this->params()->fromRoute('id_keyword',false);

        if (false === $this->id_keyword) {
            $this->id_keyword = 0;
        }

        if (0 == $this->id_keyword) {
            $this->renderer->headTitle('Add new keyword to parent root');
        }

        $this->view->partialData = array('id_keyword'  => $this->id_keyword,
                                         'active_page' => 'add');

        $this->view->id_keyword                 = $this->id_keyword;
        $this->view->keyword_id_parent          = $this->id_keyword;
        $this->view->keyword_branch_result      = array();
        $this->view->attribute_groups           = array();
        $this->view->attribute_id_group         = '';
        $this->view->keyword_title              = '';
        $this->view->keyword_description        = '';
        $this->view->keyword_id_status          = 100;
        $this->view->keyword_lang               = 'en';
        $this->view->error                      = array();
    }

    public function indexAction()
    {
        $this->init_data();
        return $this->view;
    }

    /**
     * add new keyword
     */
    public function addAction()
    {
        // cancel action ?
        //
        $cancel = $this->request->getPost()->cancel;

        $this->view->id_keyword = $this->id_keyword = $this->request->getPost()->id_keyword;

        if ($cancel !== null) {
            return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/keyword/index/id_keyword/' . $this->id_keyword);
        }

        // this array will contains data for the new keyword
        $params  = array();
        $_error  = array();

        $attribute_id_group = $this->request->getPost()->attribute_id_group;

        // fetch addditional attributes values if set
        //
        if ($attribute_id_group !== null) {

            $AttributeAssignKeyValue = $this->CoreModel('AttributeAssignKeyValue');

            $this->view->attribute_id_group = $attribute_id_group;

            // we pass this array to the partial view helper "_additional_attributes.phtml"
            //
            $this->view->additionalAttributes = array();
            $_attr_result = $this->view->additionalAttributes['attributes']
                          = $AttributeAssignKeyValue->getAttributesWithValuesByOrder( $attribute_id_group, $this->request->getPost() );

            $this->view->additionalAttributes['id_attribute_group'] = $attribute_id_group;
            $this->view->additionalAttributes['tabindex']           = 7;

            // get tabindex after the additional attribute fields
            //
            $this->view->tabindex_after_attributes += count($this->view->additionalAttributes['attributes']) - 1;

            // fetch attribute values and prepare/convert it in postgresql array format for insert as new keyword
            //
            $assign_result = $attributes_values->assign( $attribute_id_group, $this->request->getPost() );

            $params = $assign_result;
            $params['id_attribute_group'] = $attribute_id_group;
        }

        $this->view->keyword_title =
            $params['title'] = $this->request->getPost()->keyword_title;

        $this->view->keyword_description =
            $params['description'] = $this->request->getPost()->keyword_description;

        $this->view->keyword_id_parent =
            $params['id_parent'] = $this->request->getPost()->keyword_id_parent;

        $this->view->keyword_id_status =
            $params['id_status'] = $this->request->getPost()->keyword_id_status;

        $this->view->keyword_lang =
            $params['lang'] = $this->request->getPost()->keyword_lang;

        if (empty($params['title'])) {
            $_error[] = 'keyword name is empty';
        }

        if (count($_error) > 0) {
            $this->init_data();
            $this->view->error = $_error;
            $this->renderer->headTitle(implode('|', $_error));
            return $this->view;
        }

        $KeywordAdd = $this->CoreModel('KeywordAdd');

        $KeywordAdd->run( $params );

        return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/keyword/index/id_keyword/' . $this->view->keyword_id_parent);
    }

    private function init_data()
    {
        if(0 != $this->id_keyword)
        {
            // get current keyword as parent of the new keyword
            //
            $KeywordGet = $this->CoreModel('KeywordGet');

            $params  = array('id_keyword' => $this->id_keyword);

            $result  = $KeywordGet->run( $params );

            if ($result === false) {
                throw new \Exception ('Keyword id dosent exists: ' . $this->id_keyword);
            }

            $this->view->id_keyword = $this->view->keyword_id_parent = $result['id_keyword'];

            $this->renderer->headTitle('Add new keyword to parent ' . $result['title']);
        }

        // get keyword branch branch
        //
        $KeywordGetBranch = $this->CoreModel('KeywordGetBranch');

        $params  = array('id_keyword' => $this->id_keyword) ;

        $this->view->keyword_branch_result = $KeywordGetBranch->run( $params );


        // get only languages that are flagged enabled=true
        //
        $LanguagesGet = $this->CoreModel('LanguagesGet');

        $params = array('enable' => 'true');

        $this->view->languages = $LanguagesGet->run( $params );

        // get all attribute groups related to keywords (id_table = 4)
        //
        $AttributeGroupsGet = $this->CoreModel('AttributeGroupsGet');

        $params = array('id_table' => 4);

        $this->view->attribute_groups = $AttributeGroupsGet->run( $params );
    }
}

