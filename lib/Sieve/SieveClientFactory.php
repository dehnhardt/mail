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

namespace OCA\Mail\Sieve;

use Horde_Imap_Client_Socket;
use Horde\ManageSieve;
use OCA\Mail\Account;
use OCA\Mail\Cache\Cache;
use OCA\Mail\Db\MailAccount;
use OCA\Mail\IMAP\IMAPClientFactory;
use OCP\IConfig;
use OCP\ILogger;
use OCP\Security\ICrypto;

class SieveClientFactory {

	/** @var ICrypto */
	private $crypto;

	/** @var IConfig */
	private $config;

	/** @var IMAPClientFactory */
	private $imapClientFactory;

	/** @var ILogger */
	private $logger;

	private $cache = [];

	/**
	 * @param ICrypto $crypto
	 * @param IConfig $config
	 * @param IMAPClientFactory $imapClientFactory
	 */
	public function __construct(ICrypto $crypto , IConfig $config, IMAPClientFactory $imapClientFactory) {
		$this->crypto = $crypto;
		$this->config = $config;
		$this->imapClientFactory = $imapClientFactory;
	}

	/**
	 * @param Account $account
	 * @return ManageSieve
	 */
	public function getSieveClient(Account $account): ManageSieve {
		if (!isset($this->cache[$account->getId()])) {
			$imapClient = $this->imapClientFactory->getClient($account);
			$mailAccount = $account->getMailAccount();
			$host = $mailAccount->getSieveHost();
			$user = $mailAccount->getSieveUser();
			$password = $mailAccount->getSievePassword();
			$password = $this->crypto->decrypt($password);
			$port = $mailAccount->getSievePort();
			$ssl_mode = $account->convertSslMode($mailAccount->getSieveSslMode());

			$params = [
				'host' => $host,
				'port' => $port,
				'user' => $user,
				'password' => $password,
				'secure' => $ssl_mode,
			];

			$this->cache[$account->getId()] = new ManageSieve($params);
		}
		return $this->cache[$account->getId()];
	}
}
