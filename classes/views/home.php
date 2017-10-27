<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name = "keywords" content = "tnh, framework, php, HTML, Javascript, CSS3" />
    <meta name="description" content="A PHP simple framework created using the concept of codeigniter with bootstrap twitter">
    <meta name="author" content="Tony NGUEREZA">
    <title>TNH Framework</title>
    <link href="<?php echo Assets::css('bootstrap.min');?>" rel="stylesheet" type = "text/css" >
	<link href="<?php echo Assets::css('font-awesome.min');?>" rel="stylesheet" type = "text/css" >
    <link href="<?php echo Assets::css('responsive');?>" rel="stylesheet" type = "text/css" >
	<link rel="icon" href="<?php echo Assets::img('favicon.ico');?>">
	<!--[if lt IE 9]>
	<script src="<?php echo Assets::js('html5shiv');?>"></script>
	<script src="<?php echo Assets::js('respond.min');?>"></script>
    <![endif]-->
	<style type = "text/css">
		
	</style>
  </head>
  <body>
	<br />
	<br />
	<div class = "container">
		<div class = "row">
			<div class = "col-md-offset-2 col-md-8 col-md-offset-2">
				<div class = "panel panel-default">
					<div class = "panel-heading">
						<h2>Welcome on <?php echo TNH_NAME;?> v<?php echo TNH_VERSION;?></h2>
					</div>
					<div class = "panel-body">
						<h3>
							A simple PHP framework created using the concept of codeigniter with bootstrap twitter
						</h3>
						<br />
						<p><?php echo Html::a('http://www.iacademy.cf', 'Web site', array('class' => 'btn btn-primary', 'target' => '_blank'));?></p>
					</div>
				</div>
				<div class = "panel panel-default">
					<div class = "panel-heading">
						<h2>Server information</h2>
					</div>
					<div class = "panel-body">
						<h4>Running on : <b><?php echo php_uname();?></b></h4>
						<h4>PHP server SAPI : <b><?php echo php_sapi_name();?></b></h4>
						<h4>PHP Version : <b><?php echo phpversion();?></b></h4>
						<h4>PHP Loaded extensions : (<?php echo count(get_loaded_extensions());?> extensions)</h4>
							<table class = "table table-striped table-condensed table-bordered table-responsive">
								<tr>
									<th>Name</th>
									<th>Version</th>
								</tr>
								<?php foreach(get_loaded_extensions() as $e):?>
									<tr>
										<td><?php echo $e;?></td>
										<td><?php echo phpversion($e);?></td>
									</tr>
								<?php endforeach;?>
							</table>
					</div>
				</div>
				<div class = "panel panel-default">
					<div class = "panel-heading">
						<h2>Framework information</h2>
					</div>
					<div class = "panel-body">
						<h4>Version : <b><?php echo TNH_VERSION;?></b></h4>
						<h4>Required PHP version : <b>PHP >= <?php echo TNH_REQUIRED_PHP_MIN_VERSION;?>, PHP <= <?php echo TNH_REQUIRED_PHP_MAX_VERSION;?></b></h4>
						<h4>Build date : <b><?php echo TNH_BUILD_DATE;?></b></h4>
						<h4>Author : <b><?php echo TNH_AUTHOR;?></b></h4>
						<h4>Author E-mail : <b><?php echo TNH_AUTHOR_EMAIL;?></b></h4>
						<h4>Loaded files : (<?php echo count(get_included_files());?> files)</h4>
							<table class = "table table-striped table-condensed table-bordered table-responsive">
								<tr>
									<th>Path</th>
									<th>File</th>
								</tr>
								<?php foreach(get_included_files() as $f):?>
									<tr>
										<td><?php echo $f;?></td>
										<td><?php echo basename($f);?></td>
									</tr>
								<?php endforeach;?>
							</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script src="<?php echo Assets::js('jquery');?>"></script>
	<script src="<?php echo Assets::js('bootstrap.min');?>"></script>
	</body>
</html>
