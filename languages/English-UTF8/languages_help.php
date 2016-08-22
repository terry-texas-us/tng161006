<?php
require '../../helplib.php';
echo help_header("Help: Languages");
?>

<body class="helpbody">
  <section class='container'>
    <h2>Languages</h2>
    <h4>Search</h4>
    <p>Locate existing Languages by searching for all or part of the <strong>Display Name</strong> or <strong>Folder Name</strong>.
      Searching with no value in the search box will find all Languages in your database.</p>

    <p>Your search criteria for this page will be remembered until you click the <strong>Reset</strong> button, which restores all default values and searches again.</p>

    <span>Actions</span>
    <p>The Action buttons next to each language allow you to edit or delete that language.</p>

    <h4>Add New / Edit Existing Languages</h4>
    <p>The TNG display messages have been translated into several different languages. To allow visitors to your site to view the site in any language besides
      your default language, you must add a record here for each language you support, <strong>including</strong> your default language. For example,
      if your default language is English and you want to support French as well, you must add language records in Admin/Languages for both English and French.</p>

    <p>To add a new Language, click on the <strong>Add New</strong> tab, then fill out the form.
      Take note of the following:</p>

    <span>Language folder</span>
    <p>Use the dropdown to choose the location of the messages for this language. If your new language needs the UTF-8 character set, be sure to choose a folder with "UTF8" in the name.
      If you want to support a new language not previously supported by TNG, add a folder for that language within the TNG languages folder, then return to this page to select it.</p>

    <span>Name for this language as it will be displayed for visitors</span>
    <p>Enter the name of the language as it will be shown to visitors in the languages options box. It is recommended that you enter this name in the language it
      represents so that visitors can more easily identify it. For example, use "Norsk" instead of "Norwegian".</p>

    <span>Character set</span>
    <p>The character set used for this language. If left blank, ISO-8859-1 will be used.</p>

    <span>Required fields:</span>
    <p>You must enter a language display name, and you must choose the name of the language folder.</p>

    <p><strong>IMPORTANT:</strong> If you plan to allow dynamic language switching, <strong>you must set up your default language</strong> (from Setup/General Settings) as a language on this page.
      If you do not, you will not be able to switch back to your default language after switching to another one.</p>

    <h4>Deleting Languages</h4>
    <p>To delete a Language, use the <a href="#search">Search</a> tab to locate the Language, then click on the Delete icon next to that Language record. The row will
      change color and then vanish as the Language is deleted. <strong>Note</strong>: The associated folder on your site will not be deleted.</p>

  </section> <!-- .container -->
</body>
</html>
