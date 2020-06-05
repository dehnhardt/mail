import {generateUrl} from '@nextcloud/router'
import HttpClient from '@nextcloud/axios'

export const updateAccount = (data) => {
	const url = generateUrl('/apps/mail/api/sieve/{id}/updateAccount', {
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
