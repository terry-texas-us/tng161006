<?php
require '../../helplib.php';
echo help_header("Help: Citations");
?>

<body class="helpbody">
  <section class='container'>
    <h2>Citations</h2>
    <h4>What are Citations?</h4>

    <p>A <strong>Citation</strong> is a reference to a Source record, made with the intent of proving the veracity of some piece of information. The Source usually
      describes in general where the information was found (e.g., a book or a census), while the Citation usually contains more specific information (e.g., on which page).
      The same Source record can be cited multiple times for different people, families, notes and events.</p>

    <h4>Adding/Editing/Deleting Citations</h4>

    <p>To add, edit or delete citations, click on the Citations icon at the top of the screen or next to any note or event (if citations already exist,
      a green dot will be present on the icon). When the icon is clicked, a small popup will appear showing
      all citations existing for the active entity or event.</p>

    <p>To add a new citation, click on the "Add New" button and fill out the form. If the selected entity or event did not have any previous
      citations, you will be sent directly to the "Add New Citation" screen.</p>

    <p>To edit or delete an existing citation, click on the appropriate icon next to that citation.</p>

    <p>While adding or editing a citation, please take note of the following:</p>

    <span>Source ID</span>
    <p>Enter the ID of the source to be cited, or click the "Find" button to search for it. If the source has not yet been created, you
      can go to Admin/Sources to create the source in the proper tree, then return to the citations list, or you can click the "Create" button
      to enter the information for the new source. Once that information is saved, the new Source ID will be entered into this field.</p>
    <p>If you have already made at least one citation for the same type of entity (person, family, etc.) during your current session, you will also see a "Copy Last" button. Clicking that
      button will populate all the fields with the same values that you used in your last citation.</p>

<!--
    <span>Description</span>
    <p>If your desktop genealogy program does not assign ID numbers to your sources, your citation will have a Description instead. You will not see
    the Description field for a new citation.</p>
-->
    <span>Page</span>
    <p>Enter the page of the selected source relevant to this event (optional).</p>

    <span>Reliability</span>
    <p>Select a number (0-3) indicating how reliable the source is (optional). Higher numbers indicate greater reliability.</p>

    <span>Citation Date</span>
    <p>The date associated with this citation (optional).</p>

    <span>Actual Text</span>
    <p>An short excerpt of the source material (optional).</p>

    <span>Notes</span>
    <p>Any helpful comments you may have concerning this source (optional).</p>

  </section> <!-- .container -->
</body>
</html>
