<?php
namespace Szurubooru\SearchServices;

class NamedSearchToken extends SearchToken
{
	private $key = false;

	public function setKey($key)
	{
		$this->key = $key;
	}

	public function getKey()
	{
		return $this->key;
	}
}
