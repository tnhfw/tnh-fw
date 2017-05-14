<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Tony NGUEREZA">

    <title>Page not found</title>

    <link href="<?php echo Assets::css('bootstrap.min');?>" rel="stylesheet" type = "text/css" >
	<link href="<?php echo Assets::css('font-awesome.min');?>" rel="stylesheet" type = "text/css" >
	<link href="<?php echo Assets::css('responsive');?>" rel="stylesheet" type = "text/css" >
	<link rel="icon" href="<?php echo Assets::img('favicon.ico');?>">
	<!--[if lt IE 9]>
	<script src="<?php echo Assets::js('html5shiv');?>"></script>
	<script src="<?php echo Assets::js('respond.min');?>"></script>
    <![endif]--> 
  </head>
  <body>
	<div class="container">
	<br />
		<div class = "panel panel-primary">
			<div class = "panel-heading">
				<h1>404 Page Not Found</h1>
			</div>
			<div class = "panel-body">
				<h3>Désolé, la page que vous demandez n'existe pas ou a été déplacée voire supprimée. Retourner à la <a href = "<?php echo Url::base_url();?>"> page d'accueil</a></h3>
			</div>
		</div>
	</div> <!-- ./container-->	
	<script src="<?php echo Assets::js('jquery');?>"></script>
	<script src="<?php echo Assets::js('bootstrap.min');?>"></script>
</body>
</html>