<?php

/**
 * 命名空间注册配置
 */

$autoload = [
	'Events\Api' => $dir . '/library/events/api/',
	'Micro\Messages' => $dir . '/library/micro/messages/',
	'Utilities\Debug' => $dir . '/library/utilities/debug/',
	'Security\Hmac' => $dir . '/library/security/hmac/',
	'Application' => $dir . '/library/application/',
	'Interfaces' => $dir . '/library/interfaces/',
	'Controllers' => $dir . '/controllers/',
	'Models' => $dir . '/models/'
];

return $autoload;
