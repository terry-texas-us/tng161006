<?php
require '../../helplib.php';
echo help_header("Help: Branches");
?>

<body class="helpbody">
  <section class='container'>
    <h2>Branches</h2>
    <h4>What is a Branch?</h4>
    <p>A <strong>Branch</strong> is a set of individuals within a tree that all share a common label. This label allows TNG to restrict access to these labeled
      individuals based on user permissions. In other words, users who are assigned to a particular Branch will have their rights restricted
      to the people and families in that Branch. An individual in the database may belong to more than one Branch. Users may only be assigned to
      a single Branch at most, but this restriction can be circumvented by creating a "dummy" Branch whose label is actually a substring of
      more than one other label. For example, a user assigned to the "smith" Branch would have rights to both the "blacksmith" and "smithson" Branches because
      both names contain the word "smith".</p>

    <h4>Search</h4>
    <p>Locate existing Branches by searching for all or part of the <strong>Branch ID</strong> or <strong>Description</strong>. Select a Tree to further narrow your search.
      Searching with no options selected and no value in the search box will find all Branches in your database.</p>

    <p>Your search criteria for this page will be remembered until you click the <strong>Reset</strong> button, which restores all default values and searches again.</p>

    <span>Actions</span>
    <p>The Action buttons next to each search result allow you to edit, delete or add labels to that Branch. To delete more than one Branch at a time, click the box in the
      <strong>Select</strong> column for each Branch to be deleted, then click the "Delete Selected" button at the top of the list. Use the <strong>Select All</strong> or <strong>Clear All</strong>
      buttons to toggle all select boxes at once.</p>

    <h4>Add New / Edit Existing Branches</h4>
    <p>To add a new Branch, click on the <strong>Add New</strong> tab, then fill out the form.
      Take note of the following:</p>

    <span>Branch ID</span>
    <p>This should be a short, unique, one-word identifier for the Branch. Do not include non-alphanumeric characters (stick to numbers and letters), and do not use spaces.
      This information will not appear anywhere, so it can be all lowercase. 20 character max.</p>

    <span>Description:</span>
    <p>This can be a longer description of this Branch or the data it contains.</p>

    <span>Starting Individual</span>
    <p>Enter or find the ID of the individual with whom your branch begins. All
      partial branches are defined by a starting individual and a number of ancestral or descendant generations from that individual. You can add additional names
      by repeating this process and picking a different "Starting Individual". When you save your branch, only the most recent Starting Individual will be remembered,
      but all labels added previously will not be affected.</p>

    <span>Number of Generations</span>
    <p>Indicate the number of generations back (Ancestors) or forward (Descendants) from the starting individual that you wish to label. When
      labeling ancestors, you can also indicate how many descendant generations to label in from each ancestor.</p>

    <h4>Deleting Branches</h4>
    <p>To delete a Branch, use the <a href="#search">Search</a> tab to locate the Branch, then click on the Delete icon next to that Branch record. The row will
      change color and then vanish as the Branch is deleted. To delete more than one Branch at a time, check the box in the Select column next to each Branch to be
      deleted, then click the "Delete Selected" button at the top of the page.</p>

    <h4>Labeling Branches</h4>
    <p>To assign a branch label to individuals in your database, click on the <strong>Add labels</strong> button at the bottom of the Edit Branch page,
      then follow the instructions in the window you see next. After selecting options, click the "Add labels" button at the bottom. Options on that page include:</p>

    <span>Action</span>
    <p>Choose whether you'll be adding new labels or clearing out existing ones. If you're clearing labels, then you will also choose whether this action will clear
      the branch label  from All members of your tree or just clear the labels based on the criteria selected. </p>

    <span>Existing labels</span>
    <p>Your selection here determines what to do if any of the people you selected
      for labeling already have a branch label. You may elect to leave the existing label(s) untouched, you may
      choose to overwrite what's there, or you can decide to append the new label. If you choose the last option,
      the affected individual(s) will	now belong to multiple branches.</p>

    <span>Show people with this tree/branch (label):</span>
    <p>Click this button to display all individuals who already have
      the selected branch label within the selected tree. From the display, click the Add Labels link
      to return to the previous page, or click on any individual to edit their personal record.</p>
  </section> <!-- .container -->
</body>
</html>
