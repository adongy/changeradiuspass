<?php

require_once __DIR__.'/../config.php';

if ($use_cas) {
	if (!@include_once 'CAS.php')
		require_once __DIR__.'/../CAS/CAS.php';
}

session_start();

if ($use_cas && isset($_REQUEST['cas'])) {
	phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
	phpCAS::setCasServerCACert($cas_server_ca_cert_path);

	phpCAS::forceAuthentication();

	$_SESSION['logged_in_from'] = 'cas';
	$_SESSION['user'] = phpCAS::getUser();
} else {
	if (!empty($_POST['user']) && !empty($_POST['pass'])) {
		if (login_user($_POST['user'], $_POST['pass'])) {
			$_SESSION['logged_in_from'] = 'db';
			$_SESSION['user'] = $_POST['user'];
		} else {
			$msg = 'Invalid username or password.';
		}
	}
}

if (isset($_REQUEST['logout'])) {
	if ($use_cas && $_SESSION['logged_in_from'] == 'cas') {
		//phpCAS handles destroying the session
		phpCAS::logout();
	} else {
		session_destroy();
	}
	$msg = 'Successfully logged out.';
}

if (!empty($_SESSION['user'])) {
        if (!check_user($_SESSION['user'])) {
                $msg = 'Please register an account before trying to change your password.';
        } else {
                header('Location: ../');
                exit();
        }
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../pass.css">
    <title>Log in</title>
  </head>
  <body>
    <p><img src="../eclair.png" alt="ECLAIR"/></p>
    <?php if (isset($msg)) { echo '<p>'.$msg.'</p>'; } ?>
    <p>
      <form action="." method="POST">
        <fieldset>
          <p class="grouptop">
            <input type="text" name="user" id="user" placeholder="Username" value="" autofocus autocomplete="off" autocapitalize="off" autocorrect="off" required />
          </p>
          <p class="groupbottom">
            <input type="password" name="pass" id="pass" placeholder="Password" value="" autofocus autocomplete="off" autocapitalize="off" autocorrect="off" required />
          </p>
          <input type="submit" id="submit" class="login primary" value="Log in"/>
        </fieldset>
      </form>
    </p>
    <?php if ($use_cas) echo '<p><a href="?cas=">Login with CAS</a></p>'; ?>
    <!-- Développé pour ECLAIR par Anthony Dong -->
  </body>
</html>

