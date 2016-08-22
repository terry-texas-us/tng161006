<?php
require '../../helplib.php';
echo help_header("Help: Notes");
?>

<body class="helpbody">
  <section class='container'>
    <h2>Notes</h2>
    <h4>Adding/Editing/Deleting Notes</h4>

    <p>To add, edit or delete notes for a person, family, source, repository or event, click on the Notes icon at the top of the screen or next to any event (if notes already exist,
      a green dot will be present on the icon). When the icon is clicked, a small popup will appear showing
      all notes existing for the active entity or event.</p>

    <p>To add a new note, click on the "Add New" button and fill out the form. If the selected entity or event did not have any previous
      notes, you will be sent directly to the "Add New Note" screen.</p>

    <p>To edit or delete an existing note, click on the appropriate icon next to that note.</p>

    <p>While adding or editing a note, please enter your note or make your changes in the large <strong>Note</strong> field and click the "Save" button. Notes are saved at that point, even if other
      information for the active entity is not. You may enter HTML code in the field. PHP and Javascript code will not work.</p>

    <p>To sort notes, click anywhere in the row (not on one of the icons) and drag the note up or down.</p>

    <span>Private</span>
    <p>Check this box to prevent the note from being displayed in the public area. This is independent of any Private tag you may have associated with a person
      or family.</p>

    <h4>Adding Source Citations for Notes</h4>
    <p>To add or edit source citations for a note, first save the note, then click the Citations icon next to that note record in the current list of notes
      For more information on citations, please see <a href="citations_help.php">Help: Citations</a>.</p>

  </section> <!-- .container -->
</body>
</html>
