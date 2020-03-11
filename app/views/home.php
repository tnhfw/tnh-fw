<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name = "keywords" content = "tnh, framework, php, HTML, Javascript, CSS3" />
    <meta name="description" content="A simple PHP framework using HMVC architecture">
    <meta name="author" content="Tony NGUEREZA">
    <title>TNH Framework</title>
    <link href="<?php echo Assets::css('bootstrap.min'); ?>" rel="stylesheet" type = "text/css" >
	<link href="<?php echo Assets::css('fontawesome-all.min'); ?>" rel="stylesheet" type = "text/css" >
    <link rel="icon" href="<?php echo Assets::img('favicon.ico'); ?>">
  </head>
  <body>
	<br />
	<br />
    <div class="row justify-content-center align-items-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-white font-weight-bold">
            <h3>Welcome on <?php echo TNH_NAME; ?> v<?php echo TNH_VERSION; ?></h3>
          </div>
          <div class="card-body">
            <img src = "<?php echo Assets::img('logo.png'); ?>" class = "img-responsive" style = "float:left;" />
            <h4>A simple PHP framework using HMVC architecture</h4>
            <br />
            <p><?php echo Html::a('https://github.com/tnhfw/tnh-fw', 'Framework on Github', array('class' => 'btn btn-sm btn-primary', 'target' => '_blank')); ?></p>
            <br />
            <hr />
            <p>Version : <b><?php echo TNH_VERSION; ?></b></p>
            <p>Required PHP version : <b>PHP >= <?php echo TNH_MIN_PHP_VERSION; ?>, PHP < <?php echo TNH_MAX_PHP_VERSION; ?></b></p>
            <p>Release date : <b><?php echo TNH_RELEASE_DATE; ?></b></p>
            <hr />
            <p>Current controller: <b class = "text-muted label-danger"><?php echo APPS_CONTROLLER_PATH . 'Home.php';?></b>
            <p>Current view: <b class = "text-muted label-danger"><?php echo APPS_VIEWS_PATH . 'home.php';?></b>
          </div>
        </div>
      </div>
    </div>
    
	<script src="<?php echo Assets::js('jquery.min'); ?>"></script>
	<script src="<?php echo Assets::js('bootstrap.bundle.min'); ?>"></script>
	</body>
</html>
