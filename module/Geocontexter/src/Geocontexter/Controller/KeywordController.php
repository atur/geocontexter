<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Main keyword controller
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $LastChangedBy: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class KeywordController extends AbstractController
{
    public function init()
    {

        $this->initView( 'geocontexter/keyword/index.phtml' );

        $this->id_keyword = $this->params()->fromRoute('id_keyword',false);

        if (false === $this->id_keyword) {
            $this->id_keyword = 0;
            $this->renderer->headTitle('Show child keywords of Root');
        }

        $this->sessionOffsetSet('ListController_id_list', $this->id_keyword);

        $this->view->keyword_result             = array();
        $this->view->id_keyword                 = $this->id_keyword;

        $this->view->partialData = array('id_keyword'  => $this->id_keyword,
                                         'active_page' => 'main');

        $this->view->error = array();
    }

    public function indexAction()
    {
        if (0 != $this->id_keyword) {

            $KeywordGet = $this->CoreModel('KeywordGet');

            $params  = array('id_keyword' => $this->id_keyword);
            $result  = $KeywordGet->run( $params );

            if ($result === false) {
                throw new \Exception ('Keyword id dosent exists: ' . $this->id_keyword);
            }

            $this->renderer->headTitle('Show child keywords of ' . $result['title']);
        }

        // fetch vars to move keyword in order up or down
        //
        $move_id_keyword_up   = $this->params()->fromRoute('moveUp',false);
        $move_id_keyword_down = $this->params()->fromRoute('moveDown',false);

        if (false !== $move_id_keyword_up) {

            $KeywordMoveOrder = $this->CoreModel('KeywordMoveOrder');

            $KeywordMoveOrder->moveUp(array('id_keyword' => $move_id_keyword_up));

            $this->renderer->headTitle('Moved keyword up: ');
        }

        if (false !== $move_id_keyword_down) {

            $KeywordMoveOrder = $this->CoreModel('KeywordMoveOrder');

            $KeywordMoveOrder->moveDown(array('id_keyword' => $move_id_keyword_down));

            $this->renderer->headTitle('Moved keyword down: ');
        }

        $KeywordGetBranch = $this->CoreModel('KeywordGetBranch');

        $params  = array('id_keyword' => $this->id_keyword) ;

        $this->view->keyword_branch_result = $KeywordGetBranch->run( $params );

        $KeywordGetChilds = $this->CoreModel('KeywordGetChilds');

        $params  = array('id_parent'       => $this->id_keyword,
                         'preferred_order' => "asc",
                         'system_serial'   => true) ;

        $this->view->keyword_result = $KeywordGetChilds->run( $params );

        return $this->view;
    }
}

