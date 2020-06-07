<template>
	<div class="section">
		<form id="sieve-form">
			<h2>{{ t('mail', 'Sieve Account') }}</h2>
			<div class="flex-row">
				<input
					id="sieve-disabled"
					v-model="sieveAccount.sieveEnabled"
					type="radio"
					name="sieve-active"
					:disabled="loading"
					value="0"
				/>
				<label class="button" for="sieve-disabled" :class="{primary: sieveAccount.sieveEnabled === '0'}">
					{{ t('mail', 'Disabled') }}
				</label>
				<input
					id="sieve-enabled"
					v-model="sieveAccount.sieveEnabled"
					type="radio"
					name="sieve-active"
					:disabled="loading"
					value="1"
				/>
				<label class="button" for="sieve-enabled" :class="{primary: sieveAccount.sieveEnabled === '1'}">
					{{ t('mail', 'Enabled') }}
				</label>
			</div>
			<template v-if="sieveAccount.sieveEnabled === '1'">
				<label for="sieve-host">{{ t('mail', 'Sieve Host') }}</label>
				<input id="sieve-host" v-model="sieveAccount.sieveHost" type="text" required />
				<h4>{{ t('mail', 'Sieve Security') }}</h4>
				<div class="flex-row">
					<input
						id="sieve-sec-none"
						v-model="sieveAccount.sieveSslMode"
						type="radio"
						name="sieve-sec"
						:disabled="loading"
						value="none"
						@change="onSieveSslModeChange"
					/>
					<label
						class="button"
						for="sieve-sec-none"
						:class="{primary: sieveAccount.sieveSslMode === 'none'}"
						>{{ t('mail', 'None') }}</label
					>
					<input
						id="sieve-sec-ssl"
						v-model="sieveAccount.sieveSslMode"
						type="radio"
						name="sieve-sec"
						:disabled="loading"
						value="ssl"
					/>
					<label class="button" for="sieve-sec-ssl" :class="{primary: sieveAccount.sieveSslMode === 'ssl'}">
						{{ t('mail', 'SSL/TLS') }}
					</label>
					<input
						id="sieve-sec-tls"
						v-model="sieveAccount.sieveSslMode"
						type="radio"
						name="sieve-sec"
						:disabled="loading"
						value="tls"
						@change="onSieveSslModeChange"
					/>
					<label class="button" for="sieve-sec-tls" :class="{primary: sieveAccount.sieveSslMode === 'tls'}">
						{{ t('mail', 'STARTTLS') }}
					</label>
				</div>
				<label for="sieve-port">{{ t('mail', 'Sieve Port') }}</label>
				<input id="sieve-port" v-model="sieveAccount.sievePort" type="text" required />
				<label for="sieve-user">{{ t('mail', 'Sieve User') }}</label>
				<input id="sieve-user" v-model="sieveAccount.sieveUser" type="text" required />
				<label for="sieve-password">{{ t('mail', 'Sieve Password') }}</label>
				<input id="sieve-password" v-model="sieveAccount.sievePassword" type="password" required />
				<slot name="feedback"></slot>
				<input
					type="submit"
					class="primary"
					:disabled="loading"
					:value="submitButtonText"
					@click.prevent="onSubmit"
				/>
			</template>
		</form>
	</div>
</template>

<script>
import logger from '../logger'
export default {
	name: 'SieveSettings',
	props: {
		account: {
			type: Object,
			required: true,
		},
	},
	data() {
		const fromAccountOr = (prop, def) => {
			if (this.account !== undefined && this.account[prop] !== undefined) {
				return this.account[prop]
			} else {
				return def
			}
		}
		return {
			sieveAccount: {
				accountId: this.account.accountId,
				sieveEnabled: this.account.sieveEnabled ? '1' : '0',
				sieveHost: this.account.sieveHost
					? this.account.sieveHost
					: this.account.imapHost
					? this.account.imapHost
					: '',
				sievePort: this.account.sievePort ? this.account.sievePort : '4190',
				sieveUser: this.account.sieveUser
					? this.account.sieveUser
					: this.account.imapUser
					? this.account.imapUser
					: '',
				sieveSslMode: this.account.sieveSslMode ? this.account.sieveSslMode : 'none',
				sievePassword: '',
			},
			loading: false,
			submitButtonText: this.account ? t('mail', 'Save') : t('mail', 'Connect'),
		}
	},
	computed: {
		// nothing here
	},
	methods: {
		onSubmit() {
			logger.info('Submit sieve account');
			this.loading = true;
			return this.$store
				.dispatch('updateSieveAccount', {...this.sieveAccount} )
				.then(() => {
					this.loading = false
				})
				.catch((error) => {
					logger.error('sieve account update failed:', {error})
					this.loading = false
					throw error
				})
		},
	},
}
</script>

<style>
#sieve-form {
	width: 250px;
	top: 15%;
}
input {
	width: 100%;
}
label {
	padding-right: 12px;
}
</style>

<style scoped>
h4 {
	text-align: left;
}

.flex-row {
	display: flex;
}

label.button {
	display: inline-block;
	text-align: center;
	flex-grow: 1;
}

input[type='radio'] {
	display: none;
}

input[type='radio'][disabled] + label {
	cursor: default;
	opacity: 0.5;
}
</style>
