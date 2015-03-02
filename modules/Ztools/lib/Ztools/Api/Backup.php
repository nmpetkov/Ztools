<?php
/**
 * Ztools Zikula Module
 *
 * @copyright Nikolay Petkov
 * @license GNU/GPL
 */
 
use Ifsnop\Mysqldump as IMysqldump;

class Ztools_Api_Backup extends Zikula_AbstractApi
{
     private $debugmode = true;

     private $starttime = 0;
     
     private $max_execution_time = 0;
     
     private $export_method = 1;
     
     private $typeoutput = 1; // 1 file, 2 return content
     
     private $filename = '';
     
     private $fhandle = 0;
     
     private $doctr_conn_name = 'default';
     
     private $connection = null;

     private $tables_all = true;

     private $aTables = array();

     private $returnContent = '';

     /**
     * Create database backup
     * @parameters array
     *      ['export_method'] Export (dump) methid
     *          1 - Mysqldump-php class
     *          2 - Ztools internal method
     *      ['typeoutput'] Output destination
     *          1 - content will write in file
     *          2 - content will return in array element ['content']
     *      ['filename'] (only if ['typeoutput'] = 1) - file name with path - where to write created backup content
     *      ['doctr_conn_name'] name of valid doctrine connection to database, defaults to 'default'
     *      ['tables'] array with tables to backup, defaults to all if set to null
     * @return array
     *      ['success'] - true/false
     *      ['content'] (only if ['typeoutput'] = 2) - content of created backup, returned only if no valid handle of open file passed
     *      ['exectime'] execution time, sec
     */
    public function createBackup($args)
    {
        $aResult = array();
        $aResult['success'] = false;
        $this->starttime = time();

        $this->export_method = isset($args['export_method']) ? $args['export_method'] : 1;
        $this->typeoutput = isset($args['typeoutput']) ? $args['typeoutput'] : 1;
        
        $this->filename = isset($args['filename']) ? $args['filename'] : '';
        if (empty($this->filename)) {
            LogUtil::registerError($this->__('Error! File name can not be empty.'));
            return false;
        }

        if (isset($args['doctr_conn_name']) && $args['doctr_conn_name']) {
            $this->doctr_conn_name =  $args['doctr_conn_name'];
            $this->prepareConnection();
            if (!$this->connection) {
                return $aResult;
            }
        } else if (!$this->connection) {
            $this->prepareConnection();
            if (!$this->connection) {
                return $aResult;
            }
        } else {
            return false;
        }

        // Attempt to avoid some specific errors
        error_reporting(E_ALL);
        ini_set('display_errors', '1');    // how all errors (if by default is set to 0 - this is admin user)
        //ini_set('max_execution_time', $this->max_execution_time);  // 0 for no time limit
        LogUtil::registerStatus($this->__f('Create backup start time: %s.', DateUtil::formatDatetime(time(), '%Y-%m-%d %H:%M:%S')));

        // prepare array with tables to backup
        if (isset($args['tables']) && is_array($args['tables'])) {
            $this->aTables = $args['tables'];
            $this->tables_all = false;
        } else {
            $this->aTables = $this->getTables();
            $this->tables_all = true;
        }

        // Use speciies export method
        if ($this->export_method == 1) {
            if (!$this->exportByMysqldumpphp()) {
                return false;
            }
        } elseif ($this->export_method == 2) {
            if (!$this->exportByZtools()) {
                return false;
            }
        }

        // Prepare result and return
        $exectime = time() - $this->starttime;
        LogUtil::registerStatus($this->__f('Create backup end time: %s.', DateUtil::formatDatetime(time(), '%Y-%m-%d %H:%M:%S')) . ' ' . $this->__f('Execution time: %s seconds.', $exectime));
        $aResult['success'] = true;
        $aResult['exectime'] = $exectime;
        if ($this->typeoutput == 2) {
            $aResult['content'] = $this->returnContent;
        }

        return $aResult;
    }

     /**
     * Export by Mysqldump-php class
     */
    public function exportByMysqldumpphp()
    {
        global $ZConfig;
        // [host], [user],[password],[dbname],[dbdriver] => mysql,  [dbtabletype] => myisam/..., [charset] => utf8, [collate] => utf8_general_ci
        $dbinfo = $ZConfig['DBInfo']['databases']['default'];

        include_once("modules/Ztools/lib/vendor/Mysqldump/Mysqldump.php");

        $dump = new IMysqldump\Mysqldump($dbinfo['dbname'], $dbinfo['user'], $dbinfo['password'], $dbinfo['host'], $dbinfo['dbdriver'],
          array('include-tables' => $this->tables_all ? array() : $this->aTables,
                'exclude-tables' => array(),
                'default-character-set' => IMysqldump\Mysqldump::UTF8,
                'compress' => IMysqldump\Mysqldump::NONE,
                'no-data' => false,
                'add-drop-table' => true,
                'single-transaction' => true,
                'lock-tables' => true,
                'add-locks' => true,
                'extended-insert' => false,
                'disable-keys' => true,
                'skip-triggers' => false,
                'add-drop-trigger' => true,
                'databases' => false,
                'add-drop-database' => false,
                'skip-tz-utz' => false,
                'no-autocommit' => true,
                'hex-blob' => true,
                'no-create-info' => false,
                'where' => '')
            );
        try {
            $dump->start($this->filename);
        } catch (Exception $e) {
            LogUtil::registerError($this->__('Error!') . ' ' . $e->getMessage());
            return false;
        }

        return true;
    }

     /**
     * Export by Ztools method
     */
    public function exportByZtools()
    {
        // Open output file
        if ($this->typeoutput == 1) {
            $this->fhandle = fopen($this->filename, 'wb');
            if (!$this->fhandle) {
                LogUtil::registerError($this->__f('Error! Can not create file %s.', $this->filename));
                return false;
            }
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

        // close output file
        if ($this->typeoutput == 1) {
            fclose($this->fhandle);
        }

        return true;
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
        if ($this->typeoutput == 1) {
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