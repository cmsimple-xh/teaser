<?php
/**
 * Teaser_XH for CMSimple_XH
 * Frontend
 * @version 1.2, May 2014
 * @author svasti@svasti.de
 */


function teaser($teaserfile=false)
{
  global $plugin_cf,$plugin_tx,$pth, $c, $s, $sl;
  $o = '';

    $datapath = $plugin_cf['teaser']['path']
              ? $datapath = $plugin_cf['teaser']['path_starts_in_content_folder']
                  ? $pth['folder']['content'] .$plugin_cf['teaser']['path']
                  : $pth['folder']['base'] .$plugin_cf['teaser']['path']
              : $pth['folder']['plugins'] . 'teaser/data/';

    $teasercontents = $teaserfile
                    ? file_get_contents($datapath.$teaserfile)
                    : file_get_contents($datapath.'teaser_'.$sl.'.txt');

    $o .= "\n\n" . '<!-- T E A S E R -->' . "\n" . $teasercontents . "\n";


  return $o;
}
?>
