<?php

declare(strict_types=1);

/*
 * @copyright 2020 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2020 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Mail\Controller;

use OCA\Mail\AppInfo\Application;
use OCA\Mail\Contracts\ITrustedSenderService;
use OCA\Mail\Http\JsonResponse;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\IRequest;

class TrustedSendersController extends Controller {

	/** @var string|null */
	private $uid;

	/** @var ITrustedSenderService */
	private $trustedSenderService;

	public function __construct(IRequest $request,
								?string $UserId,
								ITrustedSenderService $trustedSenderService) {
		parent::__construct(Application::APP_ID, $request);

		$this->uid = $UserId;
		$this->trustedSenderService = $trustedSenderService;
	}

	/**
	 * @NoAdminRequired
	 * @TrapError
	 *
	 * @param string $email
	 *
	 * @return JsonResponse
	 */
	public function setTrusted(string $email): JsonResponse {
		$this->trustedSenderService->trust(
			$this->uid,
			$email
		);

		return JsonResponse::success(null, Http::STATUS_CREATED);
	}

	/**
	 * @NoAdminRequired
	 * @TrapError
	 *
	 * @param string $email
	 *
	 * @return JsonResponse
	 */
	public function removeTrust(string $email): JsonResponse {
		$this->trustedSenderService->trust(
			$this->uid,
			$email,
			false
		);

		return JsonResponse::success(null);
	}
	/**
	 * @NoAdminRequired
	 * @TrapError
	 *
	 *
	 * @return JsonResponse
	 */
	public function list(): JsonResponse {
		$list = $this->trustedSenderService->getTrusted(
			$this->uid
		);

		return JsonResponse::success($list);
	}
}
