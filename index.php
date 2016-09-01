<?php
require 'tng_begin.php';

require 'classes/chooseLanguage.php';
require 'classes/personSearchForm.class.php';
require 'classes/surname_cloud.class.php';

$tngconfig['showshare'] = 0;
$tngconfig['showprint'] = 1;
$tngconfig['showbmarks'] = 1;

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header("Content-type: text/html; charset=" . $session_charset);
$headSection->setTitle($sitename ? "" : uiTextSnippet('mnuheader'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php
    echo $publicHeaderSection->build();
    echo "<br>";
    echo "<div class='row'>\n";
    echo "<div class='offset-md-6 col-md-6'>";
    $form = new PersonSearchForm();
    echo $form->get();
    echo "</div>\n";
    echo "</div>\n";
    ?>
    <hr>
    <div class='row'>
      <section>
        <article class='col-lg-4 text-xs-center'>
          <h2><?php echo getTemplateMessage('welcome'); ?></h2>
          <?php echo getTemplateMessage('mainpara'); ?>
        </article>
        <article class='col-lg-4 text-xs-center'>
          <div class='card'>
            <img class='card-img-top' style='width:100%;' src="<?php echo $tmp['photol']; ?>" alt="">
            <div class='card-block'>
              <h3 class='card-title'>
                <a href="<?php echo $tmp['featurelink1']; ?>"><?php echo getTemplateMessage('phototitlel'); ?></a>
              </h3>
              <p class='card-text'><?php echo getTemplateMessage('photocaptionl'); ?></p>
            </div> <!-- .card-block -->
          </div> <!-- .card -->
        </article>
        <article class='col-lg-4 text-xs-center'>
          <div class='card card-block'>
            <h3 class='card-header'><?php echo getTemplateMessage('topsurnames'); ?></h3>
            <?php
            $nc = new Surname_cloud();
            $nc->display(32);
            ?>
          </div> <!-- .card -->
        </article>
      </section>
    </div> <!-- .row -->
    <?php
    echo "<div class='row'>\n";
    echo "<div class='offset-md-8 col-md-3'>";
    $chooseLanguage = new ChooseLanguage();
    echo $chooseLanguage->buildForm($instance);
    echo "</div>\n";
    echo "</div>\n";
    echo $publicFooterSection->build();
    ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>
