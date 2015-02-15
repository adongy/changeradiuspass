<?php

$app_name = 'ECLAIR';
$app_logo = 'eclair.png';

$use_cas = true;
$cas_host = 'example.com';
$cas_port = 443;
$cas_context = '';
//This is realtive to the login/ folder
$cas_server_ca_cert_path = '../cachain.pem';

$db_driver = 'mysql';
$db_host = 'localhost';
$db_port = '3306';
$db_user = 'radius';
$db_pass = 'radpass';
$db_name = 'radius';

//Do not edit below this line

try {
	$dbh = new PDO($db_driver . ':host=' . $db_host . ';port=' . $db_port . ';dbname=' . $db_name, $db_user, $db_pass);
} catch (PDOException $e) {
	echo "Error connecting to database. Please retry later.";
	die();
}

//Check if user is in radcheck
//Assume that if username exists then it has a *-Password attribute
function check_user($user) {
	global $dbh;
	
	$sql = $dbh->prepare(
		"SELECT COUNT(*)
		FROM radcheck
		WHERE UserName=:user"
	);
	$sql->bindValue(':user', $user);
	
	$sql->execute();

	if ($sql->fetchColumn()) {
		return 1;
	} else {
		return 0;
	}
}
	
//Compute NTLM hash of input
function ntlm_hash($input) {
	$input = iconv('utf-8', 'utf-16le', $input);
	return hash('md4', $input);
}

//Try to log in user. Returns 1 on success, 0 otherwise
function login_user($user, $pass) {
	global $dbh;

	$sql = $dbh->prepare(
		"SELECT Attribute, Value
		FROM radcheck
		WHERE UserName=:user
		AND op=':='
		AND Attribute LIKE '%-Password'"
	);
	$sql->bindValue(':user', $user);

	$sql->execute();

	//Try to login with Cleartext and NT
	$passwords = $sql->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);
	if (array_key_exists('Cleartext-Password', $passwords)) {
		return $passwords['Cleartext-Password']['Value'] === $pass;
	} elseif (array_key_exists('NT-Password', $passwords)) {
		return $passwords['NT-Password']['Value'] === ntlm_hash($pass);
	} else {
		return 0;
	}
}

//Update password, inserting NT-Password and removing Cleartext-Password if needed
function update_user_password($user, $pass) {
	global $dbh;
	
	$dbh->beginTransaction();

	$sql = $dbh->prepare(
		"DELETE FROM radcheck
		WHERE UserName=:user
		AND Attribute='Cleartext-Password'"
	);
	$sql->bindValue(':user', $user);

	$sql->execute();

	if ($sql->rowCount()) {
		//Assume that if user has Cleartext-Password he does not have NT-Password
		$sql = $dbh->prepare(
			"INSERT INTO radcheck
			(UserName, Attribute, op, Value)
			VALUES (:user, 'NT-Password', ':=', :pass)"
		);
	} else {
		$sql = $dbh->prepare(
			"UPDATE radcheck
			SET Value=:pass
			WHERE UserName=:user
			AND Attribute='NT-Password'"
		);
	}
	$sql->bindValue(':user', $user);
	$sql->bindValue(':pass', ntlm_hash($pass));

	$sql->execute();
	
	$dbh->commit();

	return 'Password updated.';
}

?>
