<?php
require 'tng_begin.php';

$currentuser = $_SESSION['currentuser'];
$currentuserdesc = $_SESSION['currentuserdesc'];

$query = "SELECT languageID, display, folder FROM $languagesTable ORDER BY display";
$result = tng_query($query);

$numrows = tng_num_rows($result);

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle(uiTextSnippet('changelanguage'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
<?php echo $publicHeaderSection->build(); ?>
<h2><?php echo uiTextSnippet('changelanguage'); ?></h2>
<br clear='all'>
<?php 
if ($numrows) {
    $str .= buildFormElement("savelanguage", "post", "");
    echo "$str";

    echo uiTextSnippet('language') . ": \n";
?>
    <select name="newlanguage">
<?php
    while( $row = tng_fetch_assoc($result)) {
    echo "<option value=\"{$row['languageID']}\"";
    if( $row['folder'] == $mylanguage )
      {echo " selected";}
    echo ">{$row['display']}</option>\n";
    }
    tng_free_result($result);
?>
    </select>
    <br><br>
    <input type='submit' value="<?php echo uiTextSnippet('savechanges'); ?>">
    <br><br>
</form>
<?php
}
else
    {echo uiTextSnippet('language') . ": $mylanguage\n";}

echo $publicFooterSection->build();
echo scriptsManager::buildScriptElements($flags, 'public');
