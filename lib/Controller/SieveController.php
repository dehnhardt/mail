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

use OCA\Mail\Service\SieveService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\ILogger;

class SieveController extends Controller {

	/** @var SieveService */
	private $service;
	/** @var ILogger */
	private $logger;

	public function __construct(string $appName,
								IRequest $request,
								SieveService $service,
								ILogger $logger) {
		parent::__construct($appName, $request);

		$this->service = $service;
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
	 * @return JSONResponse
	 */
	public function updateAccount(int $accountId, bool $sieveEnabled, string $sieveHost, int $sievePort, string $sieveUser, string $sieveSslMode, string $sievePassword): JSONResponse {
		$this->logger->info("update account (from SieveController");
		return new JSONResponse($this->service->updateAccount($accountId));
	}
}
