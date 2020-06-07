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

namespace OCA\Mail\Controller;

use OCA\Mail\Exception\ServiceException;
use OCA\Mail\Exception\ClientException;
use OCA\Mail\Service\AccountService;
use OCA\Mail\Service\SieveService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\ILogger;

class SieveController extends Controller {

	/** @var AccountService */
	private $accountService;
	/** @var UserId */
	private $currentUserId;
	/** @var SieveService */
	private $sieveService;
	/** @var ILogger */
	private $logger;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param AccountService $accountService
	 * @param string $UserId
	 * @param SieveService $sieveService
	 * @param ILogger $logger
	 */

	public function __construct(string $appName,
								IRequest $request,
								AccountService $accountService,
								$UserId,
								SieveService $sieveService,
								ILogger $logger) {
		parent::__construct($appName, $request);

		$this->accountService = $accountService;
		$this->currentUserId = $UserId;
		$this->sieveService = $sieveService;
		$this->logger = $logger;
	}

	/**
	 * @NoAdminRequired
	 * @TrapError
	 *
	 * @param int $accountId
	 * @param bool $sieveEnabled
	 * @param string $sieveHost
	 * @param int $sievePort
	 * @param string $sieveUSer
	 * @param string $sieveSslMode
	 * @param string $sievePassword
	 *
	 * @return JSONResponse
	 * @throws ClientException
	 */
	public function updateSieveAccount(int $accountId, bool $sieveEnabled, string $sieveHost, int $sievePort, string $sieveUser, string $sieveSslMode, string $sievePassword): JSONResponse {
		$this->logger->info("update account (from SieveController");
		$account = $this->accountService->find($this->currentUserId, $accountId);

		$params = [
			'host' => $sieveHost,
			'port' => $sievePort,
			'user' => $sieveUser,
			'password' => $sievePassword,
			'secure' => $sieveSslMode,
		];

		try {
			$ret = $this->sieveService->updateSieveAccount($account, $params);
		} catch (ServiceException $e) {
			throw new ClientException($e->getMessage(), $e->getCode());
		}
		return new JSONResponse(['sieveAccountEnabled', $ret]);
	}
}
