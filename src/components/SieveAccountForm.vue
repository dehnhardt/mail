<template>
	<div class="section">
		<form id="sieve-form">
			<h2>{{ t('mail', 'Sieve Account') }}</h2>
							<div class="flex-row">
				<input
					id="sieve-disabled"
					v-model="sieveEnabled"
					type="radio"
					name="sieve-active"
					:disabled="loading"
					value=false
				/>
				<label
					class="button"
					for="sieve-sec-none"
					:class="{primary: sieveSslMode === 'none'}"
					>{{ t('mail', 'Disabled') }}</label
				>
				<input
					id="sieve-enables"
					v-model="sieveEnabled"
					type="radio"
					name="sieve-active"
					:disabled="loading"
					value=true
				/>
				<label
					class="button"
					for="sieve-sec-ssl"
					:class="{primary: sieveSslMode === 'ssl'}"
					>{{ t('mail', 'Enabled') }}</label
				>
			</div>
			<p>
				<label for="sieve-enabled">{{ t('mail', 'Enable Sieve Filter') }}</label>
				<input id="sieve-enabled" type="checkbox" v-model="sieveEnabled"/>
			</p>
			<template
				v-if="sieveEnabled"
			>
				<label for="sieve-host">{{ t('mail', 'Sieve Host') }}</label>
				<input id="sieve-host" type="text" v-model="sieveHost" required/>
								<h4>{{ t('mail', 'SMTP Security') }}</h4>
				<div class="flex-row">
					<input
						id="sieve-sec-none"
						v-model="sieveSslMode"
						type="radio"
						name="sieve-sec"
						:disabled="loading"
						value="none"
						@change="onSieveSslModeChange"
					/>
					<label
						class="button"
						for="sieve-sec-none"
						:class="{primary: sieveSslMode === 'none'}"
						>{{ t('mail', 'None') }}</label
					>
					<input
						id="sieve-sec-ssl"
						v-model="sieveSslMode"
						type="radio"
						name="sieve-sec"
						:disabled="loading"
						value="ssl"
						@change="onSieveSslModeChange"
					/>
					<label
						class="button"
						for="sieve-sec-ssl"
						:class="{primary: sieveSslMode === 'ssl'}"
						>{{ t('mail', 'SSL/TLS') }}</label
					>
					<input
						id="sieve-sec-tls"
						v-model="sieveSslMode"
						type="radio"
						name="sieve-sec"
						:disabled="loading"
						value="tls"
						@change="onSieveSslModeChange"
					/>
					<label
						class="button"
						for="sieve-sec-tls"
						:class="{primary: sieveSslMode === 'tls'}"
						>{{ t('mail', 'STARTTLS') }}</label
					>
				</div>
				<label for="sieve-port">{{ t('mail', 'Sieve Port') }}</label>
				<input id="sieve-port" type="text" v-model="sievePort" required/>
				<label for="sieve-user">{{ t('mail', 'Sieve User') }}</label>
				<input id="sieve-user" type="text" v-model="sieveUser" required/>
				<label for="sieve-password">{{ t('mail', 'Sieve Password') }}</label>
				<input id="sieve-password" type="password" v-model="sievePassword" required/>
			</template>
		</form>
	</div>
</template>

<script>
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
			sieveEnabled: this.account.sieveEnabled,
			sieveHost: fromAccountOr(sieveHost, fromAccountOr(imapHost, '')),
			/*sievePort: fromAccountOr(sievePort, '4190'),
			sieveUser: fromAccountOr(sieveUser, fromAccountOr(imapUser, '')),
			sieveSslMode: fromAccountOr(sieveSslMode, 'none'),
			sievePassword: '',*/
			//sieveHost: this.account.sieveHost,
			sievePort: this.account.sievePort,
			sieveUser: this.account.sieveUser,
			sieveSslMode: this.account.sieveSslMode,
			sievePassword: '',
			loading: false,
		}
	},
	computed: {
		sieveSslMode() {
			if ( this.account.sieveSslMode !== undefined ){
				return this.account.sieveSslMode
			}
		},
	},
	methods: {
		onSieveSslModeChange() {
		},
	}

}
</script>

<style>
div {
	width: 350px;
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
