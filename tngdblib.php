<?php

function countChildren($familyID) {
  $query = "SELECT count(ID) AS ccount FROM children WHERE familyID = '$familyID'";

  return tng_query($query);
}

function getChildrenMinimal($familyID) {
  $query = "SELECT UPPER(personID) AS personID FROM children WHERE familyID = '$familyID' ORDER BY ordernum";

  return tng_query($query);
}

function getChildrenMinimalExcept($familyID, $personID) {
  $query = "SELECT UPPER(personID) AS personID FROM children WHERE familyID = '$familyID' AND personID != '$personID' ORDER BY ordernum";

  return tng_query($query);
}

function getChildrenMinimalPlusGender($familyID) {
  global $people_table;

  $query = "SELECT children.personID AS personID, sex FROM (children, $people_table) WHERE familyID = '$familyID' AND children.personID = $people_table.personID ORDER BY ordernum";

  return tng_query($query);
}

function getChildrenSimple($familyID) {
  global $people_table;

  $query = "SELECT $people_table.personID AS pID, $people_table.personID AS personID, firstname, lnprefix, lastname, prefix, suffix, nameorder, living, private, branch FROM (children, $people_table) WHERE familyID = '$familyID' AND children.personID = $people_table.personID ORDER BY ordernum";

  return tng_query($query);
}

function getChildrenData($familyID) {
  global $people_table;

  $query = "SELECT $people_table.personID AS personID, firstname, lnprefix, lastname, prefix, suffix, nameorder, birthdate, birthdatetr, birthplace, altbirthdate, altbirthdatetr, altbirthplace, deathdate, deathdatetr, deathplace, burialdate, burialdatetr, burialplace, burialtype, haskids, sex, living, private, branch FROM ($people_table, children) WHERE familyID = '$familyID' AND $people_table.personID = children.personID ORDER BY ordernum";

  return tng_query($query);
}

function getChildrenDataPlusDates($familyID) {
  global $people_table;

  $query = "SELECT $people_table.personID AS personID, firstname, lnprefix, lastname, prefix, suffix, nameorder, birthdate, birthdatetr, altbirthdate, altbirthdatetr, deathdate, burialdate, sex, living, private, branch, ordernum, IF(birthdate != '', YEAR(birthdatetr), YEAR(altbirthdatetr)) AS birth, IF(deathdate != '', YEAR(deathdatetr), YEAR(burialdatetr)) AS death FROM (children, $people_table) WHERE familyID = '$familyID' AND children.personID = $people_table.personID ORDER BY ordernum";

  return tng_query($query);
}

function getChildFamily($personID, $orderfield) {
  $query = "SELECT familyID FROM children WHERE personID = '$personID' ORDER BY $orderfield";

  return tng_query($query);
}

function getChildParentsFamily($personID) {
  $query = "SELECT personID, familyID, sealdate, sealdatetr, sealplace, mrel, frel FROM children WHERE personID = '$personID' ORDER BY parentorder";

  return tng_query($query);
}

function getChildParentsFamilyMinimal($personID) {
  $query = "SELECT husband, wife FROM (families, children) WHERE personID = '$personID' AND children.familyID = families.familyID";

  return tng_query($query);
}

function getParentData($familyID, $spouse) {
  global $people_table;

  $query = "SELECT personID, lastname, lnprefix, firstname, prefix, suffix, nameorder, birthdate, birthdatetr, birthplace, altbirthdate, altbirthdatetr, altbirthplace, deathdate, deathdatetr, deathplace, burialdate, burialdatetr, burialplace, burialtype, marrdate, marrplace, $people_table.living, $people_table.private, $people_table.branch, sex FROM ($people_table, families) WHERE personID = $spouse AND familyID = '$familyID'";
  return tng_query($query);
}

function getParentDataCrossPlusDates($familyID, $spouse1, $spouse1ID, $spouse2) {
  global $people_table;

  //Get opposite parent for a family plus dates
  $query = "SELECT personID, firstname, lnprefix, lastname, prefix, suffix, nameorder, sex, $people_table.living, $people_table.private, $people_table.branch, IF(birthdate != '', YEAR(birthdatetr), YEAR(altbirthdatetr)) AS birth, IF(deathdate != '', YEAR(deathdatetr), YEAR(burialdatetr)) AS death FROM (families, $people_table) WHERE $spouse1 = '$spouse1ID' AND personID = $spouse2 AND familyID = '$familyID'";

  return tng_query($query);
}

function getParentSimple($familyID, $spouse) {
  global $people_table;

  $query = "SELECT personID, lastname, lnprefix, firstname, prefix, suffix, nameorder, $people_table.living, $people_table.private, $people_table.branch FROM ($people_table, families) WHERE personID = $spouse AND familyID = '$familyID'";

  return tng_query($query);
}

function getParentSimplePlusDates($familyID, $spouse) {
  global $people_table;

  $query = "SELECT personID, lastname, lnprefix, firstname, prefix, suffix, nameorder, birthdate, YEAR(birthdatetr) AS birthyear, deathdate, YEAR(deathdatetr) AS deathyear, $people_table.living, $people_table.private, $people_table.branch FROM ($people_table, families) WHERE personID = $spouse AND familyID = '$familyID'";

  return tng_query($query);
}

function getFamilyMinimal($familyID) {
  $query = "SELECT UPPER(husband) AS husband, UPPER(wife) AS wife FROM families WHERE familyID = '$familyID'";

  return tng_query($query);
}

function getFamilyData($familyID) {
  //Get husband and wife IDs for a family
  $query = "SELECT husband, wife, living, private, branch, marrdate, marrdatetr, marrplace, divdate, divdatetr, divplace, familyID FROM families WHERE familyID = '$familyID'";

  return tng_query($query);
}

function getSpouseFamilyMinimal($spouse1, $spouse1ID, $spouseorder) {
  $query = "SELECT husband, wife, familyID FROM families WHERE $spouse1 = '$spouse1ID' ORDER BY $spouseorder";

  return tng_query($query);
}

function getSpouseFamilyMinimalUnion($spouse1ID) {
  //Get family data for a known spouse with unknown gender
  $query = "SELECT husband, wife, familyID FROM families WHERE families.wife = '$spouse1ID'
      UNION
        SELECT husband, wife, familyID FROM families WHERE families.husband = '$spouse1ID'";

  return tng_query($query);
}

function getSpouseFamilyMinimalExcept($spouse1, $spouse1ID, $spouse2, $spouse2ID) {
  // Get family ID, plus spouse ID for a known person, except the one indicated
  $query = "SELECT familyID, UPPER(husband) AS husband, UPPER(wife) AS wife FROM families WHERE $spouse1 = '$spouse1ID' AND $spouse2 != '$spouse2ID'";

  return tng_query($query);
}

function getSpouseFamilyData($spouse1, $spouse1ID, $spouseorder) {
  $query = "SELECT husband, wife, familyID, marrdate, marrplace, marrtype, divdate, divplace, living, private, branch FROM families WHERE $spouse1 = '$spouse1ID' ORDER BY $spouseorder";

  return tng_query($query);
}

function getSpouseFamilyDataUnion($spouse1ID) {
  // Get most family data for a known spouse with unknown gender
  $query = "SELECT husband, wife, familyID, marrdate, marrplace, marrtype, divdate, divplace, living, private, branch FROM families WHERE husband = '$spouse1ID'
      UNION
        SELECT husband, wife, familyID, marrdate, marrplace, marrtype, divdate, divplace, living, private, branch FROM families WHERE wife = '$spouse1ID'";

  return tng_query($query);
}

function getSpouseFamilyDataPlusDates($spouse1, $spouse1ID, $spouseorder) {
  $query = "SELECT husband, wife, familyID, marrdate, marrdatetr, marrplace, marrtype, living, private, branch, YEAR(marrdatetr) AS marryear, MONTH(marrdatetr) AS marrmonth, DAYOFMONTH(marrdatetr) AS marrday, marrplace, sealdate, sealplace FROM families WHERE $spouse1 = '$spouse1ID' ORDER BY $spouseorder";

  return tng_query($query);
}

function getSpouseFamilyDataUnionPlusDates($spouse1ID) {
  // Get most family data for a known spouse with unknown gender
  $query = "SELECT husband, wife, familyID, marrdate, marrdatetr, marrplace, marrtype, living, private, branch, YEAR(marrdatetr) AS marryear, MONTH(marrdatetr) AS marrmonth, DAYOFMONTH(marrdatetr) AS marrday, marrplace, sealdate, sealplace FROM families WHERE husband = '$spouse1ID'
      UNION
        SELECT husband, wife, familyID, marrdate, marrdatetr, marrplace, marrtype, living, private, branch, YEAR(marrdatetr) AS marryear, MONTH(marrdatetr) AS marrmonth, DAYOFMONTH(marrdatetr) AS marrday, marrplace, sealdate, sealplace FROM families WHERE wife = '$spouse1ID'";

  return tng_query($query);
}

function getSpouseFamilyFull($spouse1, $spouse1ID, $spouseorder) {
  $query = "SELECT *, DATE_FORMAT(changedate,\"%e %b %Y\") AS changedate FROM families WHERE $spouse1 = '$spouse1ID' ORDER BY $spouseorder";

  return tng_query($query);
}

function getSpouseFamilyFullUnion($spouse1ID) {
  // Get all family data for a known spouse with unknown gender
  $query = "SELECT *, DATE_FORMAT(changedate,\"%e %b %Y %H:%i:%s\") AS changedate FROM families WHERE husband = '$spouse1ID'
      UNION
        SELECT *, DATE_FORMAT(changedate,\"%e %b %Y %H:%i:%s\") AS changedate FROM families WHERE wife = '$spouse1ID'";

  return tng_query($query);
}

function getSpousesSimple($spouse1, $spouse1ID, $spouse2, $spouseorder) {
  global $people_table;

  // Get basic information for all spouses of a person (spouse1)
  $query = "SELECT UPPER($spouse2) AS $spouse2, familyID, sex FROM families LEFT JOIN $people_table ON $people_table.personID = $spouse2 WHERE $spouse1 = '$spouse1ID' ORDER BY $spouseorder";

  return tng_query($query);
}

function getPersonData($personID) {
  global $people_table;

  $query = "SELECT UPPER(personID) AS personID, firstname, lnprefix, lastname, prefix, suffix, sex, nameorder, living, private, branch, birthdate, birthdatetr, birthplace, altbirthdate, altbirthdatetr, altbirthplace, deathdate, deathdatetr, deathplace, burialdate, burialdatetr, burialplace, burialtype, famc, baptdate, baptplace, confdate, confplace, initdate, initplace, endldate, endlplace FROM $people_table WHERE personID = '$personID'";

  return tng_query($query);
}

function getPersonDataPlusDates($personID) {
  global $people_table;

  $query = "SELECT personID, firstname, lnprefix, lastname, prefix, suffix, sex, nameorder, living, private, branch, birthdate, birthdatetr, altbirthdate, altbirthdatetr, deathdate, deathdatetr, burialdate, burialdatetr, famc, IF(birthdatetr != '0000-00-00', YEAR(birthdatetr), YEAR(altbirthdatetr)) AS birth, IF(deathdatetr != '0000-00-00', YEAR(deathdatetr), YEAR(burialdatetr)) AS death FROM $people_table WHERE personID = '$personID'";

  return tng_query($query);
}

function getPersonFullPlusDates($personID) {
  global $people_table;

  $query = "SELECT *, DATE_FORMAT(changedate,\"%e %b %Y\") AS changedate, IF(birthdatetr !='0000-00-00', YEAR(birthdatetr), YEAR(altbirthdatetr)) AS birth, IF(deathdatetr !='0000-00-00', YEAR(deathdatetr), YEAR(burialdatetr)) AS death FROM $people_table WHERE personID = '$personID'";

  return tng_query($query);
}

function getPersonGender($personID) {
  global $people_table;

  $query = "SELECT sex FROM $people_table WHERE personID = '$personID'";

  return tng_query($query);
}

function getPersonSimple($personID) {
  global $people_table;

  $query = "SELECT personID, firstname, lnprefix, lastname, prefix, suffix, sex, nameorder, living, private, branch, birthdate, birthdatetr, altbirthdatetr, deathdate FROM $people_table WHERE personID = '$personID'";

  return tng_query($query);
}

function getBranchesSimple($branch) {
  $query = "SELECT description FROM branches WHERE branch = '$branch'";

  return tng_query($query);
}

function getAssociations($personID) {
  $query = "SELECT passocID, relationship, reltype FROM associations WHERE personID = '$personID'";

  return tng_query($query);
}

function getPersonEventData($personID) {
  $query = "SELECT eventID, display, eventdate, eventplace, info FROM (events, eventtypes)
      WHERE persfamID = '$personID' AND events.eventtypeID = eventtypes.eventtypeID AND keep = '1' AND parenttag = ''  ORDER BY ordernum, tag, description, eventdatetr, info, eventID";

  return tng_query($query);
}