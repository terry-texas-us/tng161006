<?php

require 'tng_begin.php';
$tngprint = 1;

require 'personlib.php';

define('FPDF_FONTPATH', $rootpath . $endrootpath . 'font/');
require 'tngpdf.php';
require 'rpt_utils.php';
$pdf = new TNGPDF($orient, 'in', $pagesize);
setcookie('tng_pagesize', $pagesize, time() + 31536000, '/');

// define formatting defaults
$lineheight = $pdf->GetFontSize($rptFont, $rptFontSize) + 0.1; // height of each row on the page
$shading = 220;    // value of shaded lines (255 = white, 0 = black)
$citefontsub = 4; // number of font pts to take off for superscript
// load fonts
$pdf->AddFont($hdrFont, 'B');
$pdf->AddFont($lblFont);
$pdf->AddFont($lblFont, 'B');
$pdf->AddFont($rptFont);
$pdf->AddFont($rptFont, 'B');

$ldsOK = determineLDSRights(true);

// compute the label width based on the longest string that will be displayed
$labelwidth = getMaxStringWidth([uiTextSnippet('name'), uiTextSnippet('born'), uiTextSnippet('christened'), uiTextSnippet('died'), uiTextSnippet('buried'),
        uiTextSnippet('cremated'), uiTextSnippet('spouse'), uiTextSnippet('married')], $lblFont, 'B', $lblFontSize, ':');
if ($ldsOK) {
  $labelwidth = getMaxStringWidth([uiTextSnippet('baptizedlds'), uiTextSnippet('endowedlds'), uiTextSnippet('sealedslds')], $lblFont, 'B', $lblFontSize, ':', $labelwidth);
}
$labelwidth += 0.1;

// create Header
if ($blankform == 1) {
  $title = uiTextSnippet('indreport');
} else {
  $result = getPersonData($personID);
  if ($result) {
    $row = tng_fetch_assoc($result);

    $rights = determineLivingPrivateRights($row);
    $row['allow_living'] = $rights['living'];
    $row['allow_private'] = $rights['private'];

    $namestr = getName($row);
  }
  $title = uiTextSnippet('indreportfor') . " $namestr ($personID)";
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

$citations = [];
$citedisplay = [];
$citestring = [];

// create a blank form if that's what they asked for
if ($blankform == 1) {

  nameLine(uiTextSnippet('name'), '', uiTextSnippet('gender'), '');
  doubleLine(uiTextSnippet('born'), '', uiTextSnippet('place'), '');
  if (!$tngconfig['hidechr']) {
    doubleLine(uiTextSnippet('christened'), '', uiTextSnippet('place'), '');
  }
  doubleLine(uiTextSnippet('died'), '', uiTextSnippet('place'), '');
  doubleLine(uiTextSnippet('buried'), '', uiTextSnippet('place'), '');
  if ($ldsOK) {
    doubleLine(uiTextSnippet('baptizedlds'), '', uiTextSnippet('place'), '');
    doubleLine(uiTextSnippet('endowedlds'), '', uiTextSnippet('place'), '');
  }
  singleLine(uiTextSnippet('spouse'), '', 'B');
  doubleLine(uiTextSnippet('married'), '', uiTextSnippet('place'), '');
  if ($ldsOK) {
    doubleLine(uiTextSnippet('sealedslds'), '', uiTextSnippet('place'), '');
  }
  childLine(1, '');
  childLine(2, '');
  childLine(3, '');
  childLine(4, '');
  childLine(5, '');
  $pdf->SetFont($lblFont, 'B', $lblFontSize);
  pageBox();
  titleLine(uiTextSnippet('general'));
} // create a filled in form
else {
  if ($citesources && $rights['both']) {
    getCitations($personID, 0);
  }

  //$y = $pdf->GetY();
  $cite = reorderCitation($personID . '_', 0);
  $cite2 = reorderCitation($personID . '_NAME', 0);
  if ($cite2) {
    $cite .= $cite ? ", $cite2" : $cite2;
  }
  $gender = strtoupper($row['sex']);
  if ($gender == 'M') {
    $gender = uiTextSnippet('male');
  } else {
    if ($gender == 'F') {
      $gender = uiTextSnippet('female');
    } else {
      if ($gender == 'U') {
        $gender = uiTextSnippet('unknown');
      } else {
        $gender = $row['sex'];
      }
    }
  }
  nameLine(uiTextSnippet('name'), $namestr, uiTextSnippet('gender'), $gender, $cite);

  // birth
  if ($rights['both']) {
    $cite = reorderCitation($personID . '_BIRT', 0);
    doubleLine(uiTextSnippet('born'), displayDate($row['birthdate']), uiTextSnippet('place'), $row['birthplace'], $cite);

    if (!$tngconfig['hidechr']) {
      $cite = reorderCitation($personID . '_CHR', 0);
      doubleLine(uiTextSnippet('christened'), displayDate($row['altbirthdate']), uiTextSnippet('place'), $row['altbirthplace'], $cite);
    }

    $custevents = getPersonEventData($personID);
    while ($custevent = tng_fetch_assoc($custevents)) {
      $displayval = getEventDisplay($custevent['display']);
      $fact = [];
      if ($custevent['info']) {
        $fact = checkXnote($custevent['info']);
        if ($fact[1]) {
          $xnote = $fact[1];
          array_pop($fact);
        }
      }
      $done = false;
      if ($custevent['eventdate'] || $custevent['eventplace']) {
        $cite = reorderCitation($personID . '_' . $custevent['eventID'], 0);
        doubleLine($displayval, displayDate($custevent['eventdate']), uiTextSnippet('place'), $custevent['eventplace'], $cite);
        $done = true;
      }
      if ($custevent['info']) {
        if ($done) {
          $cite = reorderCitation($personID . '_' . $custevent['eventID'], 0);
          $displayval = uiTextSnippet('cont');
        } else {
          $cite = '';
        }
        singleLine($displayval, $custevent['info'], '', $cite);
      }
    }
    tng_free_result($custevents);

    $cite = reorderCitation($personID . '_DEAT', 0);
    doubleLine(uiTextSnippet('died'), displayDate($row['deathdate']), uiTextSnippet('place'), $row['deathplace'], $cite);

    $cite = reorderCitation($personID . '_BURI', 0);
    $burialmsg = $row['burialtype'] ? uiTextSnippet('cremated') : uiTextSnippet('buried');
    doubleLine($burialmsg, displayDate($row['burialdate']), uiTextSnippet('place'), $row['burialplace'], $cite);
  } else {
    doubleLine(uiTextSnippet('born'), '', uiTextSnippet('place'), '');
    if (!$tngconfig['hidechr']) {
      doubleLine(uiTextSnippet('christened'), '', uiTextSnippet('place'), '');
    }
    doubleLine(uiTextSnippet('died'), '', uiTextSnippet('place'), '');
    doubleLine(uiTextSnippet('buried'), '', uiTextSnippet('place'), '');
  }

  if ($rights['lds']) {
    if ($rights['both']) {
      $cite = reorderCitation($personID . '_BAPL', 0);
      doubleLine(uiTextSnippet('baptizedlds'), displayDate($row['baptdate']), uiTextSnippet('place'), $row['baptplace'], $cite);
      $cite = reorderCitation($personID . '_CONF', 0);
      doubleLine(uiTextSnippet('conflds'), displayDate($row['confdate']), uiTextSnippet('place'), $row['confplace'], $cite);
      $cite = reorderCitation($personID . '_INIT', 0);
      doubleLine(uiTextSnippet('initlds'), displayDate($row['initdate']), uiTextSnippet('place'), $row['initplace'], $cite);
      $cite = reorderCitation($personID . '_ENDL', 0);
      doubleLine(uiTextSnippet('endowedlds'), displayDate($row['endldate']), uiTextSnippet('place'), $row['endlplace'], $cite);
    } else {
      doubleLine(uiTextSnippet('baptizedlds'), '', uiTextSnippet('place'), '');
      doubleLine(uiTextSnippet('endowedlds'), '', uiTextSnippet('place'), '');
    }
  }

  // do parents
  $parents = getChildParentsFamily($personID);
  if ($parents && tng_num_rows($parents)) {
    $titleConfig = ['title' => $title,
            'font' => $hdrFont,
            'fontSize' => $hdrFontSize,
            'justification' => 'L',
            'lMargin' => $lftmrg,
            'skipFirst' => false,
            'line' => false];
    while ($parent = tng_fetch_assoc($parents)) {
      $gotfather = getParentSimplePlusDates($parent['familyID'], 'husband');
      if ($gotfather) {
        $fathrow = tng_fetch_assoc($gotfather);

        $frights = determineLivingPrivateRights($fathrow);
        $fathrow['allow_living'] = $frights['living'];
        $fathrow['allow_private'] = $frights['private'];

        if ($fathrow['firstname'] || $fathrow['lastname']) {
          $fathname = getName($fathrow);
        }
        $fathtext = generateDates($fathrow);
        if ($citesources && $frights['both']) {
          getCitations($fathrow['personID'], 0);
        }
        $cite = reorderCitation($fathrow['personID'] . '_', 0);
        $cite2 = reorderCitation($fathrow['personID'] . '_NAME', 0);
        if ($cite2) {
          $cite .= $cite ? ", $cite2" : $cite2;
        }
        singleLine(uiTextSnippet('father'), "$fathname $fathtext", '', $cite);
      } else {
        singleLine(uiTextSnippet('father'), '');
      }

      $gotmother = getParentSimplePlusDates($parent['familyID'], 'wife');
      if ($gotmother) {
        $mothrow = tng_fetch_assoc($gotmother);

        $mrights = determineLivingPrivateRights($mothrow);
        $mothrow['allow_living'] = $mrights['living'];
        $mothrow['allow_private'] = $mrights['private'];

        if ($mothrow['firstname'] || $mothrow['lastname']) {
          $mothname = getName($mothrow);
        }
        $mothtext = generateDates($mothrow);
        if ($citesources && $mrights['both']) {
          getCitations($mothrow['personID'], 0);
        }
        $cite = reorderCitation($mothrow['personID'] . '_', 0);
        $cite2 = reorderCitation($mothrow['personID'] . '_NAME', 0);
        if ($cite2) {
          $cite .= $cite ? ", $cite2" : $cite2;
        }
        singleLine(uiTextSnippet('mother'), "$mothname $mothtext", '', $cite);
      } else {
        singleLine(uiTextSnippet('mother'), '');
      }
      if ($rights['lds']) {
        if ($rights['both']) {
          doubleLine(uiTextSnippet('sealedplds'), displayDate($parent['sealdate']), uiTextSnippet('place'), $row['sealplace']);
        } else {
          doubleLine(uiTextSnippet('sealedplds'), '', uiTextSnippet('place'), '');
        }
      }
    }
  } // print two empty fields
  else {
    singleLine(uiTextSnippet('father'), '');
    singleLine(uiTextSnippet('mother'), '');
  }

  if ($row['sex'] == 'M') {
    $spouse = 'wife';
    $spouseorder = 'husborder';
    $self = 'husband';
  } else {
    if ($row['sex'] == 'F') {
      $spouse = 'husband';
      $spouseorder = 'wifeorder';
      $self = 'wife';
    } else {
      $spouseorder = '';
    }
  }
  if ($spouseorder) {
    $marriages = getSpouseFamilyDataPlusDates($self, $personID, $spouseorder);
  } else {
    $marriages = getSpouseFamilyDataUnionPlusDates($personID);
  }
  if (!tng_num_rows($marriages) && $spouseorder) {
    $marriages = getSpouseFamilyDataUnionPlusDates($personID);
    $spouseorder = 0;
  }
  while ($marriagerow = tng_fetch_assoc($marriages)) {
    $mrights = determineLivingPrivateRights($marriagerow);
    $marriagerow['allow_living'] = $mrights['living'];
    $marriagerow['allow_private'] = $mrights['private'];

    if ($citesources && $mrights['both']) {
      getCitations($marriagerow['familyID'], 0);
    }
    if (!$spouseorder) {
      $spouse = $marriagerow['husband'] == $personID ? wife : husband;
    }
    if ($marriagerow[$spouse]) {
      $spouseresult = getPersonSimple($marriagerow[$spouse]);
      $spouserow = tng_fetch_assoc($spouseresult);

      $srights = determineLivingPrivateRights($spouserow);
      $spouserow['allow_living'] = $srights['living'];
      $spouserow['allow_private'] = $srights['private'];

      $namestr = getName($spouserow);
      $spousetext = generateDates($spouserow);
      if ($citesources && $srights['both']) {
        getCitations($marriagerow[$spouse], 0);
      }
      $cite = reorderCitation($marriagerow[$spouse] . '_', 0);
      $cite2 = reorderCitation($marriagerow[$spouse] . '_NAME', 0);
      if ($cite2) {
        $cite .= $cite ? ", $cite2" : $cite2;
      }
      singleLine(uiTextSnippet('spouse'), "$namestr $spousetext", '', $cite);
    }
    if ($mrights['both']) {
      $cite = reorderCitation($marriagerow['familyID'] . '_MARR', 0);
      doubleLine(uiTextSnippet('married'), displayDate($marriagerow['marrdate']), uiTextSnippet('place'), $marriagerow['marrplace'], $cite);
    } else {
      doubleLine(uiTextSnippet('married'), '', uiTextSnippet('place'), '');
    }
    if ($mrights['lds']) {
      if ($mrights['both']) {
        $cite = reorderCitation($marriagerow['familyID'] . '_SLGS', 0);
        doubleLine(uiTextSnippet('sealedslds'), displayDate($marriagerow['sealdate']), uiTextSnippet('place'), $marriagerow['sealplace'], $cite);
      } else {
        doubleLine(uiTextSnippet('sealedslds'), '', uiTextSnippet('place'), '');
      }
    }

    // get the children from this marriage
    $children = getChildrenDataPlusDates($marriagerow['familyID']);
    if ($children && tng_num_rows($children)) {
      $childcnt = 1;
      while ($child = tng_fetch_assoc($children)) {
        $crights = determineLivingPrivateRights($child);
        $child['allow_living'] = $crights['living'];
        $child['allow_private'] = $crights['private'];

        $namestr = getName($child);
        $childtext = generateDates($child);
        if ($citesources && $crights['both']) {
          getCitations($child['pID'], 0);
        }
        $cite = reorderCitation($child['pID'] . '_NAME', 0);
        childLine($childcnt, "$namestr $childtext", $cite);
        $childcnt++;
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
    $indnotes = getNotes($personID, 'I');
    $notes = '';
    $lasttitle = '---';
    foreach ($indnotes as $key => $note) {
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
    $allowable_tags = '';
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
      //$cite = preg_replace("/\n/", " ", $cite);
      $pdf->MultiCell($paperdim['w'] - $rtmrg - ($lftmrg * 1.5), $pdf->GetFontSize(), "$citectr. $cite\n\n", 0, 'L', 0, 0);
      //$pdf->WriteHTML("<br>$citectr. $cite<br>");
      $citectr++;
    }
  }
}
$pdf->Output();

function childLine($childnum, $data, $cite = '') {
  global $pdf, $paperdim, $lftmrg, $rtmrg, $lineheight;
  global $rptFont, $rptFontSize, $lblFont, $lblFontSize, $citefontsub;
  global $labelwidth;

  $pdf->SetFont($lblFont, 'B', $lblFontSize);

  $pdf->Cell($labelwidth, $lineheight, "$childnum", 1, 0, 'R');
  if ($childnum == 1) {
    $pdf->SetX($lftmrg);
    $pdf->Cell($labelwidth, $lineheight, uiTextSnippet('children') . ':', 0, 0, 'L');
  }
  $pdf->SetFont($rptFont, '', $rptFontSize);
  $pdf->Cell($paperdim['w'] - $pdf->GetX() - $rtmrg, $lineheight, $data, 1, 0, 'L');
  if ($cite != '') {
    $pdf->SetX($lftmrg + $labelwidth + $pdf->GetStringWidth($data));
    $pdf->SetFont($rptFont, '', $rptFontSize - $citefontsub);
    $pdf->Cell(0, $lineheight / 2, " $cite");
  }
  $pdf->Ln($lineheight);
}

function singleLine($label, $data, $datastyle = '', $cite = '') {
  global $pdf, $paperdim, $lftmrg, $rtmrg, $lineheight;
  global $rptFont, $rptFontSize, $lblFont, $lblFontSize, $citefontsub;
  global $labelwidth;

  if ($label) {
    $label .= ':';
  }

  $spaceWidth = $paperdim['w'] - $lftmrg - $rtmrg - $labelwidth;
  $pdf->SetFont($rptFont, $datastyle, $rptFontSize);
  $stringWidth = $pdf->GetStringWidth($data);

  $borderWidth = $stringWidth > $spaceWidth ? 0 : 1;

  $pdf->SetFont($lblFont, 'B', $lblFontSize);
  $pdf->Cell($labelwidth, $lineheight, $label, $borderWidth, 0, 'L');
  $pdf->SetFont($rptFont, $datastyle, $rptFontSize);

  if ($stringWidth > $spaceWidth) {
    $topY = $pdf->GetY();
    $pdf->MultiCell($paperdim['w'] - $rtmrg - $lftmrg - $labelwidth, $pdf->GetFontSize(), $data, 1, 'L', 0, 0);
    $lowerY = $pdf->GetY();
    $diff = $lowerY - $topY;
    $pdf->SetY($topY);
    if ($diff > 0) {
      $pdf->Cell($labelwidth, $diff, '', 1, 0, 'L');
    }
    $pdf->SetY($lowerY);
    $lineWidth = $spaceWidth - .2;    //for citations
  } else {
    $pdf->Cell($paperdim['w'] - $pdf->GetX() - $rtmrg, $lineheight, $data, 1, 0, 'L');
    $lineWidth = $pdf->GetStringWidth($data);
  }
  if ($cite != '') {
    $pdf->SetX($lftmrg + $labelwidth + $lineWidth);
    $pdf->SetFont($rptFont, $datastyle, $rptFontSize - $citefontsub);
    $pdf->Cell(0, $lineheight / 2, " $cite");
  }
  if ($stringWidth <= $spaceWidth) {
    $pdf->Ln($lineheight);
  }
}

function nameLine($label1, $data1, $label2, $data2, $cite = '') {
  global $pdf;
  global $paperdim;
  global $lftmrg;
  global $rtmrg;
  global $lineheight;
  global $rptFont;
  global $rptFontSize;
  global $lblFont;
  global $lblFontSize;
  global $citefontsub;
  global $labelwidth;

  $genderwidth = 1.0;
  $pdf->SetFont($lblFont, 'B', $lblFontSize);
  $label2width = $pdf->GetStringWidth($label2 . ':  ');

  $pdf->SetFont($lblFont, 'B', $lblFontSize);
  $pdf->Cell($labelwidth, $lineheight, $label1 . ':', 1, 0, 'L');
  $pdf->SetFont($rptFont, 'B', $rptFontSize);
  $pdf->CellFit($paperdim['w'] - $rtmrg - $lftmrg - $genderwidth - $label2width - $labelwidth, $lineheight, $data1, 1, 0, 'L', 0, '', 1, 0);
  if ($cite != '') {
    $x = $pdf->GetX();
    $pdf->SetX($lftmrg + $labelwidth + $pdf->GetStringWidth($data1));
    $pdf->SetFont($rptFont, 'B', $rptFontSize - $citefontsub);
    $pdf->Cell(0, $lineheight / 2, " $cite");
    $pdf->SetX($x);
  }
  $pdf->SetFont($lblFont, 'B', $lblFontSize);
  $pdf->Cell($label2width, $lineheight, $label2 . ':', 1, 0, 'L');
  $pdf->SetFont($rptFont, '', $rptFontSize);
  $pdf->CellFit($genderwidth, $lineheight, $data2, 1, 0, 'L', 0, '', 1, 0);
  $pdf->Ln($lineheight);
}

function doubleLine($label1, $data1, $label2, $data2, $cite = '') {
  global $pdf, $paperdim, $lftmrg, $rtmrg, $lineheight;
  global $rptFont, $rptFontSize, $lblFont, $lblFontSize, $citefontsub;
  global $labelwidth;

  $datewidth = 2.0;         // width of date box in inches
  $pdf->SetFont($lblFont, 'B', $lblFontSize);
  $placewidth = $pdf->GetStringWidth($label2 . ':  ');

  $pdf->SetFont($lblFont, 'B', $lblFontSize);
  $pdf->CellFit($labelwidth, $lineheight, $label1 . ':', 1, 0, 'L', 0, '', 1, 0);
  $pdf->SetFont($rptFont, '', $rptFontSize);
  $pdf->CellFit($datewidth, $lineheight, $data1, 1, 0, 'L', 0, '', 1, 0);
  $pdf->SetFont($lblFont, 'B', $lblFontSize);
  $pdf->Cell($placewidth, $lineheight, $label2 . ':', 1, 0, 'L');
  $pdf->SetFont($rptFont, '', $rptFontSize);
  $pdf->CellFit($paperdim['w'] - $pdf->GetX() - $rtmrg, $lineheight, $data2, 1, 0, 'L', 0, '', 1, 0);
  if ($cite != '') {
    if ($data2 == '') {
      $x = $labelwidth + $pdf->GetStringWidth($data1) + $lftmrg;
    } else {
      $x = $labelwidth + $datewidth + $placewidth + $pdf->GetStringWidth($data2) + $lftmrg;
    }
    $pdf->SetX($x);
    $pdf->SetFont($rptFont, '', $rptFontSize - $citefontsub);
    $pdf->Cell(0, $lineheight / 2, " $cite");
  }
  $pdf->Ln($lineheight);
}