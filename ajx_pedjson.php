<?php

set_time_limit(0);
require 'tng_begin.php';

require $subroot . 'pedconfig.php';
require 'pedbox.php';

initMediaTypes();

function xmlPhoto($persfamID, $living, $gender) {
  $photoInfo = getPhotoSrc($persfamID, $living, $gender);
  $photoref = "\"photosrc\":\"{$photoInfo['ref']}\"";
  $photolink = $photoInfo['link'] ? "\"photolink\":\"{$photoInfo['link']}\"" : "\"photolink\":\"-1\"";

  return $photoref . "," . $photolink;
}

function xmlPerson($currperson, $backperson, $generation) {
  global $pedigree;
  global $parentset;
  global $generations;
  global $display;
  global $people;
  global $familylist;
  global $families;

  $result = getPersonData($currperson);
  $row = tng_fetch_assoc($result);
  tng_free_result($result);

  $person = "{\"personID\":\"$currperson\",\"backperson\":\"$backperson\",\"gender\":\"{$row['sex']}\",";
  //look up info
  $rightbranch = checkbranch($row['branch']);
  $rights = determineLivingPrivateRights($row, $rightbranch);
  $row['allow_living'] = $rights['living'];
  $row['allow_private'] = $rights['private'];
  $nameinfo = xmlcharacters(getName($row));
  $person .= "\"name\":\"$nameinfo\",";

  $parentfamID = "";
  $locparentset = $parentset;
  $parentscount = 0;
  $parentfamIDs = [];
  $parents = $familyresult = getChildFamily($currperson, "parentorder");
  if ($parents) {
    $parentscount = tng_num_rows($parents);
    if ($parentscount > 0) {
      if ($locparentset > $parentscount) {
        $locparentset = $parentscount;
      }
      $i = 0;
      while ($parentrow = tng_fetch_assoc($parents)) {
        $i++;
        if ($i == $locparentset) {
          $parentfamID = $parentrow['familyID'];
        }
        $parentfamIDs[$i] = $parentrow['familyID'];
      }
      if (!$parentfamID) {
        $parentfamID = $row['famc'];
      }
    }
    tng_free_result($parents);
  }

  $person .= $parentfamID ? "\"famc\":\"$parentfamID\"," : "\"famc\":\"-1\",";
  if ($display == "standard" && $pedigree['inclphotos']) {
    $person .= xmlPhoto($currperson, $rights['both'], $row['sex']) . ",";
  } else {
    $person .= "\"photosrc\":\"-1\",\"photolink\":\"\",";
  }

  if ($rights['both']) {
    $dataflag = $row['birthdate'] || $row['altbirthdate'] || $row['altbirthplace'] || $row['deathdate'] || $row['burialdate'] || $row['burialplace'] ? 1 : 0;

    // get birthdate info
    if ($row['altbirthdate'] && !$row['birthdate']) {
      $bd = $row['altbirthdate'];
      $bp = $row['altbirthplace'];
      $birthabbr = "capaltbirthabbr";
    } elseif ($dataflag) {
      $bd = $row['birthdate'];
      $bp = $row['birthplace'];
      $birthabbr = "capbirthabbr";
    } else {
      $bd = $bp = $birthabbr = "";
    }

    // get death/burial date info
    if ($row['burialdate'] && !$row['deathdate']) {
      $dd = $row['burialdate'];
      $dp = $row['burialplace'];
      $deathabbr = "capburialabbr";
    } elseif ($dataflag) {
      $dd = $row['deathdate'];
      $dp = $row['deathplace'];
      $deathabbr = "capdeathabbr";
    } else {
      $dd = $dp = $deathabbr = "";
    }
  } else {
    $bd = $bp = $birthabbr = $dd = $dp = $deathabbr = $md = $mp = $marrabbr = "";
  }
  $person .= "\"babbr\":\"" . uiTextSnippet($birthabbr) . "\",";
  $person .= "\"bdate\":\"" . xmlcharacters(displayDate($bd)) . "\",";
  $person .= "\"bplace\":\"" . xmlcharacters($bp) . "\",";
  $person .= "\"dabbr\":\"" . uiTextSnippet($deathabbr) . "\",";
  $person .= "\"ddate\":\"" . xmlcharacters(displayDate($dd)) . "\",";
  $person .= "\"dplace\":\"" . xmlcharacters($dp) . "\"";

  if ($parentscount > 1) {
    $parents = "";
    for ($i = 1; $i <= $parentscount; $i++) {
      if ($parents) {
        $parents .= ",";
      }
      $parents .= "{";
      $parentinfo = getParentInfo($parentfamIDs[$i]);
      $parents .= "\"famID\":\"" . $parentfamIDs[$i] . "\",";
      $parents .= "\"fatherID\":\"" . $parentinfo['fathID'] . "\",";
      $parents .= "\"fathername\":\"" . xmlcharacters($parentinfo['fathname']) . "\",";
      $parents .= "\"motherID\":\"" . $parentinfo['mothID'] . "\",";
      $parents .= "\"mothername\":\"" . xmlcharacters($parentinfo['mothname']) . "\"";
      $parents .= "}";
    }
    $person .= ",\n\"parents\":[" . $parents . "]";
  }

  //do spouses
  $spiceNames = [];
  $spiceIDs = [];
  $spicekidcount = [];
  $spousecount = 1;

  $spouse = $self = $spouseorder = "";
  if ($row['sex']) {
    if ($row['sex'] == 'M') {
      $spouse = 'wife';
      $self = 'husband';
      $spouseorder = 'husborder';
    } elseif ($row['sex'] == 'F') {
      $spouse = 'husband';
      $self = 'wife';
      $spouseorder = 'wifeorder';
    }
    //do query with OR
    //get person's gender that way (male if it matches husband, female if it matches wife), assign $spouse, $self and $spouseorder
  }
  if ($spouseorder) {
    $spouseresults = getSpouseFamilyFull($self, $currperson, $spouseorder);
  } else {
    $spouseresults = getSpouseFamilyFullUnion($currperson);
    $marrtot = tng_num_rows($spouseresults);
    if ($marrtot) {
      $spouserow = tng_fetch_assoc($spouseresults);
      if ($currperson == $spouserow['husband']) {
        $spouse = 'wife';
        $self = 'husband';
        $spouseorder = 'husborder';
      } elseif ($currperson == $spouserow['wife']) {
        $spouse = 'husband';
        $self = 'wife';
        $spouseorder = 'wifeorder';
      }
      $spouseresults = getSpouseFamilyFullUnion($currperson);
    }
  }
  if ($spouseorder) {
    $spfams = "";
    while ($spouserow = tng_fetch_assoc($spouseresults)) {
      if ($spfams) {
        $spfams .= ",";
      }
      $spfams .= "{";
      $sp = "";
      if ($spouserow[$spouse]) {
        $spouseIDresult = getPersonSimple($spouserow[$spouse]);
        $spouseIDrow = tng_fetch_assoc($spouseIDresult);
        $rightbranch = checkbranch($spouseIDrow['branch']);
        $rights = determineLivingPrivateRights($spouseIDrow, $rightbranch);
        $spouseIDrow['allow_living'] = $rights['living'];
        $spouseIDrow['allow_private'] = $rights['private'];
        $sp = "\"spID\":\"" . $spouserow[$spouse] . "\",";
        $sp .= "\"spname\":\"" . xmlcharacters(getName($spouseIDrow)) . "\",";
        //$sp .= "\"spname\":\"" . htmlentities( getName( $spouseIDrow ), ENT_QUOTES ) . "\",";
        tng_free_result($spouseIDresult);
      }
      $sp .= "\"spFamID\":\"" . $spouserow['familyID'] . "\"";
      $spfams .= $sp . "}\n";
      //if family not already in $families, use xmlfamily to add it here
      if (!in_array($spouserow['familyID'], $familylist)) {
        $families[] = getFamily($spouserow);
      }
    }
    tng_free_result($spouseresults);
    $person .= ",\n\"spfams\":[" . $spfams . "]";  //used to be spFam
  }

  $person .= "}";
  $people[] = $person;

  $generation++;
  if ($generation <= $generations) {
    if ($parentfamID) {
      xmlFamily($parentfamID, $currperson, $generation);
    }
  }
}

function getChildren($familyID) {
  global $pedigree;

  $children = "";
  if ($pedigree['popupkids']) {
    $childrenresults = getChildrenSimple($familyID);
    if ($childrenresults && tng_num_rows($childrenresults)) {
      while ($child = tng_fetch_assoc($childrenresults)) {
        if ($children) {
          $children .= ",\n";
        }
        $rightbranch = checkbranch($child['branch']);
        $rights = determineLivingPrivateRights($child, $rightbranch);
        $child['allow_living'] = $rights['living'];
        $child['allow_private'] = $rights['private'];
        $children .= "{\"childID\":\"" . $child['pID'] . "\",\"name\":\"" . xmlcharacters(getName($child)) . "\"}";
      }
      $children = "\"children\":[$children]";  //used to be child
    }
    tng_free_result($childrenresults);
  }

  return $children;
}

function getFamily($famrow) {
  $family = "{\"famID\":\"" . $famrow['familyID'] . "\",\"husband\":\"" . $famrow['husband'] . "\",\"wife\":\"" . $famrow['wife'] . "\",";
  $rightbranch = checkbranch($famrow['branch']);
  $rights = determineLivingPrivateRights($famrow, $rightbranch);
  $famrow['allow_living'] = $rights['living'];
  $famrow['allow_private'] = $rights['private'];
  if ($rights['both']) {
    $marrdate = displayDate($famrow['marrdate']);
    $marrplace = $famrow['marrplace'];
    if ($famrow['marrdate'] || $famrow['marrplace']) {
      $marrabbr = uiTextSnippet('capmarrabbr');
    } else {
      $marrabbr = "";
    }
  } else {
    $marrdate = $marrplace = $marrabbr = "";
  }
  $family .= "\"mdate\":\"" . xmlcharacters($marrdate) . "\",";
  $family .= "\"mplace\":\"" . xmlcharacters($marrplace) . "\",";
  $family .= "\"mabbr\":\"$marrabbr\"";

  $children = getChildren($famrow['familyID']);
  if ($children) {
    $family .= "," . $children;
  }
  $family .= "}";

  return $family;
}

function xmlFamily($famc, $backperson, $generation) {
  global $families;
  global $familylist;

  $famresult = getFamilyData($famc);
  if ($famresult) {
    $famrow = tng_fetch_assoc($famresult);
    $families[] = getFamily($famrow);
    $familylist[] = $famc;
    if ($famrow['husband']) {
      xmlPerson($famrow['husband'], $backperson, $generation);
    }
    if ($famrow['wife']) {
      xmlPerson($famrow['wife'], $backperson, $generation);
    }

    tng_free_result($famresult);
  }
}

function getParentInfo($famid) {
  $parentarray = [];
  $parentresult = getParentSimple($famid, 'husband');
  if ($parentresult) {
    $row = tng_fetch_assoc($parentresult);
    $parentarray['fathID'] = $row['personID'];
    $rightbranch = checkbranch($row['branch']);
    $rights = determineLivingPrivateRights($row, $rightbranch);
    $row['allow_living'] = $rights['living'];
    $row['allow_private'] = $rights['private'];
    $parentarray['fathname'] = xmlcharacters(getName($row));
    tng_free_result($parentresult);
  }

  $parentresult = getParentSimple($famid, 'wife');
  if ($parentresult) {
    $row = tng_fetch_assoc($parentresult);
    $parentarray['mothID'] = $row['personID'];
    $rightbranch = checkbranch($row['branch']);
    $rights = determineLivingPrivateRights($row, $rightbranch);
    $row['allow_living'] = $rights['living'];
    $row['allow_private'] = $rights['private'];
    $parentarray['mothname'] = xmlcharacters(getName($row));
    tng_free_result($parentresult);
  }
  return $parentarray;
}
// how many generations to show?
if (!$pedigree['maxgen']) {
  $pedigree['maxgen'] = 6;
}
if ($generations > $pedigree['maxgen']) {
  $generations = $pedigree['maxgen'];
} else {
  if (!$generations) {
    $generations = $pedigree['maxgen'] < 4 ? $pedigree['maxgen'] : 4;
  } else {
    $generations = intval($generations);
  }
}
// alternate parent display?
$parentset = $parentset ? intval($parentset) : 0;

$people = [];
$families = [];
$familylist = [];

header("Content-Type: application/json; charset=" . $session_charset);
$generation = 1;
$newfam = 1;
$backperson = "";
if ($personID) {
  xmlPerson($personID, $backperson, $generation);
} else {
  eval("\$backpers = \$backpers$newfam;");
  eval("\$famc = \$famc$newfam;");
  while ($famc) {
    xmlFamily($famc, $backpers, $generation);
    $newfam++;
    eval("\$backpers = \$backpers$newfam;");
    eval("\$famc = \$famc$newfam;");
  }
}

echo "{";

$numfamilies = count($families);
if (count($people)) {
  echo "\"people\":[" . implode(",", $people) . "]";
  if ($numfamilies) {
    echo ",";
  }
}
if ($numfamilies) {
  echo "\"families\":[" . implode(",", $families) . "]";
}
echo "}";
