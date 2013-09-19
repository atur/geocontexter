<?php

namespace Core\Library;

use Zend\InputFilter;
use Zend\Form\Element;
use Zend\Form\Form;

class FileUploadForm
{
    /**
     * constructor
     *
     * @var $error array
     */
    public function __construct( $service )
    {
        $this->service  = $service;
    }

    public function init($target, $file_input_name, $form_name, $options = array())
    {
        $this->form = new Form();

        $this->form->setName($form_name);

        if (!empty($options)) {
            $this->form->setOptions($options);
        }

        $this->addElements($file_input_name);
        $this->addInputFilter($file_input_name, $target);
    }

    public function addElements($file_input_name)
    {
        // File Input
        $file = new Element\File($file_input_name);
        $file->setAttribute('id', $file_input_name);
        $this->form->add($file);
    }

    public function addInputFilter($file_input_name, $target)
    {
        $inputFilter = new InputFilter\InputFilter();

        // File Input
        $fileInput = new InputFilter\FileInput($file_input_name);
        $fileInput->setRequired(true);
        $fileInput->getFilterChain()->attachByName(
            'filerenameupload',
            array(
                'target'    => $target,
                'randomize' => true,
            )
        );
        $inputFilter->add($fileInput);

        $this->form->setInputFilter($inputFilter);
    }

}
