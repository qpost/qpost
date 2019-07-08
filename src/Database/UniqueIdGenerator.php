<?php

namespace qpost\Database;

use Doctrine\ORM\Id\AbstractIdGenerator;
use qpost\Util\Util;

class UniqueIdGenerator extends AbstractIdGenerator {
	/**
	 * {@inheritdoc}
	 */
	public function generate(\Doctrine\ORM\EntityManager $em, $entity) {
		$id = Util::getRandomString(128);

		if (null !== $em->getRepository(get_class($entity))->findOneBy(["id" => $id])) {
			$id = $this->generate($em, $entity);
		}

		return $id;
	}
}