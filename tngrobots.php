<?php
if (function_exists('mbtng_filename')) {
  $tngscript = basename(mbtng_filename(), '.php');
} else {
  $tngscript = basename($_SERVER['SCRIPT_NAME'], '.php');
}
//No index only
$NOI = "<meta name=\"robots\" content=\"noindex\" />\n";

//No follow only
$NOF = "<meta name=\"robots\" content=\"nofollow\" />\n";

//No index AND no follow
$NOINOF = "<meta name=\"robots\" content=\"noindex,nofollow\" />\n";

//each "case" is the name of the script file without the ".php" at the end
if ($tngprint) {
  $flags['norobots'] = $NOINOF;
} else {
  switch ($tngscript) {
    //no index, no follow
    case 'addnewacct':
    case 'ahnentafel':
    case 'anniversaries':
    case 'calendar':
    case 'changelanguage':
    case 'descend':
    case 'descendtext':
    case 'desctracker':
    case 'gedform':
    case 'login':
    case 'newacctform':
    case 'pdfform':
    case 'pedigree':
    case 'pedigreetext':
    case 'places-all':
    case 'places-containing':
    case 'places':
    case 'placesearch':
    case 'places-top':
    case 'register':
    case 'relateform':
    case 'relationship':
    case 'savelanguage2':
    case 'searchform':
    case 'sendlogin':
    case 'showlog':
    case 'suggest':
    case 'timeline':
    case 'timeline2':
    case 'ultraped':
      $flags['norobots'] = $NOINOF;
      break;

    //no indexing, but allow link following
    case 'mediaShow':
    case 'browsedocs':
    case 'browseheadstones':
    case 'notesShow':
    case 'browsephotos':
    case 'repositoriesShow':
    case 'sourcesShow':
    case 'extrastree':
    case 'reportsShow':
    case 'search':
    case 'reportsShowReport':
    case 'surnamesTop':
    case 'whatsnew':
      $flags['norobots'] = $NOI;
      break;

    //allow full indexing
    case 'cemeteriesShow':
    case 'peopleShowPerson':
    case 'familiesShowFamily':
    case 'headstones':
    case 'mostwanted':
    case 'showmedia':
    case 'cemeteriesShowCemetery':
    case 'showphoto':
    case 'repositoriesShowItem':
    case 'sourcesShowSource':
    case 'showtree':
    case 'surnames':
    case 'surnamesAll':
    case 'surnamesFirstLetter':
      $flags['norobots'] = '';
      break;

    //all pages not named get full indexing as well
    //no pages come in here unless they include genlib.php
    default:
      //$flags['norobots'] = '';
      break;
  }
}
