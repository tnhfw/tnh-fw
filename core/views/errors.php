<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Tony NGUEREZA">
    <title><?php echo $title;?></title>
	<style type = 'text/css'>
	/* reset */
		*{
			padding : 0;
			margin : 0;
		}
		body{
			margin : 10px auto;
			padding : 10px;
			font-family : Helvetica, arial, sans-serif;
		}
		
		.container{
			margin : 20px auto;
			max-width : 50%;
			border : 2px solid #ccc;
			box-shadow : 2px 2px 2px #ccc;
		}
		
		.title, .body{
			border-bottom : 2px solid #ccc;
			padding : 15px;
		}
		
		
		.title{
			background : #d9534f;
		}
		
		.title h2{
			text-transform : uppercase;
			font-weight : 200px;
			color : #fdfdff;
		}
		
		.body{
			border-bottom : none;
		}
		
		.body p{
			font-weight : none;
		}
		
		a{
			text-decoration : none;
			color : #ff2626;
		}
		
		a:hover{
			text-decoration : underline;
			color : #4000ff;
		}
	</style>
  </head>
  <body>
	<div class="container">
		<div class = "title">
			<h2><?php echo $title;?></h2>
		</div>
		<div class = "body">
			<p><?php echo $error;?></p>
		</div>
	</div> <!-- ./container-->
   </body>
</html>