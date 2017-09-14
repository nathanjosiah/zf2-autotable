<?php

namespace AutoTable;

class UnitOfWork {
	const TYPE_CREATE = 'create';
	const TYPE_UPDATE = 'update';
	const TYPE_DELETE = 'delete';
	const TYPE_LINK = 'link';
	const TYPE_UNLINK = 'UNLINK';

	public $object,$type;

	public function __construct($object, string $type) {
		$this->object = $object;
		$this->type = $type;
	}

	public static function create($object,string $type) : self {
		if($type === self::TYPE_LINK || $type === self::TYPE_UNLINK) {
			return new self($object,$type);
		}
		if(!$object instanceof Proxy) {
			throw new \Exception('Work must be for a Proxy');
		}
		return new self($object,$type);
	}
}

