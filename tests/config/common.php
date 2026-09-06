<?php

$dsn = getenv('HUMHUB_TEST_DB_DSN');

if ($dsn === false || $dsn === '') {
	return [];
}

return [
	'components' => [
		'db' => [
			'dsn' => $dsn,
			'username' => getenv('HUMHUB_TEST_DB_USER') ?: 'root',
			'password' => getenv('HUMHUB_TEST_DB_PASSWORD') ?: '',
			'charset' => 'utf8mb4',
		],
	],
];
