<?php
require 'tng_begin.php';

require 'classes/surname_cloud.class.php';

$tngconfig['showshare'] = 0;
$tngconfig['showprint'] = 1;
$tngconfig['showbmarks'] = 1;

scriptsManager::setShowShare($tngconfig['showshare'], $http);
initMediaTypes();

header('Content-type: text/html; charset=' . $session_charset);
$headSection->setTitle($sitename ? '' : uiTextSnippet('mnuheader'));
?>
<!DOCTYPE html>
<html>
<?php echo $headSection->build($flags, 'public', $session_charset); ?>
<body id='public'>
  <section class='container'>
    <?php echo $publicHeaderSection->build(); ?>
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
            $nc->display(50);
            ?>
          </div> <!-- .card -->
        </article>
      </section>
    </div> <!-- .row -->
    <?php
    echo $publicFooterSection->build();
    ?>
  </section> <!-- .container -->
  <?php echo scriptsManager::buildScriptElements($flags, 'public'); ?>
</body>
</html>
