<?php
/**
 * Ztools Zikula Module
 *
 * @copyright Nikolay Petkov
 * @license GNU/GPL
 */
class Ztools_Api_Admin extends Zikula_AbstractApi
{
    /**
     * Get available admin panel links
     *
     * @return array array of admin links
     */
    public function getLinks()
    {
        $links = array();
    
        if (SecurityUtil::checkPermission('Ztools::', '::', ACCESS_READ)) {
            $links[] = array(
                'url' => ModUtil::url($this->name, 'admin', 'displaysysinfo'),
                'text' => $this->__('Server'),
                'class' => 'z-icon-es-info');
        }
        if (SecurityUtil::checkPermission('Ztools::', '::', ACCESS_OVERVIEW)) {
            $links[] = array(
                'url' => ModUtil::url($this->name, 'admin', 'displaybrowserinfo'),
                'text' => $this->__('Client'),
                'class' => 'z-icon-es-info');
        }
        if (SecurityUtil::checkPermission('Ztools::', '::', ACCESS_EDIT)) {
            $links[] = array(
                'url' => ModUtil::url($this->name, 'admin', 'backupdb'),
                'text' => $this->__('Backup'),
                'class' => 'z-icon-es-export');
        }
        if (SecurityUtil::checkPermission('Ztools::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => ModUtil::url($this->name, 'admin', 'scripts'),
                'text' => $this->__('Scripts'),
                'class' => 'z-icon-es-gears');
        }
        if (SecurityUtil::checkPermission('Ztools::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => ModUtil::url('Zfiler', 'admin', 'filer'),
                'text' => $this->__('Filer'),
                'class' => 'z-icon-es-folder');
        }
        if (SecurityUtil::checkPermission('Ztools::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => ModUtil::url($this->name, 'admin', 'modifyconfig'),
                'text' => $this->__('Settings'),
                'class' => 'z-icon-es-config');
        }
        if (SecurityUtil::checkPermission('Ztools::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => 'https://github.com/nmpetkov/Ztools/wiki',
                'text' => $this->__('Wiki'),
                'class' => 'z-icon-es-help');
        }

        return $links;
    }

    public function createFile($args)
    {
        $filecontent = isset($args['filecontent']) ? $args['filecontent'] : '';
        $filename = isset($args['filename']) ? $args['filename'] : '';

        if (empty($filename)) {
            LogUtil::registerError($this->__('Error! File name can not be empty.'));
            return false;
        } elseif (file_exists($filename)) {
            LogUtil::registerError($this->__f('Error! File %s already exist.', $filename));
            return false;
        }

        $handle = fopen($filename, 'w');
        if (!$handle) {
            LogUtil::registerError($this->__f('Error! Can not create file %s.', $filename));
            return false;
        }

        if ($filecontent) {
            if (!fwrite($handle, $filecontent)) {
                LogUtil::registerError($this->__f('Error! Can not write to file %s.', $filename));
                fclose($handle);
                return false;
            }
        }
        fclose($handle);

        return true;
    }

    public function executeScript($args)
    {
        $filename = isset($args['filename']) ? $args['filename'] : '';
        $filenameWithpath = DataUtil::formatForOS($this->getScriptsDir() . $filename);

        if (file_exists($filenameWithpath)) {
            ob_start();
            try {
                include $filenameWithpath;
            } catch (Exception $e) {
                LogUtil::registerError($this->__('Error!', $e->getMessage()));
            }
            $content = ob_get_clean();
            LogUtil::registerStatus('<strong>' . $this->__f('Result from: %s', $filename) . '</strong>');
            LogUtil::registerStatus($content);
        } else {
            LogUtil::registerError($this->__f('Error! File does not exist: %s', $filenameWithpath));
            return false;
        }

        return true;
    }

    public function getBackupsDir()
    {
        $dir = $this->getVar('ztools_backupsdir');

        return $dir . (substr($dir, -1) == DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR);
    }

    public function getScriptsDir()
    {
        $dir = $this->getVar('ztools_scriptsdir');

        return $dir . (substr($dir, -1) == DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR);
    }

    public function getScriptsDirLockStatus($args)
    {
        $type = isset($args['type']) ? $args['type'] : ''; // 'human' to return string, otherwise return logical

        $htaccessFile = DataUtil::formatForOS($this->getScriptsDir() . '.htaccess');

        if (file_exists($htaccessFile)) {
            $filecontent = file_get_contents($htaccessFile);
            if (strpos($filecontent, 'deny from all') !== false) {
                return true;
            }
        }

        return false;
    }

    public function setScriptsDirLockStatus($args)
    {
        $type = isset($args['type']) ? $args['type'] : ''; // 'lock' or 'unlock'

        $htaccessFile = DataUtil::formatForOS($this->getScriptsDir() . '.htaccess');

        if (file_exists($htaccessFile)) {
            unlink($htaccessFile);
        }

        if ($type == 'lock') {
            $handle = fopen($htaccessFile, 'w');
            if (!$handle) {
                LogUtil::registerError($this->__f('Error! Can not create file %s.', $htaccessFile));
                return false;
            }
            $filecontent = 'Order deny,allow' . PHP_EOL . 'deny from all' . PHP_EOL;
            if (!fwrite($handle, $filecontent)) {
                LogUtil::registerError($this->__f('Error! Can not write to file %s.', $htaccessFile));
                fclose($handle);
                return false;
            }
            fclose($handle);
        } elseif ($type == 'unlock') {
            if (file_exists($htaccessFile)) {
                LogUtil::registerError($this->__f('Error! File %s can not be deleted.', $htaccessFile));
                return false;
            }
        }

        return true;
    }

    /**
     * Get cleaned phpinfo() content
     */
    public function getphpinfoclean()
    {
        ob_start();
        phpinfo();
        // $matches [1]; # Style information
        // $matches [2]; # Body information
        preg_match('%<style type="text/css">(.*?)</style>.*?<body>(.*?)</body>%s', ob_get_clean(), $matches);

        ob_start();
        echo "<div class='phpinfodisplay'><style type='text/css'>\n",
            implode("\n",
                array_map(create_function('$i', 'return ".phpinfodisplay " . preg_replace( "/,/", ",.phpinfodisplay ", $i );'),
                    preg_split('/\n/', trim(preg_replace("/\nbody/", "\n", $matches[1]))))
                ),
            ".phpinfodisplay .center table { margin-left: 0; max-width: 600px; } .phpinfodisplay td { padding: 4px; }\n</style>\n",
            $matches[2],
            "\n</div>\n";

        return ob_get_clean();
    }

    /**
     * Download a file
     */
    public function downloadFile($args)
    {
        $filenameFull = isset($args['filename']) ? $args['filename'] : '';
        $useranges = isset($args['useranges']) ? $args['useranges'] : false;

        if (is_file($filenameFull)) {
            $file_size  = filesize($filenameFull);
            $handle = @fopen($filenameFull,"rb");
            if ($handle) {
                $path_parts = pathinfo($filenameFull);
                $file_ext   = $path_parts['extension'];
                $file_name  = $path_parts['basename'];

                // set the headers, prevent caching
                header_remove(); // Unsetting all previously set headers
                header('Pragma: public');
                header('Expires: -1');
                header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
                if ($useranges && isset($_REQUEST['stream'])) {
                    // stream
                    header('Content-Disposition: inline;');
                    header('Content-Transfer-Encoding: binary');
                } else {
                    // attachment
                    header('Content-Disposition: attachment; filename="' . $file_name . '"');
                }
                // set the mime type based on extension
                $content_types = array("zip" => "application/zip", "jpg" => "image/jpeg", "png" => "image/png", "png" => "image/png", "gif" => "image/gif");
                header("Content-Type: " . isset($content_types[$file_ext]) ? $content_types[$file_ext] : "application/octet-stream");

                // determine range or all file to send
                // check if http_range is sent by client
                if ($useranges && isset($_SERVER['HTTP_RANGE'])) {
                    list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                    if ($size_unit == 'bytes') {
                        // multiple ranges could be specified at same time, only serve first range, http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
                        list($range, $extra_ranges) = explode(',', $range_orig, 2);
                    } else {
                        $range = '';
                        header('HTTP/1.1 416 Requested Range Not Satisfiable');
                        exit;
                    }
                } else {
                    $range = '';
                }
                list($seek_start, $seek_end) = explode('-', $range, 2);
                // set start and end based on range (if set), else set defaults, check for invalid ranges.
                if (empty($seek_end)) {
                    $seek_end = $file_size - 1;
                } else {
                    $seek_end = min(abs(intval($seek_end)), $file_size - 1);
                }
                if (empty($seek_start) || $seek_end < abs(intval($seek_start))) {
                    $seek_start = 0;
                } else {
                    $seek_start = max(abs(intval($seek_start)),0);
                }
                // headers based on length
                if ($seek_start > 0 || $seek_end < ($file_size - 1)) {
                    // Only send partial content header if downloading a piece of the file (IE workaround)
                    header('HTTP/1.1 206 Partial Content');
                    header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$file_size);
                    header('Content-Length: '.($seek_end - $seek_start + 1));
                } else {
                    header("Content-Length: " . $file_size);
                }
                header('Accept-Ranges: bytes');

                // send file content
                @set_time_limit(0);
                fseek($handle, $seek_start);
                while(!feof($handle)) {
                    print(@fread($handle, 1024*8));
                    ob_flush();
                    flush();
                    if (connection_status()!=0) {
                        @fclose($handle);
                        exit;
                    }
                }
                @fclose($handle);
                exit;
            } else {
                LogUtil::registerStatus($this->__f('Can not read from file %s.', $filename));
                return false;
            }
        } else {
            LogUtil::registerStatus($this->__f('File does not exist: %s', $filename));
            return false;
        }

        return true;
    }

    public function createBackup($args)
    {
        $export_method = isset($args['export_method']) ? $args['export_method'] : 1;
        $export_compress = isset($args['export_compress']) ? $args['export_compress'] : 0;
        $filename = isset($args['filename']) ? $args['filename'] : '';
        $tables = isset($args['tables']) ? $args['tables'] : null;

        if (empty($filename)) {
            LogUtil::registerError($this->__('Error! File name can not be empty.'));
            return false;
        } elseif (file_exists($filename)) {
            LogUtil::registerError($this->__f('Error! File %s already exist.', $filename));
            return false;
        }

        $aResult = ModUtil::apiFunc($this->name, 'backup', 'createBackup', 
                array('export_method' => $export_method, 'export_compress' => $export_compress, 'filename' => $filename, 'tables' => $tables));
        if (is_array($aResult) && isset($aResult['success']) && $aResult['success']) {
            LogUtil::registerStatus($this->__f('Done! Database backup is created in file %s.', $filename));
        } else {
            LogUtil::registerStatus($this->__f('Error creating database backup in file %s.', $filename));
        }

        if (file_exists($filename)) {
            LogUtil::registerStatus($this->__f('Size of created file is %s bytes.', filesize($filename)));
        }

        return true;
    }
}