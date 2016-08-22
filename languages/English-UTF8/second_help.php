<?php
require '../../helplib.php';
echo help_header("Help: Secondary Processes");
?>

<body class="helpbody">
  <section class='container'>
    <h2>Secondary Processes</h2>
    <h4>What are Secondary Processes?</h4>
    <p>Secondary Processes are operations you may want to perform on your data directly following an import. To perform one of these operations,
      you must first select whether it should apply to "All Trees" or
      only one tree in particular. If only one tree, select that tree here. Operations you can perform include:</p>

    <span>Track Lines</span>
    <p>Once you have imported your data, click here to trace through the selected tree and mark all individuals with children. This will allow visitors
      to your site to more easily find your primary lines of descent.</p>

    <span>Sort Children</span>
    <p>Click here to sort the children in each family of the selected tree according to birth date. This will supersede any previous sorting done in
      other parts of TNG or in your desktop application.</p>

    <span>Sort Spouses</span>
    <p>Click here to sort spouses for each person of the selected tree according to marriage date. This will supersede any previous sorting done in
      other parts of TNG or in your desktop application.</p>

    <span>Relabel Branches</span>
    <p>Re-importing your GEDCOM with the <span class="emphasis">Replace All Data</span> option will cause any previously existing branch labels to be
      removed. Click this button to restore those labels (IDs must match those from your previous data).</p>

    <span>Create GENDEX</span>
    <p>Click here to create an indexable file in GENDEX format. You determine the folder name (where the file will be stored) in the General Settings.
      If you selected "All Trees", this file will be named "gendex.txt". If you selected a tree, the name of your GENDEX file will be the TreeID (not the Tree Name),
      plus .txt for an extension. To have the file indexed by GENDEX, visit
      <a href="http://tngnetwork.lythgoes.net" target="_blank">http://tngnetwork.lythgoes.net</a> for further instructions.</p>

    <span>Post your GENDEX file on the TNG Network</span>
    <p>To have the file indexed by GENDEX, visit the <a href="http://tngnetwork.lythgoes.net" target="_blank">TNG Network</a> and click on "Register your site".
      You will be asked to create an account, after which you will be able to import your GENDEX file. Any time you want to update your listings on the TNG Network,
      you will need to recreate and re-import your GENDEX file.</p>

    <span>Trim Media Menus</span>
    <p>TNG includes menu options for several standard media collections (Photos, Documents, Histories, Headstones, Videos and Recordings). If you don't have any items
      for one or more	of those collections, you can remove them from the menus by clicking this option. If you add an item for any of the "trimmed" collections in
      the future, it will automatically be added back for you.</p>
</section> <!-- .container -->
</body>
</html>
