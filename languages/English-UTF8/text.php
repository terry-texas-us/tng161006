<?php

switch ($textpart) {
  //browsesources.php, showsource.php
  case "sources":
    $text['browseallsources'] = "Browse All Sources";
    $text['shorttitle'] = "Short Title";
    $text['callnum'] = "Call Number";
    $text['author'] = "Author";
    $text['publisher'] = "Publisher";
    $text['other'] = "Other Information";
    $text['sourceid'] = "Source ID";
    $text['moresrc'] = "More sources";
    $text['repoid'] = "Repository ID";
    $text['browseallrepos'] = "Browse All Repositories";
    break;

  //changelanguage.php, savelanguage.php
  case "language":
    $text['newlanguage'] = "New language";
    $text['changelanguage'] = "Change Language";
    $text['languagesaved'] = "Language Saved";
    $text['sitemaint'] = "Site maintenance in progress";
    $text['standby'] = "The site is temporarily unavailable while we update our database. Please try again in a few minutes. If the site remains down for an extended period of time, please <a href=\"suggest.php\">contact the site owner</a>.";
    break;

  //gedcom.php, gedform.php
  case "gedcom":
    $text['gedstart'] = "GEDCOM starting from";
    $text['producegedfrom'] = "Produce a GEDCOM file from";
    $text['numgens'] = "Number of generations";
    $text['includelds'] = "Include LDS information";
    $text['buildged'] = "Build GEDCOM";
    $text['gedstartfrom'] = "GEDCOM starting from";
    $text['nomaxgen'] = "You must indicate a maximum number of generations. Please use the Back button to return to the previous page and correct the error";
    $text['gedcreatedfrom'] = "GEDCOM created from";
    $text['gedcreatedfor'] = "created for";
    $text['creategedfor'] = "Create GEDCOM";
    $text['email'] = "Your Email";
    $text['suggestchange'] = "Suggest a change";
    $text['yourname'] = "Your Name";
    $text['comments'] = "Description of<br />proposed changes";
    $text['comments2'] = "Comments";
    $text['submitsugg'] = "Submit Suggestion";
    $text['proposed'] = "Proposed Change";
    $text['mailsent'] = "Thank you. Your message has been sent.";
    $text['mailnotsent'] = "We're sorry, but your message could not be delivered. Please contact xxx directly at yyy.";
    $text['mailme'] = "Send a copy to this address";
    $text['entername'] = "Please enter your name";
    $text['entercomments'] = "Please enter your comments";
    $text['sendmsg'] = "Send Message";
    //added in 9.0.0
    $text['subject'] = "Subject";
    break;

  //getextras.php, getperson.php
  case "getperson":
    $text['photoshistoriesfor'] = "Photos and Histories for";
    $text['indinfofor'] = "Individual info for";
    $text['pp'] = "pp.";
    $text['age'] = "Age";
    $text['agency'] = "Agency";
    $text['cause'] = "Cause";
    $text['suggested'] = "Suggested";
    $text['closewindow'] = "Close this window";
    $text['thanks'] = "Thank you";
    $text['received'] = "Your suggestion was forwarded to the site administrator for review.";
    $text['indreport'] = "Individual Report";
    $text['indreportfor'] = "Individual Report for";
    $text['general'] = "General";
    $text['bkmkvis'] = "<strong>Note:</strong> These bookmarks are only visible on this computer and in this browser.";
    //added in 9.0.0
    $text['reviewmsg'] = "You have a suggested change that needs your review. This submission concerns:";
    $text['revsubject'] = "Suggested change needs your review";
    break;

  //relateform.php, relationship.php, findpersonform.php, findperson.php
  case "relate":
    $text['relcalc'] = "Relationship Calculator";
    $text['findrel'] = "Find Relationship";
    $text['person1'] = "Person 1:";
    $text['person2'] = "Person 2:";
    $text['calculate'] = "Calculate";
    $text['select2inds'] = "Please select two individuals.";
    $text['findpersonid'] = "Find Person ID";
    $text['enternamepart'] = "enter part of first and/or last name";
    $text['pleasenamepart'] = "Please enter a portion of a first or last name.";
    $text['clicktoselect'] = "click to select";
    $text['nobirthinfo'] = "No birth info";
    $text['relateto'] = "Relationship to";
    $text['sameperson'] = "The two individuals are the same person.";
    $text['notrelated'] = "The two individuals are not related within xxx generations.";
    $text['findrelinstr'] = "To display the relationship between two people, use the 'Find' buttons below to locate the individuals (or keep the people displayed), then click 'Calculate'.";
    $text['sometimes'] = "(Sometimes checking over a different number of generations yields a different result.)";
    $text['findanother'] = "Find another relationship";
    $text['brother'] = "the brother of";
    $text['sister'] = "the sister of";
    $text['sibling'] = "the sibling of";
    $text['uncle'] = "the xxx uncle of";
    $text['aunt'] = "the xxx aunt of";
    $text['uncleaunt'] = "the xxx uncle/aunt of";
    $text['nephew'] = "the xxx nephew of";
    $text['niece'] = "the xxx niece of";
    $text['nephnc'] = "the xxx nephew/niece of";
    $text['removed'] = "times removed";
    $text['rhusband'] = "the husband of ";
    $text['rwife'] = "the wife of ";
    $text['rspouse'] = "the spouse of ";
    $text['son'] = "the son of";
    $text['daughter'] = "the daughter of";
    $text['rchild'] = "the child of";
    $text['sil'] = "the son-in-law of";
    $text['dil'] = "the daughter-in-law of";
    $text['sdil'] = "the son- or daughter-in-law of";
    $text['gson'] = "the xxx grandson of";
    $text['gdau'] = "the xxx granddaughter of";
    $text['gsondau'] = "the xxx grandchild of";
    $text['great'] = "great";
    $text['spouses'] = "are spouses";
    $text['is'] = "is";
    $text['changeto'] = "Change to (enter the ID):";
    $text['notvalid'] = "is not a valid Person ID number or does not exist in this database. Please try again.";
    $text['halfbrother'] = "the half brother of";
    $text['halfsister'] = "the half sister of";
    $text['halfsibling'] = "the half sibling of";
    //changed in 8.0.0
    $text['gencheck'] = "Maximum generations to check";
    $text['mcousin'] = "the xxx cousin yyy of";
    $text['fcousin'] = "the xxx cousin yyy of";
    $text['cousin'] = "the xxx cousin yyy of";
    $text['mhalfcousin'] = "the xxx half cousin yyy of";
    $text['fhalfcousin'] = "the xxx half cousin yyy of";
    $text['halfcousin'] = "the xxx half cousin yyy of";
    //added in 8.0.0
    $text['oneremoved'] = "once removed";
    $text['gfath'] = "the xxx grandfather of";
    $text['gmoth'] = "the xxx grandmother of";
    $text['gpar'] = "the xxx grandparent of";
    $text['mothof'] = "the mother of";
    $text['fathof'] = "the father of";
    $text['parof'] = "the parent of";
    $text['maxrels'] = "Maximum relationships to show";
    $text['dospouses'] = "Show relationships involving a spouse";
    $text['rels'] = "Relationships";
    $text['dospouses2'] = "Show Spouses";
    $text['fil'] = "the father-in-law of";
    $text['mil'] = "the mother-in-law of";
    $text['fmil'] = "the father- or mother-in-law of";
    $text['stepson'] = "the stepson of";
    $text['stepdau'] = "the stepdaughter of";
    $text['stepchild'] = "the stepchild of";
    $text['stepgson'] = "the xxx step-grandson of";
    $text['stepgdau'] = "the xxx step-granddaughter of";
    $text['stepgchild'] = "the xxx step-grandchild of";
    //added in 8.1.1
    $text['ggreat'] = "great";
    //added in 8.1.2
    $text['ggfath'] = "the xxx great grandfather of";
    $text['ggmoth'] = "the xxx great grandmother of";
    $text['ggpar'] = "the xxx great grandparent of";
    $text['ggson'] = "the xxx great grandson of";
    $text['ggdau'] = "the xxx great granddaughter of";
    $text['ggsondau'] = "the xxx great grandchild of";
    $text['gstepgson'] = "the xxx great step-grandson of";
    $text['gstepgdau'] = "the xxx great step-granddaughter of";
    $text['gstepgchild'] = "the xxx great step-grandchild of";
    $text['guncle'] = "the xxx great uncle of";
    $text['gaunt'] = "the xxx great aunt of";
    $text['guncleaunt'] = "the xxx great uncle/aunt of";
    $text['gnephew'] = "the xxx great nephew of";
    $text['gniece'] = "the xxx great niece of";
    $text['gnephnc'] = "the xxx great nephew/niece of";
    break;

  case "familygroup":
    $text['familygroupfor'] = "Family Group Sheet for";
    $text['ldsords'] = "LDS Ordinances";
    $text['baptizedlds'] = "Baptized (LDS)";
    $text['endowedlds'] = "Endowed (LDS)";
    $text['sealedplds'] = "Sealed to Parents (LDS)";
    $text['sealedslds'] = "Sealed to Spouse (LDS)";
    $text['otherspouse'] = "Other Spouse";
    $text['husband'] = "Father";
    $text['wife'] = "Mother";
    break;

  //pedigree.php
  case "pedigree":
    $text['capbirthabbr'] = "B";
    $text['capaltbirthabbr'] = "A";
    $text['capdeathabbr'] = "D";
    $text['capburialabbr'] = "B";
    $text['capplaceabbr'] = "P";
    $text['capmarrabbr'] = "M";
    $text['capspouseabbr'] = "SP";
    $text['redraw'] = "Redraw with";
    $text['scrollnote'] = "Notes: You may have to scroll down or right to see chart.";
    $text['unknownlit'] = "Unknown";
    $text['popupnote1'] = "Additional information";
    $text['popupnote2'] = "New pedigree";
    $text['pedcompact'] = "Compact";
    $text['pedstandard'] = "Standard";
    $text['pedtextonly'] = "Text";
    $text['descendfor'] = "Descendancy for";
    $text['maxof'] = "Maximum of";
    $text['gensatonce'] = "generations displayed at one time.";
    $text['sonof'] = "son of";
    $text['daughterof'] = "daughter of";
    $text['childof'] = "child of";
    $text['stdformat'] = "Standard Format";
    $text['ahnentafel'] = "Ahnentafel";
    $text['addnewfam'] = "Add New Family";
    $text['editfam'] = "Edit Family";
    $text['side'] = "Side";
    $text['familyof'] = "Family of";
    $text['paternal'] = "Paternal";
    $text['maternal'] = "Maternal";
    $text['gen1'] = "Self";
    $text['gen2'] = "Parents";
    $text['gen3'] = "Grandparents";
    $text['gen4'] = "Great Grandparents";
    $text['gen5'] = "2nd Great Grandparents";
    $text['gen6'] = "3rd Great Grandparents";
    $text['gen7'] = "4th Great Grandparents";
    $text['gen8'] = "5th Great Grandparents";
    $text['gen9'] = "6th Great Grandparents";
    $text['gen10'] = "7th Great Grandparents";
    $text['gen11'] = "8th Great Grandparents";
    $text['gen12'] = "9th Great Grandparents";
    $text['graphdesc'] = "Descendancy chart to this point";
    $text['pedbox'] = "Box";
    $text['regformat'] = "Register";
    $text['extrasexpl'] = "= At least one photo, history or other media item exists for this individual.";
    $text['popupnote3'] = "New chart";
    $text['mediaavail'] = "Media Available";
    $text['pedigreefor'] = "Pedigree Chart for";
    $text['pedigreech'] = "Pedigree Chart";
    $text['datesloc'] = "Dates and Locations";
    $text['borchr'] = "Birth/Alt - Death/Burial";
    $text['nobd'] = "No Birth or Death Dates";
    $text['bcdb'] = "All Birth/Alt/Death/Burial data";
    $text['numsys'] = "Numbering System";
    $text['gennums'] = "Generation Numbers";
    $text['henrynums'] = "Henry Numbers";
    $text['abovnums'] = "d'Aboville Numbers";
    $text['devnums'] = "de Villiers Numbers";
    $text['dispopts'] = "Display Options";
    //added in 10.0.0
    $text['no_ancestors'] = "No ancestors found";
    $text['ancestor_chart'] = "Vertical ancestor chart";
    $text['opennewwindow'] = "Open in a new window";
    $text['pedvertical'] = "Vertical";
    break;

  //search.php, searchform.php
  //merged with reports and showreport in 5.0.0
  case "search":
  case "reports":
    $text['noreports'] = "No reports exist.";
    $text['reportname'] = "Report Name";
    $text['allreports'] = "All Reports";
    $text['report'] = "Report";
    $text['error'] = "Error";
    $text['reportsyntax'] = "The syntax of the query run with this report";
    $text['wasincorrect'] = "was incorrect, and as a result the report could not be run. Please contact the system administrator at";
    $text['query'] = "Query";
    $text['errormessage'] = "Error Message";
    $text['equals'] = "equals";
    $text['endswith'] = "ends with";
    $text['soundexof'] = "soundex of";
    $text['metaphoneof'] = "metaphone of";
    $text['plusminus10'] = "+/- 10 years from";
    $text['lessthan'] = "less than";
    $text['greaterthan'] = "greater than";
    $text['lessthanequal'] = "less than or equal to";
    $text['greaterthanequal'] = "greater than or equal to";
    $text['equalto'] = "equal to";
    $text['tryagain'] = "Please try again";
    $text['text_for'] = "for";
    $text['joinwith'] = "Join with";
    $text['cap_and'] = "AND";
    $text['cap_or'] = "OR";
    $text['showspouse'] = "Show spouse (will show duplicates if individual has more than one spouse)";
    $text['submitquery'] = "Submit Query";
    $text['birthplace'] = "Birth Place";
    $text['deathplace'] = "Death Place";
    $text['birthdatetr'] = "Birth Year";
    $text['deathdatetr'] = "Death Year";
    $text['plusminus2'] = "+/- 2 years from";
    $text['resetall'] = "Reset All Values";
    $text['showdeath'] = "Show death/burial information";
    $text['altbirthplace'] = "Christening Place";
    $text['altbirthdatetr'] = "Christening Year";
    $text['burialplace'] = "Burial Place";
    $text['burialdatetr'] = "Burial Year";
    $text['event'] = "Event";
    $text['day'] = "Day";
    $text['month'] = "Month";
    $text['keyword'] = "Keyword (ie, \"Abt\")";
    $text['explain'] = "Enter date components to see matching events. Leave a field blank to see matches for all.";
    $text['enterdate'] = "Please enter or select at least one of the following: Day, Month, Year, Keyword";
    $text['fullname'] = "Full Name";
    $text['birthdate'] = "Birth Date";
    $text['altbirthdate'] = "Christening Date";
    $text['marrdate'] = "Marriage Date";
    $text['spouseid'] = "Spouse ID";
    $text['spousename'] = "Spouse Name";
    $text['deathdate'] = "Death Date";
    $text['burialdate'] = "Burial Date";
    $text['changedate'] = "Last Modified Date";
    $text['gedcom'] = "Tree";
    $text['baptdate'] = "Baptism Date (LDS)";
    $text['baptplace'] = "Baptism Place (LDS)";
    $text['endldate'] = "Endowment Date (LDS)";
    $text['endlplace'] = "Endowment Place (LDS)";
    $text['ssealdate'] = "Seal Date S (LDS)";
    $text['ssealplace'] = "Seal Place S (LDS)";
    $text['psealdate'] = "Seal Date P (LDS)";
    $text['psealplace'] = "Seal Place P (LDS)";
    $text['marrplace'] = "Marriage Place";
    $text['spousesurname'] = "Spouse's Last Name";
    $text['spousemore'] = "If you enter a value for Spouse's Last Name, you must select a Gender.";
    $text['plusminus5'] = "+/- 5 years from";
    $text['exists'] = "exists.";
    $text['dnexist'] = "does not exist";
    $text['divdate'] = "Divorce Date";
    $text['divplace'] = "Divorce Place";
    $text['otherevents'] = "Other Search Criteria";
    $text['numresults'] = "Results per page";
    $text['mysphoto'] = "Mystery Photos";
    $text['mysperson'] = "Elusive People";
    $text['joinor'] = "The 'Join with OR' option cannot be used with Spouse's Last Name";
    $text['tellus'] = "Tell us what you know";
    $text['moreinfo'] = "More information";
    //added in 8.0.0
    $text['marrdatetr'] = "Marriage Year";
    $text['divdatetr'] = "Divorce Year";
    $text['mothername'] = "Mother's Name";
    $text['fathername'] = "Father's Name";
    $text['filter'] = "Filter";
    $text['more'] = "More";
    $text['notliving'] = "Not Living";
    $text['nodayevents'] = "Events for this month that are not associated with a specific day:";
    //added in 9.0.0
    $text['csv'] = "Comma-delimited CSV file";
    //added in 10.0.0
    $text['confdate'] = "Confirmation Date (LDS)";
    $text['confplace'] = "Confirmation Place (LDS)";
    $text['initdate'] = "Initiatory Date (LDS)";
    $text['initplace'] = "Initiatory Place (LDS)";
    break;

  //showlog.php
  case "showlog":
    $text['logfilefor'] = "Log file for";
    $text['mostrecentactions'] = "Most Recent Actions";
    $text['autorefresh'] = "Auto Refresh (30 seconds)";
    $text['refreshoff'] = "Turn off Auto Refresh";
    break;

  case "headstones":
  case "showphoto":
    $text['cemeteriesheadstones'] = "Cemeteries and Headstones";
    $text['showallhsr'] = "Show all headstone records";
    $text['in'] = "in";
    $text['showmap'] = "Show map";
    $text['headstonefor'] = "Headstone for";
    $text['photoof'] = "Photo of";
    $text['firstpage'] = "First Page";
    $text['lastpage'] = "Last Page";
    $text['photoowner'] = "Owner/Source";
    $text['nocemetery'] = "No Cemetery";
    $text['iptc005'] = "Title";
    $text['iptc020'] = "Supp. Categories";
    $text['iptc040'] = "Special Instructions";
    $text['iptc055'] = "Creation Date";
    $text['iptc080'] = "Author";
    $text['iptc085'] = "Author's Position";
    $text['iptc090'] = "City";
    $text['iptc095'] = "State/Province";
    $text['iptc101'] = "Country";
    $text['iptc103'] = "OTR";
    $text['iptc105'] = "Headline";
    $text['iptc110'] = "Source";
    $text['iptc115'] = "Photo Source";
    $text['iptc116'] = "Copyright Notice";
    $text['iptc120'] = "Caption";
    $text['iptc122'] = "Caption Writer";
    $text['mapof'] = "Map of";
    $text['regphotos'] = "Descriptive View";
    $text['gallery'] = "Thumbnails Only";
    $text['cemphotos'] = "Cemetery Photos";
    $text['photosize'] = "Dimensions";
    $text['iptc010'] = "Priority";
    $text['filesize'] = "File Size";
    $text['seeloc'] = "See Location";
    $text['showall'] = "Show All";
    $text['editmedia'] = "Edit Media";
    $text['viewitem'] = "View this item";
    $text['editcem'] = "Edit Cemetery";
    $text['numitems'] = "# Items";
    $text['allalbums'] = "All Albums";
    $text['slidestop'] = "Pause Slide Show";
    $text['slideresume'] = "Resume Slide Show";
    $text['slidesecs'] = "Seconds for each slide:";
    $text['minussecs'] = "minus 0.5 seconds";
    $text['plussecs'] = "plus 0.5 seconds";
    $text['nocountry'] = "Unknown country";
    $text['nostate'] = "Unknown state";
    $text['nocounty'] = "Unknown county";
    $text['nocity'] = "Unknown city";
    $text['nocemname'] = "Unknown cemetery name";
    $text['editalbum'] = "Edit Album";
    $text['mediamaptext'] = "<strong>Note:</strong> Move your mouse pointer over the image to show names. Click to see a page for each name.";
    //added in 8.0.0
    $text['allburials'] = "All Burials";
    $text['moreinfo'] = "Click for more information on this image";
    //added in 9.0.0
    $text['iptc025'] = "Keywords";
    $text['iptc092'] = "Sub-location";
    $text['iptc015'] = "Category";
    $text['iptc065'] = "Originating Program";
    $text['iptc070'] = "Program Version";
    break;

  //surnames.php, surnames100.php, surnames-all.php, surnames-oneletter.php
  case "surnames":
  case "places":
    $text['surnamesstarting'] = "Show surnames starting with";
    $text['showtop'] = "Show top";
    $text['showallsurnames'] = "Show all surnames";
    $text['sortedalpha'] = "sorted alphabetically";
    $text['byoccurrence'] = "ordered by occurrence";
    $text['firstchars'] = "First characters";
    $text['mainsurnamepage'] = "Main surname page";
    $text['allsurnames'] = "All Surnames";
    $text['showmatchingsurnames'] = "Click on a surname to show matching records.";
    $text['backtotop'] = "Back to top";
    $text['beginswith'] = "Begins with";
    $text['allbeginningwith'] = "All surnames beginning with";
    $text['numoccurrences'] = "number of total localities in parentheses";
    $text['placesstarting'] = "Show largest localities starting with";
    $text['showmatchingplaces'] = "Click on a place to show smaller localities. Click on the search icon to show matching individuals.";
    $text['totalnames'] = "total individuals";
    $text['showallplaces'] = "Show all largest localities";
    $text['totalplaces'] = "total places";
    $text['mainplacepage'] = "Main places page";
    $text['allplaces'] = "All Largest Localities";
    $text['placescont'] = "Show all places containing";
    //changed in 8.0.0
    $text['top30'] = "Top xxx surnames";
    $text['top30places'] = "Top xxx largest localities";
    break;

  //whatsnew.php
  case "whatsnew":
    $text['pastxdays'] = "(past xx days)";

    $text['photo'] = "Photo";
    $text['history'] = "History/Document";
    $text['husbid'] = "Father ID";
    $text['husbname'] = "Father's Name";
    $text['wifeid'] = "Mother ID";
    break;

  //timeline.php, timeline2.php
  case "timeline":
    $text['text_delete'] = "Delete";
    $text['addperson'] = "Add Person";
    $text['nobirth'] = "The following individual does not have a valid birth date and could not be added";
    $text['event'] = "Event(s)";
    $text['chartwidth'] = "Chart width";
    $text['timelineinstr'] = "Add People";
    $text['togglelines'] = "Toggle Lines";
    //changed in 9.0.0
    $text['noliving'] = "The following individual is flagged as living or private and could not be added because you are not logged in with proper permissions";
    break;

  //browsetrees.php
  //login.php, newacctform.php, addnewacct.php
  case "trees":
  case "login":
    $text['browsealltrees'] = "Browse All Trees";
    $text['treename'] = "Tree Name";
    $text['owner'] = "Owner";
    $text['address'] = "Address";
    $text['city'] = "City";
    $text['state'] = "State/Province";
    $text['zip'] = "Zip/Postal Code";
    $text['country'] = "Country";
    $text['email'] = "Email";
    $text['phone'] = "Phone";
    $text['username'] = "Username";
    $text['password'] = "Password";
    $text['loginfailed'] = "Login failed.";

    $text['regnewacct'] = "Register for New User Account";
    $text['realname'] = "Your Real Name";
    $text['phone'] = "Phone";
    $text['email'] = "Email";
    $text['address'] = "Address";
    $text['acctcomments'] = "Notes or Comments";
    $text['submit'] = "Submit";
    $text['leaveblank'] = "(leave blank if requesting a new tree)";
    $text['required'] = "Required fields";
    $text['enterpassword'] = "Please enter a password.";
    $text['enterusername'] = "Please enter a username.";
    $text['failure'] = "We're sorry, but the username you entered is already in use. Please use the Back button on your browser to return to the previous page and select a different username.";
    $text['success'] = "Thank you. We have received your registration. We will contact you when your account is active or if more information is needed.";
    $text['emailsubject'] = "New TNG user registration request";
    $text['website'] = "Web Site";
    $text['nologin'] = "Don't have a login?";
    $text['loginsent'] = "Login information sent";
    $text['loginnotsent'] = "Login information not sent";
    $text['enterrealname'] = "Please enter your real name.";
    $text['rempass'] = "Stay logged in on this computer";
    $text['morestats'] = "More statistics";
    $text['accmail'] = "<strong>NOTE:</strong> In order to receive mail from the site administrator regarding your account, please make sure that you are not blocking mail from this domain.";
    $text['newpassword'] = "New Password";
    $text['resetpass'] = "Reset your password";
    $text['nousers'] = "This form cannot be used until at least one user record exists. If you are the site owner, please go to Admin/Users to create your Administrator account.";
    $text['noregs'] = "We're sorry, but we are not accepting new user registrations at this time. Please <a href=\"suggest.php\">contact us</a> directly if you have comments or questions regarding anything on this site.";
    //changed in 8.0.0
    $text['emailmsg'] = "You have received a new request for a TNG user account. Please log into your TNG Admin area and assign proper permissions to this new account.";
    $text['accactive'] = "The account has been activated, but the user will have no special rights until you assign them.";
    $text['accinactive'] = "Go to Admin/Users/Review to access the account settings. The account will remain inactive until you edit and save the record at least once.";
    $text['pwdagain'] = "Password again";
    $text['enterpassword2'] = "Please enter your password again.";
    $text['pwdsmatch'] = "Your passwords do not match. Please enter the same password in each field.";
    //added in 8.0.0
    $text['acksubject'] = "Thank you for registering";
    $text['ackmessage'] = "Your request for a user account has been received. Your account will be inactive until it has been reviewed by the site administrator. You will be notified by email when your login is ready for use.";
    break;

  //added in 10.0.0
  case "branches":
    $text['browseallbranches'] = "Browse All Branches";
    break;

  //statistics.php
  case "stats":
    $text['quantity'] = "Quantity";
    $text['totindividuals'] = "Total Individuals";
    $text['totmales'] = "Total Males";
    $text['totfemales'] = "Total Females";
    $text['totunknown'] = "Total Unknown Gender";
    $text['totliving'] = "Total Living";
    $text['totfamilies'] = "Total Families";
    $text['totuniquesn'] = "Total Unique Surnames";
    //$text['totphotos'] = "Total Photos";
    //$text['totdocs'] = "Total Histories &amp; Documents";
    //$text['totheadstones'] = "Total Headstones";
    $text['totsources'] = "Total Sources";
    $text['avglifespan'] = "Average Lifespan";
    $text['earliestbirth'] = "Earliest Birth";
    $text['longestlived'] = "Longest Lived";
    $text['days'] = "days";
    $text['age'] = "Age";
    $text['agedisclaimer'] = "Age-related calculations are based on individuals with recorded birth <em>and</em> death dates.  Due to the existence of incomplete date fields(e.g., a death date listed only as \"1945\" or \"BEF 1860\"), these calculations cannot be 100% accurate.";
    $text['treedetail'] = "More information on this tree";
    $text['total'] = "Total";
    break;

  case "notes":
    $text['browseallnotes'] = "Browse All Notes";
    break;

  case "help":
    $text['menuhelp'] = "Menu Key";
    break;

  case "install":
    $text['perms'] = "Permissions have all been set.";
    $text['noperms'] = "Permissions could not be set for these files:";
    $text['manual'] = "Please set them manually.";
    $text['folder'] = "Folder";
    $text['created'] = "has been created";
    $text['nocreate'] = "could not be created. Please create it manually.";
    $text['infosaved'] = "Information saved, connection verified!";
    $text['tablescr'] = "The tables have been created!";
    $text['notables'] = "The following tables could not be created:";
    $text['nocomm'] = "TNG is not communicating with your database. No tables were created.";
    $text['newdb'] = "Information saved, connection verified, new database created:";
    $text['noattach'] = "Information saved. Connection made and database created, but TNG cannot attach to it.";
    $text['nodb'] = "Information saved. Connection made, but database does not exist and could not be created here. Please verify that the database name is correct, and that the database user has proper access, or use your control panel to create it.";
    $text['noconn'] = "Information saved but connection failed. One or more of the following is incorrect:";
    $text['exists'] = "already exists.";
    $text['loginfirst'] = "You must log in first.";
    $text['noop'] = "No operation was performed.";
    //added in 8.0.0
    $text['nouser'] = "User was not created. Username may already exist.";
    $text['notree'] = "Tree was not created. Tree ID may already exist.";
    $text['infosaved2'] = "Information saved";
    $text['renamedto'] = "renamed to";
    $text['norename'] = "could not be renamed";
    break;

  case "imgviewer":
    $text['zoomin'] = "Zoom In";
    $text['zoomout'] = "Zoom Out";
    $text['magmode'] = "Magnify Mode";
    $text['panmode'] = "Pan Mode";
    $text['pan'] = "Click and drag to move within the image";
    $text['fitwidth'] = "Fit Width";
    $text['fitheight'] = "Fit Height";
    $text['newwin'] = "New Window";
    $text['opennw'] = "Open image in a new window";
    $text['magnifyreg'] = "Click to magnify a region of the image";
    $text['imgctrls'] = "Enable Image Controls";
    $text['vwrctrls'] = "Enable Image Viewer Controls";
    $text['vwrclose'] = "Close Image Viewer";
    break;
}

//common
$text['matches'] = "Matches";
$text['description'] = "Description";
$text['notes'] = "Notes";
$text['status'] = "Status";
$text['newsearch'] = "New Search";
$text['pedigree'] = "Pedigree";
$text['birthabbr'] = "b.";
$text['chrabbr'] = "c.";
$text['seephoto'] = "See photo";
$text['andlocation'] = "&amp; location";
$text['accessedby'] = "accessed by";
$text['family'] = "Family";
$text['children'] = "Children";
$text['tree'] = "Tree";
$text['alltrees'] = "All Trees";
$text['nosurname'] = "[no surname]";
$text['thumb'] = "Thumb";
$text['people'] = "People";
$text['title'] = "Title";
$text['suffix'] = "Suffix";
$text['nickname'] = "Nickname";
$text['deathabbr'] = "d.";
$text['lastmodified'] = "Last Modified";
$text['married'] = "Married";
//$text['photos'] = "Photos";
$text['name'] = "Name";
$text['lastfirst'] = "Last Name, Given Name(s)";
$text['bornchr'] = "Born/Christened";
$text['individuals'] = "Individuals";
$text['families'] = "Families";
$text['personid'] = "Person ID";
$text['sources'] = "Sources";
$text['unknown'] = "Unknown";
$text['father'] = "Father";
$text['mother'] = "Mother";
$text['christened'] = "Christened";
$text['died'] = "Died";
$text['buried'] = "Buried";
$text['spouse'] = "Spouse";
$text['parents'] = "Parents";
$text['text'] = "Text";
$text['language'] = "Language";
$text['burialabbr'] = "bur.";
$text['descendchart'] = "Descendancy";
$text['extractgedcom'] = "GEDCOM";
$text['indinfo'] = "Individual";
$text['edit'] = "Edit";
$text['date'] = "Date";
$text['place'] = "Place";
$text['login'] = "Login";
$text['logout'] = "Logout";
$text['marrabbr'] = "m.";
$text['groupsheet'] = "Group Sheet";
$text['text_and'] = "and";
$text['generation'] = "Generation";
$text['filename'] = "File name";
$text['id'] = "ID";
$text['search'] = "Search";
$text['user'] = "User";
$text['firstname'] = "First Name";
$text['lastname'] = "Last Name";
$text['searchresults'] = "Search Results";
$text['diedburied'] = "Died/Buried";
$text['homepage'] = "Home";
$text['find'] = "Find...";
$text['relationship'] = "Relationship";
$text['relationship2'] = "Relationship";
$text['timeline'] = "Timeline";
$text['yesabbr'] = "Y";
$text['divorced'] = "Divorced";
$text['indlinked'] = "Linked to";
$text['branch'] = "Branch";
$text['moreind'] = "More individuals";
$text['morefam'] = "More families";
$text['source'] = "Source";
$text['surnamelist'] = "Surname List";
$text['generations'] = "Generations";
$text['refresh'] = "Refresh";
$text['whatsnew'] = "What's New";
$text['reports'] = "Reports";
$text['placelist'] = "Place List";
$text['baptizedlds'] = "Baptized (LDS)";
$text['endowedlds'] = "Endowed (LDS)";
$text['sealedplds'] = "Sealed P (LDS)";
$text['sealedslds'] = "Sealed S (LDS)";
//$text['photoshistories'] = "Photos &amp; Histories";
$text['ancestors'] = "Ancestors";
$text['descendants'] = "Descendants";
//$text['sex'] = "Sex";
$text['lastimportdate'] = "Date of Last GEDCOM Import";
$text['type'] = "Type";
$text['savechanges'] = "Save Changes";
$text['familyid'] = "Family ID";
$text['headstone'] = "Headstones";
$text['historiesdocs'] = "Histories";
$text['anonymous'] = "anonymous";
$text['places'] = "Places";
$text['anniversaries'] = "Dates and Anniversaries";
$text['administration'] = "Administration";
$text['help'] = "Help";
//$text['documents'] = "Documents";
$text['year'] = "Year";
$text['all'] = "All";
$text['repository'] = "Repository";
$text['address'] = "Address";
$text['suggest'] = "Suggest";
$text['editevent'] = "Suggest a change for this event";
$text['findplaces'] = "Find all individuals with events at this location";
$text['morelinks'] = "More Links";
$text['faminfo'] = "Family Information";
$text['persinfo'] = "Personal Information";
$text['srcinfo'] = "Source Information";
$text['fact'] = "Fact";
$text['goto'] = "Select a page";
$text['tngprint'] = "Print";
$text['databasestatistics'] = "Statistics";
$text['child'] = "Child";
$text['repoinfo'] = "Repository Information";
$text['tng_reset'] = "Reset";
$text['noresults'] = "No results found";
$text['allmedia'] = "All Media";
$text['repositories'] = "Repositories";
$text['albums'] = "Albums";
$text['cemeteries'] = "Cemeteries";
$text['surnames'] = "Surnames";
$text['dates'] = "Dates";
$text['link'] = "Link";
$text['media'] = "Media";
$text['gender'] = "Gender";
$text['latitude'] = "Latitude";
$text['longitude'] = "Longitude";
$text['bookmarks'] = "Bookmarks";
$text['bookmark'] = "Bookmark";
$text['mngbookmarks'] = "Go to Bookmarks";
$text['bookmarked'] = "Bookmark Added";
$text['remove'] = "Remove";
$text['find_menu'] = "Find";
$text['info'] = "Info";
$text['cemetery'] = "Cemetery";
$text['gmapevent'] = "Event Map";
$text['gevents'] = "Event";
$text['glang'] = "&amp;hl=en";
$text['googleearthlink'] = "Link to Google Earth";
$text['googlemaplink'] = "Link to Google Maps";
$text['gmaplegend'] = "Pin Legend";
$text['unmarked'] = "Unmarked";
$text['located'] = "Located";
$text['albclicksee'] = "Click to see all the items in this album";
$text['notyetlocated'] = "Not yet located";
$text['cremated'] = "Cremated";
$text['missing'] = "Missing";
$text['pdfgen'] = "PDF Generator";
$text['blank'] = "Blank Chart";
$text['none'] = "None";
$text['fonts'] = "Fonts";
$text['header'] = "Header";
$text['data'] = "Data";
$text['pgsetup'] = "Page Setup";
$text['pgsize'] = "Page Size";
$text['orient'] = "Orientation";
$text['portrait'] = "Portrait";
$text['landscape'] = "Landscape";
$text['tmargin'] = "Top Margin";
$text['bmargin'] = "Bottom Margin";
$text['lmargin'] = "Left Margin";
$text['rmargin'] = "Right Margin";
$text['createch'] = "Create Chart";
$text['prefix'] = "Prefix";
$text['mostwanted'] = "Most Wanted";
$text['latupdates'] = "Latest Updates";
$text['featphoto'] = "Featured Photo";
$text['news'] = "News";
$text['ourhist'] = "Our Family History";
$text['ourhistanc'] = "Our Family History and Ancestry";
$text['ourpages'] = "Our Family Genealogy Pages";
$text['pwrdby'] = "This site powered by";
$text['writby'] = "written by";
$text['searchtngnet'] = "Search TNG Network (GENDEX)";
$text['viewphotos'] = "View all photos";
$text['anon'] = "You are currently anonymous";
$text['whichbranch'] = "Which branch are you from?";
$text['featarts'] = "Feature Articles";
$text['maintby'] = "Maintained by";
$text['createdon'] = "Created on";
$text['reliability'] = "Reliability";
$text['labels'] = "Labels";
$text['inclsrcs'] = "Include Sources";
$text['cont'] = "(cont.)";
$text['mnuheader'] = "Home Page";
$text['mnusearchfornames'] = "Search";
$text['mnulastname'] = "Last Name";
$text['mnufirstname'] = "First Name";
$text['mnusearch'] = "Search";
$text['mnureset'] = "Start Over";
$text['mnulogon'] = "Log In";
$text['mnulogout'] = "Log Out";
$text['mnufeatures'] = "Other Features";
$text['mnuregister'] = "Register for a User Account";
$text['mnuadvancedsearch'] = "Advanced Search";
$text['mnulastnames'] = "Surnames";
$text['mnustatistics'] = "Statistics";
$text['mnuphotos'] = "Photos";
$text['mnuhistories'] = "Histories";
$text['mnumyancestors'] = "Photos &amp; Histories for Ancestors of [Person]";
$text['mnucemeteries'] = "Cemeteries";
$text['mnutombstones'] = "Headstones";
$text['mnureports'] = "Reports";
$text['mnusources'] = "Sources";
$text['mnuwhatsnew'] = "What's New";
$text['mnushowlog'] = "Access Log";
$text['mnulanguage'] = "Change Language";
$text['mnuadmin'] = "Administration";
$text['welcome'] = "Welcome";
$text['contactus'] = "Contact Us";
//changed in 8.0.0
$text['born'] = "Born";
$text['searchnames'] = "Search People";
//added in 8.0.0
$text['editperson'] = "Edit Person";
$text['loadmap'] = "Load the map";
$text['birth'] = "Birth";
$text['wasborn'] = "was born";
$text['startnum'] = "First Number";
$text['searching'] = "Searching";
//moved here in 8.0.0
$text['location'] = "Location";
$text['association'] = "Association";
$text['collapse'] = "Collapse";
$text['expand'] = "Expand";
$text['plot'] = "Plot";
$text['searchfams'] = "Search Families";
//added in 8.0.2
$text['wasmarried'] = "Married";
$text['anddied'] = "Died";
//added in 9.0.0
$text['share'] = "Share";
$text['hide'] = "Hide";
$text['disabled'] = "Your user account has been disabled. Please contact the site administrator for more information.";
$text['contactus_long'] = "If you have any questions or comments about the information on this site, please <span class=\"emphasis\"><a href=\"suggest.php\">contact us</a></span>. We look forward to hearing from you.";
$text['features'] = "Features";
$text['resources'] = "Resources";
$text['latestnews'] = "Latest News";
$text['trees'] = "Trees";
$text['wasburied'] = "was buried";
//moved here in 9.0.0
$text['emailagain'] = "Email again";
$text['enteremail2'] = "Please enter your email address again.";
$text['emailsmatch'] = "Your emails do not match. Please enter the same email address in each field.";
$text['getdirections'] = "Click to get directions";
$text['calendar'] = "Calendar";
//changed in 9.0.0
$text['directionsto'] = " to ";
$text['slidestart'] = "Slide Show";
$text['livingnote'] = "At least one living or private individual is linked to this note - Details withheld.";
$text['livingphoto'] = "At least one living or private individual is linked to this item - Details withheld.";
$text['waschristened'] = "was christened";
//added in 10.0.0
$text['branches'] = "Branches";
$text['detail'] = "Detail";
$text['moredetail'] = "More detail";
$text['lessdetail'] = "Less detail";
$text['otherevents'] = "Other Events";
$text['conflds'] = "Confirmed (LDS)";
$text['initlds'] = "Initiatory (LDS)";
$text['wascremated'] = "was cremated";

include_once("captcha_text.php");
include_once("alltext.php");
if (!$alltextloaded)
  getAllTextPath();
