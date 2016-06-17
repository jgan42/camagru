<?php
	require_once 'config/database.php';

	try
	{
		$db = new PDO($DB_DSN, $DB_USER, $DB_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	}
	catch (Exception $e)
	{
		exit('Erreur: ' . $e->getMessage());
	}
	$db->exec("CREATE DATABASE IF NOT EXISTS `camagru`");
	if (!$db->exec("USE `camagru`") || !$db->exec("SELECT 1 from `camagru`"))
		include_once ('config/setup.php');
?>
