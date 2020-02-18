 <?php
           
            $pdo = new PDO('sqlite:D:\wamp\www\tnh-fw\tests\assets\db_tests.db', '', '');
            $pdo->exec("SET NAMES 'UTF8' COLLATE 'utf8_general_ci'");
            $pdo->exec("SET CHARACTER SET 'UTF8'");
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $pdo->setAttribute(PDO::ATTR_CURSOR, PDO::CURSOR_FWDONLY);
			$r = $pdo->query("SELECT * FROM ses WHERE s_id = 'tony' LIMIT 1");
			
			$nb = $r->rowCount();
			echo $nb;
			$d = $r->fetch(PDO::FETCH_OBJ);
			
			var_dump(count($d));