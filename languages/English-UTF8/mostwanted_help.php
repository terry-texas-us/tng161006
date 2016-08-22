<?php
require '../../helplib.php';
echo help_header("Help: Most Wanted");
?>

<body class="helpbody">
  <section class='container'>
    <h2>Most Wanted</h2>
    <h4>Adding New Entries</h4>
    <p>The <strong>Most Wanted</strong> feature allows you to make a list of critical people or photos you may be having trouble researching.
      The list is divided into two categories, <strong>Elusive People</strong> and <strong>Mystery Photos</strong>. To add a new entry to one of these
      categories, click on the "Add New" button under the appropriate heading, then fill out the form. Take note of the following:</p>

    <span>Title</span>
    <p>Give your entry a title, which may actually be a question. For example, <em>Who is this person?</em> or <em>Who is John Carlisle's father?</em></p>

    <span>Description</span>
    <p>Give your entry a short description as well. This could consist of any current evidence you've gathered, any brick walls you've run into,
      or some specific piece of information you're looking for.</p>

    <span>Tree</span>
    <p>If desired, you can associate this entry with a Tree (optional).</p>

    <span>Person</span>
    <p>If this entry is closely associated with a person, enter the Person ID or click on the magnifying glass icon to look it up. When you find the desired
      individual, click on the "Select" link to return to the Most Wanted form with the selected ID.</p>

    <span>Select Photo</span>
    <p>If this entry is closely associated with a photo, click on the "Select Photo" button to search for that photo from among the Photo records
      already in your database. When you find the desired Photo, click on the "Select" link to return to the Most Wanted form with the selected ID.</p>

    <p>When you are finished, click the "Save" button to return to the list. Your new entry will be added to the bottom of the category where you added it.</p>

    <h4>Editing Existing Entries</h4>
    <p>To edit an existing entry, hold your mouse pointer over the entry to be edited. Links for "Edit" and "Delete" should appear for that entry. Click
      the "Edit" link to bring up the form where you can make your changes. All the fields are the same as the ones described above under "Adding New Entries".</p>

    <h4>Sorting Entries</h4>
    <p>To change the order of the Most Wanted entries you've created, just drag and drop them to the desired location (click on the "Drag" area, then hold the mouse down
      as you move your pointer to the desired location, then release the mouse button). </p>

    <p><strong>NOTE:</strong> You <strong>can</strong> drag and drop entries from one list to the other (e.g., drag an entry from "Elusive People" to "Mystery Photos").</p>

    <h4>Deleting Existing Entries</h4>
    <p>To delete an existing entry, hold your mouse pointer over the entry to be deleted. Links for "Edit" and "Delete" should appear for that entry. Click
      the "Delete" link to remove the entry (you will be asked to confirm your deletion before it is made final).</p>

  </section> <!-- .container -->
</body>
</html>
