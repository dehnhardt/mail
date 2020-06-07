/*
 * @copyright 2020 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2020 Holger Dehnhardt <holger@dehnhardt.org>
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

import {generateUrl} from '@nextcloud/router'
import HttpClient from '@nextcloud/axios'

export const updateSieveAccount = (data) => {
	const url = generateUrl('/apps/mail/api/sieve/{id}/updateSieveAccount', {
		id: data.accountId,
	})
	return HttpClient.post(url, data)
		.then((resp) => resp.data)
		.catch((e) => {
			if (e.response && e.response.status === 400) {
				throw e.response.data
			}

			throw e
		})
}
