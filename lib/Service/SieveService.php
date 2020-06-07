<?php

declare(strict_types=1);

/**
 * @author Holger Dehnhardt <holger@dehnhardt.org>
 *
 * Mail
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Mail\Service;

use OCA\Mail\Account;
use OCA\Mail\Db\MailAccountMapper;
use OCA\Mail\Exception\ServiceException;
use OCA\Mail\Sieve\SieveClientFactory;
use OCP\ILogger;
use OCP\Security\ICrypto;

class SieveService {

	/** @var SieveClientFactory */
	private $sieveClientFactory;

	/** @var MailAccountMapper */
	private $mapper;

	/** @var ICrypto */
	private $crypto;

	/** @var ILogger */
	private $logger;

	/**
	 * @param SieveClientFactory $sieveClientFactory
	 * @param MailAccountMapper $mailAccountMapper
	 * @param ICrypto $crypto
	 * @param ILogger $logger
	 */

	public function __construct(SieveClientFactory $sieveClientFactory, MailAccountMapper $mailAccountMapper, ICrypto $crypto, ILogger $logger) {
		$this->sieveClientFactory = $sieveClientFactory;
		$this->mapper = $mailAccountMapper;
		$this->crypto = $crypto;
		$this->logger = $logger;
	}

	/**
	 * @param Account $account
	 * @param array $sieveParams
	 *
	 * @return bool
	 * @throws ServiceException
	 */
	public function updateSieveAccount(Account $account, array $sieveParams): bool {
		$this->logger->info('updateSieveAccount from SieveService');
		/* Try to establish a sieve connection and save the entered values only if successful */
		try {
			$sieveClient = $this->sieveClientFactory->getSieveClient($account, $sieveParams);
		} catch (ServiceException $e) {
			throw $e;
		} catch (\Throwable $throwable) {
			throw new ServiceException($throwable->getMessage());
		}
		$mailAccount = $account->getMailAccount();
		$mailAccount->setSieveUser($sieveParams['user']);
		$mailAccount->setSievePassword($this->crypto->encrypt($sieveParams['password']));
		$mailAccount->setSieveHost($sieveParams['host']);
		$mailAccount->setSievePort($sieveParams['port']);
		$mailAccount->setSieveSslMode($sieveParams['secure']);
		$mailAccount->setSieveEnabled(true);
		try {
			$this->mapper->save($mailAccount);
		} catch (\Throwable $throwable) {
			throw new ServiceException($throwable->getMessage());
		}
		return true;
	}
}
