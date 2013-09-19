<?php
/**
 * GeoContexter
 * @link http://code.google.com/p/geocontexter/
 * @package GeoContexter
 */

/**
 * Edit list item
 *
 * @package GeoContexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev: 828 $ / $LastChangedDate: 2011-02-27 10:30:28 +0100 (So, 27 Feb 2011) $ / $Author: armand.turpel $
 */

namespace Geocontexter\Controller;

use Zend\View\Model\ViewModel;
use Core\Controller\AbstractController;

class ItemEditController extends AbstractController
{
   public function init()
    {
        $this->initView( 'geocontexter/item-edit/index.phtml' );

        $this->view->id_item = $this->id_item = $this->params()->fromRoute('id_item',false);
        $this->view->id_list = $this->id_list = $this->params()->fromRoute('id_list',false);

        if ((false === $this->id_item) || (0 == $this->id_item)) {
            return $this->error( '0 or no id_item request parameter defined.', __file__, __line__ );
        }

        if ((false === $this->id_list) || (0 == $this->id_list)) {
            return $this->error( '0 or no id_list request parameter defined.', __file__, __line__ );
        }

        // fetch the active tab number for jquery ui tabs
        //
        $this->view->tab_number = $this->params()->fromRoute('tab_number',false);

        if (false === $this->view->tab_number) {
          $this->view->tab_number = 0;
        }

        $this->view->id_list                    = $this->id_list;
        $this->view->item_branch_result      = array();
        $this->view->attribute_groups        = array();
        $this->view->lists_result            = array();
        $this->view->lists_childs_result     = array();
        $this->view->item_result             = array();
        $this->view->attribute_id_group      = 0;
        $this->view->item_name               = '';
        $this->view->item_description        = '';
        $this->view->item_id_status          = 100;
        $this->view->item_lang               = 'en';
        $this->view->tabindex_after_attributes = 8;
        $this->view->error = array();
        $this->view->result = array();
    }

    public function indexAction()
    {
        $this->view->partialData = array('id_list'     => $this->id_list,
                                         'id_item'     => $this->id_item,
                                         'active_page' => 'edit_item');

        // should we move an item list order
        //
        $this->move_item_list_order();
        $this->move_image_order();
        $this->move_file_order();

        $this->init_data();

        $this->delete_image();
        $this->delete_file();

        return $this->view;
    }

  /**
   * Delete item image
   *
   */
    public function delete_image()
    {
        // fetch vars to move attributes order up or down
        //
        $id_image   = $this->request->getPost()->deleteImage;

        if (null !== $id_image) {

            $ItemImageDelete = $this->CoreModel('ItemImageDelete');

            $params = array('id_image'     => $id_image,
                            'files_folder' => $this->view->item_result['files_folder']);

            $ItemImageDelete->run( $params );

            return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/item-edit/index/id_item/'.$this->id_item.'/id_list/'.$this->id_list.'/tab_number/' . $this->view->tab_number);
        }
    }

  /**
   * Move order of an item image
   *
   */
    public function move_image_order()
    {
        // fetch vars to move attributes order up or down
        //
        $move_image_up   = $this->request->getPost()->imageMoveUp;
        $move_image_down = $this->request->getPost()->imageMoveDown;
        $id_item         = $this->request->getPost()->id_item;

        if (null !== $move_image_up) {

            if (null === $id_item) {
                return $this->error( 'no id_item request parameter defined.', __file__, __line__ );
            }

            $ItemImageMoveOrder = $this->CoreModel('ItemImageMoveOrder');

            $ItemImageMoveOrder->moveUp(array('id_item'  => $id_item,
                                              'id_image' => $move_image_up));

            $this->view->headTitle('Moved image up: ');
        }

        if (null !== $move_image_down) {

            if (null === $id_item) {
                return $this->error( 'no id_item request parameter defined.', __file__, __line__ );
            }

            $ItemImageMoveOrder = $this->CoreModel('ItemImageMoveOrder');

            $ItemImageMoveOrder->moveDown(array('id_item'  => $id_item,
                                                'id_image' => $move_image_down));

            $this->view->headTitle('Moved image down: ');
        }
    }

  /**
   * Delete item file
   *
   */
    public function delete_file()
    {
        // fetch vars to move attributes order up or down
        //
        $id_file   = $this->request->getPost()->deleteFile;

        if (null !== $id_file) {

            $ItemFileDelete = $this->CoreModel('ItemFileDelete');

            $params = array('id_file'      => $id_file,
                            'files_folder' => $this->view->item_result['files_folder']);

            $ItemFileDelete->run( $params );

            return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/item-edit/index/id_item/'.$this->id_item.'/id_list/'.$this->id_list.'/tab_number/' . $this->view->tab_number);
        }
    }

  /**
   * Move order of an item file
   *
   */
    public function move_file_order()
    {
        // fetch vars to move file order up or down
        //
        $move_file_up   = $this->request->getPost()->fileMoveUp;
        $move_file_down = $this->request->getPost()->fileMoveDown;
        $id_item        = $this->request->getPost()->id_item;

        if (null !== $move_file_up) {

            if (null === $id_item) {
                return $this->error( 'no id_item request parameter defined.', __file__, __line__ );
            }

            $ItemFileMoveOrder = $this->CoreModel('ItemFileMoveOrder');

            $ItemFileMoveOrder->moveUp(array('id_item' => $id_item,
                                             'id_file' => $move_file_up));

            $this->renderer->headTitle('Moved file up: ');
        }

        if (null !== $move_file_down) {

            if (null === $id_item) {
                return $this->error( 'no id_item request parameter defined.', __file__, __line__ );
            }

            $ItemFileMoveOrder = $this->CoreModel('ItemFileMoveOrder');

            $ItemFileMoveOrder->moveDown(array('id_item' => $id_item,
                                               'id_file' => $move_file_down));

            $this->renderer->headTitle('Moved file down: ');
        }
    }

  /**
   * Move order of an item list
   *
   */
    public function move_item_list_order()
    {
        // fetch vars to move attributes order up or down
        //
        $move_list_up   = $this->params()->fromRoute('moveUp',false);
        $move_list_down = $this->params()->fromRoute('moveDown',false);
        $id_list_item   = $this->params()->fromRoute('id_list_item',false);
        $id_item        = $this->params()->fromRoute('id_item',false);

        if (false !== $move_list_up) {

            if (false === $id_item) {
                return $this->error( 'no id_item request parameter defined.', __file__, __line__ );
            }

            if (false === $id_list_item) {
                return $this->error( 'no id_list_item request parameter defined.', __file__, __line__ );
            }

            $ListItemMoveOrder = $this->CoreModel('ListItemMoveOrder');

            $ListItemMoveOrder->moveUp(array('id_item'      => $id_item,
                                             'id_list_item' => $id_list_item));

            $this->renderer->headTitle('Moved list up of item: ');
        }

        if (false !== $move_list_down) {
            if (false === $id_item) {
                return $this->error( 'no id_item request parameter defined.', __file__, __line__ );
            }

            if (false === $id_list_item) {
                return $this->error( 'no id_list_item request parameter defined.', __file__, __line__ );
            }

            $ListItemMoveOrder = $this->CoreModel('ListItemMoveOrder');

            $ListItemMoveOrder->moveDown(array('id_item'      => $id_item,
                                               'id_list_item' => $id_list_item));

            $this->renderer->headTitle('Moved list down of item: ');
        }
    }

    /**
     * update item data
     */
    public function updateAction()
    {
        $save          = $this->request->getPost()->save;
        $this->id_item = $this->request->getPost()->id_item;
        $this->id_list = $this->request->getPost()->id_list;

        // check on cancel action
        //
        $cancel = $this->request->getPost()->cancel;

        if ($cancel !== null) {
            return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/list/index/id_list/' . $this->id_list);
        }

        $this->view->partialData = array('id_list'     => $this->id_list,
                                         'id_item'     => $this->id_item,
                                         'active_page' => 'edit_item');

        $this->init_data();

        $params  = array();

        $this->_error = array();

        // check if upload image action
        //
        if (isset($_FILES['item_image']['name']) && !empty($_FILES['item_image']['name'])) {
            // check if a files folder entry for the current item exists
            //
            if (empty($this->view->item_result['files_folder'])) {
                $params['data']['files_folder'] = $data_folder_name = md5(uniqid($this->view->item_result['id_item'], true));
            } else {
                $data_folder_name = $this->view->item_result['files_folder'];
            }

            $ImageUpload = $this->CoreModel('ImageUpload');

            $folder_path = $this->config['public_folder'] .
                           '/data/gc_item/' .
                           $this->view->item_result['id_item'] .
                           '_'.
                           $data_folder_name .
                           '/images';

            $upload_params  = array('request'     => $this->request,
                                    'post_name'   => 'item_image',
                                    'form_name'   => 'item_image',
                                    'folder_path' => $folder_path);

            try {

                $image_result  = $ImageUpload->run( $upload_params );

            } catch(\Exception $e) {

               $this->error( $image_result->getErrorString(), __file__, __line__, true );

               // assign view error
               //
               $__error = $image_result->getErrorArray();

               foreach ($__error as $key => $val) {
                  if (is_array($val)) {
                      foreach ($val as $err) {
                          $this->_error[] = $err;
                      }
                  } else {
                      $this->_error[] = $val;
                  }
               }

               $save = false;

               $this->vies->error = $this->_error;
               return $this->view;
            }

            // add item image entry in geocontexter.gc_item_image
            //
            $image_params = array('id_item'     => $this->view->item_result['id_item'],
                                  'file_mime'   => $image_result['type'],
                                  'file_name'   => $image_result['name'],
                                  'file_size'   => $image_result['size'],
                                  'file_width'  => $image_result['image_width'],
                                  'file_height' => $image_result['image_height']
                                  );

            $ItemImageAdd = $this->CoreModel('ItemImageAdd');

            try {
                $result  = $ItemImageAdd->run($image_params);
            } catch (\Exception $e) {
                $image_file->removeFile();
            }

            $save = true;

            $this->view->tab_number   = 5;

        } else if(isset($_FILES['item_file']['name']) && !empty($_FILES['item_file']['name'])) {
            // check if a files folder entry for the current item exists
            //
            if (empty($this->view->item_result['files_folder'])) {
                $params['data']['files_folder'] = $data_folder_name = md5(uniqid($this->view->item_result['id_item'], true));
            } else {
                $data_folder_name = $this->view->item_result['files_folder'];
            }

            $FileUpload = $this->CoreModel('FileUpload');

            $upload_params  = array('post_name'   => 'item_file',
                                    'data_folder' => DATA_PATH . '/gc_item/' . $data_folder_name);

            try {
                $file_result  = $FileUpload->run( $upload_params );
            } catch (\Exception $e) {
               $this->error( $file_result->getErrorString(), __file__, __line__, true );

               // assign view error
               //
               $__error = $file_result->getErrorArray();

               foreach ($__error as $key => $val) {
                  if (is_array($val)) {
                      foreach ($val as $err) {
                          $this->_error[] = $err;
                      }
                  } else {
                      $this->_error[] = $val;
                  }
               }

               $save = false;
            }

            // add item image entry in geocontexter.gc_item_file
            //
            $file_params = array('id_item'     => $this->view->item_result['id_item'],
                                 'title'       => '',
                                 'description' => '',
                                 'file_mime'   => $file_result['type'],
                                 'file_name'   => $file_result['name'],
                                 'file_size'   => $file_result['size']
                                 );

            $ItemFileAdd = $this->CoreModel('ItemFileAdd');

            try {
                $result  = $ItemFileAdd->run($file_params);
            } catch (\Exception $e) {
                $file_file->removeFile();

                $this->_error[] = $result->getErrorArray();
                return $this->error( $result->getErrorString(), __file__, __line__, true  );
            }

            $save = true;

            $this->view->tab_number   = 6;
        } else {
            $this->view->tab_number   = 0;
        }

        $attribute_id_group = $this->request->getPost()->id_attribute_group;

        // fetch addditional attributes values if set
        //
        if ($attribute_id_group !== null) {
            $this->view->attribute_id_group = $attribute_id_group;

            $AttributeJsonEncode = $this->CoreModel('AttributeJsonEncode');

            $serialized_attributes = $AttributeJsonEncode->encode( $attribute_id_group, $this->request->getPost() );

            $params['data']['attribute_value']    = $serialized_attributes;
            $params['data']['id_attribute_group'] = $attribute_id_group;

            $this->view->additionalAttributes['id_attribute_group'] = $attribute_id_group;
            $this->view->additionalAttributes['tabindex']           = 7;

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

        $params['id_item'] = $this->id_item;

        $remove_item_list = $this->request->getPost()->delete_id_list;
        if (($remove_item_list !== null) && is_array($remove_item_list)) {
            $params['remove_item_list'] = $remove_item_list;
        }

        $params['data']['title'] = $this->request->getPost()->item_name;

        $params['data']['description'] = $this->request->getPost()->item_description;

        $params['data']['id_status'] = $this->request->getPost()->item_id_status;

        $params['data']['lang'] = $this->request->getPost()->item_lang;

        $preferred_list = $this->request->getPost()->preferred_id_list;

        if (null !== $preferred_list) {
            $params['preferred_list'] = $preferred_list;
        }

        $remove_synonym_of = trim($this->request->getPost()->remove_synonym_of);

        if (!empty($remove_synonym_of)) {
            $params['data']['synonym_of'] = null;
        }

        $delete_id_keyword = $this->request->getPost()->delete_id_keyword;

        if (($delete_id_keyword !== null) && (count($delete_id_keyword) > 0)) {
            $params['remove_id_keyword'] = $delete_id_keyword;
        }

        $delete_list_id_keyword = $this->request->getPost()->delete_item_list_id_keyword;

        if (($delete_list_id_keyword !== null) && (count($delete_list_id_keyword) > 0)) {
            $params['remove_list_id_keyword'] = $delete_list_id_keyword;
            $params['id_list_item']           = $this->item_list['id_list_item'];
        }

        if (empty($params['data']['title'])) {
            $this->_error[] = 'Item name is empty';
        }

        if (count($this->_error) > 0) {
            $this->renderer->headTitle(implode('|', $this->_error));
            $this->view->result = $params['data'];
            $this->view->error = $this->_error;
            $this->init_data();

            return $this->view;
        }

        $this->update_images();
        $this->update_files();

        // add item attribute
        //
        $ItemUpdate = $this->CoreModel('ItemUpdate');

        $result = $ItemUpdate->run( $params );

        if ($save === null) {

            return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/list/index/id_list/' . $this->sessionOffsetGet('ListController_id_list'));


        } else {

            return $this->redirect()->toUrl($this->getAdminBaseUrl() . '/item-edit/index/id_item/'.$this->id_item. '/id_list/' . $this->sessionOffsetGet('ListController_id_list').'/tab_number/' . $this->view->tab_number);

        }
    }

    /**
     * update image
     *
     */
    private function update_images()
    {
        $id_image          = $this->request->getPost()->id_image;
        $image_title       = $this->request->getPost()->image_title;
        $image_description = $this->request->getPost()->image_description;
        $x                 = 0;

        if(is_array($image_title)) {

            $ItemImageUpdate = $this->CoreModel('ItemImageUpdate');

            foreach ($image_title as $title) {

                $params = array('id_image' => $id_image[$x],
                                'data'     => array('title'       => strip_tags($title),
                                                    'description' => strip_tags($image_description[$x])));

                $ItemImageUpdate->run( $params );

                $x++;
            }
        }
    }

    /**
     * update files
     *
     */
    private function update_files()
    {
        $id_file           = $this->request->getPost()->id_file;
        $file_title        = $this->request->getPost()->file_title;
        $file_description  = $this->request->getPost()->file_description;
        $x                 = 0;

        if(is_array($file_title)) {

            $ItemFileUpdate = $this->CoreModel('ItemFileUpdate');

            foreach ($file_title as $title) {

                $params = array('id_file' => $id_file[$x],
                                'data'     => array('title'       => strip_tags($title),
                                                    'description' => strip_tags($file_description[$x])));

                $ItemFileUpdate->run( $params );

                $x++;
            }
        }
    }

    /**
     * get item synonym
     *
     * @param  bigint $synonym_of
     * @return array
     */
    private function get_item_synonym_of( $synonym_of )
    {
        $synonym_result = array();

        if (!empty($synonym_of)) {

           $ListGetItemRelated = $this->CoreModel('ListGetItemRelated');

           $params  = array('id_item'                  => $synonym_of,
                            'order_by_preferred_order' => true);

           $synonym_result  = $ListGetItemRelated->run( $params );

        }

        return $synonym_result;
    }

    private function init_data()
    {
        $ItemGet = $this->CoreModel('ItemGet');

        $params  = array('id_item'         => $this->id_item,
                         'default_display' => 0,
                         'system_serial'   => true);

        $item_result  = $ItemGet->run( $params );

        if ($item_result === false) {
            throw new \Exception ('List item id dosent exists: ' . $this->id_item);
        }

        $this->view->item_result = $item_result;

        // assign item attributes
        //
        if ($item_result['id_attribute_group'] != null) {

            $AttributeJsonDecode = $this->CoreModel('AttributeJsonDecode');

            // we pass this array to the partial view helper "_additional_attributes.phtml"
            //
            $this->view->additionalAttributes = array('attributes' =>  $AttributeJsonDecode->decode( $item_result['attribute_value'],
                                                                                                     $item_result['id_attribute_group'] ),
                                                      'id_attribute_group' => $item_result['id_attribute_group'],
                                                      'tabindex'           => 7,
                                                      'escape'             => $this->view->escape);

            // get tabindex after the aaditional attribute fields
            //
            $this->view->tabindex_after_attributes += count($this->view->additionalAttributes['attributes']) - 1;
        }

        // fetch the synonym of the requested item
        //
        $this->view->synonym_result = $this->get_item_synonym_of( $item_result['synonym_of'] );

        // get item images
        //
        $ItemImageGetAll = $this->CoreModel('ItemImageGetAll');

        $params  = array('id_item' => $this->id_item);

        $this->view->item_images = $ItemImageGetAll->run( $params );

        // get item files
        //
        $ItemFileGetAll = $this->CoreModel('ItemFileGetAll');

        $params  = array('id_item' => $this->id_item);

        $this->view->item_files = $ItemFileGetAll->run( $params );

        // get item lists
        //
        $ListGetItemRelated = $this->CoreModel('ListGetItemRelated');

        $params  = array('id_item'                  => $this->id_item,
                         'order_by_preferred_order' => true,
                         'system_serial'            => true);

        $this->view->lists_result = $ListGetItemRelated->run( $params );

        // get availaible languages
        //
        $LanguagesGet = $this->CoreModel('LanguagesGet');

        // optional
        $params = array('enable' => 'true');

        $this->view->languages = $LanguagesGet->run( $params );

        // get all list related attribute groups
        //
        $AttributeGroupsGet = $this->CoreModel('AttributeGroupsGet');

        $params = array('id_table' => 3);

        $this->view->attribute_groups = $AttributeGroupsGet->run( $params );

        // get current item list
        //
        $ItemListGet = $this->CoreModel('ItemListGet');

        $params  = array('id_list' => $this->id_list,
                         'id_item' => $this->id_item);

        $this->view->list_result = $this->item_list = $ItemListGet->run( $params );

        $ListGetChilds = $this->CoreModel('ListGetChilds');

        $params  = array('id_parent' => 0) ;

        $this->view->lists_childs_result = $ListGetChilds->run( $params );

        $ItemGetKeywordBranches = $this->CoreModel('ItemGetKeywordBranches');

        $params  = array('id_item' => $this->id_item);

        $this->view->keywords_result = $ItemGetKeywordBranches->run( $params );

        $ItemListGetKeywordBranches = $this->CoreModel('ItemListGetKeywordBranches');

        $params  = array('id_item' => $this->id_item,
                         'id_list' => $this->id_list);

        $this->view->item_list_keywords_result = $ItemListGetKeywordBranches->run( $params );

        // assign html head title
        //
        $this->renderer->headTitle('Edit item ' . $item_result['title']);

        // init of model callbacks. used for new window model calls.
        //
        $this->register_model_callbacks();
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
        $this->opener_url = $this->getAdminBaseUrl() . '/item-edit/index/id_item/'.$this->id_item.'/id_list/'.$this->id_list;

        //
        // item_list_keywords
        //
        $params_item_list_keyword =
                array('model_class'         => 'ItemListAddKeyword',
                      'model_class_methode' => 'run',
                      'model_field'         => 'id_keyword',
                      'id_name'             => 'id_list_item',
                      'id_value'            => $this->item_list['id_list_item'],
                      'input_type'          => 'checkbox',
                      'opener_url'          => $this->opener_url . '/tab_number/3');

        $callback_number  = $this->ModelCallback->register( $params_item_list_keyword );

        $this->view->item_list_keyword_callback_number = $callback_number;

        //
        // item_keywords
        //
        $params_item_keyword =
                  array('model_class'         => 'ItemAddKeywords',
                        'model_class_methode' => 'run',
                        'model_field'         => 'id_keyword',
                        'id_name'             => 'id_item',
                        'id_value'            => $this->id_item,
                        'input_type'          => 'checkbox',
                        'opener_url'          => $this->opener_url . '/tab_number/2');

        $callback_number  = $this->ModelCallback->register( $params_item_keyword );

        $this->view->item_keyword_callback_number = $callback_number;

        // item synonym
        //
        $params_item_synonym =
                  array('model_class'         => 'ItemChangeSynonym',
                        'model_class_methode' => 'run',
                        'model_field'         => 'synonym_of',
                        'id_name'             => 'id_item',
                        'id_value'            => $this->id_item,
                        'input_type'          => 'radio',
                        'opener_url'          => $this->opener_url . '/tab_number/0');

        $callback_number  = $this->ModelCallback->register( $params_item_synonym );

        $this->view->item_synonym_callback_number = $callback_number;

        //
        // item_lists
        //
        $params_item_list =
                  array('model_class'         => 'ItemAddList',
                        'model_class_methode' => 'run',
                        'model_field'         => 'id_list',
                        'id_name'             => 'id_item',
                        'id_value'            => $this->id_item,
                        'input_type'          => 'checkbox',
                        'opener_url'          => $this->opener_url . '/tab_number/1');

        $callback_number  = $this->ModelCallback->register( $params_item_list );

        $this->view->item_list_callback_number = $callback_number;
    }

    /**
     * upload attribute groups backup file for import
     */
    public function file_upload( $form_name )
    {

        $upload = $this->getServiceLocator()->get('CoreUploadForm');
        $upload->init($form_name);

        $request = $this->getRequest();

        if ($request->isPost()) {

            // Make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $upload->form->setData($post);

            if ($upload->form->isValid()) {

                return $upload->form->getData();

            }
        }

        return false;
    }
}

