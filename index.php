<?php

require_once __DIR__.'/config.php';

session_start();

if (empty($_SESSION['user']) || !check_user($_SESSION['user'])) {
	header('Location: login/');
    exit();
}

if (!empty($_POST['pass']) && !empty($_POST['pass_confirm'])) {
	if ($_POST['pass'] != $_POST['pass_confirm']) {
		$msg = 'Passwords do not match.';
	} else {
		$msg = update_user_password($_SESSION['user'], $_POST['pass']);
	}
} else {
	if (!empty($_POST['pass']) || !empty($_POST['pass_confirm'])) {
		$msg = 'A field was empty.';
	}
}


?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="pass.css">
    <title>Change your password</title>
  </head>
  <body>
    <p><img src="eclair.png" alt="ECLAIR"/></p>
    <h2>Hello <b><?php echo $_SESSION['user']; ?></b>.</h2>
    <?php if (isset($msg)) { echo '<p>'.$msg.'</p>'; } ?>
    <p>
      <form action="." method="POST">
        <fieldset>
          <p class="grouptop">
            <input type="password" name="pass" id="pass" placeholder="New password" value="" autofocus autocomplete="off" autocapitalize="off" autocorrect="off" required />
          </p>
          <p class="groupbottom">
            <input type="password" name="pass_confirm" id="pass_confirm" placeholder="Retype password" value="" autofocus autocomplete="off" autocapitalize="off" autocorrect="off" required />
          </p>
          <input type="submit" id="submit" class="login primary" value="Change password"/>
        </fieldset>
      </form>
    </p>
    <p><a href="login/?logout=<?php echo $_SESSION['logged_in_from'] == 'cas' ? '&cas=' : '' ?>">Logout</a></p>
    <!-- Développé pour ECLAIR par Anthony Dong -->
  </body>
</html>
