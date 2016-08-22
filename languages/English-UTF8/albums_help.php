<?php
require '../../helplib.php';
echo help_header("Help: Albums");
?>

<body class="helpbody">
  <section class='container'>
    <h2>Albums</h2>
    <h4>Search</h4>
    <p>Locate existing media by searching for all or part of the <strong>Album Name, Description</strong> or
      <strong>Keywords</strong>. Searching with no value in the search box will find all albums in your database.</p>

    <p>Your search criteria for this page will be remembered until you click the <strong>Reset</strong> button, which restores all default values and searches again.</p>

    <span>Actions</span>
    <p>The Action buttons next to each Album allow you to edit, delete or preview that Album.</p>
    <h4>Adding New Albums</h4>
    <p>An <strong>Album</strong> in TNG is a grouping of media. An Album can include any number of media, and any particular media item can belong to multiple albums.
      Like individual media, Albums can be linked to people, families, sources, repositories or places.</p>

    <p>To add a new Album, click on the <strong>Add New</strong> tab, then fill out the form. Some information, including the media to include and
      links to people, families and other entities, can be added after saving the record. Take note of the following:</p>

    <p><span>Album Name</span><br />
      The name of your album.</p>

    <p><span>Description</span><br />
      A short description of the album or the items contained in it.</p>

    <p><span>Keywords</span><br />
      Any number of keywords outside the Album Name or Description that can be used to locate this album when searching.</p>

    <p><span>Active</span><br />
      If an album is flagged as "Active", it will show on the public listing of all albums on your site. If the Active flag is set to "No", then visitors to your
      site will not see it.</p>

    <p><span>Always viewable</span><br />
      If an active album is flagged as "Always viewable" and is linked to a person, family, source or repository, it will always show on the pages for those entities, even if it is
      linked to a living person or family. Normally, active albums and other media linked to living individuals are hidden from visitors who who do not have rights to view living data.
    </p>

    <p><span>Required fields:</span> Album Name is he only required field, but it is in your best interest to fill in the other fields as well.</p>
    <h4>Editing Existing Albums</h4>
    <p>To make changes to an existing Album, use the <a href="#search">Search</a> tab to locate the Album, then click on the Edit icon next to that Album.
      Take note of the following items that are not on the "Add New Album" page:</p>

    <span>Album Media</span>
    <p>To add media to an album, click on the "Add Media" button, then use the form in the resulting popup to select media from the items currently in
      your database. To do that, select a Collection and/or a Tree (both optional), then enter part of the media name or description in the "Search for" field
      and click the "Search" button. When you locate an item that you'd like to add to the Album, click on the "Add" link to the left of the item row. That
      item will be added and the popup will remain. Repeat this step to locate and add more media, or click on the "Close Window" link to return to the Edit Album page.</p>

    <p>To remove media from an album, move your mouse pointer over the item. A "Remove" link will be revealed. Click that link to remove the item. After the
      confirmation, the item will fade away.</p>

    <p>To choose one thumbnail to be the <strong>Default Photo</strong> for the current Album, move your mouse pointer over the item. A "Make Default" link will be revealed.
      Click that link to designate the thumbnail for that item as the Album's Default Photo. To select a different Default Photo, repeat the process with a different
      item in the list. To remove the Default Photo designation altogether, click on the "Remove default photo" link at the top of the page.</p>

    <p>To reorder the media within the Album, click on the "Drag" area for any item and hold the mouse button down while moving the mouse pointer to the desired location
      within the list. When the item has reached the selected point, release the mouse button ("drag and drop"). Changes are automatically saved at that point.</p>

    <p>Another way to re-order the items is to enter a sequence number in the small box next to the "Drag" area, then click the "Go" link beneath the box or press Enter.
      This might be a good way to move items if the list is too long to all fit on the screen at once.</p>

    <p>You can also move any item directly to the top of the list by clicking on the double up arrow icon to the right of the "Drag" area.</p>

    <span>Album Links</span>
    <p>You can link this Album to people, families, sources, repositories or places. For each link, first select the Tree associated with the link entity.
      Next, select the Link Type (Person, Family, Source, Repository or Place), and finally, enter the ID number or name (Places only) of the link entity. After
      all the information has been entered, click on the "Add" button.</p>

    <p>If you don't know the ID number or exact Place Name, click the magnifying glass icon to search for it. A popup window will appear to let you do the searching.
      When you find the desired entity description, click the "Add" link at the left. You may click "Add" for multiple entities. When you are finished creating
      links, click on the "Close Window" link.</p>

    <p>NOTE: All changes relating to Album Media and Album Links are saved immediately and do not require you to click the "Save" button at the bottom of the screen.
      Changes in the "Album Information" section do require you to click "Save".</p>
    <h4>Deleting Albums</h4>
    <p>To delete an Album, use the <a href="#search">Search</a> tab to locate the Album, then click on the Delete icon next to that Albumm. The row will
      change color and then vanish as the item is deleted.</p>
    <h4>Sorting Albums</h4>
    <p>By default, Albums linked to a Person, Family, Source, Repository or Place are sorted by the order in which they were linked to that entity. To change that
      order, you must indicate a new order on the Album/Sort tab.</p>

    <span>Tree, Link Type, Collection:</span>
    <p>Select the Tree associated with the entity for which you would like to sort Albums. Next, select a Link Type (Person, Family, Source, Repository or Place) and
      the Collection you would like to sort.</p>

    <span>ID:</span>
    <p>Enter the ID number or name (Places only) of the entity. If you don't know the ID number or exact place name, click the magnifying glass icon to search for it.
      When you find the desired entity, click on the "Select" link next to that entity. The popup will close and the selected ID will appear in the ID field.</p>

    <span>Sorting Procedure</span>
    <p>After selecting or entering an ID, click on the "Continue" button to display all Albums for the selected entity and Collection in their current order.
      To reorder the Albums, click on the "Drag" area for any item and hold the mouse button down while moving the mouse pointer to the desired location
      within the list. When the item has reached the selected point, release the mouse button ("drag and drop"). Changes are automatically saved at that point.</p>

    <p>Another way to re-order the items is to enter a sequence number in the small box next to the "Drag" area, then click the "Go" link beneath the box or press Enter.
      This might be a good way to move items if the list is too long to all fit on the screen at once.</p>

    <p>You can also move any item directly to the top of the list by clicking on the double up arrow icon to the right of the "Drag" area.</p>
  </section> <!-- .container -->
</body>
</html>