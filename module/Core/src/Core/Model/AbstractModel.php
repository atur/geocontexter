<?php
/**
 * Geocontexter
 * @link http://code.google.com/p/mozend/
 * @package Geocontexter
 */

/**
 * Basic abstract model that every geocontexter module model class should extends
 *
 * @package Geocontexter
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @author Armand Turpel <geocontexter@gmail.com>
 * @version $Rev$ / $LastChangedDate$ / $LastChangedBy$
 */

namespace Core\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\AbstractResultSet;

abstract class AbstractModel
{
    private $modelFactory;
    private $service;
    private $connection;
    protected $db;

    /**
     * error content array
     *
     * @access private
     * @var $error_content array
     */
    private $error_content = array();

    /**
     * error flag
     *
     * @access private
     * @var $error_flag bool
     */
    private $error_flag = false;

    public function __construct($service)
    {
        $this->service    = $service;
        $this->db         = $service->get('db');
        $this->platform   = $this->db->getPlatform();
        $this->connection = $this->db->getDriver()->getConnection();
    }

    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    public function commit()
    {
        $this->connection->commit();
    }

    public function rollback()
    {
        $this->connection->rollback();
    }

    /**
     * build demanded model instance
     *
     * @param string $model_class Class name
     * @param string $namespace
     */
    protected function CoreModel($model_class, $namespace = 'Geocontexter')
    {
        if (!isset($this->modelFactory)) {
            $this->modelFactory = $this->service->get('CoreModel');
        }
        return $this->modelFactory->getModelInstance($model_class, $namespace = 'Geocontexter');
    }

    /**
     * run sql query
     *
     * @param string $sql
     * @param array  $params Parameters to insert into sql
     * @param string $result_type Result type: array or assocc
     * @return mixed Result array or if error instance of \Core\Library\Exception
     */
    protected function query($sql, $params = array(), $result_type = 'array')
    {
        $result = $this->db->query($sql, $params);

        $resultSet = new ResultSet;
        $resultSet->initialize($result);

        if ( $result_type === 'array') {
            return $resultSet->toArray();
        } else {
            return $resultSet;
        }
    }

    /**
     * db insert
     *
     * @param string $table Table name
     * @param string $schema Db schema name
     * @param aray $params Data to insert eg: array('field' => data,...)
     * @return mixed Result array or if error instance of \Core\Library\Exception
     */
    protected function insert($table, $schema, $params)
    {
        $table  = new \Zend\Db\Sql\TableIdentifier($table, $schema);
        $sql    = new \Zend\Db\Sql\Sql($this->db);

        $insert = $sql->insert($table);
        $insert->values($params);

        $selectString = $sql->getSqlStringForSqlObject($insert);

        return $this->db->query($selectString)->execute();
    }

    /**
     * db update
     *
     * @param string $table Table name
     * @param string $schema Db schema name
     * @param array $params Data to insert eg: array('field' => data,...)
     * @param array $where Condition eg: array('id' => 3)
     * @return mixed Result array or if error instance of \Core\Library\Exception
     */
    protected function update($table, $schema, $params, $where = array())
    {
        $table  = new \Zend\Db\Sql\TableIdentifier($table, $schema);
        $sql    = new \Zend\Db\Sql\Sql($this->db);

        $update = $sql->update($table);
        $update->set($params);
        $update->where($where);

        $selectString = $sql->getSqlStringForSqlObject($update);

        return $this->db->query($selectString)->execute();
    }

    /**
     * db delete
     *
     * @param string $table Table name
     * @param string $schema Db schema name
     * @param array $where Condition eg: array('id' => 3)
     * @return mixed Result array or if error instance of \Core\Library\Exception
     */
    protected function delete($table, $schema, $where = array())
    {
        $table  = new \Zend\Db\Sql\TableIdentifier($table, $schema);
        $sql    = new \Zend\Db\Sql\Delete($this->db);

        $delete = $sql->from($table);
        $delete->where($where);

        $deleteString = $delete->getSqlString($this->platform);

        return $this->db->query($deleteString)->execute();
    }

    /**
     * set error message
     *
     * @access private
     * @param $error string
     */
    protected function set_error( $error )
    {
        $this->error_flag = true;
        $this->error_content[] = $error;
    }

    /**
     * check if errors occured
     *
     * throw exception om errors
     */
    protected function check_errors()
    {
        if (true === $this->error_flag) {
            throw new \Exception($this->get_error( true ));
        }
    }

    /**
     * Get the error array
     *
     * @access private
     * @return array
     */
    protected function get_error( $as_string = false )
    {
        if($as_string === true)
        {
            return var_export($this->error_content, true);
        }
        return $this->error_content;
    }
}
