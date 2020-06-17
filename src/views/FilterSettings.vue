<template>
	<Content app-name="mail">
		<Navigation />
		<AppContent>
			<div class="section">
				<h2>{{ t('mail', 'Filter settings') }}</h2>
				<h3>{{ account.emailAddress }}</h3>
			</div>
			<div class="wrapper">
				<label for="filterset" class="icon-filterset">{{ t('mail', 'select filterset') }}</label>
				<Multiselect id="filterset" v-model="selectedScript" :options="scripts" @change="loadScriptFile" />
			</div>
			<div id="app-content-wrapper" class="filter-content">
				<SieveFilterNavigation :account="account" :filterrules="filterRules" />
				<SieveFilterRules :filterrules="filterRules" :supportedsievestructure="supportedSieveStructure" />
			</div>
		</AppContent>
	</Content>
</template>

<script>
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import Content from '@nextcloud/vue/dist/Components/Content'
import Logger from '../logger'
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
import Navigation from '../components/Navigation'
import SieveFilterNavigation from '../components/SieveFilterNavigation'
import SieveFilterRules from '../components/SieveFilterRules'
import Vue from 'vue'

export default {
	name: 'FilterSettings',
	components: {
		AppContent,
		Content,
		Navigation,
		SieveFilterNavigation,
		SieveFilterRules,
		Multiselect,
	},
	data() {
		const account = this.$store.getters.getAccount(this.$route.params.accountId)
		return {
			account,
			signature: account.signature,
			activeScript: '',
			selectedScript: '',
			scripts: Array(),
			scriptContent: Array(),
			filterRules: Array(),
			supportedSieveStructure: Object(),
		}
	},
	computed: {
		displayName() {
			return this.$store.getters.getAccount(this.$route.params.accountId).name
		},
		email() {
			return this.$store.getters.getAccount(this.$route.params.accountId).emailAddress
		},
	},
	mounted() {
		this.$store.dispatch('listSieveScripts', this.account.accountId).then((data) => {
			this.scripts = data.scripts
			this.activeScript = data.activeScript
			this.selectedScript = data.activeScript
			this.scriptContent = data.scriptContent
			this.supportedSieveStructure = data.supportedSieveStructure
			this.extractFilterRules()
		})
	},
	methods: {
		extractFilterRules() {
			var i = 0
			this.scriptContent.forEach((rule, index) => {
				if (rule.type == 'rule') {
					rule.index = index
					Vue.set(this.filterRules, i, rule)
					i++
				}
			})
			this.filterRules.splice(i)
		},
		loadScriptFile(scriptName) {
			Logger.debug('Fetch script file: ' + scriptName)
			this.$store
				.dispatch('getSieveScriptContent', {accountId: this.account.accountId, scriptName})
				.then((data) => {
					Logger.debug('scriptFile loaded')
					data.scriptContent.forEach((content, index) => {
						Vue.set(this.scriptContent, index, content)
					})
					this.scriptContent.splice(data.scriptContent.length)
					this.extractFilterRules()
				})
		},
	},
}
</script>

<style lang="scss" scoped>
.section {
	margin-bottom: 0;
}
.multiselect {
	z-index: 2000;
}
.wrapper {
	padding-left: 18px;
}
.filterNavigation {
	z-index: 1000;
}
.filter-content {
	padding-left: 10px;
}
.icon-filterset {
	display: inline-block;
	vertical-align: middle;
	background-size: 16px 16px;
	background-position-x: left;
	padding-left: 18px;
	margin: 0 10px 25px 0;
}
</style>
