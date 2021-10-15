<?php

    session_start();

    require('src/log.php');

    if(!empty(($_POST['email'])) && !empty($_POST['password']) && !empty($_POST['pseudo'])){

        require ('src/connect.php');

        $pseudo = htmlspecialchars($_POST['pseudo']);
        $email = htmlspecialchars($_POST['email']);
        $password = htmlspecialchars($_POST['password']);


        // ADRESSE EMAIL SYNTAXE
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            header('location: index.php?error=1&message=Votre adresse email est invalide.');
            exit();
        }

        // VERIFICATION PSEUDO
        $req = $db->prepare('SELECT COUNT(*) AS numberPseudo FROM user WHERE pseudo = ?');
        $req->execute(array($pseudo));

        while($pseudo_verification = $req->fetch()){
            if($pseudo_verification['numberPseudo'] != 1){
                header('location: index.php?error=1&message=Impossible de vous authentifier correctement.');
                exit();
            }
        }

        // CHIFFRAGE DU MOT DE PASSE
        $password = "aq1".sha1($password."123")."25";

        // EMAIL DEJA UTILISE
        $req = $db->prepare('SELECT COUNT(*) AS numberEmail FROM user WHERE email = ?');
        $req->execute(array($email));

        while($email_verification = $req->fetch()){
            if($email_verification['numberEmail'] != 1){
                header('location: index.php?error=1&message=Impossible de vous authentifier correctement.');
                exit();
            }
        }

        // CONNEXION
        $req = $db->prepare('SELECT * FROM user WHERE email = ?');
        $req->execute(array($email));

        while($user = $req->fetch()){
            if($password == $user['password'] && $user['blocked'] == 0){
                $_SESSION['connect'] = 1;
                $_SESSION['pseudo'] = $user['pseudo'];

                if(isset($_POST['auto'])){
                    setcookie('auth', $user['secret'], time() + 365*24*3600, '/', null, false, true);
                }

                $_SESSION['email'] = $user['email'];

                header('location: index.php?success=1');
                exit();
            }
            else {
                header('location: index.php?error=1&message=Impossible de vous authentifier correctement.');
                exit();
            }
        }
    }

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Netflix</title>
	<link rel="stylesheet" type="text/css" href="design/default.css">
	<link rel="icon" type="image/pngn" href="img/favicon.png">
</head>
<body>

	<?php include('src/header.php'); ?>
	
	<section>
		<div id="login-body">
            <?php if(isset($_SESSION['connect'])){?>

              <h1>Bonjour <?= $_SESSION['pseudo'].' ;)'?></h1>
                <?php
                    if(isset($_GET['success'])){
                        echo '<div class="alert success">Vous êtes maintenant connecté.</div>';
                    }
                ?>
                <p>Qu'allez-vous regarder aujourd'hui ?</p>
                <small><a href="logout.php">Déconnexion</a></small>
           <?php } else { ?>
                    <h1>S'identifier</h1>

                <?php

                    if(isset($_GET['error'])){
                        if (isset($_GET['message'])){
                            echo '<div class="alert error">'.htmlspecialchars($_GET['message']).' </div>';
                        }
                    }
                ?>
                    <form method="post" action="index.php">
                        <input type="pseudo" name="pseudo" placeholder="Votre psuedo" required />
                        <input type="email" name="email" placeholder="Votre adresse email" required />
                        <input type="password" name="password" placeholder="Mot de passe" required />
                        <button type="submit">S'identifier</button>
                        <label id="option"><input type="checkbox" name="auto" checked />Se souvenir de moi</label>
                    </form>

				<p class="grey">Première visite sur Netflix ? <a href="inscription.php">Inscrivez-vous</a>.</p>
            <?php } ?>
		</div>
	</section>

	<?php include('src/footer.php'); ?>
</body>
</html>