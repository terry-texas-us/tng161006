<?php
require '../../helplib.php';
echo help_header("Help: Associations");
?>

<body class="helpbody">
  <section class='container'>
    <h2>Associations</h2>
    <h4>What are Associations?</h4>

    <p>An <strong>Association</strong> is a record of a relationship between two people, between two families, or between a person and a family.
      The relationship may not be obvious from the regular tree structure of your genealogy. In fact, two people/families who
      are linked in an Association may not be related at all.</p>

    <h4>Adding/Editing/Deleting Associations</h4>

    <p>To add, edit or delete associations for an individual, look up person in Admin/People and edit
      the individual record, then click on the Associations icon at the top of the screen (if associations already exist,
      a green dot will be present on the icon). When the icon is clicked, a small popup will appear showing
      all associations existing for the active individual. To do the same for family associations, look up the family in Admin/Families
      and edit the family record, then proceed as outlined above.</p>

    <p>To add a new association, click on the "Add New" button and fill out the form. If the selected person or family did not have any previous
      associations, you will be sent directly to the "Add New Association" screen. Once you're on that screen, you will be able to indicate
      whether the associated entity is a person or family.</p>

    <p>To edit or delete an existing association, click on the appropriate icon next to that association.</p>

    <p>While adding or editing an association, take note of the following:</p>

    <span>Person ID or Family ID</span>
    <p>Enter the ID of the person or family to be associated with the active person or family, or click the Find icon to search for the ID.</p>

    <span>Relationship</span>
    <p>Enter the nature of the association. For example, <em>Godfather</em>, <em>Mentor</em> or <em>Witness</em>.</p>

    <span>Reverse Association?</span>
    <p>Sometimes an association goes both ways. For example, an association relationship of <em>Friend</em> could apply in both directions. If that
      is true and you want to create a second association going in the reverse direction, then check this box. If the relationship doesn't seem to
      apply when the association is reversed (e.g., <em>Godfather</em> or <em>Mentor</em>), then you should create a different association, starting
      from the other person or family, to show the reverse relationship.</p>

    <p>When you are done adding, editing or deleting associations for this person or family, click the "Finish" button to close the window.</p>
  </section> <!-- .container -->
</body>
</html>
