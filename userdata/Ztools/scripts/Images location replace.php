<?php
$idcodereplace = 'xxx';

$tables = array();
$columns = array();

// News
$tables[] = 'news';
$idcolumn[] = 'sid';
$columns[] = array('hometext', 'bodytext');
$columns_are_urls[] = false;
$destdir[] = 'articles/news/';
$displayurl[] = DataUtil::formatForDisplay(ModUtil::url('News', 'user', 'display', array('sid' => $idcodereplace)));
$file_dest_makenamefromid[] = false; // make unique name from column id, instead of using original name
// Pages
$tables[] = 'pages';
$idcolumn[] = 'pageid';
$columns[] = array('content');
$columns_are_urls[] = false;
$destdir[] = 'articles/pages/';
$displayurl[] = DataUtil::formatForDisplay(ModUtil::url('Pages', 'user', 'display', array('pageid' => $idcodereplace)));
$file_dest_makenamefromid[] = false; // make unique name from column id, instead of using original name
//  guide_places
$tables[] = 'guide_places';
$idcolumn[] = 'idplace';
$columns[] = array('iconurl', 'iconurl_b', 'iconurl_s', 'iconurl_i', 'iconurl_d', 'iconurl_a', 'iconurl_t');
$columns_are_urls[] = true;
$destdir[] = 'cdb/images/icons/';
$displayurl[] = DataUtil::formatForDisplay('cdb.php?f=placeinfo&idPlace='.$idcodereplace);
$file_dest_makenamefromid[] = true; // make unique name from column id, instead of using original name

// Search string to filter_has_var
//$findtext = 'albums/'; // Menalto Gallery 1
$findtext = 'main.php?'; // Menalto Gallery 2

// Settings
$files_checkonly = false; // otherwise copy
$files_checkfor_destination = true;
$files_replace = true; // replace in column content with destination file
$domain_if_not = 'http://www.climbingguidebg.com/'; // if domain in url is missing - force this domain in source url (not for destination)

$connection = Doctrine_Manager::getInstance()->getCurrentConnection();

foreach ($tables as $key => $table) {
    if (!$files_checkonly && !file_exists($destdir[$key])) {
        mkdir($destdir[$key]);
    }

    foreach ($columns[$key] as $column) {
        echo '<strong>' . $table . '.' . $column . '</strong><br />';
        $query = "SELECT `" . $idcolumn[$key] . "`,`" . $column . "` FROM `" . $table . "` WHERE `" . $column . "` LIKE '%" . $findtext . "%'";
        $stmt = $connection->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(Doctrine_Core::FETCH_ASSOC);
        foreach ($result as $row) {
            $src_urls = array();
            if ($columns_are_urls[$key]) {
                $src_urls[] = $row[$column];
            } else {
                $doc = new DOMDocument();
                @$doc->loadHTML($row[$column]);
                $tags = $doc->getElementsByTagName('img');
                foreach ($tags as $tag) {
                    $src_urls[] = $tag->getAttribute('src');
                }
            }
            $key1 = 0;
            foreach ($src_urls as $src) {
                $pos = strpos($src, $findtext);
                if ($pos !== false) {
                    $key1 ++;
                    if ($file_dest_makenamefromid[$key]) {
                        $file_stem = $idcolumn[$key] . $row[$idcolumn[$key]] . '_' . $key1;
                    } else {
                        $file_stem = basename($src);
                        $file_stem = _Clean_Filestem($file_stem);
                    }
                    $dest = $destdir[$key] . (substr($destdir[$key], -1) == '/' ? '' : '/') . $file_stem;
                    $src_full = $src;
                    if (!empty($domain_if_not)) {
                        if ((substr($src, 0, 1) != '/') && (substr($src, 0, 7) != 'http://') && (substr($src, 0, 8) != 'https://')) {
                            $src_full = $domain_if_not . (substr($domain_if_not, -1) == '/' ? '' : '/') . $src;
                        }
                    }
                    // check if image type is in dest extention
                    $ext = pathinfo($dest, PATHINFO_EXTENSION);
                    if (empty($ext)) {
                        $imageinfo = getimagesize($src_full);
                        if ($imageinfo) {
                            $ext = image_type_to_extension($imageinfo[2]);
                            if (!empty($ext)) {
                                if ($ext == '.jpeg') {
                                    $ext = '.jpg';
                                }
                                $dest .= $ext;
                            }
                        }
                    }
                    //
                    if ($files_checkonly) {
                        // check files for existence only
                        if (@file_get_contents($src_full, 0, NULL, 0, 1)) {
                            echo 'exist: ';
                        } else {
                            echo 'missing: ';
                        }
                    } else {
                        // copy files
                        if ($files_checkfor_destination && file_exists($dest)) {
                            // destination file exist
                            echo 'already copied: ';
                        } else {
                            if (copy($src_full, $dest)) {
                                echo 'copied: ';
                            } else {
                                echo 'failed: ';
                            }
                        }
                    }
                    if ($files_replace && file_exists($dest)) {
                        // plane replace
                        $query = "UPDATE `" . $table . "` SET `" . $column . "`= REPLACE(`".$column."`, '".$src."', '".$dest."') WHERE `" . $idcolumn[$key] . "`=" . $row[$idcolumn[$key]];
                        $stmt = $connection->prepare($query);
                        $stmt->execute();
                        // also treat with htmlentities
                        $query = "UPDATE `" . $table . "` SET `" . $column . "`= REPLACE(`".$column."`, '".htmlentities($src)."', '".$dest."') WHERE `" . $idcolumn[$key] . "`=" . $row[$idcolumn[$key]];
                        $stmt = $connection->prepare($query);
                        $stmt->execute();
                        echo 'replaced: ';
                    }
                    echo $src . ', ' . '<a href="'.str_replace($idcodereplace, $row[$idcolumn[$key]], $displayurl[$key]).'">' .$idcolumn[$key].'='.$row[$idcolumn[$key]]. '</a>, dest: '.$dest.'<br />';
                }
            }
        }
    }
}

function _Clean_Filestem($file_stem) {
    // g2cb/main.php?g2_view=core.DownloadItem&g2_itemId=1384966&g2_serialNumber=2
    $file_stem = strtr($file_stem, array('main.php?g2_view=core.DownloadItem&g2_itemId=' => 'item', '&g2_serialNumber=' => '_'));
    
    return $file_stem;
}