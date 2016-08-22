<?php
require '../../helplib.php';
echo help_header("Help: Utilities");
?>

<body class="helpbody">
  <section class='container'>
    <h2>Utilities</h2>

    <h4>Back up, Restore &amp; Optimize Table Data</h4>
    <p>Use these functions to secure your data and keep your site running fast.</p>

    <span>Back Up</span>
    <p>To back up a single table, click on the Backup icon in the Action column next to the table to be backed up. You will see a success or failure message to the
      right of the row. The Last Backup column will also be updated, as will the size of the resulting file. If the operation is not successful,
      you may not have created a "Backups" folder (not necessarily named that; check your General Settings), or it may not have adequate permissions.
      Create the folder in your main TNG folder and give it read/write/execute rights for world/group/owner n (755 or 775, some systems will require 777),
      then go to the General Settings and make sure the folder name there matches the actual folder name exactly (case matters). After you have
      created an initial set of backups, you should be able to set permissions back to 771 for added security.
      <strong>NOTE</strong>: If all your individual and family data comes from a GEDCOM
      import, it is not necessary to back up the People, Children and Families tables, as these backups could be quite large and will only take up valuable space.
      In the event of a data loss, you can simply re-import your GEDCOM file to restore these tables.</p>

    <span>Restore</span>
    <p>To restore a single table, click on the Restore icon in the Action column next to the table to be restored. You will see a success or failure message to the
      right of the row. If the Restore icon is not present next to a particular table name, no backup for that table is available.
      <strong>NOTE</strong>: If a restore attempt produces a database error, you could be trying to restore a table whose current column
      structure does not match the structure that was in place when the last backup was made. That could mean that you made the backup prior to an upgrade that
      changed the table structure.</p>

    <span>Optimize</span>
    <p>To optimize a single table, click on the Optimize icon in the Action column next to the table to be optimized. You will see a success or failure message to the
      right of the row. A table should be optimized if you have deleted a large part of the table, have done several imports since your last optimization
      or if you have made many changes to variable-length fields. This will reclaim the unused space and defragment
      the datafile, usually resulting in improved performance. Some risks are associated with optimizing large tables, so be sure to back up your tables before
      you optimize them.</p>

    <span>Back Selected / Restore Selected / Optimize Selected / Delete Selected</span>
    <p>To back up, restore or optimize multiple tables at once, or to delete backup files, check the box in the Select column next to the desired tables, then select
      the appropriate action from the "With selected" dropdown box at the top of the page. To select all tables for any of these operations, click the "Select All" button.
      Likewise, all selections can be cleared by clicking the "Clear All" button.</p>

    <span>Other Recommendations</span>
    <p>After making a backup, the backup file will be stored in the Backups folder (as defined in your General Settings). It is recommended that you copy these files to
      your home computer, since any catastrophic event that affects your database tables could also affect any backups stored on the same computer. If your backup files
      take up too much space, you could then delete them from your web site and copy them back if a restore is needed.</p>
    <p>It is also highly recommended that you name your Backups folder something other than 'backups', as other TNG users with dishonorable intentions could easily steal your data.
      It is also recommended that you give your Backups folder 771 permissions if possible (after creation of initial backups), as this will prevent anyone from obtaining a directory listing of the folder.</p>

    <h4>Back up Table Structure</h4>
    <p>To back up your TNG table structure, click on the Backup icon in this section. If the operation is successful, the page will be redisplayed with a red message at the top
      detailing the action taken. The Last Backup column will also be populated, as will the size of the file. Having a backup of your table structure will enable you to easily restore your data
      in the event that your server suffers a catastrophic failure, especially if your current table structure differs at all from the structure you get from creating the tables again from scratch.</p>
    <p>To restore your TNG table structure, click on the Restore icon in this section. If the operation is successful, the page will be redisplayed with a red message at the top
      detailing the action taken.</p>
    <strong>WARNING: Restoring the table structure will delete all existing data!</strong>

    <h4>Resequence IDs</h4>
    <p>This feature allows you to assign new, sequential ID numbers to all of your people, families, sources and/or repositories. You must be in Maintenance Mode
      to run this utility. To enter Maintenance Mode, go to Admin/Setup/General Settings and select the Maintenance Mode option in the "Database" section.</p>

    <p><strong>WARNING: Performing this operation could have serious side effects!</strong> It could break links or bookmarks pointing to your site, it could corrupt search engine
      indexing, and it could cause your ID numbers to be out of synch with your original GEDCOM file (if you had one). It is highly recommended that you back up your
      tables before proceeding, just in case you don't like the results.</p>

    <p>Options on this page include:</p>

    <span>Tree</span>
    <p>You must select a Tree. This operation can only be performed on one tree at a time.</p>

    <span>ID Type</span>
    <p>Choices are People, Families, Sources or Repositories. Resequencing one type without doing the others should not have
      any adverse side affects not covered in the above warning.</p>

    <span>Minimum Digits</span>
    <p>This number dictates how long your new IDs will be. If the number of any given individual is less than the minimum number of digits, the remaining digits
      will be filled in with leading zeros. For example, if your Minimum Digits value is 5, your smaller numbers will be I00001, I00002, I00003, etc. If you don't
      want any leading zeros, leave this number at 1 (does not include ID prefix).</p>

  </section> <!-- .container -->
</body>
</html>
