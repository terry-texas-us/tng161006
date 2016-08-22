<?php
require '../../helplib.php';
echo help_header("Help: General Settings");
?>

<body class="helpbody">
  <section class='container'>
    
    <h2>General Settings</h2>
    <h4>Database</h4>

    <span>Database Host, Name, User Name, Password</span>
    <p>This is the information TNG and PHP will use to connect to your database. These fields must be filled in before your database 
      can be accessed. <strong>Note</strong>: The user name and password mentioned here may be different from
      your regular web site login. If, after entering this information, you continue to see an error message that TNG is not communicating
      with your database, then you know at least one of these values is incorrect. If you don't know the correct information, ask your web 
      hosting provider. The host name may also require a port number or a socket path (i.e., "localhost:3306" or "localhost:/path/to/socket"). 
      Case is important, so be mindful to type in everything exactly as it was given
      to you. If you are acting as your own webmaster, be sure you have created a database
      and added a user to it (the user must have ALL rights).</p>

    <span>Maintenance Mode</span>
    <p>When TNG is in Maintenance Mode, the data cannot be accessed from the public side of your site. Instead, visitors will see a 
      polite message telling them that you are performing maintenance on the site and they should try again later. You might wish to
      put your site in Maintenance Mode while you are re-importing your data. If you are resequencing your IDs, Maintenance Mode is
      required. If you ever find yourself "stuck" in Maintenance Mode, you can edit your config.php file directly and reset the $tngconfig['maint'] variable
      to 0 or blank.</p>

    <h4>Table Names</h4>

    <span>Table Names</span>
    <p>You shouldn't have to change any of the default names unless you already have one or more tables with one or more of these 
      names. Always make sure all table names are filled in and that all names are unique. Do not change any existing table names.</p>

    <h4>Paths and Folders</h4>

    <span>Root Path</span>
    <p>This is the system path to the folder or directory where your TNG files are located. It is not a web address.
      You must include a trailing slash. When you first open this page, your Root Path should be correct. Do not change it unless you are an advanced user
      or have been instructed to do so. If you blank out the field and save the page, the correct path will appear here the next time you load the page, but you
      will need to save the page again to keep the new path.</p>

    <span>Config Path</span>
    <p>If you would like to put your TNG configuration files in a more secure location outside of the "web root" directory (so they aren't
      accessible from the web), enter that path here. It <strong>must</strong> end with a trailing slash (/). It will likely be the first part of the Root Path.
      For example, if your Root Path is "/home/www/username/public_html/genealogy/", then you might choose "/home/www/username/" as your Config Path.</p>

    <p><strong>IMPORTANT:</strong> Use of this field is
      completely optional and will not affect the operation of your site one way or the other. You should only enter something here
      if you are very familiar with your web site's directory structure. If you do enter a path here, you <strong>must move the following files 
        to the Config Path immediately after saving</strong> and make them writeable (664 or 666 permissions): config.php, customconfig.php, importconfig.php,
      logconfig.php, mapconfig.php, pedconfig.php and templateconfig.php. If you don't, nothing on the site will be operational. If you make a mistake and your site stops working,
      you will need to manually edit your subroot.php file in order to correct the $tngconfig['subroot'] path (setting it back to blank will return your system
      to the way it was before).</p>

    <span>Photo / Document / History / Headstone / Multimedia / GENDEX / Backup / Mods / Extensions Folders</span>
    <p>Please enter folder or directory names for these respective entities. All should have global read+write+execute permissions (755 or 775, although some systems will require 777).
      The Multimedia folder is intended as a "catch all" for any media items that don't fit cleanly into the other categories (e.g., videos and
      audio recordings). These folders can be created from this screen by clicking on the "Make Folder" buttons.</p>

    <h4>Site Design and Definition</h4>

    <span>Home Page</span>
    <p>All TNG menus include a link to the "Home Page". Enter the address for this link here. By default this is the index.php page in the folder with your other
      TNG files. It must be a relative link ("index.php" or "../otherhomepage.html"), not an absolute link ("http://yoursite.com").</p>

    <span>Genealogy URL</span>
    <p>The web address for your genealogy folder (i.e., "http://mysite.com/genealogy").</p>

    <span>Site Name</span>
    <p>If you enter something here, it will be included in the HTML "Title" tag on every page and will show up at the top of your
      browser window.</p>

    <span>Site Description</span>
    <p>A short description of your site for use on the RSS feed page.</p>

    <span>Doctype Declaration</span>
    <p>This string is placed at the top of every page in the public area to give the user's browser the information it needs
      to render the page correctly. Validation tests run against the pages will use this information to determine what problems
      may exist. If you leave this blank, the default XHTML Transitional doctype will be used.</p>

    <span>Site Owner</span>
    <p>Your name, or your business name. This name will appear on outgoing e-mail messages originating from TNG.</p>

    <span>Target Frame</span>
    <p>If your site uses frames, use this field to indicate in which frame the TNG pages should display. If you are not using frames, 
      leave this as "_self".</p>

    <span>Custom Header / Footer / Meta</span>
    <p>File names for the page fragments to be used as your TNG page header, footer and HEAD section ("meta"). Files with the default names are supplied.
      To use PHP coding in these files, they must have .php extensions. To make use of TNG's design templates, you must keep these the header and footer named
      topmenu.php and footer.php respectively.</p>

    <span>Tab Style Sheet</span>
    <p>[tas] removed.</p>

    <span>Menu Location</span>
    <p>The TNG menu may be located on the top left of every page, just above the individual's name or other page heading, or on the top right of every page, directly across from
      the name or other page heading. The dynamic language selection dropdown will be located in the same section of the screen.</p>

    <span>Show Home / Search / Login/Logout / Share / Print / Add Bookmark Links</span>
    <p>Some of these options (Home/Search/Login) are located at the top left of every page, just under the page heading and above the row of tabs. The others
      (Share/Print/Add Bookmark) are located at the top right, just under the menu bar.
      Each of these options can be turned on or off using these controls.</p>

    <span>Search Link Destination</span>
    <p>The default behavior of the Search link at the top of every page is to open a small window where you can search by entering a name or ID. This is called "Quick Search".
      You may choose instead to have this link go to the Advanced Search page by choosing that option.</p>

    <span>Hide Christening Labels</span>
    <p>This option allows you to hide all mention of the "Christening" event.</p>

    <span>Default Tree</span>
    <p>When more than one tree exists, all pages where a selection is possible (including the search utility
      on your home page) will default to "All Trees". If you instead want to point this to one tree in particular,
      select that tree here. Whenever a user enters a URL without a tree ID (or with a blank tree ID), the request
      will be rerouted to this tree. <strong>NOTE</strong>: If you have only one tree, it is better to leave this field blank.</p>

    <h4>Media</h4>

    <span>Photos Extension</span>
    <p>The file extension assigned to all small pedigree-style photos. Other photos need not have this extension. The .jpg extension is recommended for most photos.</p>

    <span>Show Extended Photo Info</span>
    <p>If this option is checked, any available extended information will be displayed for each photo. This includes the physical file name, the dimensions in pixels, and any
      existing IPTC data.</p>

    <span>Image Max Height and Width</span>
    <p>When these values are set (pixels), images larger than these dimensions will be scaled down (using HTML) when displayed in the public area.</p>

    <span>Thumbnail Max Width</span>
    <p>When TNG automatically creates a thumbnail image, the image will be no wider than this (pixels).</p>

    <span>Thumbnails Prefix</span>
    <p>When generating thumbnails automatically, TNG will prepend this value to the original image file name to create the thumbnail file name. If the file name of the original includes path
      information, the prefix will be included directly before the file name. This prefix can include a folder name (ie, "thumbnails/"). If you
      will be using a folder name as part of the prefix, be sure that this folder exists and has the same permissions as the main Photos folder.</p>

    <span>Thumbnails Suffix</span>
    <p>When generating thumbnails automatically, TNG will append this value to the original image file name to create the thumbnail file name.</p>

    <span>Thumbnail Max Height</span>
    <p>When TNG automatically creates a thumbnail image, the image will be no taller than this (pixels).</p>

    <span>Thumbnail Max Width</span>
    <p>When TNG automatically creates a thumbnail image, the image will be no wider than this (pixels).</p>

    <span>Use default thumbnails</span>
    <p>If a person does not have a default photo and this option is enabled, a generic, gender-specific thumbnail will be used instead on all pages that reference
      this person.</p>

    <span>Columns in Thumbnail View</span>
    <p>When browsing all photos in thumbnail view, this many thumbnails will be displayed in a single row. If more
      exist, additional rows will be displayed up to the "Maximum Search Result" number of rows.</p>

    <span>Max characters in list notes</span>
    <p>If you want notes to be truncated when they are shown on list pages (like on the public Photos, Documents and Histories pages), set this to the maximum
      number of characters that should be displayed. Leave it blank to always show the entire note.</p>

    <span>Enable Slide Show</span>
    <p>Allows a set of photos to be shown automatically in succession from the public area when the "Start Slideshow" link is clicked. Setting
      this option to 'No' hides the link and disables the feature.</p>

    <span>Slide Show Auto Repeat</span>
    <p>Setting this option to 'Yes' allows the slide show to run continuously.</p>

    <span>Enable Image Viewer</span>
    <p>Setting this option to 'Always' shows every image-based media item (.jpg, .gif and .png files) in the image viewer. Setting it to 'Documents only' turns the
      image viewer off for all image-based media that are not 'Documents' or other media types that behave like Documents.</p>

    <span>Image Viewer Height</span>
    <p>Setting this option to 'Always show full image' will ensure that the entire image is viewable by default. Setting it to 'Fixed (640px)' causes images taller than
      640 pixels to be cropped at that height when the image is initially displayed. The viewer controls may still be used to pan around the image or to zoom in or out.</p>

    <span>Hide Personal Media</span>
    <p>If this option is set to "Yes", then media listings on a person's individual page will start in a collapsed state. Instead of seeing thumbnails and descriptions,
      you will see only a total count for each media type. Visitors will still be able to expand each media section, but they will be collapsed again if the page is refreshed.</p>

    <h4>Language</h4>

    <span>Language</span>
    <p>Your default language folder (i.e., 'English'). You may have more than one language available to visitors, but this language will always display first.</p>

    <span>Character Set</span>
    <p>The character set for your default language. If this is left blank, the browser's default character set will be used. The character set for English and other languages using the 26-character
      Roman alphabet is ISO-8859-1.</p>

    <span>Dynamic Language Change</span>
    <p>If you have set up more than one language and want users to be able to select a different language "on the fly",
      select <em>Allow</em>.</p>

    <h4>Privacy</h4>

    <span>Require Login</span>
    <p>Normally anyone can view your public pages, with a login to see data for living individuals being optional. If, however,
      you want to require everyone to log in before they can see anything beyond the home page, check this box.</p>

    <span>Restrict access to assigned tree</span>
    <p>If Require Login is set to 'Yes', then setting this option to 'Yes' will cause users to only see information associated with their
      assigned trees. All other individuals, families, sources, etc. will be hidden.</p>

    <span>Show LDS Data</span>
    <p>To always show LDS data (where available), select <em>Always</em> (this was the default before). To turn off all LDS
      information and the ability to manually enter LDS data, select <em>Never</em>. To make this switch dependent on
      user permissions, select <i>Depending on user rights</i>. In this case, only logged-in users who have rights
      to see LDS data will see it. It will be hidden from all others.</p>

    <span>Show Living Data</span>
    <p>To always show living data (dates and places for living individuals), select <i>Always</i>. To turn off all living
      information, select <i>Never</i>. To make the display of living data dependent on
      user permissions, select <i>Depending on user rights</i>. In this case, only logged-in users who have rights
      to see living data will see it. It will be hidden from all others.</p>

    <span>Show Names for Living</span>
    <p>To hide the names of individuals marked as Living (no death or burial information, plus a birthdate less than 110 years ago), select <em>No</em>. Names of living
      individuals will be replaced with the word "Living". To show the surname and first initial(s) of living individuals, select <em>Abbreviate first name</em>. To
      always show the names of living individuals for everyone, select <em>Yes</em>.</p>

    <span>Show Names for Private</span>
    <p>To hide the names of individuals marked as Private, select <em>No</em>. Names of private
      individuals will be replaced with the word "Private". To show the surname and first initial(s) of private individuals, select <em>Abbreviate first name</em>. To
      always show the names of private individuals for everyone, select <em>Yes</em>.</p>

    <h4>Names</h4>

    <span>Name Order</span>
    <p>Dictates how names will be displayed in most cases (some lists always display the surname first). Choose to display the first name first (Western) or the surname first (Oriental).
      If nothing is selected, names will be displayed "first name first".</p>

    <span>Uppercase All Surnames</span>
    <p>Allows you to dispay all surnames in upper case. If this option is set to "No", then names will appear as they were entered or imported.</p>

    <span>Surname Prefixes</span>
    <p>Governs how surname prefixes (i.e., "de" or "van") are treated. By default, anything imported in the GEDCOM surname field is part of the surname, and this dictates how
      surnames are sorted ("de Kalb" comes before "van Buren"). You can elect to keep surname prefixes as part of the surname, or you can choose to treat them
      as separate entities (thus, "van Buren" would then sort before "de Kalb"). Existing surnames will not be affected unless manually edited or converted with surnameconvert400.php.</p>

    <span>Prefix Detection on Import</span>
    <p>If you have elected to treat surname prefixes as separate entities, this section will provide rules to help the import routine decide what is a prefix. Prefixes are defined as
      portions of the name separated by spaces, but you can choose how many prefixes from each name will be part of TNG's prefix. In other words, if you indicate that
      the "Num. prefixes each (max)" is 1, then only the "van" from "van der Merwe" would be moved to the prefix field. On the other hand, if you set this value to 2 or higher, "van der"
      would be the prefix. You may also indicate one or more specific prefixes that should always be treated as full prefixes. In other words, if you set this value to "van der", then
      "van der" will always be considered a valid prefix, regardless of how high or low you set the previous value. Separate multiple values with commas. To recognize a
      prefix offset by an apostrophe, include the apostrophe in this list. For example: "van,vander,van der,d',a',de,das".</p>

    <h4>Cemeteries</h4>

    <span>Max lines per column (approx.)</span>
    <p>If you have a lot of cemeteries defined, this number will tell TNG to split the list and create another column when the
      number is reached.</p>

    <span>Suppress "Unknown" categories</span>
    <p>If you define a cemetery with a missing locality (e.g., no state or no county), TNG will normally create a heading labeled
      "Unknown" to accommodate the empty fields. Choosing this option will cause TNG to leave the "Unknown" headings off.</p>

    <h4>Mail and Registration</h4>

    <span>Email Address</span>
    <p>Your email address. When visitors request a new user account, an email message will be sent to this address. Submissions from the 
      "Contact Us" page will also be sent to this address. Messages originating from the "Suggest" form will go this address if there is
      no email address associated with the tree corresponding to the page from which the suggestion was sent (otherwise, the message
      will be sent there).</p>

    <span>Send all mail from address above</span>
    <p>When someone sends you a message using TNG, the program attempts to send it as if it came from them so that you
      can easily reply. Some hosting providers do not allow that, however. They will refuse to send email unless the sender's
      email address comes from the same domain as your site. If you find that email from TNG is not being sent, your host
      may be doing this to you. If that's the case, setting this option to Yes cause TNG to send all mail from the 
      TNG administrator's address (entered above on this page). That should correct the problem.</p>

    <span>Allow new user registrations</span>
    <p>Allows you to turn off the option for visitors to request a user account on your site.</p>

    <span>Notify on reviewable submissions</span>
    <p>Setting this option to "Yes" will cause an email message to be sent to the administrator whenever a tentative change is entered by someone
      with "Submitter" rights and is waiting for administrative review.</p>

    <span>Create new tree for user</span>
    <p>If this option is set to Yes, a new tree will automatically be created for each new user registration, and the
      user will be assigned to that tree.</p>

    <span>Auto approve new users</span>
    <p>Normally, all new user registrations require the administrator's approval before the accounts can become active.
      Changing this setting to Yes will automatically activate all new user requests. You will still want to edit
      the account settings to make sure the user has the rights you want them to have.</p>

    <span>Send acknowledgement email</span>
    <p>If this option is set to Yes, an email will be sent to each potential new user, informing them that their request
      has been received and is being processed. Does not apply if new registrations are automaticaly activated.</p>

    <span>Include password in welcome email</span>
    <p>Normally a user's chosen password is included in the "welcome" email informing them that their account is
      now active. If you don't want the password to be included, set this option to No.</p>

    <span>Use SMTP Authentication</span>
    <p>Normally TNG sends email by way of the PHP "mail" function. If you'd rather use the Simple Mail Transfer Protocol (meaning that a login
      must be supplied by the program before the mail can be sent), then set this option to "Yes". More options will then become visible. They are: SMTP host name,
      Mail username, Mail password, Port number and Encryption. Your hosting provider should be able to give you the correct values for these fields.</p>

    <h4>Miscellaneous</h4>

    <span>Max Search Results</span>
    <p>This limits the number of results that can be displayed for any public search query. This should be a relatively small, manageable number in order to maximize
      efficiency and enhance the user experience.</p>

    <span>Individuals Start With</span>
    <p>This indicates which information will be initially viewable when an individual record is displayed. If you
      select "Personal Information Only", then other categories such as Notes, Citations or Photos and Histories will
      be hidden until they or "All" are explicitly selected by the user.</p>

    <span>Show Notes</span>
    <p>Allows you to choose where notes are displayed on the individual page. The options are:</p>

    <ul>
      <li>In Notes Section: Displays all notes in a separate block at the bottom of the page.</li>
      <li>Underneath corresponding events where possible: Event-specific notes are displayed directly underneath the events to which they correspond. General notes are displayed
        at the bottom of the "Personal" section and each "Family" section. If the general notes are long, scroll bars will be provided to prevent the pages from getting too long
        (the max height of the area can be found in genstyle.css, in the "notearea" block).</li>
      <li>Underneath events, except general notes: Same as above, but general notes are always displayed in a separate block at the bottom of the page. No max height is imposed.</li>
    </ul>

    <span>Scroll citations</span>
    <p>Setting this to "Yes" will cause the Sources area at the bottom of each person's individual page to have a maximum height. If any person has enough source citations
      to make the area taller than the maximum height, then the area will be scrollable.</p>

    <span>Server time offset (hours)</span>
    <p>If your server is in a different time zone than you are, enter the difference in hours here. If your time is behind the server time, enter a negative number.</p>

    <span>Edit timeout (minutes)</span>
    <p>The number of minutes a user is allowed to have exclusive edit rights to any individual or family record. During this time, any other user who tries to edit
      the same record will see a message indicating that the record is locked. If the original user is still editing the record when the time is about to elapse, that user
      will see a message warning them to save their changes soon. If the user does not attempt to save before another user succeeds in gaining access to the record, those changes
      will be lost.</p>

    <span>Max Generations, GEDCOM download</span>
    <p>The number of maximum number generations that can be exported in a publicly requested GEDCOM file.</p>

    <span>What's New Days</span>
    <p>The number of days to keep new items on the "What's New" page. To remove this limitation, set the value to zero. Doing that will cause older
      items to remain on the list until bumped off by newer items.</p>

    <span>What's New Limit</span>
    <p>The maximum number of items in each category to display on the "What's New" page.</p>

    <span>Numeric Date Preference</span>
    <p>If you enter a numeric date (e.g., 04/09/2008), this option determines whether to interpret the entry as Month/Day/Year (9 Apr 2008)
      or Day/Month/Year (4 Sep 2008).</p>

    <span>First day of week</span>
    <p>When the Calendar page is displayed, this day will be in the first column from the left.</p>

    <span>Parental data on person page</span>
    <p>Choose which events (if any) to display relating to the family of the individual's parents.</p>

    <span>Line Ending</span>
    <p>This is the character string that will included at the end of each line when exporting a GEDCOM file. It's also
      the string that will define the end of a line when importing. The default is "\r\n", which means "carriage return
      plus line feed". Some programs or operating systems prefer just a carriage return (\r) or a line feed (\n), so
      you may wish to adjust this setting at least temporarily in some cases.</p>

    <span>Encryption type</span>
    <p>Passwords in TNG are encrypted before they are stored in the database. Because of that, you can't simply change or remove your password
      by manually editing the database. The default encryption method is md5, but you may select another method here.</p>

    <span>Assign Place records to Trees</span>
    <p>If this is set to "Yes", then each Place record will be associated with one of your Trees. That means that if you have multiple trees, you could have
      the same place appear multiple times in your Places table because it is associated with more than one tree. If you change this option to "No", you 
      will be given the chance to automatically merge all Places into a single list. If you change this option to "Yes", you will see the option to assign
      a particular Tree to all the Places (since they previously wouldn't have had any assignment).</p>

    <span>Geocode all new places</span>
    <p>If this option is set to "Yes", then all new places entered in Admin/People and Admin/Families will be automatically geocoded (assumes an
      Internet connection).</p>

    <span>Re-use deleted IDs</span>
    <p>If this option is set to "Yes", new People, Families, Sources and Repositories will try to reuse ID numbers that were previously deleted.</p>

    <span>Show last import</span>
    <p>If this option is set to "Yes", the date of the most recent GEDCOM import will be shown on the What's New and Statistics pages when a tree is selected.</p>

  </section> <!-- .container -->
</body>
</html>