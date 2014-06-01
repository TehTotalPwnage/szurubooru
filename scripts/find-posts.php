<?php
require_once __DIR__ . '/../src/core.php';

Access::disablePrivilegeChecking();

array_shift($argv);
$query = join(' ', $argv);

$posts = PostSearchService::getEntities($query, null, null);
foreach ($posts as $post)
{
	echo implode("\t",
	[
		$post->getId(),
		$post->getName(),
		$post->getContentPath(),
		$post->getMimeType(),
	]). PHP_EOL;
}
