<?php

declare(strict_types=1);

/**
 * @author Christoph Wurst <holger@dehnhardt.org>
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

namespace OCA\Mail\Tests\Unit\Controller;

use ChristophWurst\Nextcloud\Testing\TestCase;

use OCA\Mail\Account;
use OCA\Mail\Controller\SieveController;
use OCA\Mail\Exception\ServiceException;
use OCA\Mail\Service\AccountService;
use OCA\Mail\Service\SieveService;

use OCP\AppFramework\Http\JSONResponse;
use OCP\ILogger;
use OCP\IRequest;

use PHPUnit\Framework\MockObject\MockObject;

class SieveControllerTest extends TestCase {

	/** @var int */
	private $accountId = 1;

	/** @var string */
	private $appName = 'mail';

	/** @var SieveController */
	private $controller;

	/** @var AccountService|MockObject*/
	private $accountService;

	/** @var SieveService|MockObject*/
	private $sieveService;

	/** @var string */
	private $userId = 'john';

	/** @var IRequest|MockObject */
	private $request;

	/** @var ILogger|MockObject */
	private $logger;

	/** @var MockObject|Account */
	private $account;

	protected function setUp(): void {
		parent::setUp();
		$this->request = $this->createMock(IRequest::class);
		$this->accountService = $this->createMock(AccountService::class);
		$this->sieveService = $this->createMock(SieveService::class);
		$this->account = $this->createMock(Account::class);
		$this->logger = $this->createMock(ILogger::class);

		$this->controller = new SieveController(
			$this->appName,
			$this->request,
			$this->accountService,
			$this->userId,
			$this->sieveService,
			$this->logger
		);
	}

	public function testUpdateSieveAccount() {
		$accountId = 3;
		$sieveEnabled = true;
		$sieveHost = 'localhost';
		$sievePort = 1234;
		$sieveUser = $this->userId;
		$sieveSslMode = '';
		$sievePassword = 'password';

		$params = [
			'host' => $sieveHost,
			'port' => $sievePort,
			'user' => $sieveUser,
			'password' => $sievePassword,
			'secure' => $sieveSslMode,
		];

		$expectedResponse = new JSONResponse(
			['sieveEnabled' => true,
				'message' => 'account modified successfully']
		);
		$this->sieveService->expects($this->once())
			->method('updateSieveAccount')
			->willThrowException(new \Exception);

		$this->expectException(ServiceException::class);
		$this->controller->updateSieveAccount($accountId, $sieveEnabled, $sieveHost, $sievePort, $sieveUser, $sieveSslMode, $sievePassword);
	}
}
