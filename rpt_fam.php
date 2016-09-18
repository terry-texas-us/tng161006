<?php
// PDF Family Report
// Author: Bret Rumsey
//
require 'begin.php';
require 'genlib.php';
require 'getlang.php';

$tngprint = 1;
require 'checklogin.php';
require 'personlib.php';

define('FPDF_FONTPATH', $rootpath . $endrootpath . 'font/');
require 'tngpdf.php';
require 'rpt_utils.php';
$pdf = new TNGPDF($orient, 'in', $pagesize);
setcookie('tng_pagesize', $pagesize, time() + 31536000, '/');

// define formatting defaults
$lineheight = $pdf->GetFontSize($rptFont, $rptFontSize) + 0.1;    // height of each row on the page
$shading = 220;                            // value of shaded lines (255 = white, 0 = black)

// load fonts
$pdf->AddFont($hdrFont, 'B');
$pdf->AddFont($lblFont);
$pdf->AddFont($lblFont, 'B');
$pdf->AddFont($rptFont);
$pdf->AddFont($rptFont, 'B');

$ldsOK = determineLDSRights();

// compute the label width based on the longest string that will be displayed
$labelwidth = getMaxStringWidth([uiTextSnippet('name'), uiTextSnippet('born'), uiTextSnippet('christened'), uiTextSnippet('died'), uiTextSnippet('buried'), uiTextSnippet('cremated'), uiTextSnippet('spouse'), uiTextSnippet('married')], $lblFont, 'B', $lblFontSize, ':');
if ($ldsOK) {
  $labelwidth = getMaxStringWidth([uiTextSnippet('baptizedlds'), uiTextSnippet('endowedlds'), uiTextSnippet('sealedslds')], $lblFont, 'B', $lblFontSize, ':', $labelwidth);
}

// header and footer config
if ($blankform == 1) {
  $title = uiTextSnippet('familygroupfor');
} else {
  $query = "SELECT familyID, husband, wife, living, private, marrdate, branch FROM families WHERE familyID = '$familyID'";
  $result = tng_query($query);
  if ($result) {
    $famrow = tng_fetch_assoc($result);
    $famname = getFamilyName($famrow);

    $rights = determineLivingPrivateRights($famrow);
    $famrow['allow_living'] = $rights['living'];
    $famrow['allow_private'] = $rights['private'];

    $title = uiTextSnippet('familygroupfor') . " $famname";
  }
}
$pdf->SetTitle($title);
$titleConfig = ['title' => $title,
        'font' => $hdrFont,
        'fontSize' => $hdrFontSize,
        'justification' => 'L',
        'lMargin' => $lftmrg,
        'skipFirst' => false,
        'header' => false,
        'line' => false];
$footerConfig = ['font' => $hdrFont,
        'fontSizeLarge' => 8,
        'fontSizeSmall' => 6,
        'printWordPage' => true,
        'bMargin' => $botmrg,
        'lMargin' => $lftmrg,
        'skipFirst' => false,
        'line' => false];

// set margins
$pdf->SetTopMargin($topmrg);
$pdf->SetLeftMargin($lftmrg);
$pdf->SetRightMargin($rtmrg);
$pdf->SetAutoPageBreak(1, $botmrg + $pdf->GetFooterHeight() + $lineheight); // this sets the bottom margin for us
$pdf->SetFillColor($shading);

// PDF settings
$pdf->SetAuthor($dbowner);

// let's get started
$pdf->AddPage();
$paperdim = $pdf->GetPageSize();

// citation vars
$citations = [];
$citedisplay = [];
$citestring = [];

// create a blank form if that's what they asked for
if ($blankform == 1) {
  nameLine(uiTextSnippet('husband'), '', 1, '');
  dateLine(uiTextSnippet('born'), '', '', '');
  dateLine(uiTextSnippet('christened'), '', '', '');
  dateLine(uiTextSnippet('died'), '', '', '');
  dateLine(uiTextSnippet('buried'), '', '', '');
  if ($ldsOK) {
    dateLine(uiTextSnippet('baptizedlds'), '', '', '');
    dateLine(uiTextSnippet('endowedlds'), '', '', '');
  }
  parentLine(uiTextSnippet('father'), '', uiTextSnippet('mother'), '', '', '');
  dateLine(uiTextSnippet('married'), '', '');
  if ($ldsOK) {
    dateLine(uiTextSnippet('sealedslds'), '', '');
  }
  nameLine(uiTextSnippet('wife'), '', 1, '');
  dateLine(uiTextSnippet('born'), '', '', '');
  dateLine(uiTextSnippet('christened'), '', '', '');
  dateLine(uiTextSnippet('died'), '', '', '');
  dateLine(uiTextSnippet('buried'), '', '', '');
  if ($ldsOK) {
    dateLine(uiTextSnippet('baptizedlds'), '', '', '');
    dateLine(uiTextSnippet('endowedlds'), '', '', '');
  }
  parentLine(uiTextSnippet('father'), '', uiTextSnippet('mother'), '', '', '');
  titleLine(uiTextSnippet('children'));
  for ($i = 1; $i <= 3; $i++) {
    childNameLine($i, '', '', '');
    dateLine(uiTextSnippet('born'), '', '', '');
    dateLine(uiTextSnippet('christened'), '', '', '');
    dateLine(uiTextSnippet('died'), '', '', '');
    dateLine(uiTextSnippet('buried'), '', '', '');
    if ($ldsOK) {
      dateLine(uiTextSnippet('baptizedlds'), '', '', '');
      dateLine(uiTextSnippet('endowedlds'), '', '', '');
    }
    spouseLine(uiTextSnippet('spouse'), $spousename, $marrplace, $cite1, $cite2);
  }
} // create a filled in form
else {
  if ($rights['both'] && $citesources) {
    getCitations($familyID, 0);
    $citekey = $familyID . '_';
    $cite = reorderCitation($citekey);
  }

  // husband & spouses
  if ($famrow['husband']) {
    displayIndividual($famrow['husband'], 1, 1);
    titleLine(uiTextSnippet('children'));

    // for each child
    $query = "SELECT people.personID AS personID, branch, firstname, lnprefix, lastname, prefix, suffix, nameorder, living, private, famc, sex, birthdate, birthplace, altbirthdate, altbirthplace, haskids, deathdate, deathplace, burialdate, burialplace, burialtype, baptdate, baptplace, confdate, confplace, initdate, initplace, endldate, endlplace, sealdate, sealplace FROM people, children WHERE people.personID = children.personID AND children.familyID = \"$famrow[familyID]\" ORDER BY ordernum";
    $children = tng_query($query);
    if ($children && tng_num_rows($children)) {
      $childcount = 0;
      while ($childrow = tng_fetch_assoc($children)) {
        $childcount++;
        displayChild($childrow['personID'], $childcount);
      }
    }
  }

  // notes and such
  // draw the box to contain the notes
  pageBox();
  titleLine(uiTextSnippet('general'));
  $titleConfig['header'] = uiTextSnippet('general') . ' ' . uiTextSnippet('cont');
  $titleConfig['headerFont'] = $lblFont;
  $titleConfig['headerFontSize'] = $lblFontSize;
  $titleConfig['outline'] = true;

  if ($rights['both']) {
    $famnotes = getNotes($familyID, 'F');
    $notes = '';
    $lasttitle = '---';
    foreach ($famnotes as $key => $note) {
      if ($note['title'] != $lasttitle) {
        if ($notes) {
          $notes .= "\n\n";
        }
        if ($note['title']) {
          $notes .= $note['title'] . "\n";
        }
      }
      $notes .= $note['text'];
    }
    $notes = preg_replace('/&nbsp;/', ' ', $notes);
    $notes = preg_replace('/<li>/', '* ', $notes);
    $notes = preg_replace('/<br\s*\/?>/', '', $notes);
    if (!isset($allowable_tags)) {
      $allowable_tags = '<a>';
    }
    $notes = strip_tags($notes, $allowable_tags);

    $pdf->Ln(0.05);
    $pdf->SetFont($rptFont, '', $rptFontSize);
    $pdf->MultiCell($paperdim['w'] - $rtmrg - $lftmrg, $pdf->GetFontSize(), $notes, 0, 'L', 0, 0);
  }
  // create the citations page
  if ($citesources) {
    $titleConfig['header'] = uiTextSnippet('sources');
    $titleConfig['headerFont'] = $lblFont;
    $titleConfig['headerFontSize'] = $lblFontSize;
    $titleConfig['outline'] = true;
    $pdf->AddPage();
    $titleConfig['header'] = uiTextSnippet('sources') . ' ' . uiTextSnippet('cont');

    // reduce the font
    $pdf->SetFont($rptFont, '', $rptFontSize - 2);

    // push in our left margin a bit
    $pdf->SetLeftMargin($lftmrg * 1.5);
    $citectr = 1;
    foreach ($citestring as $cite) {
      $cite = strip_tags($cite);
      $cite = preg_replace("/\n/", ' ', $cite);
      $pdf->MultiCell($paperdim['w'] - $rtmrg - ($lftmrg * 1.5), $pdf->GetFontSize(), "$citectr. $cite\n\n", 0, 'L', 0, 0);
      //$pdf->WriteHTML("<br>$citectr. $cite<br>");
      $citectr++;
    }
  }
}

// print it out
$pdf->Output();

function displayChild($personID, $childcount) {
  global $citesources;

  $query = "SELECT * FROM people WHERE personID = '$personID'";
  $result = tng_query($query);
  $ind = tng_fetch_assoc($result);
  tng_free_result($result);

  $rights = determineLivingPrivateRights($ind);
  $ind['allow_living'] = $rights['living'];
  $ind['allow_private'] = $rights['private'];

  $label = $ind['sex'] != 'F' ? uiTextSnippet('husband') : uiTextSnippet('wife');

  if ($citesources && $rights['both']) {
    getCitations($personID, 0);
  }

  // name
  $cite = reorderCitation($personID . '_NAME', 0);
  childNameLine($childcount, $ind['sex'], getName($ind), $cite);

  // birth
  if ($rights['both']) {
    $cite = reorderCitation($personID . '_BIRT', 0);
    dateLine(uiTextSnippet('born'), displayDate($ind['birthdate']), $ind['birthplace'], $cite);
  } else {
    dateLine(uiTextSnippet('born'), '', '');
  }
  // christening
  if ($rights['both']) {
    $cite = reorderCitation($personID . '_CHR', 0);
    dateLine(uiTextSnippet('christened'), displayDate($ind['altbirthdate']), $ind['altbirthplace'], $cite);
  } else {
    dateLine(uiTextSnippet('christened'), '', '');
  }
  // death
  if ($rights['both']) {
    $cite = reorderCitation($personID . '_DEAT', 0);
    dateLine(uiTextSnippet('died'), displayDate($ind['deathdate']), $ind['deathplace'], $cite);
  } else {
    dateLine(uiTextSnippet('died'), '', '');
  }
  // buried
  $burialmsg = $ind['burialtype'] ? uiTextSnippet('cremated') : uiTextSnippet('buried');
  if ($rights['both']) {
    $cite = reorderCitation($personID . '_BURI', 0);
    dateLine($burialmsg, displayDate($ind['burialdate']), $ind['burialplace'], $cite);
  } else {
    dateLine($burialmsg, '', '');
  }
  if ($rights['lds']) {
    if ($rights['both']) {
      $cite = reorderCitation($personID . '_BAPL', 0);
      dateLine(uiTextSnippet('baptizedlds'), displayDate($ind['baptdate']), $ind['baptplace'], $cite);
      $cite = reorderCitation($personID . '_CONF', 0);
      dateLine(uiTextSnippet('conflds'), displayDate($row['confdate']), $ind['confplace'], $cite);
      $cite = reorderCitation($personID . '_INIT', 0);
      dateLine(uiTextSnippet('initlds'), displayDate($row['initdate']), $ind['initplace'], $cite);
      $cite = reorderCitation($personID . '_ENDL', 0);
      dateLine(uiTextSnippet('endowedlds'), displayDate($ind['endldate']), $ind['endlplace'], $cite);

      $query = "SELECT sealdate, sealplace FROM children WHERE familyID = \"{$ind['famc']}\" AND personID = '$personID'";
      $chresult = tng_query($query);
      $child = tng_fetch_assoc($chresult);
      getCitations($personID . '::' . $ind['famc'], 0);
      $cite = reorderCitation($personID . '::' . $ind['famc'] . '_SLGC', 0);
      dateLine(uiTextSnippet('sealedplds'), displayDate($child['sealdate']), $child['sealplace'], $cite);
      tng_free_result($chresult);
    } else {
      dateLine(uiTextSnippet('baptizedlds'), '', '');
      dateLine(uiTextSnippet('endowedlds'), '', '');
    }
  }

  // show spouses
  $query = 'SELECT familyID, personID, firstname, lnprefix, lastname, prefix, suffix, nameorder, families.living AS fliving, families.private AS fprivate, families.branch AS fbranch, people.living AS living, people.private AS private, people.branch AS branch, marrdate, marrplace, sealdate, sealplace FROM families ';
  if ($ind['sex'] == 'M') {
    $query .= "LEFT JOIN people ON families.wife = people.personID WHERE husband = \"{$ind['personID']}\" $restriction ORDER BY husborder";
  } else {
    if ($ind['sex'] = 'F') {
      $query .= "LEFT JOIN people ON families.husband = people.personID WHERE wife = \"{$ind['personID']}\" $restriction ORDER BY wifeorder";
    } else {
      $query .= "LEFT JOIN people ON (families.husband = people.personID OR families.wife = people.personID) WHERE (wife = \"{$ind['personID']}\" && husband = \"{$ind['personID']}\")";
    }
  }

  $spresult = tng_query($query);

  while ($fam = tng_fetch_assoc($spresult)) {
    $frights = determineLivingPrivateRights($fam);
    $fam['allow_living'] = $frights['living'];
    $fam['allow_private'] = $frights['private'];

    $spousename = getName($fam);
    $fam['living'] = $fam['fliving'];
    $fam['private'] = $fam['fprivate'];

    $frights = determineLivingPrivateRights($fam);

    $marrplace = '';
    if ($frights['both']) {
      $marrplace = $fam['marrdate'];
      if (!empty($fam['marrplace'])) {
        if ($marrplace != '') {
          $marrplace .= ' - ';
        }
        $marrplace .= $fam['marrplace'];
      }
    }

    if ($citesources && $frights['both']) {
      getCitations($fam['familyID'], 0);
      getCitations($fam['personID'], 0);
      $cite1 = reorderCitation($fam['personID'] . '_NAME', 0);
      $cite2 = reorderCitation($fam['familyID'] . '_MARR', 0);
    }
    spouseLine(uiTextSnippet('spouse'), $spousename, $marrplace, $cite1, $cite2);
    if ($frights['lds']) {
      if ($frights['both']) {
        dateLine(uiTextSnippet('sealedslds'), displayDate($fam['sealdate']), $fam['sealplace']);
      } else {
        dateLine(uiTextSnippet('sealedslds'), '', '');
      }
    }
  }
  tng_free_result($spresult);
}

function displayIndividual($personID, $showparents, $showmarriage) {
  global $familyID;
  global $citesources;

  $query = "SELECT * FROM people WHERE personID = '$personID'";
  $result = tng_query($query);
  $ind = tng_fetch_assoc($result);
  tng_free_result($result);

  $rights = determineLivingPrivateRights($ind);
  $ind['allow_living'] = $rights['living'];
  $ind['allow_private'] = $rights['private'];

  $label = $ind['sex'] != 'F' ? uiTextSnippet('husband') : uiTextSnippet('wife');

  if ($citesources && $rights['both']) {
    getCitations($personID, 0);
  }

  // name
  $cite = reorderCitation($personID . '_NAME', 0);
  nameLine($label, getName($ind), 1, $cite);
  // birth
  if ($rights['both']) {
    $cite = reorderCitation($personID . '_BIRT', 0);
    dateLine(uiTextSnippet('born'), displayDate($ind['birthdate']), $ind['birthplace'], $cite);
  } else {
    dateLine(uiTextSnippet('born'), '', '', '');
  }
  // christening
  if ($rights['both']) {
    $cite = reorderCitation($personID . '_CHR', 0);
    dateLine(uiTextSnippet('christened'), displayDate($ind['altbirthdate']), $ind['altbirthplace'], $cite);
  } else {
    dateLine(uiTextSnippet('christened'), '', '');
  }
  // death
  if ($rights['both']) {
    $cite = reorderCitation($personID . '_DEAT', 0);
    dateLine(uiTextSnippet('died'), displayDate($ind['deathdate']), $ind['deathplace'], $cite);
  } else {
    dateLine(uiTextSnippet('died'), '', '');
  }
  // buried
  if ($rights['both']) {
    $cite = reorderCitation($personID . '_BURI', 0);
    $burialmsg = $ind['burialtype'] ? uiTextSnippet('cremated') : uiTextSnippet('buried');
    dateLine($burialmsg, displayDate($ind['burialdate']), $ind['burialplace'], $cite);
  } else {
    dateLine(uiTextSnippet('buried'), '', '');
  }
  if ($rights['lds']) {
    if ($rights['both']) {
      $cite = reorderCitation($personID . '_BAPL', 0);
      dateLine(uiTextSnippet('baptizedlds'), displayDate($ind['baptdate']), $ind['baptplace'], $cite);
      $cite = reorderCitation($personID . '_CONF', 0);
      dateLine(uiTextSnippet('conflds'), displayDate($row['confdate']), $ind['confplace'], $cite);
      $cite = reorderCitation($personID . '_INIT', 0);
      dateLine(uiTextSnippet('initlds'), displayDate($row['initdate']), $ind['initplace'], $cite);
      $cite = reorderCitation($personID . '_ENDL', 0);
      dateLine(uiTextSnippet('endowedlds'), displayDate($ind['endldate']), $ind['endlplace'], $cite);
    } else {
      dateLine(uiTextSnippet('baptizedlds'), '', '');
      dateLine(uiTextSnippet('endowedlds'), '', '');
    }
  }

  if ($showparents) {
    //show parents (for hus&wif)
    $cite1 = $cite2 = '';
    $query = "SELECT YEAR(birthdatetr) AS birthyear, YEAR(deathdatetr) AS deathyear, familyID, personID, firstname, lnprefix, lastname, prefix, suffix, nameorder, people.living, people.private, people.branch FROM (families, people) WHERE families.familyID = \"{$ind['famc']}\" AND people.personID = families.husband";
    $presult = tng_query($query);
    $parent = tng_fetch_assoc($presult);

    $prights = determineLivingPrivateRights($parent);
    $parent['allow_living'] = $prights['living'];
    $parent['allow_private'] = $prights['private'];

    $fathername = getName($parent) . generateYears($parent);
    $fatherID = $parent['personID'];
    tng_free_result($presult);

    if ($citesources && $prights['both']) {
      getCitations($fatherID, 0);
      $cite1 = reorderCitation($fatherID . '_NAME', 0);
    }

    $query = "SELECT YEAR(birthdatetr) AS birthyear, YEAR(deathdatetr) AS deathyear, familyID, personID, firstname, lnprefix, lastname, prefix, suffix, nameorder, people.living, people.private, people.branch FROM (families, people) WHERE families.familyID = \"{$ind['famc']}\" AND people.personID = families.wife";
    $presult = tng_query($query);
    $parent = tng_fetch_assoc($presult);

    $prights = determineLivingPrivateRights($parent);
    $parent['allow_living'] = $prights['living'];
    $parent['allow_private'] = $prights['private'];

    $mothername = getName($parent) . generateYears($parent);
    $motherID = $parent['personID'];
    tng_free_result($presult);

    if ($citesources && $prights['both']) {
      getCitations($motherID, 0);
      $cite2 = reorderCitation($motherID . '_NAME', 0);
    }
    parentLine(uiTextSnippet('father'), $fathername, uiTextSnippet('mother'), $mothername, $cite1, $cite2);
    if ($rights['lds'] && $rights['both']) {
      $query = "SELECT sealdate, sealplace FROM children WHERE familyID = \"{$ind['famc']}\" AND personID = '$personID'";
      $chresult = tng_query($query);
      $child = tng_fetch_assoc($chresult);
      $cite = reorderCitation($personID . '::' . $ind['famc'] . '_SLGC', 0);
      dateLine(uiTextSnippet('sealedplds'), displayDate($child['sealdate']), $child['sealplace'], $cite);
      tng_free_result($chresult);
    }
  }

  if ($showmarriage) {
    // marriages
    $query = "SELECT husband, wife, marrdate, marrplace, divdate, divplace, sealdate, sealplace, living, private, branch FROM families WHERE familyID = '$familyID'";
    $result = tng_query($query);
    $fam = tng_fetch_assoc($result);

    $frights = determineLivingPrivateRights($fam);
    $fam['allow_living'] = $frights['living'];
    $fam['allow_private'] = $frights['private'];

    tng_free_result($result);

    // married
    if ($frights['both']) {
      $cite = reorderCitation($familyID . '_MARR', 0);
      dateLine(uiTextSnippet('married'), displayDate($fam['marrdate']), $fam['marrplace'], $cite);
    } else {
      dateLine(uiTextSnippet('married'), '', '');
    }
    if ($frights['lds']) {
      if ($frights['both']) {
        dateLine(uiTextSnippet('sealedslds'), displayDate($fam['sealdate']), $fam['sealplace']);
      } else {
        dateLine(uiTextSnippet('sealedslds'), '', '');
      }
    }
    displayIndividual($fam['wife'], 1, 0);
  }
}

function childNameLine($label1, $data1, $data2, $cite = '') {
  global $pdf, $paperdim, $rtmrg, $lineheight;
  global $rptFont, $rptFontSize, $lblFont, $lblFontSize;
  global $labelwidth;

  $numwidth = 0.3;        // width of column for child number

  $pdf->SetFont($lblFont, 'B', $lblFontSize);

  $pdf->CellFit($numwidth, $lineheight, "$label1", 1, 0, 'C', 1, '', 1, 0);
  $pdf->CellFit($labelwidth - $numwidth, $lineheight, $data1, 1, 0, 'C', 0, '', 1, 0);
  $pdf->SetFont($rptFont, 'B', $rptFontSize);
  $origx = $pdf->GetX();
  $pdf->CellFit($paperdim['w'] - $pdf->GetX() - $rtmrg, $lineheight, $data2, 1, 0, 'L', 0, '', 1, 0);
  if ($cite != '') {
    $pdf->SetX($origx + $pdf->GetStringWidth($data2));
    $pdf->SetFont($rptFont, 'B', $rptFontSize - 4);
    $pdf->Cell(0, $lineheight / 2, " $cite");
  }
  $pdf->Ln($lineheight);
}

function nameLine($label1, $data1, $shade = 0, $cite = '') {
  global $pdf, $paperdim, $rtmrg, $lineheight;
  global $rptFont, $rptFontSize, $lblFont, $lblFontSize;
  global $labelwidth;

  if ($shade) {
    $bold = 'B';
  } else {
    $bold = '';
  }
  $pdf->SetFont($lblFont, $bold, $lblFontSize);
  $pdf->Cell($labelwidth, $lineheight, $label1, 1, 0, 'L', $shade);
  //$pdf->CellFit($labelwidth, $lineheight, $label1, 1, 0, 'L', $shade);
  $pdf->SetFont($rptFont, $bold, $rptFontSize);
  $origx = $pdf->GetX();
  $pdf->CellFit($paperdim['w'] - $pdf->GetX() - $rtmrg, $lineheight, $data1, 1, 0, 'L', $shade, '', 1, 0);
  if ($cite != '') {
    $pdf->SetX($origx + $pdf->GetStringWidth($data1));
    $pdf->SetFont($rptFont, $bold, $rptFontSize - 4);
    $pdf->Cell(0, $lineheight / 2, " $cite");
  }
  $pdf->Ln($lineheight);
}

function spouseLine($label1, $data1, $data2, $cite1 = '', $cite2 = '') {
  global $pdf, $paperdim, $lftmrg, $rtmrg, $lineheight;
  global $rptFont, $rptFontSize, $lblFont, $lblFontSize;
  global $labelwidth;

  $pdf->SetFont($rptFont, '', $rptFontSize);
  $width = $pdf->GetStringWidth($data1);
  if ($cite1 != '') {
    $pdf->SetFont($rptFont, '', $rptFontSize - 4);
    $width += $pdf->GetStringWidth("    $cite1");
  } else {
    $citewidth = 0;
  }

  $pdf->SetFont($lblFont, 'B', $lblFontSize);
  $pdf->CellFit($labelwidth, $lineheight, $label1, 1, 0, 'L', 0, '', 1, 0);
  $pdf->SetFont($rptFont, '', $rptFontSize);
  $pdf->CellFit($width, $lineheight, $data1, 1, 0, 'L', 0, '', 1, 0);
  if ($cite1 != '') {
    $pdf->SetX($lftmrg + $labelwidth + $pdf->GetStringWidth($data1));
    $pdf->SetFont($rptFont, '', $rptFontSize - 4);
    $pdf->Cell(0, $lineheight / 2, " $cite1");
    $pdf->SetX($lftmrg + $labelwidth + $width);
    $pdf->SetFont($rptFont, '', $rptFontSize);
  }
  $pdf->CellFit($paperdim['w'] - $pdf->GetX() - $rtmrg, $lineheight, $data2, 1, 0, 'L', 0, '', 1, 0);
  if ($cite2 != '') {
    $pdf->SetX($lftmrg + $labelwidth + $width + $pdf->GetStringWidth($data2));
    $pdf->SetFont($rptFont, '', $rptFontSize - 4);
    $pdf->Cell(0, $lineheight / 2, " $cite2");
  }
  $pdf->Ln($lineheight);
}

function dateLine($label1, $data1, $data2, $cite = '') {
  global $pdf, $paperdim, $lftmrg, $rtmrg, $lineheight;
  global $rptFont, $rptFontSize, $lblFont, $lblFontSize;
  global $labelwidth;

  $datewidth = 1.5;  // width of date field, in inches

  $pdf->SetFont($lblFont, 'B', $lblFontSize);
  $pdf->CellFit($labelwidth, $lineheight, $label1, 1, 0, 'L', 0, '', 1, 0);
  $pdf->SetFont($rptFont, '', $rptFontSize);
  $pdf->CellFit($datewidth, $lineheight, $data1, 1, 0, 'L', 0, '', 1, 0);
  $pdf->CellFit($paperdim['w'] - $pdf->GetX() - $rtmrg, $lineheight, $data2, 1, 0, 'L', 0, '', 1, 0);
  if ($cite != '') {
    if ($data2 == '') {
      $x = $labelwidth + $pdf->GetStringWidth($data1) + $lftmrg;
    } else {
      $x = $labelwidth + $datewidth + $pdf->GetStringWidth($data2) + $lftmrg;
    }
    $pdf->SetX($x);
    $pdf->SetFont($rptFont, '', $rptFontSize - 4);
    $pdf->Cell(0, $lineheight / 2, " $cite");
  }
  $pdf->Ln($lineheight);
}

function parentLine($label1, $data1, $label2, $data2, $cite1 = '', $cite2 = '') {
  global $pdf, $paperdim, $lftmrg, $rtmrg, $lineheight;
  global $rptFont, $rptFontSize, $lblFont, $lblFontSize;
  global $labelwidth;

  // set the width of the field for parent name to be half of whatever is left over
  $datawidth = ($paperdim['w'] - $lftmrg - $rtmrg - ($labelwidth * 2)) / 2;

  // determine if we can fit both mother and father on the same line
  $pdf->SetFont($rptFont, '', $rptFontSize);
  $fwidth = $pdf->GetStringWidth($data1);
  $mwidth = $pdf->GetStringWidth($data2);
  $pdf->SetFont($rptFont, '', $rptFontSize - 4);
  $fwidth += $pdf->GetStringWidth(" $cite1");
  $mwidth += $pdf->GetStringWidth(" $cite2");
  if ($fwidth > $datawidth || $mwidth > $datawidth) {
    nameLine($label1, $data1, 0, $cite1);
    nameLine($label2, $data2, 0, $cite2);
  } else {
    $pdf->SetFont($lblFont, 'B', $lblFontSize);
    $pdf->CellFit($labelwidth, $lineheight, $label1, 1, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($rptFont, '', $rptFontSize);
    $origx = $pdf->GetX();
    $pdf->CellFit($datawidth, $lineheight, $data1, 1, 0, 'L', 0, '', 1, 0);
    $origend = $pdf->GetX();
    if ($cite1 != '') {
      $pdf->SetX($origx + $pdf->GetStringWidth($data1));
      $pdf->SetFont($rptFont, '', $rptFontSize - 4);
      $pdf->Cell(0, $lineheight / 2, " $cite1");
      $pdf->Setx($origend);
    }

    $pdf->SetFont($lblFont, 'B', $lblFontSize);
    $pdf->CellFit($labelwidth, $lineheight, $label2, 1, 0, 'L', 0, '', 1, 0);
    $pdf->SetFont($rptFont, '', $rptFontSize);
    $origx = $pdf->GetX();
    $pdf->CellFit($datawidth, $lineheight, $data2, 1, 0, 'L', 0, '', 1, 0);
    if ($cite2 != '') {
      $pdf->SetX($origx + $pdf->GetStringWidth($data2));
      $pdf->SetFont($rptFont, '', $rptFontSize - 4);
      $pdf->Cell(0, $lineheight / 2, " $cite2");
    }
    $pdf->Ln($lineheight);
  }
}