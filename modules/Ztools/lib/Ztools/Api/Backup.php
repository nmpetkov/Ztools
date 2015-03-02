<?php
/**
 * Ztools Zikula Module
 *
 * @copyright Nikolay Petkov
 * @license GNU/GPL
 */
class Ztools_Api_Backup extends Zikula_AbstractApi
{
     private $debugmode = true;

     private $starttime = 0;
     
     private $max_execution_time = 0;
     
     private $fhandle = 0;
     
     private $doctr_conn_name = 'default';
     
     private $connection = null;

     private $aTables = array();

     private $returnContent = '';

     /**
     * Create database backup
     * @parameters array
     *      ['fhandle'] file handle where to write created backup content
     *          valid (>0) - content will write in previously opened file
     *          empty - content will return in array element ['content']
     *      ['doctr_conn_name'] name of valid doctrine connection to database, defaults to 'default'
     *      ['tables'] array with tables to backup, defaults to all if set to null
     * @return array
     *      ['success'] - true/false
     *      ['content'] (only if ['fhandle'] is empty) - content of created backup, returned only if no valid handle of open file passed
     *      ['exectime'] execution time, sec
     */
    public function createBackup($args)
    {
        $aResult = array();
        $aResult['success'] = false;
        $this->starttime = time();

        $this->fhandle = isset($args['fhandle']) ? $args['fhandle'] : '';
        if (isset($args['doctr_conn_name']) && $args['doctr_conn_name']) {
            $this->doctr_conn_name =  $args['doctr_conn_name'];
            $this->prepareConnection();
            if (!$this->connection) {
                return $aResult;
            }
        }
        if (!$this->connection) {
            $this->prepareConnection();
            if (!$this->connection) {
                return $aResult;
            }
        }

        // Attempt to avoid some specific errors
        ini_set('display_errors', '1');    // how all errors (if by default is set to 0 - this is admin user)
        //ini_set('max_execution_time', $this->max_execution_time);  // 0 for no time limit
        LogUtil::registerStatus($this->__f('Create backup start time: %s.', DateUtil::formatDatetime(time(), '%Y-%m-%d %H:%M:%S')));

        // prepare array with tables to backup
        if (isset($args['tables']) && is_array($args['tables'])) {
            $this->aTables = $args['tables'];
        } else {
            $this->aTables = $this->getTables();
        }

        // start statements
        $sql = '';
        $sql .= "--\n";
        $sql .= "-- Mysql Backup\n";
        $sql .= "--\n";
        $sql .= '-- Created: ' . DateUtil::formatDatetime(time(), '%Y-%m-%d %H:%M:%S') . "\n";
        $sql .= "--\n";
        $result = $this->connection->fetchArray('SELECT DATABASE()');
        $sql .= "-- Database: " . $result[0] . "\n";
        $sql .= "--\n";
        $sql .= 'SET AUTOCOMMIT = 0;' . "\n";
        $sql .= 'SET FOREIGN_KEY_CHECKS=0;' . "\n\n";
        $sql .= 'SET SESSION SQL_MODE = NO_AUTO_VALUE_ON_ZERO;' . "\n\n";
        $this->writeToOutput($sql);

        // export table by table
        foreach ($this->aTables as $table) {
            $this->backupTable($table);
        }

        // end statements
        $sql = '';
        $sql .= 'SET FOREIGN_KEY_CHECKS = 1;' . "\n"; 
        $sql .= 'COMMIT;' . "\n";
        $sql .= 'SET AUTOCOMMIT = 1;' . "\n"; 
        $this->writeToOutput($sql);

        $exectime = time() - $this->starttime;
        LogUtil::registerStatus($this->__f('Create backup end time: %s.', DateUtil::formatDatetime(time(), '%Y-%m-%d %H:%M:%S')) . ' ' . $this->__f('Execution time: %s seconds.', $exectime));

        // prepare result and return
        $aResult['exectime'] = $exectime;
        if (empty($fhandle)) {
            $aResult['content'] = $this->returnContent;
        }
        return $aResult;
    }

     /**
     * Backup given table
     */
    public function backupTable($table)
    {
        // number of fields in table
        $result = $this->connection->fetchArray('SELECT count(*) FROM '.$table);
        $num_fields = $result[0];

        // comments
        $sql = "--\n" ;
        $sql .= '-- Tabel structure for table `' . $table . '`' . "\n" ;
        $sql .= "--\n" ;

        // Drop the table
        $sql .= 'DROP TABLE IF EXISTS `' . $table . '`;' . "\n";

        // Put CREATE TABLE statement before data
        $result = $this->connection->fetchArray('SHOW CREATE TABLE '.$table);
        $create_table = $result[1];
        $sql .= $create_table.";" . "\n\n" ;

        // select all data from the table
        $items = $this->connection->fetchAssoc('SELECT * FROM '.$table);
        foreach ($items as $item) {
            $sql .= 'INSERT INTO `'. $table .'`  VALUES (';
            $fieldCount = count($item);
            $i = 0;
            foreach ($item as $field => $fieldContent) {
                $i++;
                $sql .= '"'. ereg_replace("\n", "\\n", addslashes($fieldContent)) .'"' ;
                $sql .= $i < $fieldCount ? ', ' : '';
            }
            $sql .= ");" ."\n" ;
        }
        $sql .= "\n\n" ; 
        $this->writeToOutput($sql);

        unset($sql);
        $sql = null; // free used memory

        return true;
    }

     /**
     * Get list of tables in database
     *
     * @return array
     */
    public function getTables($args = array())
    {
        if (isset($args['doctr_conn_name']) && $args['doctr_conn_name']) {
            $this->doctr_conn_name =  $args['doctr_conn_name'];
            $this->prepareConnection();
            if (!$this->connection) {
                return false;
            }
        }
        if (!$this->connection) {
            $this->prepareConnection();
            if (!$this->connection) {
                return false;
            }
        }

        $aTables = array();
        $stmt = $this->connection->prepare('SHOW TABLES');
        try {
            $stmt->execute();
        } catch (Exception $e) {
            LogUtil::registerError($this->__('Error!') . ' ' . $e->getMessage());
        }
        $items = $stmt->fetchAll(Doctrine_Core::FETCH_NUM);

        foreach ($items as $key => $item) {
            $aTables[] =$item[0];
        }

        return $aTables;
    }

    private function writeToOutput($content = '')
    {
        if ($this->fhandle) {
                $nbytes = fwrite($this->fhandle, $content);
            if ($this->debugmode) {
                fwrite($this->fhandle, '-- TIME: ' .  (time() - $this->starttime) . ' s, MEMORY: ' . memory_get_peak_usage(). "\n");
            }
            return $nbytes;
        } else {
            $this->returnContent .= $content;
        }
        unset($content);
        $content = null; // free used memory
        flush(); // flushes output buffers to free some memory!

        return true;
    }

    private function prepareConnection()
    {
        // prepare connection to database
        $this->connection = Doctrine_Manager::getInstance()->getConnection($this->doctr_conn_name);
        if (!$this->connection) {
            LogUtil::registerError($this->__('Error! Can not obtain connection to database.'));
            return false;
        }

        return true;
    }
}