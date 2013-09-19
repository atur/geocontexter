<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * get child keywords
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class AjaxKeywordGetChildsHtmlController extends AbstractController
{
    public function init()
    {
        $this->initView( 'geocontexter/ajax-keyword-get-childs-html/index.phtml' );
        $this->view->setTerminal(true);
    }

    /**
     * get group attributes
     */
    public function indexAction()
    {
        $this->view->id_keyword = $this->request->getPost()->id_keyword;
        $this->view->input_type = $this->request->getPost()->input_type;
        $this->view->id_value   = $this->request->getPost()->id_value;
        $id_parent              = $this->request->getPost()->id_parent;

        // get the item group attributes
        //
        $this->view->keyword_result = array();

        $KeywordGetChilds = $this->CoreModel('KeywordGetChilds');

        $params  = array('id_parent' => $id_parent) ;

        $this->view->keyword_result = $KeywordGetChilds->run( $params );

        return $this->view;
    }
}

