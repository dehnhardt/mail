<?php

declare(strict_types=1);

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\Mail\Command;

use OCA\Mail\Db\MailAccount;
use OCA\Mail\Service\AccountService;
use OCP\Security\ICrypto;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateAccount extends Command {
	public const ARGUMENT_USER_ID = 'user-id';
	public const ARGUMENT_NAME = 'name';
	public const ARGUMENT_EMAIL = 'email';
	public const ARGUMENT_IMAP_HOST = 'imap-host';
	public const ARGUMENT_IMAP_PORT = 'imap-port';
	public const ARGUMENT_IMAP_SSL_MODE = 'imap-ssl-mode';
	public const ARGUMENT_IMAP_USER = 'imap-user';
	public const ARGUMENT_IMAP_PASSWORD = 'imap-password';
	public const ARGUMENT_SMTP_HOST = 'smtp-host';
	public const ARGUMENT_SMTP_PORT = 'smtp-port';
	public const ARGUMENT_SMTP_SSL_MODE = 'smtp-ssl-mode';
	public const ARGUMENT_SMTP_USER = 'smtp-user';
	public const ARGUMENT_SMTP_PASSWORD = 'smtp-password';
	public const ARGUMENT_SIEVE_ENABLED = 'sieve-enabled';
	public const ARGUMENT_SIEVE_HOST = 'sieve-host';
	public const ARGUMENT_SIEVE_PORT = 'sieve-port';
	public const ARGUMENT_SIEVE_SSL_MODE = 'sieve-ssl-mode';
	public const ARGUMENT_SIEVE_USER = 'sieve-user';
	public const ARGUMENT_SIEVE_PASSWORD = 'sieve-password';

	/** @var AccountService */
	private $accountService;

	/** @var \OCP\Security\ICrypto */
	private $crypto;

	public function __construct(AccountService $service, ICrypto $crypto) {
		parent::__construct();

		$this->accountService = $service;
		$this->crypto = $crypto;
	}

	/**
	 * @return void
	 */
	protected function configure() {
		$this->setName('mail:account:create');
		$this->setDescription('creates IMAP account');
		$this->addArgument(self::ARGUMENT_USER_ID, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_NAME, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_EMAIL, InputArgument::REQUIRED);

		$this->addArgument(self::ARGUMENT_IMAP_HOST, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_IMAP_PORT, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_IMAP_SSL_MODE, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_IMAP_USER, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_IMAP_PASSWORD, InputArgument::REQUIRED);

		$this->addArgument(self::ARGUMENT_SMTP_HOST, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_SMTP_PORT, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_SMTP_SSL_MODE, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_SMTP_USER, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_SMTP_PASSWORD, InputArgument::REQUIRED);

		$this->addArgument(self::ARGUMENT_SIEVE_ENABLED, InputArgument::REQUIRED);
		$this->addArgument(self::ARGUMENT_SIEVE_HOST, InputArgument::OPTIONAL);
		$this->addArgument(self::ARGUMENT_SIEVE_PORT, InputArgument::OPTIONAL);
		$this->addArgument(self::ARGUMENT_SIEVE_SSL_MODE, InputArgument::OPTIONAL);
		$this->addArgument(self::ARGUMENT_SIEVE_USER, InputArgument::OPTIONAL);
		$this->addArgument(self::ARGUMENT_SIEVE_PASSWORD, InputArgument::OPTIONAL);
	}

	/**
	 * @return void
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$userId = $input->getArgument(self::ARGUMENT_USER_ID);
		$name = $input->getArgument(self::ARGUMENT_NAME);
		$email = $input->getArgument(self::ARGUMENT_EMAIL);

		$imapHost = $input->getArgument(self::ARGUMENT_IMAP_HOST);
		$imapPort = $input->getArgument(self::ARGUMENT_IMAP_PORT);
		$imapSslMode = $input->getArgument(self::ARGUMENT_IMAP_SSL_MODE);
		$imapUser = $input->getArgument(self::ARGUMENT_IMAP_USER);
		$imapPassword = $input->getArgument(self::ARGUMENT_IMAP_PASSWORD);

		$smtpHost = $input->getArgument(self::ARGUMENT_SMTP_HOST);
		$smtpPort = $input->getArgument(self::ARGUMENT_SMTP_PORT);
		$smtpSslMode = $input->getArgument(self::ARGUMENT_SMTP_SSL_MODE);
		$smtpUser = $input->getArgument(self::ARGUMENT_SMTP_USER);
		$smtpPassword = $input->getArgument(self::ARGUMENT_SMTP_PASSWORD);

		$sieveEnabled = $input->getArgument(self::ARGUMENT_SIEVE_ENABLED);
		$sieveHost = $input->getArgument(self::ARGUMENT_SIEVE_HOST);
		$sievePort = $input->getArgument(self::ARGUMENT_SIEVE_PORT);
		$sieveSslMode = $input->getArgument(self::ARGUMENT_SIEVE_SSL_MODE);
		$sieveUser = $input->getArgument(self::ARGUMENT_SIEVE_USER);
		$sievePassword = $input->getArgument(self::ARGUMENT_SIEVE_PASSWORD);

		$account = new MailAccount();
		$account->setUserId($userId);
		$account->setName($name);
		$account->setEmail($email);

		$account->setInboundHost($imapHost);
		$account->setInboundPort($imapPort);
		$account->setInboundSslMode($imapSslMode);
		$account->setInboundUser($imapUser);
		$account->setInboundPassword($this->crypto->encrypt($imapPassword));

		$account->setOutboundHost($smtpHost);
		$account->setOutboundPort($smtpPort);
		$account->setOutboundSslMode($smtpSslMode);
		$account->setOutboundUser($smtpUser);
		$account->setOutboundPassword($this->crypto->encrypt($smtpPassword));

		if ($sieveEnabled === true) {
			$account->setSieveHost($sieveHost);
			$account->setSievePort($sievePort);
			$account->setSieveSslMode($sieveSslMode);
			$account->setSieveUser($sieveUser);
			$account->setSievePassword($this->crypto->encrypt($sievePassword));
		}

		$this->accountService->save($account);

		$output->writeln("<info>Account $email created</info>");
	}
}
