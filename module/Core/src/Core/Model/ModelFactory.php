<?php
/**
 * Geocontexter
 * @link http://code.google.com/p/geocontexter/
 * @package Geocontexter
 */

/**
 * Model factory
 *
 * @package Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev$ / $LastChangedDate$ / $LastChangedBy$
 */

namespace Core\Model;

class ModelFactory
{
    private $service;

    public function __construct($service)
    {
       $this->service = $service;
    }

    /**
     * build demanded model instance
     *
     * @param  string $model_class Class name
     * @param  string $namespace
     * @return object Model instance
     */
    public function getModelInstance($model_class, $namespace = 'Geocontexter')
    {
        $_class = '\\' . $namespace . '\\Model\\' . $model_class;
        return new $_class($this->service);
    }
}
