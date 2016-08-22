<?php
require '../../helplib.php';
echo help_header("Help: Users");
?>

<body class="helpbody">
  <section class='container'>
    <h2>Users</h2>
    <h4>Search</h4>
    <p>Locate existing users by searching for all or part of the <strong>Username, Description, Real Name</strong> or <strong>E-mail</strong>. Check the "Show
      Admin users only" option to further narrow your search.
      Searching with no options selected and no value in the search box will find all users in your database.</p>

    <p>Your search criteria for this page will be remembered until you click the <strong>Reset</strong> button, which restores all default values and searches again.</p>

    <span>Actions</span>
    <p>The Action buttons next to each search result allow you to edit or delete that result. To delete more than one record at a time, click the box in the
      <strong>Select</strong> column for each record to be deleted, then click the "Delete Selected" button at the top of the list. Use the <strong>Select All</strong> or <strong>Clear All</strong>
      buttons to toggle all select boxes at once.</p>

    <h4>Adding New Users</h4>
    <p>Setting up user records for your visitors allows you to give them special rights that they can enjoy only after logging in with their username and password. The
      first user you create should be the administrator (someone who has all rights and is not restricted to any tree, usually yourself). If you don't give yourself (the administrator) 
      adequate rights, you may not be able to get back into the Admin area. If you forget your username, go to the TNG login page and enter the e-mail
      address associated with your user account to have your username e-mailed to you. If you forget your password, enter your e-mail address and username to have a new,
      temporary password sent to you. After logging in with the new password, you can return to Admin/Users and reset the password to something more memorable.</p>

    <p>To add a new user, click on the <strong>Add New</strong> tab, then fill out the form. To edit an existing user, click on the Edit icon next to that user. When
      adding or editing a user, take note of the following:</p>

    <span>Description</span>
    <p>Give your user a short description to help you remember who it is. For example, you might enter "Site Administrator" or "Aunt Martha".</p>

    <span>Username</span>
    <p>A unique one-word identifier for this user (no two users may have the same username). The user will be required to enter the username when logging in. 20 characters max.</p> 

    <span>Password</span>
    <p>A secret word or string of characters (no spaces) that this user must also enter when logging in. When entered by the user in the appropriate field, the actual
      characters typed will be replaced on the screen by asterisks or some other character for privacy. 20 chars max. The password
      is encrypted in the database and may not be retrieved for viewing by anyone, including this user and Next Generation Software.</p>

    <span>Real Name</span>
    <p>The actual name (if applicable) of the user assigned to this information.</p>

    <span>Phone, E-mail, Web Site, Address, City, State/Province, Zip/Postal Code, Country, Notes</span>
    <p>Optional information pertaining to the user.</p>

    <span>Do not send mass e-mail to this user</span>
    <p>Check this box if you do not want any mass e-mail (see below) to be sent to this user.</p>

    <span>Tree / Person ID</span>
    <p>If this user corresponds to anyone in your database, you may indicate the Tree and Person ID of their individual record
      here. Doing this will allow this user to see living data for their own record even if their record is not included in
      their assigned tree or branch.</p>

    <span>Disabled</span>
    <p>Check this box to prevent this user from logging in without deleting his or her entire account.</p>

    <span>Roles and Rights</span>
    <p>See <a href="#rights">below for details on the roles and rights</a> that may be assigned to users.</p>

    <p><span>Required fields:</span> You must enter a username, a password, and a user description. All the other fields are optional, but it is highly
      recommended that you enter your e-mail address, just in case you forget your username or password at some point.</p>

    <h4>Deleting Users</h4>
    <p>To delete a user, use the <a href="#search">Search</a> tab to locate the user, then click on the Delete icon next to that user record. The row will
      change color and then vanish as the user is deleted.</p>

    <h4>Review</h4>

    <p>Click on the "Review" tab to manage new user registrations. These user records will not become active until they are edited and saved the first time. Once a record becomes 
      active, it will no longer be displayed on the Review tab. Instead, it will be findable on the "Search" tab.</p>

    <p>New user records listed on the Review page can be deleted or edited in the same way regular user records are deleted or edited. When editing a new user
      record, take note of the following:</p>

    <span>Notify this user upon account activation</span>
    <p>Check this box to send an e-mail notification to the new user upon activation (when the page is saved). The text of the message appears in the box below
      this option. Changes may be made prior to sending.</p>

    <h4>Roles and Rights</h4>

    <p>A "Right" is something a user may do when they are logged in. A "Role" is a predefined set of rights, so the
      list of selected rights (on the right side of the page) will change if you select a different role (the "Allow" rights
      at the bottom of the column are not affected by a role selection). You may
      define your own set of rights for a user by selecting "Custom" as the Role. Some roles imply that the user will be
      assigned to a tree, while others imply that the user will not be assigned to any tree. The role you select may
      therefore cause the assigned tree field to become deselected.</p>

    <p>The following rights can be assigned to a user:</p>

    <span>Allow to add any new data</span>
    <p>User may enter the Admin area to add new records, including media.</p>

    <span>Allow to add media only</span>
    <p>User may enter the Admin area to add new media, but nothing else.</p>

    <span>No Add rights</span>
    <p>User may not any new data.</p>

    <span>Allow to edit any existing data</span>
    <p>User may enter the Admin area to edit existing records, including media.</p>

    <span>Allow to edit media only</span>
    <p>User may enter the Admin area to edit existing media, but nothing else.</p>

    <span>Allow to submit edits for administrative review</span>
    <p>User may not enter the Admin area for editing purposes. Tentative changes may be made from the public area by clicking on the small
      Edit icon next to eligible events on the Individual and Family Group pages. Changes do not become permanent until approved by the administrator.</p>

    <span>No Edit rights</span>
    <p>User may not make changes to existing records.</p>

    <span>Allow to delete any existing data</span>
    <p>User may enter the Admin area to delete existing records, including media.</p>

    <span>Allow to delete media</span>
    <p>User may enter the Admin area to delete media, but nothing else.</p>

    <span>No Delete rights</span>
    <p>User may not delete any existing records.</p>

    <p>These rights are independent of the selected Role:</p>

    <span>Allow to view information for living individuals</span>
    <p>User may view information for living individuals while in the public area.</p>

    <span>Allow to view information for private individuals</span>
    <p>User may view information for private individuals while in the public area.</p>

    <span>Allow to download GEDCOMs</span>
    <p>User may use the GEDCOM tab to download a GEDCOM file from the GEDCOM tab in the public area. This overrides the setting for each tree in Admin/Trees.</p>

    <span>Allow to download PDfs</span>
    <p>User may use the PDF option to create a PDF file from various pages in the public area. This overrides the setting for each tree in Admin/Trees.</p>

    <span>Allow to view LDS information</span>
    <p>User may view LDS information while in the public area.</p>

    <span>Allow to edit user profile</span>
    <p>User may edit their user information (username, password, etc.) from a link in the public area.</p>

    <h4>Access Limits</h4>

    <p>These define the limits of a user's rights. All users (including anonymous visitors) may view information for deceased individuals at any time. No rights or access 
      limits are required.</p>

    <span>Allow access to all system settings...</span>
    <p>Check this option to allow the user to access system-wide options, such as the General Settings or Users.</p>

    <span>Restrict to Tree/Branch</span>
    <p>To restrict a user's rights to a particular tree, <span class="choice">select that tree here</span>. To restrict rights to a particular branch within the
      selected tree, select that branch here as well. Assigning a user to a branch will not prevent that user from seeing other individuals not in that branch.</p>

    <h4>E-mail</h4>
    <p>This tab allows you to send e-mail to all users, or all users assigned to a particular tree/branch combination.</p>

    <span>Subject</span>
    <p>The subject of your e-mail.</p>

    <span>Text</span>
    <p>The body of your e-mail.</p>

    <span>Tree</span>
    <p>If you want to send this message only to users assigned to a particular tree, select that tree here.</p>

    <span>Branch</span>
    <p>If you want to send this message only to users assigned to a particular branch within the selected tree,
      select that branch here.</p>

  </section> <!-- .container -->
</body>
</html>