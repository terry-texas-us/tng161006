<?php
require '../../helplib.php';
echo help_header("Help: Import Settings");
?>

<body class="helpbody">
  <section class='container'>
    
    <h2>Import Settings</h2>

    <span>GEDCOM Folder (Import/Export)</span>
    <p>The name of the folder from which TNG will import GEDCOM files, and the location where TNG will store exported GEDCOM files.</p>

    <span>Save Import State</span>
    <p>If your data import or export fails to complete for any reason, select this option and rerun the import/export. If it fails again, click on the link to resume and the import/export will continue where it left
      off. For imports, this option only works if your GEDCOM file is already in your GEDCOM folder (it does not work with files uploaded and imported directly from the Data Import screen).</p>

    <span>Record Report Number</span>
    <p>This is the number of records TNG will process between reports to the screen. To make your import run faster, make this number
      fairly high (maybe somewhere around 100). If your import fails or the progress bar fails to report any progress other
      than "finished", however, you may want to lower this number to cause TNG
      to report more often and thus fill up the screen buffer faster.</p>

    <span>Progress Interval (ms)</span>
    <p>This is the number of milliseconds TNG will wait in between checks to see if more imported records were reported.</p>

    <span>Default Replace Option</span>
    <p>This controls which Import "Replace" option will be initially selected whenever you load the Import page.</p>

    <span>If Change Date is blank</span>
    <p>If your individual, family or source records do not have an associated Change Date to indicate when they were
      last modified, TNG will assign a value based on this option. Choose to use today's date instead, or to leave
      the field blank. If left blank, the field will not overwrite an existing Change Date.</p>

    <span>If no birth date, assume</span>
    <p>TNG flags all incoming individual records as living or not. If the individual has no death or burial date or place,
      this flag is set based on the time elapsed from the person's birth. If no birth date exists for this person,
      TNG can interpret that in different ways. Choose to have such people flagged as deceased or living.</p>

    <span>If no death date, assume deceased if older than</span>
    <p>If no death or burial date or place exists for an individual, the living flag is calculated from the time
      elapsed from the individual's birth date. Individuals younger than the age indicated here are considered living.
      By default, the maximum age to be considered living is 110 years.</p>

    <span>Assume private if not dead this many years</span>
    <p>TNG will set the Private flag for an individual during a GEDCOM import if the person has not been deceased more than this many years. Leave
      this field blank or set it to zero to prevent the Private flag from ever getting set in this manner.</p>

    <span>Embedded Media</span>
    <p>If you check the box for "Allow TNG to assign names to embedded media", TNG will ignore the path and file names associated with your embedded media and will
      assign a new file name based on the convention tree ID + media ID + media extension. The file will then be placed in the Photos folder (as defined in the General Settings).
      You might want to select this option if you have imported embedded media in the past, since TNG used this convention by default prior to version 3.4.0. If you have TNG-assigned
      names for previously imported media and now import the name media without selecting this option, you will have duplicate files.</p>

    <span>Local Photo Path(s)</span>
    <p>Enter the base path or paths (separate multiple entries with commas) where photos are located on your home computer. It should correspond to the TNG Photos folder
      on your web site. In other words, if the photos on your computer are located in "C:\MyGenealogy\MyPhotos", that is what you should enter here. If some of your photo links
      refer to that location in relative terms (ie, just "MyPhotos"), include the relative path as a multiple entry. If some of your photos
      reside in subfolders of this location and you want to preserve that structure on your web site, do not include the subfolders in this path. If you want all photos to go
      into the same location (the TNG Photos folder), leave this field blank and check "Import file name only" for the last option on this page.</p>

    <span>Local History Path(s)</span>
    <p>Enter the base path or paths (separate multiple entries with commas) where histories are located on your home computer. It should correspond to the TNG Histories folder
      on your web site. In other words, if the histories on your computer are located in "C:\MyGenealogy\MyHistories", that is what you should enter here. If some of your history links
      refer to that location in relative terms (ie, just "MyHistories"), include the relative path as a multiple entry. If some of your histories
      reside in subfolders of this location and you want to preserve that structure on your web site, do not include the subfolders in this path. If you want all histories to go
      into the same location (the TNG Histories folder), leave this field blank and check "Import file name only" for the last option on this page.</p>

    <span>Local Documents Path(s)</span>
    <p>Enter the base path or paths (separate multiple entries with commas) where documents are located on your home computer. It should correspond to the TNG Documents folder
      on your web site. In other words, if the documents on your computer are located in "C:\MyGenealogy\MyDocuments", that is what you should enter here. If some of your document links
      refer to that location in relative terms (ie, just "MyDocuments"), include the relative path as a multiple entry. If some of your documents
      reside in subfolders of this location and you want to preserve that structure on your web site, do not include the subfolders in this path. If you want all documents to go
      into the same location (the TNG Documents folder), leave this field blank and check "Import file name only" for the last option on this page.</p>

    <span>Local Headstones Path(s)</span>
    <p>Enter the base path or paths (separate multiple entries with commas) where headstone images are located on your home computer. It should correspond to the TNG Headstones folder
      on your web site. In other words, if the headstones on your computer are located in "C:\MyGenealogy\MyHeadstones", that is what you should enter here. If some of your headstone links
      refer to that location in relative terms (ie, just "MyHeadstones"), include the relative path as a multiple entry. If some of your headstones
      reside in subfolders of this location and you want to preserve that structure on your web site, do not include the subfolders in this path. If you want all headstones to go
      into the same location (the TNG Headstones folder), leave this field blank and check "Import file name only" for the last option on this page.</p>

    <span>Local Path(s) for Other Media</span>
    <p>Enter the base path or paths (separate multiple entries with commas) where other media (ie, videos or recordings) are located on your home computer. It should correspond to the TNG Multimedia folder
      on your web site. In other words, if the videos or recordings on your computer are located in "C:\MyGenealogy\MyMultimedia", that is what you should enter here. If some of your multimedia links
      refer to that location in relative terms (ie, just "MyMultimedia"), include the relative path as a multiple entry. If some of your videos or recordings
      reside in subfolders of this location and you want to preserve that structure on your web site, do not include the subfolders in this path. If you want all videos/recordings to go
      into the same location (the TNG Multimedia folder), leave this field blank and check "Import file name only" for the last option on this page.</p>

    <span>If no local path match</span>
    <p>If a photo or history is imported and the file path does not originate from one of the local paths indicated above, TNG can either import the entire path "as is" (recommended if
      all your paths are relative and you want your local folder structure to match your TNG photo/history folder structure), or it can strip the path information import the file name only
      (recommended if you do not intend to put your media in subfolders of the TNG Photos or TNG Histories folders).</p>

    <span>Prefix for private notes</span>
    <p>If you would like some of your notes to be labeled as "private" on import and not be displayed in the public areas, TNG can do that if all
      notes fitting that description start with the same character. Characters commonly used for this purpose are the tilde (~) or the exclamation
      point (!). Enter the character you are using for that purpose here prior to importing your GEDCOM file, and the "private" labels will
      automatically be attached.</p>
  </section> <!-- .container -->
</body>
</html>