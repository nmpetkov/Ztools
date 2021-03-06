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

    private $export_compress = 0;
    
    private $hex_blob = 1; // dump binary columns in hex

    private $typeoutput = 1; // 1 file, 2 return content

    private $dbinfo = null;

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
     *      ['export_compress'] Export compression
     *          0 - None
     *          1 - Gzip
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
        $this->export_compress = isset($args['export_compress']) ? $args['export_compress'] : 0;
        $this->ztools_exportcompress = isset($args['ztools_exportcompress']) ? $args['ztools_exportcompress'] : 0;
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


        // [host], [user],[password],[dbname],[dbdriver] => mysql,  [dbtabletype] => myisam/..., [charset] => utf8, [collate] => utf8_general_ci
        global $ZConfig;
        $this->dbinfo = $ZConfig['DBInfo']['databases']['default'];

        // Use speciies export method
        if ($this->export_method == 1) {
            if (!$this->exportByMysqldump_php()) {
                return false;
            }
        } elseif ($this->export_method == 2) {
            if (!$this->exportByMysqldump_shell()) {
                return false;
            }
        } elseif ($this->export_method == 3) {
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
     * Export by mysqldump shell command
     */
    public function exportByMysqldump_shell()
    {       
        $command = $this->getVar('ztools_mysqldumpexe');
        if (empty($mysqldump_path)) {
            $command = 'mysqldump';
        }

        $parameters = ' --user=' . $this->dbinfo['user'] .
                      ' --password=' . $this->dbinfo['password'] .
                      ' --host=' . $this->dbinfo['host'];
        $parameters .= ' --skip-extended-insert=1'; // separate INSERT statement for each row
        $parameters .= ' --hex-blob=' . $this->hex_blob; // dump binary columns in hex
        $parameters .= ' --disable-keys=1'; 
        $parameters .= ' ' . $this->dbinfo['dbname'];
        if (!$this->tables_all && is_array($this->aTables)) {
            foreach ($this->aTables as $table) {
                $parameters .= ' ' . $table;
            }
        }
        if ($this->export_compress == 1) {
            $parameters .= ' | gzip';
        }
        $parameters .= ' > ' . System::serverGetVar('DOCUMENT_ROOT') . DIRECTORY_SEPARATOR . $this->filename;

        $return_var = 0;
        exec($command . $parameters, $return_var);
        if ($return_var) {
            LogUtil::registerStatus($this->__('Status:') . ' ' . print_r($return_var, true));
        }
        
        return true;
    }

     /**
     * Export by Mysqldump-php class
     */
    public function exportByMysqldump_php()
    {
        include_once("modules/Ztools/lib/vendor/Mysqldump/Mysqldump.php");

        $dump = new IMysqldump\Mysqldump($this->dbinfo['dbname'], $this->dbinfo['user'], $this->dbinfo['password'], $this->dbinfo['host'], $this->dbinfo['dbdriver'],
          array('include-tables' => $this->tables_all ? array() : $this->aTables,
                'exclude-tables' => array(),
                'default-character-set' => IMysqldump\Mysqldump::UTF8,
                'compress' => $this->export_compress == 1 ? IMysqldump\Mysqldump::GZIP : IMysqldump\Mysqldump::NONE,
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
                'hex-blob' => $this->hex_blob ? true : false,
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
            if ($this->export_compress == 1) {
                $this->fhandle = gzopen($this->filename, 'wb');
            } else {
                $this->fhandle = fopen($this->filename, 'wb');
            }
            if (!$this->fhandle) {
                LogUtil::registerError($this->__f('Error! Can not create file %s.', $this->filename));
                return false;
            }
        }

        // start statements
        $sql = '';
        $sql .= "-- Ztools Mysql Backup" . PHP_EOL;
        $sql .= "--" . PHP_EOL;
        $sql .= '-- Created: ' . DateUtil::formatDatetime(time(), '%Y-%m-%d %H:%M:%S') . PHP_EOL;
        $sql .= "--" . PHP_EOL;
        $sql .= "-- Host: " . $this->dbinfo['host'] . "\tDatabase: " . $this->dbinfo['dbname'] . PHP_EOL;
        $sql .= "--" . PHP_EOL;
        $sql .= 'SET AUTOCOMMIT = 0;' . "\n";
        $sql .= 'SET FOREIGN_KEY_CHECKS=0;' . "\n\n";
        $sql .= 'SET SESSION SQL_MODE = NO_AUTO_VALUE_ON_ZERO;' . "\n\n";
        $this->writeToOutput($sql);

        // export table by table
        foreach ($this->aTables as $table) {
            $this->exportTable($table);
        }

        // end statements
        $sql = '';
        $sql .= 'SET FOREIGN_KEY_CHECKS = 1;' . "\n"; 
        $sql .= 'COMMIT;' . "\n";
        $sql .= 'SET AUTOCOMMIT = 1;' . "\n"; 
        $this->writeToOutput($sql);

        // close output file
        if ($this->typeoutput == 1) {
            if ($this->export_compress == 1) {
                gzclose($this->fhandle);
            } else {
                fclose($this->fhandle);
            }
        }

        return true;
    }

     /**
     * Backup given table
     */
    public function exportTable($table)
    {
        // number of fields in table
        $result = $this->connection->fetchArray('SELECT count(*) FROM '.$table);
        $num_fields = $result[0];

        // comments
        $sql = "--\n" ;
        $sql .= '-- Structure for table `' . $table . '`' . "\n" ;
        $sql .= "--\n" ;

        // Drop the table
        $sql .= 'DROP TABLE IF EXISTS `' . $table . '`;' . "\n";

        // Put CREATE TABLE statement before data
        $result = $this->connection->fetchArray('SHOW CREATE TABLE '.$table);
        $create_table = $result[1];
        $sql .= $create_table.";" . "\n\n" ;

        // Get column types
        $columns = $this->connection->fetchAssoc('SHOW COLUMNS FROM '.$table);
        $columnTypes = array();
        foreach($columns as $key => $col) {
            // decode type column
            $types = array();
            $colParts = explode(" ", $col['Type']);
            if($fparen = strpos($colParts[0], "(")) {
                $types['type'] = substr($colParts[0], 0, $fparen);
                $types['length']  = str_replace(")", "", substr($colParts[0], $fparen+1));
                $types['attributes'] = isset($colParts[1]) ? $colParts[1] : NULL;
            } else {
                $types['type'] = $colParts[0];
            }
            $types['is_numeric'] = in_array($types['type'], array('bit', 'tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'real', 'double', 'float', 'decimal', 'numeric'));
            $types['is_blob'] = in_array($types['type'], array('tinyblob', 'blob', 'mediumblob', 'longblob', 'binary', 'varbinary', 'bit'));
            $columnTypes[$col['Field']] = array('is_numeric'=> $types['is_numeric'], 'is_blob' => $types['is_blob'], 'type' => $types['type']);
        }

        // select all data from the table
        $items = $this->connection->fetchAssoc('SELECT * FROM '.$table);
        foreach ($items as $item) {
            $sql .= 'INSERT INTO `'. $table .'`  VALUES (';
            $fieldCount = count($item);
            $i = 0;
            foreach ($item as $field => $fieldContent) {
                $i++;
                // Escape field content
                if (is_null($fieldContent)) {
                    $sql .= "NULL";
                } elseif ($this->hex_blob && $columnTypes[$field]['is_blob']) {
                    if ($columnTypes[$field]['type'] == 'bit' || !empty($fieldContent)) {
                        $sql .= "0x${fieldContent}";
                    } else {
                        $sql .= "''";
                    }
                } elseif ($columnTypes[$field]['is_numeric']) {
                    $sql .= $fieldContent;
                } else {
                    $sql .= $this->connection->quote($fieldContent);
                    //$sql .= "'" . ereg_replace("\n", "\\n", addslashes($fieldContent)) . "'";
                }
                $sql .= $i < $fieldCount ? ', ' : '';
            }
            $sql .= ");" . PHP_EOL ;
        }
        $sql .= PHP_EOL . PHP_EOL ;
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
            if ($this->export_compress == 1) {
                $nbytes = gzwrite($this->fhandle, $content);
            } else {
                $nbytes = fwrite($this->fhandle, $content);
            }
            if ($this->debugmode) {
                if ($this->export_compress == 1) {
                    gzwrite($this->fhandle, '-- TIME: ' .  (time() - $this->starttime) . ' s, MEMORY: ' . memory_get_peak_usage(). "\n");
                } else {
                    fwrite($this->fhandle, '-- TIME: ' .  (time() - $this->starttime) . ' s, MEMORY: ' . memory_get_peak_usage(). "\n");
                }
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