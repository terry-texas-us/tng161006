<?php

switch ($textpart) {
  //browsesources.php, showsource.php
  case "sources":
    $text['browseallsources'] = "Alle Quellen anzeigen";
    $text['shorttitle'] = "Kurztitel";
    $text['callnum'] = "Signatur";
    $text['author'] = "Autor";
    $text['publisher'] = "Ver�ffentlicht durch";
    $text['other'] = "Zus�tzliche Angaben";
    $text['sourceid'] = "Quellen-Kennung";
    $text['moresrc'] = "Weitere Quellen";
    $text['repoid'] = "Aufbewahrungs-Kennung";
    $text['browseallrepos'] = "Alle Aufbewahrungsorte durchbl�ttern";
    break;

  //changelanguage.php, savelanguage.php
  case "language":
    $text['newlanguage'] = "Neue Sprache";
    $text['changelanguage'] = "Sprache �ndern";
    $text['languagesaved'] = "Sprache gespeichert";
    $text['sitemaint'] = "Momentan werden auf dieser Website Wartungsarbeiten durchgef�hrt";
    $text['standby'] = "Diese Website ist zeitweilig nicht verf�gbar, da eine Datenbank-Aktualisierung l�uft. Bitte versuchen Sie es in einigen Minuten nochmals. Falls diese Website f�r l�ngere Zeit nicht verf�gbar bleibt, so <a href=\"suggest.php\">wenden Sie sich bitte an den Verwalter</a>.";
    break;

  //gedcom.php, gedform.php
  case "gedcom":
    $text['gedstart'] = "GEDCOM-Datei startet bei";
    $text['producegedfrom'] = "GEDCOM-Datei erzeugen ab";
    $text['numgens'] = "Anzahl Generationen";
    $text['includelds'] = "einschlie�lich LDS-Angaben";
    $text['buildged'] = "Erzeuge GEDCOM-Datei";
    $text['gedstartfrom'] = "GEDCOM-Datei beginnt mit";
    $text['nomaxgen'] = "Sie m�ssen die maximale Zahl der Generationen angeben. Bitte mit 'Zur�ck' zur vorangehenden Seite und den Fehler beheben";
    $text['gedcreatedfrom'] = "GEDCOM-Datei erstellt ab";
    $text['gedcreatedfor'] = "Erstellt f�r";
    $text['creategedfor'] = "GEDCOM-Datei erzeugen";
    $text['email'] = "E-Mail";
    $text['suggestchange'] = "�nderungsvorschlag f�r";
    $text['yourname'] = "Ihr Name";
    $text['comments'] = "Notiz oder Kommentar";
    $text['comments2'] = "Ihre Mitteilung";
    $text['submitsugg'] = "Absenden";
    $text['proposed'] = "Vorgeschlagene �nderung";
    $text['mailsent'] = "Ihre Mitteilung wurde abgeschickt. Vielen Dank.";
    $text['mailnotsent'] = "Ihre Mitteilung konnte nicht gesendet werden. Bitte wenden Sie sich an xxx (E-Mail: yyy).";
    $text['mailme'] = "Kopie an diese Adresse senden";
    $text['entername'] = "Bitte geben Sie Ihren Namen ein";
    $text['entercomments'] = "Bitte geben Sie Ihre Mitteilung ein";
    $text['sendmsg'] = "Nachricht absenden";
    //added in 9.0.0
    $text['subject'] = "Titel";
    break;

  //getextras.php, getperson.php
  case "getperson":
    $text['photoshistoriesfor'] = "Fotos und Geschichten von";
    $text['indinfofor'] = "Individuelle Angaben �ber";
    $text['pp'] = "S.";
    $text['age'] = "Alter";
    $text['agency'] = "Stelle";
    $text['cause'] = "Ursache";
    $text['suggested'] = "Vorgeschlagene �nderung";
    $text['closewindow'] = "Fenster schlie�en";
    $text['thanks'] = "Vielen Dank";
    $text['received'] = "Ihre Anmerkung wurde zur �berpr�fung an den Verwalter dieser Website gesendet.";
    $text['indreport'] = "Personen-Datenblatt";
    $text['indreportfor'] = "Personen-Datenblatt f�r";
    $text['general'] = "Allgemein";
    $text['bkmkvis'] = "<strong>Hinweis:</strong> Diese Lesezeichen sind nur auf diesem Rechner und nur mit diesem Browser sichtbar.";
    //added in 9.0.0
    $text['reviewmsg'] = "Sie haben einen �nderungsvorschlag erhalten, der Ihre �berpr�fung ben�tigt. Dieser Vorschlag betrifft:";
    $text['revsubject'] = "Ein �nderungsvorschlag ben�tigt Ihre �berpr�fung";
    break;

  //relateform.php, relationship.php, findpersonform.php, findperson.php
  case "relate":
    $text['relcalc'] = "Verwandtschaftsrechner";
    $text['findrel'] = "Verwandtschaftsbeziehung darstellen";
    $text['person1'] = "Person 1:";
    $text['person2'] = "Person 2:";
    $text['calculate'] = "Berechnen";
    $text['select2inds'] = "Bitte zwei Personen ausw�hlen.";
    $text['findpersonid'] = "Suche Personen-Kennung";
    $text['enternamepart'] = "Tragen Sie einen Teil des Vor- oder Nachnamens ein";
    $text['pleasenamepart'] = "Bitte tragen Sie einen Teil des Vor- oder Nachnamens ein.";
    $text['clicktoselect'] = "Klicken Sie einen Eintrag an, um ihn auszuw�hlen";
    $text['nobirthinfo'] = "Keine Geburts-Angaben";
    $text['relateto'] = "Verwandtschaftsbeziehung mit";
    $text['sameperson'] = "Die zwei Personen sind identisch.";
    $text['notrelated'] = "Die zwei Personen sind nicht innerhalb von xxx Generationen verwandt.";
    $text['findrelinstr'] = "Personen-Kennungen eingeben (oder angezeigte belassen), dann auf 'Berechnen' klicken, um die Verwandtschaftsbeziehung darzustellen.";
    $text['sometimes'] = " (Eine unterschiedliche Anzahl der zu ber�cksichtigenden Generationen kann manchmal zu unterschiedlichen Ergebnissen f�hren.)";
    $text['findanother'] = "Eine andere Verwandtschaftsbeziehung suchen";
    $text['brother'] = "der Bruder von";
    $text['sister'] = "die Schwester von";
    $text['sibling'] = "der Bruder/die Schwester von";
    $text['uncle'] = "der xxx Onkel von";
    $text['aunt'] = "die xxx Tante von";
    $text['uncleaunt'] = "der xxx Onkel/die xxx Tante von";
    $text['nephew'] = "der xxx Neffe von";
    $text['niece'] = "die xxx Nichte von";
    $text['nephnc'] = "der xxx Neffe/die xxx Nichte von";
    $text['removed'] = "fach entfernt";
    $text['rhusband'] = "der/dem Ehemann von ";
    $text['rwife'] = "die/der Ehefrau von ";
    $text['rspouse'] = "der Ehemann/die Ehefrau von ";
    $text['son'] = "der Sohn von";
    $text['daughter'] = "die Tochter von";
    $text['rchild'] = "das Kind von";
    $text['sil'] = "der Schwiegersohn von";
    $text['dil'] = "die Schwiegertochter von";
    $text['sdil'] = "der Schwiegersohn/die Schwiegertochter von";
    $text['gson'] = "der xxx Enkel von";
    $text['gdau'] = "die xxx Enkelin von";
    $text['gsondau'] = "der xxx Enkel/die xxx Enkelin von";
    $text['great'] = "Gro�-";
    $text['spouses'] = "sind Ehepartner";
    $text['is'] = "ist";
    $text['changeto'] = "�ndere zu:";
    $text['notvalid'] = "ist keine g�ltige Personen-Kennung oder existiert nicht in dieser Datenbank. Bitte nochmals versuchen.";
    $text['halfbrother'] = "der Halbbruder von";
    $text['halfsister'] = "die Halbschwester von";
    $text['halfsibling'] = "Halbgeschwister von";
    //changed in 8.0.0
    $text['gencheck'] = "Maximale Anzahl der<br />zu ber�cksichtigenden Generationen";
    $text['mcousin'] = "der xxx Cousin (Vetter) yyy von";
    $text['fcousin'] = "die xxx Cousine (Base) yyy von";
    $text['cousin'] = "der xxx Cousin (Vetter)/die xxx Cousine (Base) yyy von";
    $text['mhalfcousin'] = "der xxx Halb-Cousin (Halb-Vetter) yyy von";
    $text['fhalfcousin'] = "die xxx Halb-Cousine (Halb-Base) yyy von";
    $text['halfcousin'] = "xxx Halb-Cousin/Halb-Cousine (Halb-Vetter/Halb-Base) yyy von";
    //added in 8.0.0
    $text['oneremoved'] = "einfach entfernt";
    $text['gfath'] = "der xxx Gro�vater von";
    $text['gmoth'] = "die xxx Gro�mutter von";
    $text['gpar'] = "die xxx Gro�eltern von";
    $text['mothof'] = "die Mutter von";
    $text['fathof'] = "der Vater von";
    $text['parof'] = "die Eltern von";
    $text['maxrels'] = "Max. Anzahl der Beziehungen";
    $text['dospouses'] = "Auch Beziehungen �ber einen Ehepartner anzeigen";
    $text['rels'] = "Anzahl Beziehungen";
    $text['dospouses2'] = "Auch Beziehungen �ber Partner anzeigen";
    $text['fil'] = "der Schwiegervater von";
    $text['mil'] = "die Schwiegermutter von";
    $text['fmil'] = "der Schwiegervater oder die Schwiegermutter von";
    $text['stepson'] = "der Stiefsohn von";
    $text['stepdau'] = "die Stieftochter von";
    $text['stepchild'] = "das Stiefkind von";
    $text['stepgson'] = "der xxx Stiefenkel von";
    $text['stepgdau'] = "die xxx Stiefenkelin von";
    $text['stepgchild'] = "das xxx Stiefenkelkind von";
    //added in 8.1.1
    $text['ggreat'] = "Ur-";
    //added in 8.1.2
    $text['ggfath'] = "der xxx Urgro�vater von";
    $text['ggmoth'] = "die xxx Urgro�mutter von";
    $text['ggpar'] = "die xxx Urgro�eltern von";
    $text['ggson'] = "der xxx Urenkel von";
    $text['ggdau'] = "die xxx Urenkelin von";
    $text['ggsondau'] = "das xxx Urenkelkind";
    $text['gstepgson'] = "der xxx Stiefurenkel von";
    $text['gstepgdau'] = "die xxx Stiefurenkelin von";
    $text['gstepgchild'] = "das xxx Stiefurenkelkind von";
    $text['guncle'] = "der xxx Gro�onkel von";
    $text['gaunt'] = "die xxx Gro�tante von";
    $text['guncleaunt'] = "der/die xxx Gro�onkel/Gro�tante von";
    $text['gnephew'] = "der xxx Gro�neffe von";
    $text['gniece'] = "die xxx Gro�nichte von";
    $text['gnephnc'] = "der/die xxx Gro�neffe/Gro�nichte von";
    break;

  case "familygroup":
    $text['familygroupfor'] = "Familienblatt von";
    $text['ldsords'] = "LDS Anordnungen";
    $text['baptizedlds'] = "Getauft (LDS)";
    $text['endowedlds'] = "Begabung (LDS)";
    $text['sealedplds'] = "Siegelung an die Eltern (LDS)";
    $text['sealedslds'] = "Siegelung an den Ehepartner (LDS)";
    $text['otherspouse'] = "Andere Ehepartner";
    $text['husband'] = "Vater";
    $text['wife'] = "Mutter";
    break;

  //pedigree.php
  case "pedigree":
    $text['capbirthabbr'] = "*";
    $text['capaltbirthabbr'] = "in";
    $text['capdeathabbr'] = "+";
    $text['capburialabbr'] = "begr.";
    $text['capplaceabbr'] = "in";
    $text['capmarrabbr'] = "oo";
    $text['capspouseabbr'] = "Gatt.";
    $text['redraw'] = "Neu zeichnen mit";
    $text['scrollnote'] = "Hinweis: Evtl. m�ssen Sie nach unten oder nach rechts scrollen, um alles sehen zu k�nnen.";
    $text['unknownlit'] = "Unbekannt";
    $text['popupnote1'] = " = Zusatz-Angaben";
    $text['popupnote2'] = " = neuen Stammbaum zeigen";
    $text['pedcompact'] = "Kompakt";
    $text['pedstandard'] = "Standard";
    $text['pedtextonly'] = "Nur Text";
    $text['descendfor'] = "Nachkommen von";
    $text['maxof'] = "Maximum";
    $text['gensatonce'] = "Generationen gleichzeitig anzeigen.";
    $text['sonof'] = "Sohn von";
    $text['daughterof'] = "Tochter von";
    $text['childof'] = "Kind von";
    $text['stdformat'] = "Standardformat";
    $text['ahnentafel'] = "Ahnenliste";
    $text['addnewfam'] = "Neue Familie anlegen";
    $text['editfam'] = "Familie bearbeiten";
    $text['side'] = "-Seite";
    $text['familyof'] = "Familie von";
    $text['paternal'] = "V�terlicherseits";
    $text['maternal'] = "M�tterlicherseits";
    $text['gen1'] = "Selbst";
    $text['gen2'] = "Eltern";
    $text['gen3'] = "Gro�eltern";
    $text['gen4'] = "Urgro�eltern";
    $text['gen5'] = "Alteltern";
    $text['gen6'] = "Altgro�eltern";
    $text['gen7'] = "Alturgro�eltern";
    $text['gen8'] = "Obereltern";
    $text['gen9'] = "Obergro�eltern";
    $text['gen10'] = "Oberurgro�eltern";
    $text['gen11'] = "Stammeltern";
    $text['gen12'] = "Stammgro�eltern";
    $text['graphdesc'] = "Graphische Anzeige der Nachkommen";
    $text['pedbox'] = "Rahmen";
    $text['regformat'] = "Registerformat";
    $text['extrasexpl'] = "Falls f�r die folgenden Personen Fotos oder Geschichten vorhanden sind, werden die entsprechenden Vorschaubilder bei den Namen angezeigt.";
    $text['popupnote3'] = " = Neues Diagramm";
    $text['mediaavail'] = "Medien verf�gbar";
    $text['pedigreefor'] = "Ahnentafel f�r";
    $text['pedigreech'] = "Ahnentafel";
    $text['datesloc'] = "Daten und Orte";
    $text['borchr'] = "Geburt/Taufe - Tod/Beerdigung (zwei)";
    $text['nobd'] = "Keine Angaben zu Geburt oder Tod";
    $text['bcdb'] = "Geburt/Taufe/Tod/Beerdigung (vier)";
    $text['numsys'] = "Nummerierungs-System";
    $text['gennums'] = "Generations-Nummern";
    $text['henrynums'] = "Nummerierung nach Henry";
    $text['abovnums'] = "Nummerierung nach d'Aboville";
    $text['devnums'] = "Nummerierung nach de Villiers";
    $text['dispopts'] = "Anzeige-Optionen";
    //added in 10.0.0
    $text['no_ancestors'] = "Keine Vorfahren gefunden";
    $text['ancestor_chart'] = "Vertikale Ahnentafel";
    $text['opennewwindow'] = "In einem neuen Fenster �ffnen";
    $text['pedvertical'] = "Vertikal";
    break;

  //search.php, searchform.php
  //merged with reports and showreport in 5.0.0
  case "search":
  case "reports":
    $text['noreports'] = "Es sind keine Berichte vorhanden.";
    $text['reportname'] = "Name des Bericht";
    $text['allreports'] = "Alle Berichte";
    $text['report'] = "Bericht";
    $text['error'] = "Fehler";
    $text['reportsyntax'] = "Die Syntax der Suchabfrage f�r diesen Bericht";
    $text['wasincorrect'] = "ist ung�ltig, deswegen kann dieser Bericht nicht erstellt werden. Benachrichtigen Sie den Systemverantwortlichen";
    $text['query'] = "Suchabfrage";
    $text['errormessage'] = "Fehlermeldung";
    $text['equals'] = "ist gleich";
    $text['endswith'] = "endet auf";
    $text['soundexof'] = "soundex von";
    $text['metaphoneof'] = "metafon von";
    $text['plusminus10'] = "+/- 10 Jahre von";
    $text['lessthan'] = "kleiner als";
    $text['greaterthan'] = "gr��er als";
    $text['lessthanequal'] = "kleiner oder gleich";
    $text['greaterthanequal'] = "gr��er oder gleich";
    $text['equalto'] = "ist gleich";
    $text['tryagain'] = "Bitte erneut versuchen";
    $text['text_for'] = "f�r";
    $text['joinwith'] = "Verkn�pfen mit";
    $text['cap_and'] = "UND";
    $text['cap_or'] = "ODER";
    $text['showspouse'] = "Zeige Partner. Dubletten werden gezeigt, wenn eine Person mehrere Partner hat";
    $text['submitquery'] = "Suche";
    $text['birthplace'] = "Geburtsort";
    $text['deathplace'] = "Sterbeort";
    $text['birthdatetr'] = "Geburtsjahr";
    $text['deathdatetr'] = "Sterbejahr";
    $text['plusminus2'] = "+/- 2 Jahre von";
    $text['resetall'] = "Alle Werte zur�cksetzen";
    $text['showdeath'] = "Zeige Todestag/Beerdigungsangaben";
    $text['altbirthplace'] = "Ort der Taufe";
    $text['altbirthdatetr'] = "Jahr der Taufe";
    $text['burialplace'] = "Ort der Beerdigung";
    $text['burialdatetr'] = "Jahr der Beerdigung";
    $text['event'] = "Ereignis(se)";
    $text['day'] = "Tag";
    $text['month'] = "Monat";
    $text['keyword'] = "Suchwort (z.B. \"ABT\", \"BEF\", \"AFT\")";
    $text['explain'] = "Datum oder Datumsteile eingeben, um passende Ereignisse zu erhalten. Oder Feld leerlassen, um alle Ereignisse zu erhalten.";
    $text['enterdate'] = "Bitte mindestens eines der folgenden eingeben oder ausw�hlen: Tag, Monat, Jahr, Suchwort";
    $text['fullname'] = "Vollst�ndiger Name";
    $text['birthdate'] = "Geburtsdatum";
    $text['altbirthdate'] = "Taufdatum";
    $text['marrdate'] = "Heiratsdatum";
    $text['spouseid'] = "Partner-Kennung";
    $text['spousename'] = "Partner-Name";
    $text['deathdate'] = "Sterbedatum";
    $text['burialdate'] = "Beerdigungsdatum";
    $text['changedate'] = "Datum der letzten �nderung";
    $text['gedcom'] = "Stammbaum";
    $text['baptdate'] = "Datum der Taufe (LDS)";
    $text['baptplace'] = "Ort der Taufe (LDS)";
    $text['endldate'] = "Datum der Begabung (LDS)";
    $text['endlplace'] = "Ort der Begabung (LDS)";
    $text['ssealdate'] = "Datum der Siegelung an den Ehepartner (LDS)";
    $text['ssealplace'] = "Ort der Siegelung an den Ehepartner (LDS)";
    $text['psealdate'] = "Datum der Siegelung an die Eltern (LDS)";
    $text['psealplace'] = "Ort der Siegelung an die Eltern (LDS)";
    $text['marrplace'] = "Heiratsort";
    $text['spousesurname'] = "Nachname des Partners";
    $text['spousemore'] = "Wenn Sie einen Partner-Nachnamen eingeben, m�ssen Sie das Geschlecht der gesuchten Person ausw�hlen.";
    $text['plusminus5'] = "+/- 5 Jahre von";
    $text['exists'] = "ist vorhanden";
    $text['dnexist'] = "ist nicht vorhanden";
    $text['divdate'] = "Scheidungsdatum";
    $text['divplace'] = "Scheidungsort";
    $text['otherevents'] = "Weitere Ereignisse";
    $text['numresults'] = "Ergebnisse pro Seite";
    $text['mysphoto'] = "Fotos mit unbekannten Personen";
    $text['mysperson'] = "Personen mit fehlenden Angaben";
    $text['joinor'] = "Die Option 'Verkn�pfen mit ODER' kann nicht mit dem Nachnamen des Ehepartners verwendet werden";
    $text['tellus'] = "Teilen Sie uns mit, was Sie wissen";
    $text['moreinfo'] = "Weitere Informationen:";
    //added in 8.0.0
    $text['marrdatetr'] = "Jahr der Heirat";
    $text['divdatetr'] = "Jahr der Scheidung";
    $text['mothername'] = "Name der Mutter";
    $text['fathername'] = "Name des Vaters";
    $text['filter'] = "Filter";
    $text['more'] = "Mehr";
    $text['notliving'] = "Nicht lebend";
    $text['nodayevents'] = "Ereignisse f�r diesen Monat, die nicht einem bestimmten Tag zugeordnet sind:";
    //added in 9.0.0
    $text['csv'] = "Komma-getrennte CSV-Datei";
    //added in 10.0.0
    $text['confdate'] = "Datum der Konfirmation (LDS)";
    $text['confplace'] = "Ort der Konfirmation (LDS)";
    $text['initdate'] = "Datum der Vorverordnungen (LDS)";
    $text['initplace'] = "Ort der Vorverordnungen (LDS)";
    break;

  //showlog.php
  case "showlog":
    $text['logfilefor'] = "Protokolldatei f�r";
    $text['mostrecentactions'] = "letzte Aktionen";
    $text['autorefresh'] = "automatische Aktualisierung einschalten (alle 30 Sekunden)";
    $text['refreshoff'] = "automatische Aktualisierung abschalten";
    break;

  case "headstones":
  case "showphoto":
    $text['cemeteriesheadstones'] = "Friedh�fe und Grabsteine";
    $text['showallhsr'] = "Zeige alle Grabsteine";
    $text['in'] = "in";
    $text['showmap'] = "Karte anzeigen";
    $text['headstonefor'] = "Grabstein von";
    $text['photoof'] = "Foto von";
    $text['firstpage'] = "Erste Seite";
    $text['lastpage'] = "Letzte Seite";
    $text['photoowner'] = "Besitzer/Quelle";
    $text['nocemetery'] = "Kein Friedhof";
    $text['iptc005'] = "Titel";
    $text['iptc020'] = "Zus�tzliche Kategorien";
    $text['iptc040'] = "Spezielle Anweisungen";
    $text['iptc055'] = "Gestaltungsdatum";
    $text['iptc080'] = "Autor";
    $text['iptc085'] = "Position des Autors";
    $text['iptc090'] = "Stadt";
    $text['iptc095'] = "Staat";
    $text['iptc101'] = "Land";
    $text['iptc103'] = "Auftraggeber";
    $text['iptc105'] = "Schlagzeile";
    $text['iptc110'] = "Quelle";
    $text['iptc115'] = "Quelle des Fotos";
    $text['iptc116'] = "Copyright-Notiz";
    $text['iptc120'] = "Bildtext";
    $text['iptc122'] = "Bildtext Autor";
    $text['mapof'] = "Karte von";
    $text['regphotos'] = "�bersicht mit Kurzbeschreibungen";
    $text['gallery'] = "�bersicht mit Vorschaubildern";
    $text['cemphotos'] = "Friedhofs-Fotos";
    $text['photosize'] = "Gr��e";
    $text['iptc010'] = "Priorit�t";
    $text['filesize'] = "Dateigr��e";
    $text['seeloc'] = "Siehe Ort";
    $text['showall'] = "Alles anzeigen";
    $text['editmedia'] = "Medium bearbeiten";
    $text['viewitem'] = "Dieses Element ansehen";
    $text['editcem'] = "Friedhof bearbeiten";
    $text['numitems'] = "Elemente";
    $text['allalbums'] = "Alle Alben";
    $text['slidestop'] = "Diaschau beenden";
    $text['slideresume'] = "Diaschau fortsetzen";
    $text['slidesecs'] = "Sekunden f�r jedes Bild:";
    $text['minussecs'] = "minus 0,5 Sekunden";
    $text['plussecs'] = "plus 0,5 Sekunden";
    $text['nocountry'] = "Unbekanntes Land";
    $text['nostate'] = "Unbekannter/s (Bundes-)Staat/Land";
    $text['nocounty'] = "Unbekannte Provinz";
    $text['nocity'] = "Unbekannter Ort";
    $text['nocemname'] = "Unbekannter Friedhofs-Name";
    $text['editalbum'] = "Album bearbeiten";
    $text['mediamaptext'] = "<strong>Hinweis:</strong> Wenn Sie Ihren Mauszeiger �ber das Bild bewegen, werden Namen angezeigt. Klicken Sie diese an, um weitere Informationen zu erhalten.";
    //added in 8.0.0
    $text['allburials'] = "Alle Beerdigungen";
    $text['moreinfo'] = "Weitere Informationen:";
    //added in 9.0.0
    $text['iptc025'] = "Schlagw�rter";
    $text['iptc092'] = "genauen Aufnahmeort";
    $text['iptc015'] = "Kategorie";
    $text['iptc065'] = "erzeugendes Grafikprogramm";
    $text['iptc070'] = "Programmversion";
    break;

  //surnames.php, surnames100.php, surnames-all.php, surnames-oneletter.php
  case "surnames":
  case "places":
    $text['surnamesstarting'] = "Nachnamen anzeigen, die mit ... anfangen";
    $text['showtop'] = "Zeige die ersten";
    $text['showallsurnames'] = "Zeige alle Nachnamen";
    $text['sortedalpha'] = "alphabetisch sortiert";
    $text['byoccurrence'] = "Eintr�ge sortiert nach ihrer H�ufigkeit";
    $text['firstchars'] = "Erster Buchstabe der obersten Orts-Ebene";
    $text['mainsurnamepage'] = "�bersichtsseite Nachnamen";
    $text['allsurnames'] = "Alle Nachnamen";
    $text['showmatchingsurnames'] = "Nachnamen anklicken, um weitere Angaben zu erhalten";
    $text['backtotop'] = "Zur�ck nach oben";
    $text['beginswith'] = "Beginnt mit";
    $text['allbeginningwith'] = "Alle Nachnamen beginnend mit";
    $text['numoccurrences'] = "Anzahl der Datens�tze wird in Klammern angezeigt";
    $text['placesstarting'] = "Zeige oberste Orts-Ebenen beginnend mit";
    $text['showmatchingplaces'] = "Klicken Sie auf einen Eintrag, um die untergeordneten Ebenen anzuzeigen. Klicken Sie auf das 'Suchen'-Icon, um die Nachnamen zu diesem Ort zu zeigen.";
    $text['totalnames'] = "Anzahl der Personen";
    $text['showallplaces'] = "Zeige alle obersten Orts-Ebenen";
    $text['totalplaces'] = "Anzahl der Orte";
    $text['mainplacepage'] = "Zur�ck zur Orts-Hauptseite";
    $text['allplaces'] = "Alle obersten Orts-Ebenen";
    $text['placescont'] = "Zeige alle Orte, die ... enthalten";
    //changed in 8.0.0
    $text['top30'] = "Die xxx h�ufigsten Nachnamen";
    $text['top30places'] = "Die xxx bedeutendsten obersten Orts-Ebenen";
    break;

  //whatsnew.php
  case "whatsnew":
    $text['pastxdays'] = "(aus den letzten xx Tagen)";
    $text['historiesdocs'] = "Geschichten";
    //$text['headstones'] = "Headstones";

    $text['photo'] = "Foto";
    $text['history'] = "Geschichte/Dokument";
    $text['husbid'] = "Vater-Kennung";
    $text['husbname'] = "Name des Vaters";
    $text['wifeid'] = "Mutter-Kennung";
    break;

  //timeline.php, timeline2.php
  case "timeline":
    $text['text_delete'] = "L�schen";
    $text['addperson'] = "Person hinzuf�gen";
    $text['nobirth'] = "Die folgende Person hat kein g�ltiges Geburtsdatum und konnte daher nicht hinzugef�gt werden";
    $text['event'] = "Ereignis(se)";
    $text['chartwidth'] = "Breite der Graphik";
    $text['timelineinstr'] = "Weitere Kennungen eintragen";
    $text['togglelines'] = "Linien ein-/ausschalten";
    //changed in 9.0.0
    $text['noliving'] = "Die folgende Person ist als 'lebend' deklariert und konnte nicht hinzugef�gt werden, da Sie nicht mit den entsprechenden Berechtigungen angemeldet sind";
    break;

  //browsetrees.php
  //login.php, newacctform.php, addnewacct.php
  case "trees":
  case "login":
    $text['browsealltrees'] = "Zeige alle Stammb�ume";
    $text['treename'] = "Stammbaumname";
    $text['owner'] = "Besitzer";
    $text['address'] = "Adresse";
    $text['city'] = "Ort";
    $text['state'] = "(Bundes-)Staat/-Land";
    $text['zip'] = "Postleitzahl";
    $text['country'] = "Land";
    $text['email'] = "E-Mail";
    $text['phone'] = "Telefon";
    $text['username'] = "Benutzerkennung";
    $text['password'] = "Passwort";
    $text['loginfailed'] = "Anmeldung fehlgeschlagen.";

    $text['regnewacct'] = "Benutzerkennung beantragen";
    $text['realname'] = "Ihr Name";
    $text['phone'] = "Telefon";
    $text['email'] = "E-Mail";
    $text['address'] = "Adresse";
    $text['acctcomments'] = "Notiz oder Kommentar";
    $text['submit'] = "Eintragen";
    $text['leaveblank'] = "(Leer lassen, wenn Sie einen neuen Baum beginnen)";
    $text['required'] = "Erforderliche Angaben";
    $text['enterpassword'] = "Bitte Passwort eingeben.";
    $text['enterusername'] = "Bitte eine Benutzerkennung eingeben.";
    $text['failure'] = "Diese Benutzerkennung wird bereits verwendet. Bitte zur vorgehenden Seite zur�ck gehen und eine andere Benutzerkennung w�hlen.";
    $text['success'] = "Vielen Dank. Wir haben Ihre Registrierung empfangen. Wir werden Kontakt mit Ihnen aufnehmen, wenn Ihre Benutzerkennung freigeschaltet worden ist oder wenn wir weitere Angaben ben�tigen.";
    $text['emailsubject'] = "Registrierungsanfrage: Neuer TNG-Benutzer";
    $text['website'] = "Website (WWW-Adresse)";
    $text['nologin'] = "Sie haben keine Anmeldedaten?";
    $text['loginsent'] = "Anmeldedaten wurden versandt";
    $text['loginnotsent'] = "Anmeldedaten wurden nicht versandt";
    $text['enterrealname'] = "Bitte geben Sie Ihren Namen ein.";
    $text['rempass'] = "Auf diesem Rechner angemeldet bleiben";
    $text['morestats'] = "Weitere Statistiken";
    $text['accmail'] = "<strong>HINWEIS:</strong> Um vom Verwalter dieser Website E-Mails, betreffend Ihre Benutzerkennung, empfangen zu k�nnen, stellen Sie bitte sicher, dass E-Mails aus dieser Domain bei Ihnen nicht gesperrt werden.";
    $text['newpassword'] = "Neues Passwort";
    $text['resetpass'] = "Ihr Passwort zur�cksetzen";
    $text['nousers'] = "Dieses Formular kann nicht verwendet werden, solange nicht mindestens ein Benutzer-Datensatz existiert. Wenn Sie der Eigent�mer dieser Website sind, dann rufen Sie Verwaltung/Benutzer auf und legen Sie Ihre Verwalter-Kennung an.";
    $text['noregs'] = "Bedauerlicherweise werden momentan keine neuen Benutzer-Registrierungen akzeptiert. Bitte <a href=\"suggest.php\">kontaktieren</a> Sie uns, wenn Sie Anmerkungen oder Fragen zu dieser Website haben.";
    //changed in 8.0.0
    $text['emailmsg'] = "Sie haben eine Registrierungsanfrage f�r einen neuen TNG-Benutzer erhalten. Bitte besuchen Sie Ihren TNG-Verwaltungsbereich und stellen Sie die Zugriffsrechte ein. Wenn Sie der Registrierung zustimmen, unterrichten Sie den Antragsteller, indem Sie auf diese E-Mail antworten.";
    $text['accactive'] = "Die Benutzerkennung wurde freigeschaltet, aber der Benutzer wird solange keine besonderen Berechtigungen haben, bis Sie diese zuweisen.";
    $text['accinactive'] = "Gehen Sie zu Verwaltung/Benutzerverwaltung/�nderungsvorschl�ge pr�fen, um die Angaben zur Benutzerkennung zu �berpr�fen. Die Benutzerkennung bleibt inaktiv, bis Sie die Einstellungen �berpr�ft und den Benutzer-Datensatz mindestens einmal gespeichert haben.";
    $text['pwdagain'] = "Passwort-Wiederholung";
    $text['enterpassword2'] = "Bitte geben Sie Ihr Passwort nochmals ein.";
    $text['pwdsmatch'] = "Ihre Passwort-Angaben stimmen nicht �berein. Bitte geben Sie jeweils dasselbe Passwort ein.";
    //added in 8.0.0
    $text['acksubject'] = "Vielen Dank f�r Ihren Benutzer-Antrag";
    $text['ackmessage'] = "Ihr Antrag f�r eine Benutzerkennung ist eingegangen. Die Benutzerkennung ist inaktiv, bis sie vom Verwalter der Website �berpr�ft wurde. Sie erhalten eine E-Mail-Nachricht, sobald Ihre Benutzerkennung freigeschaltet ist.";
    break;

  //added in 10.0.0
  case "branches":
    $text['browseallbranches'] = "Alle Zweige durchsuchen";
    break;

  //statistics.php
  case "stats":
    $text['quantity'] = "Anzahl";
    $text['totindividuals'] = "Personen";
    $text['totmales'] = "M�nnliche Personen";
    $text['totfemales'] = "Weibliche Personen";
    $text['totunknown'] = "Personen mit unbekanntem Geschlecht";
    $text['totliving'] = "Lebende Personen";
    $text['totfamilies'] = "Familien";
    $text['totuniquesn'] = "Eindeutige Nachnamen";
    //$text['totphotos'] = "Total Photos";
    //$text['totdocs'] = "Total Histories &amp; Documents";
    //$text['totheadstones'] = "Total Headstones";
    $text['totsources'] = "Quellen";
    $text['avglifespan'] = "Durchschnittliche Lebensspanne";
    $text['earliestbirth'] = "Fr�heste Geburt";
    $text['longestlived'] = "�lteste Personen";
    $text['days'] = "Tage";
    $text['age'] = "Alter";
    $text['agedisclaimer'] = "Altersbasierte Berechnungen sind bezogen auf Personen mit eingetragenem Geburtstag <EM>und</EM> Sterbedatum. Durch unvollst�ndige Datumsfelder (z.B. Geburtstag nur eingetragen als \"1945\" oder \"BEF 1860\") k�nnen diese Berechnungen nicht immer 100 % korrekt sein.";
    $text['treedetail'] = "Weitere Angaben zu diesem Zweig";
    $text['total'] = "Anzahl";
    break;

  case "notes":
    $text['browseallnotes'] = "Alle Notizen durchbl�ttern";
    break;

  case "help":
    $text['menuhelp'] = "Bedeutung der Men�-Icons";
    break;

  case "install":
    $text['perms'] = "Alle Berechtigungen wurden eingerichtet.";
    $text['noperms'] = "Die Berechtigungen f�r die folgenden Dateien konnten nicht eingerichtet werden:";
    $text['manual'] = "Bitte richten Sie sie von Hand ein.";
    $text['folder'] = "Verzeichnis";
    $text['created'] = "wurde angelegt";
    $text['nocreate'] = "konnte nicht angelegt werden. Bitte von Hand anlegen.";
    $text['infosaved'] = "Information wurde gespeichert, Datenbank-Verbindung wurde �berpr�ft!";
    $text['tablescr'] = "Die Tabellen wurden angelegt!";
    $text['notables'] = "Die folgenden Tabellen konnten nicht angelegt werden:";
    $text['nocomm'] = "TNG kann nicht auf Ihre Datenbank zugreifen. Es wurden keine Tabellen angelegt.";
    $text['newdb'] = "Information wurde gespeichert, Datenbank-Verbindung wurde �berpr�ft, neue Datenbank wurde angelegt:";
    $text['noattach'] = "Information wurde gespeichert. Datenbank-Verbindung wurde hergestellt und Datenbank wurde angelegt, aber TNG kann nicht darauf zugreifen.";
    $text['nodb'] = "Information wurde gespeichert. Verbindung wurde hergestellt, aber die Datenbank ist nicht vorhanden und konnte auch nicht angelegt werden. Bitte �berpr�fen Sie, ob der angegebene Datenbankname korrekt ist, oder verwenden Sie Ihr Verwaltungsprogramm, um sie anzulegen.";
    $text['noconn'] = "Information wurde gespeichert, aber die Verbindung zur Datenbank ist fehlgeschlagen. Einer oder mehrere der folgenden Punkte sind nicht korrekt:";
    $text['exists'] = "ist vorhanden";
    $text['loginfirst'] = "Sie m�ssen sich zuerst anmelden.";
    $text['noop'] = "Es wurde keine Datenbank-Operation ausgef�hrt.";
    //added in 8.0.0
    $text['nouser'] = "Benutzerkennung wurde nicht angelegt. Die angegebene Benutzerkennung existiert m�glicherweise bereits.";
    $text['notree'] = "Baum wurde nicht angelegt. Die Baum-Kennung existiert m�glicherweise bereits.";
    $text['infosaved2'] = "Information gespeichert";
    $text['renamedto'] = "Umbenannt zu";
    $text['norename'] = "Konnte nicht umbenannt werden";
    break;

  case "imgviewer":
    $text['zoomin'] = "Vergr��ern";
    $text['zoomout'] = "Verkleinern";
    $text['magmode'] = "Vergr��erungsmodus";
    $text['panmode'] = "Zeiger";
    $text['pan'] = "Klicken und Ziehen Sie, um sich innerhalb des Bildes zu bewegen";
    $text['fitwidth'] = "Breite anpassen";
    $text['fitheight'] = "H�he anpassen";
    $text['newwin'] = "Neues Fenster";
    $text['opennw'] = "Bild in einem neuen Fenster �ffnen";
    $text['magnifyreg'] = "Klicken Sie, um einen Bereich des Bildes zu vergr��ern";
    $text['imgctrls'] = "Bildsteuerung aktivieren";
    $text['vwrctrls'] = "Bildbetrachter Steuerung aktivieren";
    $text['vwrclose'] = "Bildbetrachter schlie�en";
    break;
}

//common
$text['matches'] = "Treffer";
$text['description'] = "Beschreibung";
$text['notes'] = "Notizen";
$text['status'] = "Status";
$text['newsearch'] = "Neue Suche";
$text['pedigree'] = "Stammbaum";
$text['birthabbr'] = "geb.";
$text['chrabbr'] = "get.";
$text['seephoto'] = "Siehe Foto";
$text['andlocation'] = "&amp; Ort";
$text['accessedby'] = "besucht durch";
$text['family'] = "Familie";
$text['children'] = "Kinder";
$text['tree'] = "Stammbaum";
$text['alltrees'] = "Alle Stammb�ume";
$text['nosurname'] = "Kein Nachname";
$text['thumb'] = "Vorschaubild";
$text['people'] = "Personen";
$text['title'] = "Titel";
$text['suffix'] = "Suffix";
$text['nickname'] = "Spitzname";
$text['deathabbr'] = "gest.";
$text['lastmodified'] = "Zuletzt bearbeitet am";
$text['married'] = "Verheiratet";
//$text['photos'] = "Photos";
$text['name'] = "Name";
$text['lastfirst'] = "Nachname, Taufnamen";
$text['bornchr'] = "Geboren/Getauft";
$text['individuals'] = "Personen";
$text['families'] = "Familien";
$text['personid'] = "Personen-Kennung";
$text['sources'] = "Quellen";
$text['unknown'] = "unbekannt";
$text['father'] = "Vater";
$text['mother'] = "Mutter";
$text['christened'] = "Getauft";
$text['died'] = "Gestorben";
$text['buried'] = "Begraben";
$text['spouse'] = "Ehepartner";
$text['parents'] = "Eltern";
$text['text'] = "Text";
$text['language'] = "Sprache";
$text['burialabbr'] = "begr.";
$text['descendchart'] = "Nachkommen";
$text['extractgedcom'] = "GEDCOM";
$text['indinfo'] = "Person";
$text['edit'] = "Bearbeiten";
$text['date'] = "Datum";
$text['place'] = "Ort";
$text['login'] = "Anmelden";
$text['logout'] = "Abmelden";
$text['marrabbr'] = "verh.";
$text['groupsheet'] = "Familienblatt";
$text['text_and'] = "und";
$text['generation'] = "Generation";
$text['filename'] = "Dateiname";
$text['id'] = "Kennung";
$text['search'] = "Suche";
$text['user'] = "Benutzer";
$text['firstname'] = "Vorname";
$text['lastname'] = "Nachname";
$text['searchresults'] = "Suchergebnisse";
$text['diedburied'] = "Verstorben/begraben";
$text['homepage'] = "Startseite";
$text['find'] = "Suchen...";
$text['relationship'] = "Verwandtschaft";
$text['relationship2'] = "Beziehung";
$text['timeline'] = "Zeitstrahl";
$text['yesabbr'] = "J";
$text['divorced'] = "Geschieden";
$text['indlinked'] = "Verkn�pft mit";
$text['branch'] = "Zweig";
$text['moreind'] = "Weitere Personen...";
$text['morefam'] = "Weitere Familien...";
$text['source'] = "Quelle";
$text['surnamelist'] = "Liste der Nachnamen";
$text['generations'] = "Generationen";
$text['refresh'] = "Aktualisieren";
$text['whatsnew'] = "Aktuelles";
$text['reports'] = "Berichte";
$text['placelist'] = "Ortsliste";
$text['baptizedlds'] = "Getauft (LDS)";
$text['endowedlds'] = "Begabung (LDS)";
$text['sealedplds'] = "Siegelung an die Eltern (LDS)";
$text['sealedslds'] = "Siegelung an den Ehepartner (LDS)";
//$text['photoshistories'] = "Photos &amp; Histories";
$text['ancestors'] = "Vorfahren";
$text['descendants'] = "Nachkommen";
//$text['sex'] = "Sex";
$text['lastimportdate'] = "Datum des letzten GEDCOM-Imports";
$text['type'] = "Typ";
$text['savechanges'] = "Speichern";
$text['familyid'] = "Familien-Kennung";
$text['headstone'] = "Grabsteine";
$text['historiesdocs'] = "Geschichten";
$text['anonymous'] = "anonym";
$text['places'] = "Orte";
$text['anniversaries'] = "Daten und Jahrestage";
$text['administration'] = "Verwaltung";
$text['help'] = "Hilfe";
//$text['documents'] = "Documents";
$text['year'] = "Jahr";
$text['all'] = "Alles";
$text['repository'] = "Aufbewahrungsort";
$text['address'] = "Adresse";
$text['suggest'] = "Anmerkung";
$text['editevent'] = "�nderungsvorschlag f�r dieses Ereignis";
$text['findplaces'] = "Suche alle Personen mit Ereignissen an diesem Ort";
$text['morelinks'] = "Weitere Verkn�pfungen";
$text['faminfo'] = "Angaben zur Familie";
$text['persinfo'] = "Angaben zur Person";
$text['srcinfo'] = "Angaben zur Quelle";
$text['fact'] = "Merkmal";
$text['goto'] = "Eine Seite ausw�hlen";
$text['tngprint'] = "Drucken";
$text['databasestatistics'] = "Datenbankstatistiken";
$text['child'] = "Kind";
$text['repoinfo'] = "Angaben zum Aufbewahrungsort";
$text['tng_reset'] = "Zur�cksetzen";
$text['noresults'] = "Keine Suchergebnisse";
$text['allmedia'] = "Alle Medien";
$text['repositories'] = "Aufbewahrungsorte";
$text['albums'] = "Alben";
$text['cemeteries'] = "Friedh�fe";
$text['surnames'] = "Nachnamen";
$text['dates'] = "Jahrestage";
$text['link'] = "Link";
$text['media'] = "Medien";
$text['gender'] = "Geschlecht";
$text['latitude'] = "Geographische Breite";
$text['longitude'] = "Geographische L�nge";
$text['bookmarks'] = "Lesezeichen";
$text['bookmark'] = "Lesezeichen hinzuf�gen";
$text['mngbookmarks'] = "Zu den Lesezeichen gehen";
$text['bookmarked'] = "Lesezeichen hinzugef�gt";
$text['remove'] = "Entfernen";
$text['find_menu'] = "Suchen";
$text['info'] = "Info";
$text['cemetery'] = "Friedhof";
$text['gmapevent'] = "Ereignis-Karte";
$text['gevents'] = "Ereignis";
$text['glang'] = "&amp;hl=de";
$text['googleearthlink'] = "Link zu Google Earth";
$text['googlemaplink'] = "Link zu Google Maps";
$text['gmaplegend'] = "Pin-Bedeutungen";
$text['unmarked'] = "Nicht markiert";
$text['located'] = "Lokalisiert";
$text['albclicksee'] = "Anklicken, um alle Elemente in diesem Album anzuzeigen";
$text['notyetlocated'] = "Noch nicht lokalisiert";
$text['cremated'] = "einge�schert";
$text['missing'] = "fehlend";
$text['pdfgen'] = "PDF-Datei erzeugen";
$text['blank'] = "ohne Daten-Inhalte";
$text['none'] = "Keine";
$text['fonts'] = "Schriftname";
$text['header'] = "�berschrift";
$text['data'] = "Daten";
$text['pgsetup'] = "Seiten-Einstellungen";
$text['pgsize'] = "Seitengr��e";
$text['orient'] = "Ausrichtung";
$text['portrait'] = "Hochformat";
$text['landscape'] = "Querformat";
$text['tmargin'] = "Oberer Rand";
$text['bmargin'] = "Unterer Rand";
$text['lmargin'] = "Linker Rand";
$text['rmargin'] = "Rechter Rand";
$text['createch'] = "PDF-Datei erzeugen";
$text['prefix'] = "Pr�fix";
$text['mostwanted'] = "Gesuchte Angaben";
$text['latupdates'] = "Letzte Aktualisierungen";
$text['featphoto'] = "Aufmacher-Foto";
$text['news'] = "Aktuelles";
$text['ourhist'] = "Die Geschichte unserer Familie";
$text['ourhistanc'] = "Die Geschichte und Genealogie unserer Familie";
$text['ourpages'] = "Die Seiten zu unserer Familien-Genealogie";
$text['pwrdby'] = "Diese Website l�uft mit";
$text['writby'] = "programmiert von";
$text['searchtngnet'] = "Suche im TNG-Network (GENDEX)";
$text['viewphotos'] = "Alle Fotos ansehen";
$text['anon'] = "Sie sind momentan nicht angemeldet (anonymer Benutzer)";
$text['whichbranch'] = "Zu welchem Zweig geh�ren Sie?";
$text['featarts'] = "Aufmacher-Artikel";
$text['maintby'] = "betrieben von";
$text['createdon'] = "Erzeugt am";
$text['reliability'] = "Verl�sslichkeit";
$text['labels'] = "Beschriftungen";
$text['inclsrcs'] = "einschlie�lich Quellen-Angaben";
$text['cont'] = "(Forts.)";
$text['mnuheader'] = "Startseite";
$text['mnusearchfornames'] = "Suche nach Namen";
$text['mnulastname'] = "Nachname";
$text['mnufirstname'] = "Vorname";
$text['mnusearch'] = "Suchen";
$text['mnureset'] = "Zur�cksetzen";
$text['mnulogon'] = "Anmelden";
$text['mnulogout'] = "Abmelden";
$text['mnufeatures'] = "Weitere Funktionen";
$text['mnuregister'] = "Benutzerkennung beantragen";
$text['mnuadvancedsearch'] = "Erweiterte Suche";
$text['mnulastnames'] = "Nachnamen";
$text['mnustatistics'] = "Statistik";
$text['mnuphotos'] = "Fotos";
$text['mnuhistories'] = "Geschichten";
$text['mnumyancestors'] = "Fotos &amp; Geschichten f�r Vorfahren von [Person]";
$text['mnucemeteries'] = "Friedh�fe";
$text['mnutombstones'] = "Grabsteine";
$text['mnureports'] = "Berichte";
$text['mnusources'] = "Quellen";
$text['mnuwhatsnew'] = "Aktuelles";
$text['mnushowlog'] = "Protokoll der Zugriffe";
$text['mnulanguage'] = "Sprache �ndern";
$text['mnuadmin'] = "Verwaltung";
$text['welcome'] = "Willkommen";
$text['contactus'] = "Kontakt";
//changed in 8.0.0
$text['born'] = "Geboren";
$text['searchnames'] = "Suche nach Namen";
//added in 8.0.0
$text['editperson'] = "Person bearbeiten";
$text['loadmap'] = "Karte laden";
$text['birth'] = "Geburt";
$text['wasborn'] = "wurde geboren";
$text['startnum'] = "Erste Nummer";
$text['searching'] = "Suche l�uft";
//moved here in 8.0.0
$text['location'] = "Ort";
$text['association'] = "Verbindung";
$text['collapse'] = "Darstellung reduzieren";
$text['expand'] = "Darstellung erweitern";
$text['plot'] = "Grab-Standort";
$text['searchfams'] = "Familien Suchen";
//added in 8.0.2
$text['wasmarried'] = "Heiratete";
$text['anddied'] = "Gestorben";
//added in 9.0.0
$text['share'] = "Teilen";
$text['hide'] = "Ausblenden";
$text['disabled'] = "Ihr Benutzerkonto wurde deaktiviert. Bitte setzen Sie sich mit dem Administrator in Verbindung.";
$text['contactus_long'] = "Wenn Sie Fragen oder Anmerkungen zu dieser Website haben, so <span class=\"emphasis\"><a href=\"suggest.php\">kontaktieren</a></span> Sie uns bitte. Wir freuen uns, von Ihnen zu h�ren.";
$text['features'] = "Eigenschaften";
$text['resources'] = "Ressourcen";
$text['latestnews'] = "Aktuelle Neuigkeiten";
$text['trees'] = "Stammb�ume";
$text['wasburied'] = "wurde beigesetzt";
//moved here in 9.0.0
$text['emailagain'] = "E-Mail nochmal";
$text['enteremail2'] = "Bitte geben Sie ihre E-Mail-Adresse nochmals ein.";
$text['emailsmatch'] = "Ihre E-Mail-Adressen stimmen nicht �berein. Bitte geben Sie dieselbe E-Mail-Adresse in jedes Feld ein.";
$text['getdirections'] = "Hier klicken, um eine Wegbeschreibung zu bekommen";
$text['calendar'] = "Kalender";
//changed in 9.0.0
$text['directionsto'] = " nach ";
$text['slidestart'] = "Diaschau";
$text['livingnote'] = "Mit dieser Bemerkung ist mindestens eine lebende Person verkn�pft - Details werden aus Datenschutzgr�nden nicht angezeigt.";
$text['livingphoto'] = "Mindestens eine lebende Person ist mit diesem Foto verkn�pft - Details werden aus Datenschutzgr�nden nicht angezeigt.";
$text['waschristened'] = "Getauft";
//added in 10.0.0
$text['branches'] = "Zweige";
$text['detail'] = "Details";
$text['moredetail'] = "Mehr Details";
$text['lessdetail'] = "Weniger Details";
$text['otherevents'] = "Weitere Ereignisse";
$text['conflds'] = "Konfirmiert (LDS)";
$text['initlds'] = "Vorverordnungen (LDS)";
$text['wascremated'] = "wurde einge�schert";

include_once("captcha_text.php");
include_once("alltext.php");
if (!$alltextloaded)
  getAllTextPath();