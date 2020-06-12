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
use OCA\Mail\Sieve\SieveParser;
use OCP\ILogger;
use OCP\Security\ICrypto;


class SieveService {

	/** @var SieveClientFactory */
	private $sieveClientFactory;

	/** @var MailAccountMapper */
	private $mapper;

	/** @var ICrypto */
	private $crypto;

	/** @var SieveParser */
	private $sieveParser;

	/** @var ILogger */
	private $logger;

	/**
	 * @param SieveClientFactory $sieveClientFactory
	 * @param MailAccountMapper $mailAccountMapper
	 * @param SieveParser $sieveParser
	 * @param ICrypto $crypto
	 * @param ILogger $logger
	 */

	public function __construct(SieveClientFactory $sieveClientFactory, MailAccountMapper $mailAccountMapper, SieveParser $sieveParser, ICrypto $crypto, ILogger $logger) {
		$this->sieveClientFactory = $sieveClientFactory;
		$this->mapper = $mailAccountMapper;
		$this->crypto = $crypto;
		$this->sieveParser = $sieveParser;
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
			// Disable sieve account if an error occurs
			$this->disableSieveAccount($account);
			throw $e;
		} catch (\Throwable $throwable) {
			// Disable sieve account if an error occurs
			$this->disableSieveAccount($account);
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

	/**
	 * @param Account $account
	 *
	 * @return object
	 */
	public function listScripts(Account $account) : array{
		$sieveClient = $this->sieveClientFactory->getSieveClient($account);
		$scripts = $sieveClient->listScripts();
		$activeScript = $sieveClient->getActive();
		$scriptContent = $this->getScriptContent($account, $activeScript);
		return ["scripts" => $scripts, "activeScript" => $activeScript, "scriptContent" => $scriptContent];
	}

	/**
	 * @param Account $account
	 * @param string $scriptName
	 *
	 * @return array
	 */
	public function getScriptContent(Account $account, string $scriptName) : array{
		$sieveClient = $this->sieveClientFactory->getSieveClient($account);
		$scriptContent = $sieveClient->getScript($scriptName);
		$parsedScript = $this->sieveParser->parse($scriptContent);
		return $parsedScript;
	}

	/**
	 * @param Account $account
	 *
	 */
	private function disableSieveAccount(Account $account) {
		$mailAccount = $account->getMailAccount();
		$mailAccount->setSieveEnabled(false);
		$updated=$this->mapper->save($mailAccount);
	}
}
