<?php
/**
 * Teaser_XH for CMSimple_XH
 * Backend
 * @version 1.2.1, April 2017
 * @author svasti@svasti.de
 */


// Security check
if ((!function_exists('sv')))die('Access denied');

// if php 4 is used, this function has to be supplied
if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $data) {
        $f = @fopen($filename, 'w');
        if (!$f) {
            return false;
        } else {
        if (is_array($data)) {$data = implode('', $data);}
            $bytes = fwrite($f, $data);
            fclose($f);
            return $bytes;
        }
    }
}

if (function_exists('XH_wantsPluginAdministration') && XH_wantsPluginAdministration('teaser')
    || isset($teaser) && $teaser === 'true'
) {

    $o .= "\n\n<!-- Teaser Plugin -->\n\n";
    $plugin = basename(dirname(__FILE__),"/");
    $admin  = isset($_POST['admin']) ? $_POST['admin'] : $admin = isset($_GET['admin']) ? $_GET['admin'] : '';
    $action = isset($_POST['action']) ? $_POST['action'] : $action = isset($_GET['action']) ? $_GET['action'] : '';

    $o .= print_plugin_admin('on');


    // activation routine for updating
    // ========================================================================
    if(isset($_POST['activate_teaser'])) {

        $teaser_editor_height           = isset($plugin_cf['teaser']['editor_height'])
                                        ? $plugin_cf['teaser']['editor_height']
                                        : '300';
        $teaser_path                    = isset($plugin_cf['teaser']['path'])
                                        ? $plugin_cf['teaser']['path']
                                        : 'userfiles/plugins/teaser/';
        $teaserpath_in_content_folder   = isset($plugin_cf['teaser']['path_starts_in_content_folder'])
                                        ? $plugin_cf['teaser']['path_starts_in_content_folder']
                                        : '';
        $teaser_background_images       = isset($plugin_cf['teaser']['background_images'])
                                        ? $plugin_cf['teaser']['background_images']
                                        : 'plugins/teaser/images/';
        $teaser_external_links          = isset($plugin_cf['teaser']['external_links_open_new_windows'])
                                        ? $plugin_cf['teaser']['external_links_open_new_windows']
                                        : '1';
        $teaser_template_change         = isset($plugin_cf['teaser']['template_change_for_specific_teaserfiles'])
                                        ? $plugin_cf['teaser']['template_change_for_specific_teaserfiles']
                                        : '';

        $text = "<?php\n\n"
              . '$plugin_cf[\'teaser\'][\'version\']="1.2.1";' . "\n"
              . '$plugin_cf[\'teaser\'][\'editor_height\']="' .                             $teaser_editor_height         . '";' . "\n"
              . '$plugin_cf[\'teaser\'][\'path\']="' .                                      $teaser_path                  . '";' . "\n"
              . '$plugin_cf[\'teaser\'][\'path_starts_in_content_folder\']="' .             $teaserpath_in_content_folder . '";' . "\n"
              . '$plugin_cf[\'teaser\'][\'background_images\']="' .                         $teaser_background_images     . '";' . "\n"
              . '$plugin_cf[\'teaser\'][\'external_links_open_new_windows\']="' .           $teaser_external_links        . '";' . "\n"
              . '$plugin_cf[\'teaser\'][\'template_change_for_specific_teaserfiles\']="' .  $teaser_template_change       . '";' . "\n"
              . '$plugin_cf[\'teaser\'][\'clear_after_teaser\']="'.                         $teaser_clear_after           . '";' . "\n\n"
              . '?>';
        file_put_contents($pth['folder']['plugins'] . $plugin . '/config/config.php',$text);
        include $pth['folder']['plugins'] . $plugin . '/config/config.php';
    }




    if( !$admin || $admin == 'plugin_main') {

        // activation form for updating
        // ===============================
        if(!isset($plugin_cf['teaser']['version']) || version_compare($plugin_cf['teaser']['version'], '1.2.1', '!=')) {

            $o .=  '<h1 style="border:2px red solid;background:yellow;text-align:center;">'
                .  $plugin_tx['teaser']['activation_alert']
                .  tag('br')
                .  '<form action="" method="POST">'
                .  tag('input type="hidden" name="activate_teaser" value="1"')
                .  tag('input type="submit" style="font-weight:bold;padding:0 1em;letter-spacing:.05em;" value="'
                .  $plugin_tx['teaser']['activation_click_here']
                .  '"')
                .  '<form> '
                .  $plugin_tx['teaser']['activation_call']
                .  '</h1>';

        } else {


        $datapath = $plugin_cf['teaser']['path']
                  ? $datapath = $plugin_cf['teaser']['path_starts_in_content_folder']
                      ? $pth['folder']['content'] .$plugin_cf['teaser']['path']
                      : $pth['folder']['base'] .$plugin_cf['teaser']['path']
                  : $pth['folder']['plugins'] . $plugin . '/data/';


        // create language file if absent or empty
        $languagefile = file_get_contents($pth['folder']['plugins'].$plugin .'/languages/' . $sl . '.php');
        if(!$languagefile) {
            $handle = fopen($pth['folder']['plugins'].$plugin .'/languages/' . $sl . '.php', "w");
            fclose($handle);
            $languagefile = '<?php'."\n\n" . '?>';
        }
        // write missing ['temp...'] entries into language file
        if(strpos($languagefile,'temp_teaser_nr')===false || strpos($languagefile,'temp_teaser_file')===false) {

            if(strpos($languagefile,'temp_teaser_nr')===false) {
                $languagefile = preg_replace('!\s*\?>\s*$!',"\n".'$plugin_tx[\'teaser\'][\'temp_teaser_nr\']="1";'."\n\n?>" ,$languagefile);
            }
            if(strpos($languagefile,'temp_teaser_file')===false) {
                $languagefile = preg_replace('!\s*\?>\s*$!',"\n".'$plugin_tx[\'teaser\'][\'temp_teaser_file\']="";'."\n\n?>" ,$languagefile);
            }
            if(file_put_contents($pth['folder']['plugins'].$plugin .'/languages/' . $sl . '.php',$languagefile)===false) {
                e('cntsave', 'language', $pth['folder']['plugins'].$plugin .'/languages/' . $sl . '.php');
            }
            include($pth['folder']['plugins'].$plugin .'/languages/' . $sl . '.php');
        }



//Saving---------------------------------------------------------------------------------------------


        // save which teaser file should be displayed
        //============================================
        if (isset($_POST['newteaserfile']))
        {
            $newteaserfile = $_POST['newteaserfile'];
            $newname       = isset($_POST['newname'])   ? $_POST['newname']       : '';
            $deletefile    = isset($_POST['deletefile'])? $_POST['deletefile']    : '';
            $newclass      = isset($_POST['newclass'])  ? $_POST['newclass']      : '';
            $classedit     = isset($_POST['classedit']) ? $_POST['classedit']     : '';
            $newfile = '';


            if(($newteaserfile == 'add' || $newteaserfile == 'copy') &&  $newname) {
                if(preg_match('/[^a-zA-Z0-9\-\._]/',$newname)) {
                    $wrongchar = true;
                    $o .= '<p class="cmsimplecore_warning">' . $plugin_tx['teaser']['file-manager_wrong_char'] . '</p>';
                } else {
                    $newfile = $newname;
                    if(substr($newfile,-4,4)!='.txt') $newfile .= '.txt';

                    // copying
                    if($newteaserfile == 'copy')  {
                        $fn = $plugin_tx['teaser']['temp_teaser_file']? $plugin_tx['teaser']['temp_teaser_file'] : 'teaser_'.$sl.'.txt';
                        $oldfile = file_get_contents($datapath.$fn);
                        if($oldfile === false) e('cntopen', 'content', $datapath.$fn);
                        if (file_put_contents($datapath.$newfile, $oldfile) === false) {
                            e('cntwriteto', 'content', $datapath.$fn);
                        }
                    }
                }

            } elseif($deletefile && $newteaserfile == 'del') {
                unlink($datapath . $deletefile);

            } elseif($classedit)  {

                $newfile = $plugin_tx['teaser']['temp_teaser_file'];
                $fn = $plugin_tx['teaser']['temp_teaser_file']? $plugin_tx['teaser']['temp_teaser_file'] : 'teaser_'.$sl.'.txt';
                $teaserfile = file_get_contents($datapath.$fn);
                if($teaserfile === false) e('cntopen', 'content', $datapath.$fn);
                $newclass = $newclass? ' '.$newclass : '';
                $teaserfile = preg_replace('!^(\s*<div class="teaser)(.*)(")!U','${1}'.$newclass.'${3}',$teaserfile);
                if (file_put_contents($datapath.$fn, $teaserfile) === false) {
                    e('cntwriteto', 'content', $datapath.$fn);
                }

            } else {
                $newfile = $newteaserfile;
            }


            // writing to lang config which teaser file is "active"
            $languagefile = preg_replace('!(\'temp_teaser_file\'\]=\")(.*)(\";)!','${1}'.$newfile.'${3}',$languagefile);
            file_put_contents($pth['folder']['plugins'].$plugin .'/languages/' . $sl . '.php',$languagefile);
            include($pth['folder']['plugins'].$plugin .'/languages/' . $sl . '.php');
        }

        // if no "active" teaser file is listed in lang config, the standard file will be set as active
        $fn = $plugin_tx['teaser']['temp_teaser_file']? $plugin_tx['teaser']['temp_teaser_file'] : 'teaser_'.$sl.'.txt';

        // create the "active" teaser file and path if necessary
        if(!is_file($datapath.$fn)){
            if(!is_dir($datapath)) {
                if(mkdir($datapath,0777,true)===false) e('missing','folder',$datapath);
            } 
            $handle = fopen($datapath.$fn, "w");
            fwrite($handle, '<div class="teaser">'."\n<div>\n</div>\n</div>\n");
            fclose($handle);
        }

        // read teaser file and set variable $teaserfile for rest of the program
        $teaserfile = file_get_contents($datapath.$fn);
        if($teaserfile === false) e('cntopen', 'content', $datapath.$fn);

        // read the class of the global teaser div container, additional to class="teaser"
        preg_match('!^\s*<div class="teaser (.*)"!U',$teaserfile,$matches);
        $teaserclass = isset($matches[1])? $matches[1] : 'standard';


        // remove global div container around teaser group
        $teaserfamily = preg_replace(array(
            '/^\s*<div[^>|.]*>\s*/U',
            '/\s*<\/div>\s*$/'
            ),
            '',$teaserfile);

        $teaserarray = explode('<!---->',$teaserfamily);
        $teasercount = count($teaserarray);



        // save changes concerning which teaser to show
        //==============================================
        if (isset($_POST['teaserselect'])) {
            $languagefile = file_get_contents($pth['folder']['plugins'].$plugin .'/languages/' . $sl . '.php');
            $newnr = $_POST['teaserselect'];
            if($newnr == 'add' || $newnr == 'del' || $newnr == 'copy') {
                if($newnr == 'add') {
                    $newnr = $teasercount + 1;
                    $teaserarray[$teasercount] = "\n<div>\n</div>\n";
                }
                if($newnr == 'copy') {
                    $newnr = $teasercount + 1;
                    $teaserarray[$teasercount] = $teaserarray[($plugin_tx['teaser']['temp_teaser_nr']-1)];
                }
                if($newnr == 'del') {
                    $newnr = 1;
                    array_splice($teaserarray, ($plugin_tx['teaser']['temp_teaser_nr']-1),1);
                }
                $teaserfamily = implode('<!---->',$teaserarray);
                // make backup of old teaserfile
                file_put_contents($datapath.$fn.'bak', $teaserfile);
                // put the div container around the teaserfamily
                $teaserfinal = '<div class="teaser '.$teaserclass.'">'.$teaserfamily .'</div>'."\n";
                // save the final teaser group
                file_put_contents($datapath.$fn, $teaserfinal);
                $teasercount = count($teaserarray);
            }
            if($newnr == 'bak') {
                $newnr = $plugin_tx['teaser']['temp_teaser_nr']? $plugin_tx['teaser']['temp_teaser_nr'] : 1;
                if(!is_file($datapath.$fn.'bak')) {
                    $o .= '<p class="cmsimplecore_warning">' . $plugin_tx['teaser']['no_backup_found'] . '</p>';
                } else {
                    unlink($datapath.$fn);
                    rename($datapath.$fn.'bak',$datapath.$fn);
                    $teaserfinal = file_get_contents($datapath.$fn);
                    // remove the div container around the teaser group
                    $teaserfamily = preg_replace(array(
                        '/^\s*<div[^>|.]*>\s*/U',
                        '/\s*<\/div>\s*$/'),
                        '',$teaserfinal);
                    $teaserarray = explode('<!---->',$teaserfamily);
                    $teasercount = count($teaserarray);

                }
            }
            if(isset($_POST['move']) && $_POST['move']>0 && is_numeric($_POST['teaserselect']) ) {
                $newnr = $_POST['move'];
                $movingteaser = $teaserarray[($plugin_tx['teaser']['temp_teaser_nr']-1)];
                array_splice($teaserarray, ($plugin_tx['teaser']['temp_teaser_nr']-1),1);
                array_splice($teaserarray, ($newnr-1),0,$movingteaser);
                $teaserfamily = implode("<!---->",$teaserarray);
                // put div container around teaser family
                $teaserfinal = '<div class="teaser '.$teaserclass.'">'.$teaserfamily .'</div>'."\n";
                file_put_contents($datapath.$fn, $teaserfinal);
            }
            $languagefile = preg_replace('!(\'temp_teaser_nr\'\]=\")(.*)(\";)!','${1}'.$newnr.'${3}',$languagefile);
            file_put_contents($pth['folder']['plugins'].$plugin .'/languages/' . $sl . '.php',$languagefile);
            include($pth['folder']['plugins'].$plugin .'/languages/' . $sl . '.php');
        }



        // save changed teaser file
        //============================
        if (isset($_POST['teaser'])) { 
            $get_link    = isset($_POST['link'])?       $_POST['link']       :'';
            $get_extlink = isset($_POST['extlink'])?    $_POST['extlink']    :'';
            $teaserclass = isset($_POST['class'])?      $_POST['class']      : 'standard';
            $teaserbg    = isset($_POST['background'])? $_POST['background'] :'';
            $bgdetails   = isset($_POST['bgdetails'])?  $_POST['bgdetails']  :'';

            $saveteaser = stsl($_POST['teaser']);


            if($get_extlink && $get_link == 'ext') {
                if(strpos($get_extlink, 'http://') === false) $get_extlink = 'http://' . $get_extlink;
                $blank = $plugin_cf['teaser']['external_links_open_new_windows']? 'target="_blank"':'';
                $saveteaser = '<a href="'.$get_extlink.'" '.$blank.'><span class="teaser"></span></a>'."\n".$saveteaser;

            } elseif($get_link && $get_link != 'hide') {
                $saveteaser = '<a href="?'.$get_link.'"><span class="teaser"></span></a>'."\n".$saveteaser;
            }
            $teaserbg = $teaserbg? 'URL('.$pth['folder']['base'].$plugin_cf['teaser']['background_images'].$teaserbg.') '.$bgdetails : '';
            $teaserbg = (!$teaserbg && $bgdetails)? $bgdetails : $teaserbg;
            $teaserbg = $teaserbg? ' style="background:'.$teaserbg.';"' :'';

            $saveteaser = '<div'.$teaserbg.'>' . $saveteaser . '</div>';
            //$saveteaser = '<div>' . $saveteaser . '</div>';

            if($get_link == 'hide') $saveteaser = '<!--'.$saveteaser.'-->';

            $saveteaser = "\n" . $saveteaser . "\n";

            $teaserarray[($plugin_tx['teaser']['temp_teaser_nr']-1)] = $saveteaser ;
            $teaserfamily = implode('<!---->',$teaserarray);

            // put div container around teaser family
            $teaserfinal = '<div class="teaser '.$teaserclass.'">'.$teaserfamily .'</div>'."\n";
            if (file_put_contents($datapath.$fn, $teaserfinal) === false) {
                e('cntwriteto', 'content', $datapath.$fn);
            }
            if(!isset($_POST['preview'])) {
                // make backup when there were changes in the teaser file, except in case of preview
                if (file_put_contents($datapath.$fn.'bak', $teaserfile) === false) {
                    e('cntwriteto', 'content', $datapath.$fn.'bak');
                }
            }
        }

// end saving processes-----------------------------------------------------------------------------



// start the HTML------------------------------------------------------------------------------------


        // see if template changes are to be done
        //================================================
        $teasertemplate = $teasermorepagedata = '';

        // any changes for non standard teaser files?
        if($plugin_cf['teaser']['template_change_for_specific_teaserfiles']) {
            $pvcasesarray = explode('|',$plugin_cf['teaser']['template_change_for_specific_teaserfiles']);
            foreach ($pvcasesarray as $key=>$value) {
            	$array = explode(';',$value,3);
                $pvfile = $array[0];
                $pvtemplate=$array[1];
                $pvmorepagedata= isset($array[2])? $array[2]:'';

                if($plugin_tx['teaser']['temp_teaser_file'] &&  $pvfile == $plugin_tx['teaser']['temp_teaser_file']
                    || !$plugin_tx['teaser']['temp_teaser_file'] &&  $pvfile == 'teaser_'.$sl.'.txt') {
                    $teasertemplate = $pvtemplate;
                    $teasermorepagedata = $pvmorepagedata;
                    break;
                }
            }
        }

        // change the template if needed
        if ($teasertemplate) {
        	$cf['site']['template']          = $teasertemplate;
        	$pth['folder']['template']       = $pth['folder']['templates'].$cf['site']['template'].'/';
        	$pth['file']['template']         = $pth['folder']['template'].'template.htm';
        	$pth['file']['stylesheet']       = $pth['folder']['template'].'stylesheet.css';
        	$pth['folder']['menubuttons']    = $pth['folder']['template'].'menu/';
        	$pth['folder']['templateimages'] = $pth['folder']['template'].'images/';
        }

        // apply morepagedata if necessary
        if($teasermorepagedata) {
            $mpdarray = explode(';',$teasermorepagedata);
            foreach ($mpdarray as $key=>$value) {
                if($value) {
                	list($mpdvar,$mpdvalue) = explode('=',$value);
                    ${trim($mpdvar)} = trim($mpdvalue);
                }
            }
        }


       // show plugin name and switchable (on/off) Copyright notice
       //==========================================================
       $o .= '<h2>Teaser_XH '
          . '<span style="font:normal normal 10pt sans-serif;">v. ' . $plugin_cf['teaser']['version']
          . ' &copy; 2014 by <a href="http://frankziesing.de/cmsimple/">svasti</a> &nbsp;'
          // button to display copyright notice
          . tag('input type="button" value="license?" style="font-size:80%;" OnClick="
               if(document.getElementById(\'license\').style.display == \'none\') {
                   document.getElementById(\'license\').style.display = \'inline\';
               } else {
                   document.getElementById(\'license\').style.display = \'none\';
               }
               "')
          . '</span></h2>'."\n"
          . '<p style="display:none" id="license">'
          . $plugin_tx['teaser']['license']
          . '<a href="http://www.gnu.org/licenses/gpl.html" target="_blank">http://www.gnu.org/licenses/gpl.html</a>'
          . tag('br') . tag('br') . '</p>' . "\n" ;



       // Select teaser group
       //=====================

        $o .= '<form method="POST" action="" name="filemanagement">';
        $o .= tag('input type="hidden" value="plugin_main" name="admin"');
        $handle=opendir($datapath);
        $teaserfiles = array();
        while (false !== ($tfile = readdir($handle))) {
        	if($tfile != "." && $tfile != ".." && $tfile != 'teaser_'.$sl.'.txt' && substr($tfile,-3,3)!='bak') {
        		$teaserfiles[] = $tfile;
        		}
        	}
        closedir($handle);
        natcasesort($teaserfiles);
        $teaserfiles_select = '';
        foreach($teaserfiles as $value){
        	$selected = '';
        	if($plugin_tx['teaser']['temp_teaser_file'] == $value) {$selected = ' selected';}
        	$teaserfiles_select .= "\n<option value=$value$selected>$value</option>";
        }
        $o .= '<select name="newteaserfile" OnChange="
                if(this.options[this.selectedIndex].value == \'add\' || this.options[this.selectedIndex].value == \'copy\') {
                    document.getElementById(\'newfile\').style.display = \'inline\';
                    document.getElementById(\'delete\').style.display = \'none\';
                    document.getElementById(\'class\').style.display = \'none\';
                } else if(this.options[this.selectedIndex].value == \'del\') {
                    document.getElementById(\'delete\').style.display = \'inline\';
                    document.getElementById(\'newfile\').style.display = \'none\';
                    document.getElementById(\'class\').style.display = \'none\';
                } else {
                    document.getElementById(\'class\').style.display = \'inline\';
                    document.getElementById(\'delete\').style.display = \'none\';
                    document.getElementById(\'newfile\').style.display = \'none\';
                    this.form.submit();
                }  ; ">'

           .  "\n" . '<option value="">' . $plugin_tx['teaser']['file-manager_standard_file'] . '</option>'
           .  "\n" . $teaserfiles_select
           .  "\n" . '<option value="add">' . $plugin_tx['teaser']['file-manager_create_new_file'] . '</option>'
           .  "\n" . '<option value="copy">'. $plugin_tx['teaser']['file-manager_copy_file']       . '</option>'
           .  "\n" . '<option value="del">' . $plugin_tx['teaser']['file-manager_delete_file']     . '</option>'
           .  '</select>'

            //delete teaser file
           .  '<span id="delete" style="display:none"> &nbsp; '
           .  '<select name="deletefile">'
           .  "\n" . $teaserfiles_select
           .  '</select>'
           .  tag('input type="submit" name="delete" style="background:#fbb;" value="' . ucfirst($tx['action']['delete']).'" ')
           .  '</span>'

            //create new teaser file
           .  '<span id="newfile" style="display:none">'
           .  tag('input type="text" name="newname" placeholder="'.$plugin_tx['teaser']['file-manager_enter_new_file_name'] .'"')
           .  tag('input type="submit" value="' . ucfirst($plugin_tx['teaser']['file-manager_create_new_file']).'" name="newfilename"')
           .  '</span>';


        // select teaser group css class
        //-------------------------------
        $teasercss = file_get_contents($pth['folder']['plugins'].'teaser/css/stylesheet.css');
        preg_match_all("!\/\*start(.*)\*\/!U",$teasercss,$out);
        $teasercssarray = $out[1];

        $selected = $teasercss_select = '';
        $x = 0;
        foreach ($teasercssarray as $key=>$value) {
       	    $selected = '';
        	if($teaserclass == $value) {$selected = ' selected'; $x++;}
        	$teasercss_select .= "\n<option value=$value$selected> $value </option>";
        }
        if($teaserclass && !$x) $teasercss_select .= "\n<option value=$teaserclass selected> $teaserclass </option>";

        $o .= '<span id="class">'
           .  ' ' . $plugin_tx['teaser']['file-manager_css_class'] . ' '
           .  '<select name="newclass" OnChange="document.getElementById(\'classedit\').value = \'1\'; this.form.submit();" >'
           .  "\n" . $teasercss_select
           .  '</select>'
           .  tag('input type="hidden" id="classedit" value="" name="classedit"')
           .  '</span>'

           .  '</form>';


        // select teaser from teaser group, etc.
        //======================================

        // clean up the temp value (higher value than available teasers can lead to unwanted multiplication of teasers)
        if(!is_numeric($plugin_tx['teaser']['temp_teaser_nr'])
            || $plugin_tx['teaser']['temp_teaser_nr'] > $teasercount) {

            $languagefile = file_get_contents($pth['folder']['plugins'].$plugin .'/languages/' . $sl . '.php');
            $languagefile = preg_replace('!(\'temp_teaser_nr\'\]=\")(.*)(\";)!','${1}'. 1 .'${3}',$languagefile);
            file_put_contents($pth['folder']['plugins'].$plugin .'/languages/' . $sl . '.php',$languagefile);
            include($pth['folder']['plugins'].$plugin .'/languages/' . $sl . '.php');
        }

        // select add or delete single teaser
        $o .= '<form id="singleteaser" name="singleteaser" method="POST" action="">'
           .  tag('input type="hidden" value="plugin_main" name="admin"');

        $singleteaser_select = $position_select = '';
        for ($i = 1; $i <= $teasercount; $i++ ) {
       	    $checked = '';
        	if($plugin_tx['teaser']['temp_teaser_nr'] == $i) {$checked = ' checked="checked"';}

        	$singleteaser_select .= tag('input type="radio" title="'
                                 .  $plugin_tx['teaser']['hint_click_preview']
                                 .  '" name="teaserselect" OnChange="this.form.submit();" id="teaserselect'.$i.'" value="'.$i.'" '.$checked.'')
                                 .  '<label for="teaserselect'.$i.'">' . $i.'</label>';

        	$position_select .= "\n<option value=$i> &nbsp; $i &nbsp; </option>";
        }


        $o .=  tag('br')
           .  "\n" . $singleteaser_select
           .  ' &nbsp; ' .  "\n"
           .  '<button type="submit" name="teaserselect" value="add">'. $plugin_tx['teaser']['single-teaser_add']
           .  '</button><button type="submit" name="teaserselect" value="copy">'. $plugin_tx['teaser']['single-teaser_copy']
           .  '</button><button type="submit" name="teaserselect" value="del">'. $plugin_tx['teaser']['single-teaser_delete']
           .  '</button>'
           .  "\n" 
           .  $plugin_tx['teaser']['single-teaser_move_position'] .' '
           .  '<select name="move" OnChange="this.form.submit();">'
           .  "\n" . '<option> &nbsp; </option>'
           .  "\n" . $position_select
           .  '</select>'
           .  '<button type="submit" name="teaserselect" value="bak" >'. $plugin_tx['teaser']['restore_backup']
           .  '</button>'
           .  "\n"
           .  tag('input type="hidden" id="clickselect"')
           .  '</form>';



        // show editable teaser contents
        //===========================================
        // find out which teaser is shown, background & link
        $selectedteaser = $teaserarray[($plugin_tx['teaser']['temp_teaser_nr']-1)];

        $hideselected = strpos($selectedteaser,'<!--')!==false? ' selected':'';
        $hideteaser   = '<option value="hide"'.$hideselected.'>' . $plugin_tx['teaser']['hide_teaser'] . '</option>';
        $selectedteaser = str_replace(array('<!--','-->'),'',$selectedteaser);

        preg_match('!^\s*<div(.*)>!U',$selectedteaser,$matches);
        $teaserbg1   = isset($matches[1])? trim($matches[1]):'';
        $teaserbg2   = str_replace('style="background:','',$teaserbg1);
        $teaserbg3   = str_replace('URL('.$pth['folder']['base'].$plugin_cf['teaser']['background_images'],'',$teaserbg2);
        $teaserbg3   = str_replace('URL('.$plugin_cf['teaser']['background_images'],'',$teaserbg3);
        $x = strpos($teaserbg3,')');
        $teaserbg   = $x? substr($teaserbg3,0,$x):'';
        $bgdetails = $x? trim(substr($teaserbg3,($x+1))) : $teaserbg2;
        $bgdetails = trim($bgdetails,';"');

        preg_match('!^\s*<div(.*)><a href="(.*)"!U',$selectedteaser,$matches);
        $teaserlink = isset($matches[2])? trim($matches[2]):'';
        $teaserlink = str_replace('http://','',$teaserlink);

        $selectedteaser = preg_replace(array(
            '/^\s*<div(.*)>\s*/U',
            '/^\s*(?:(?:<a[^>]*>)?<span class="teaser"><\/span><\/a>)?\s*/U',
            '/\s*(?:<\/div>)?\s*$/'
            ),
            '',$selectedteaser);


        $pages_select = '';
        $x = 0;
        for ($i = 0; $i < $cl; $i++) {
            $selected = '';
            if(substr($teaserlink,1) == $u[$i]) {$selected = ' selected'; $x++;}
            $levelindicator = '';
            for ($j = 1; $j < $l[$i]; $j++) {$levelindicator .= '&ndash;&nbsp;';}
            $page = $levelindicator.$h[$i];
            $page = strlen($page)>35? substr($page,0,33).'...':$page;
            $pages_select .= '<option value="'.$u[$i].'"'.$selected.'>'."\n".$plugin_tx['teaser']['link_to'].': '.$page.'</option>';
        }
        $selected = $extlinkinput = $extlink = '';
        $extlinkinput = 'display:none;';
        if($teaserlink && !$x) {
            $extlinkinput = 'display:inline;';
            $selected     = ' selected';
            $extlink      = $teaserlink;
        }
        $goto_extlink = '<option value="ext"'.$selected.'>' . $plugin_tx['teaser']['external_link'] . '</option>';


        $o .= '<form action="" method="POST">'
           .  '<select name="link" OnChange="
               if(this.options[this.selectedIndex].value == \'ext\') {
                   document.getElementById(\'extlink\').style.display = \'inline\';
               } else {
                   document.getElementById(\'extlink\').style.display = \'none\';
               } ; ">'
           .  '<option value="">' . $plugin_tx['teaser']['no_global_link'] . '</option>'
           .  $goto_extlink . $hideteaser . $pages_select . '</select>'

           .  tag('input type="text" style="'.$extlinkinput.'width:15em;" name="extlink" id="extlink" placeholder="'
           .  $plugin_tx['teaser']['enter_ext_link'] . '" value="' . $extlink . '"')

           .  tag('input class="submit" type="button" value="' .$plugin_tx['teaser']['background'].'" onclick="
                if(document.getElementById(\'background\').style.display == \'none\') {
                    document.getElementById(\'background\').style.display = \'inline\';
                } else {
                    document.getElementById(\'background\').style.display = \'none\';
                }
                "')

           .  tag('input class="submit" type="submit" value=" &nbsp; ' . ucfirst($tx['action']['save']).' &nbsp; "')
           .  tag('input type="hidden" value="' .$teaserclass.'" name="class"');

        // prepare background dialog
        //=============================
        if($plugin_cf['teaser']['background_images']) {

            $handle=opendir($pth['folder']['base'] . $plugin_cf['teaser']['background_images']);
            $t_bgimages = array();
            while (false !== ($img_file = readdir($handle))) {
            	if(strpos($img_file,'.') !== 0) {
            		$t_bgimages[] = $img_file;
            		}
            	}
            closedir($handle);
            natcasesort($t_bgimages);
            $images_select = '';
            $i = 0;
            foreach($t_bgimages as $image){
            	$selected = '';
            	if($teaserbg == $image) {$selected = ' selected'; $i++;}
            	$images_select .= "\n<option value=$image$selected>$image</option>";
            }
            // background dialog
            //====================
            $show_bg_dialog = ($teaserbg || $bgdetails)? '': ' display:none;';
            $defaulttext = !$i && $teaserbg ? $plugin_tx['teaser']['wrong_image_path'] : $plugin_tx['teaser']['no_image'];
            $o .= '<span id="background" style="white-space:nowrap;'.$show_bg_dialog.'">' . tag('br')
               .  '<select name="background">'
               .  '<option value="">' . $defaulttext . '</option>'
               .  $images_select . '</select>'
               .  ' repeat etc.: '
               .  tag('input type="text" style="width:15em;" value="'.$bgdetails.'" name="bgdetails"')
               .  '<a class="teaser_pop-up" href="#">'
               .  tag('img src="'
               .  $pth['folder']['plugin'].'css/help_icon.png" width="16" height="16" alt="Help"')
               .  '<span style="white-space:normal;">' . "\n"
               .  $plugin_tx['teaser']['background_help']. "</span></a>\n"
                                                                                                
               .  '</span>';
        }
        // editor area
        //=================
        $o .= '<textarea name="teaser" class="xh-editor" cols="80" rows="25" style="width:100%; height:'.$plugin_cf['teaser']['editor_height'].'px;">'
           .  $selectedteaser . '</textarea>'
           .  '</form>';


        // making the preview clickable to put the clicked teaser into the editor
        //========================================================================
        function teaserClickable($file)
        {
            $file = str_replace('><span class="teaser"></span></a>',' onclick="return false;"><span class="teaser"></span></a>',$file);
            $pos = strpos($file,'<div',4);
            $i = 1; $check = '';
            while ($pos) {
                $file = substr_replace($file,'<div OnCLick="
                            document.getElementById(\'clickselect\').name = \'teaserselect\';
                            document.getElementById(\'clickselect\').value = '.$i.';
                            document.getElementById(\'singleteaser\').submit();
                            " ',$pos,4);
                $pos = strpos($file,'<div',($pos+1));
                $i++;
            }
        	return $file;
        }

        //preview below the editor
        //=============================
        $o .=  "\n\n\n<!--  T e a s e r   P r e v i e w  -->\n\n".tag('br');
        $o .=  isset($teaserfinal)? teaserClickable($teaserfinal)."\n" : teaserClickable($teaserfile)."\n";


        // prepare the editor initialization
        //=====================================
        // prepare editor css
        $xeditorcss = '';
        if($teaserclass) {
            $xstart = strpos($teasercss, '/*start'.$teaserclass.'*/');
            $xend = strpos($teasercss, '/*end'.$teaserclass.'*/');
            if($xstart!==false && $xend!==false) {
                $xstart = $xstart + strlen('/*start'.$teaserclass.'*/');
                $xend = strpos($teasercss, '/*end'.$teaserclass.'*/');
                $xeditorcss = substr($teasercss,$xstart,($xend-$xstart));
                $xeditorcss = str_replace('div.teaser.'.$teaserclass.' div','body',$xeditorcss);
                $xeditorcss = str_replace('.teaser.'.$teaserclass.' div','body',$xeditorcss);
                $xeditorcss = preg_replace(
                    array(
                    '!\s*div.teaser.'.$teaserclass.'\s*{.*}\s*!sU',
                    '!\s*{\s*!',
                    '!\s*}\s*!',
                    '!^\s*!m',
                    '!\s*\n\s*!'
                    ),
                    array(
                    '',
                    '{',
                    '}',
                    '',
                    ''
                    ),
                    $xeditorcss);

                if($teaserbg1) {
                    // adding the background definition
                    $editorbg = str_replace('style=','',$teaserbg1);
                    $editorbg = trim($editorbg,'" ');
                    $editorbg = $cf['editor']['external']=='ckeditor'
                              ? str_replace('URL(','URL(',$editorbg)
                              : str_replace('URL(','URL(../../.',$editorbg);
                    // in subsites/secondary languages "..." will result
                    $editorbg = str_replace('...','..',$editorbg);
                    //find background of stylesheet.css
                    $cssfile = file_get_contents($pth['file']['stylesheet']);
                    $cssfile = str_replace("\n",' ',$cssfile);
                    preg_match('!(?:\sbody|^body).*\{(.*)}!U',$cssfile,$i);
                    $cssfilebg   = isset($i[1])? trim($i[1]):'';
                    preg_match('!(?:background:|background-color:)(.*);!U',$cssfilebg,$i);
                    $cssfilebg   = isset($i[1])? trim($i[1]):'';

                    $cssfilebg = $cssfilebg? $cssfilebg : 'white';
                    $xeditorcss .= ' body{'.$editorbg.'}html{background:'.$cssfilebg.';}';
                }
            }
        }


        // initialize the editor
        if($cf['editor']['external']=='ckeditor') {
            $toolbar = isset($plugin_cf['ckeditor']['toolbar'])? $plugin_cf['ckeditor']['toolbar']:$plugin_cf['ckeditor']['init'];
            $initFile = $pth['folder']['plugins'] . 'ckeditor/' . 'inits/init_' . $toolbar . '.js';
            $config = file_get_contents($initFile);
            $css = 'html{margin-top:10px;}body.cke_show_borders{float:none;margin-right:auto;margin-left:auto;display:block;}';
            $editorcss = $xeditorcss .$css;
            $config = str_replace('\'%STYLESHEET%\'', '[\'%STYLESHEET%\',\'' . $editorcss . '\']', $config);
            $config = str_replace('\'%EDITOR_HEIGHT%\'', '[\'%EDITOR_HEIGHT%\',\'' . $plugin_cf['teaser']['editor_height'] . '\']', $config);

            //prevent wrong alert from ckeditor on save via calendar save button
            $o .=  '<script type="text/javascript">
                                        // <![CDATA[
                if (typeof window.removeEventListener != "undefined") {
                    window.removeEventListener("beforeunload", CKeditor_beforeUnload, false);
                } else {
                    window.detachEvent("onbeforeunload", CKeditor_beforeUnload);
                }
                // ]]></script>';

        }
        elseif(strpos($cf['editor']['external'],'tinymce') !== false) {
            $initFile = $pth['folder']['plugins']
                      . $cf['editor']['external']
                      . '/inits/init_'
                      . $plugin_cf[$cf['editor']['external']]['init']
                      . '.js';
            $config = file_get_contents($initFile);
            $css = 'html{margin-top:10px;}body{float:none;margin-right:auto;margin-left:auto;display:block;}';
            $editorcss = $xeditorcss . $css; 
            file_put_contents($pth['folder']['plugins'] . 'teaser/css/tinymce.css', $editorcss);
            $config = str_replace('%STYLESHEET%', '%STYLESHEET%,' . $pth['folder']['plugins'] . 'teaser/css/tinymce.css', $config);
        }
        else $config=false;

        init_editor(array('xh-editor'), $config);


    }
    } else {
        // rest of plugin menu
        //=====================
        $o .= plugin_admin_common($action, $admin, $plugin);
    }

}
?>
