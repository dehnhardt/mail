<template>
	<Content app-name="mail">
		<Navigation />
		<AppContent>
			<div class="section">
				<h2>{{ t('mail', 'Filter settings') }}</h2>
				<h3>{{ account.emailAddress }}</h3>
			</div>
			<div class="wrapper flex_row">
				<div class="flex_column filter-set-head">
					<input type="button" value="Add Filterset" class="icon-add icon" @click="modal = true" />
					<input
						v-if="selectedScript != ''"
						type="button"
						value="Remove Filterset"
						class="icon-delete icon"
						@click="removeFilterset()"
					/>
					<input
						type="button"
						:value="t('mail', 'Save Filterset')"
						class="icon-save icon"
						:disabled="!scriptSaveable"
						@click="saveScriptFile"
					/>
				</div>
				<div class="flex_column filter-set-head">
					<label for="filterset" class="icon-filterset">{{ t('mail', 'Select Filterset') }}</label>
					<Multiselect id="filterset" v-model="selectedScript" :options="scripts" @change="loadScriptFile" />
				</div>
				<div class="flex_column filter-set-head">
					<label for="script-source">{{ t('mail', 'Script Source Application') }}</label>
					<input id="script-source" v-model="scriptOrigin" type="text" disabled="disabled" />
					<div v-if="!scriptSaveable" class="note">
						{{ t('mail', 'This script was created by a different client. Saving disabled') }}
					</div>
				</div>
				<div class="flex_column filter-set-head">
					<label for="set-as-active">{{ t('mail', 'Activate Script when saving') }}</label>
					<input id="set-as-active" v-model="isActiveScript" type="checkbox" value="active" />
				</div>
				<Modal
					v-if="modal"
					class="filename"
					:title="t('mail', 'Scriptname')"
					:can-close="false"
					@close="addFilterset"
				>
					<div class="modal__content">
						<label for="new-file-name">{{
							t('mail', 'Please add a name for the new filterset or use the default one')
						}}</label>
						<input id="new-file-name" v-model="newScriptName" type="text" required />
						<input
							type="button"
							icon="icon-filter"
							:value="t('mail', 'Add Filterset')"
							:disabled="newScriptName == ''"
							@click="onModalClose"
						/>
						<input type="button" icon="icon-filter" :value="t('mail', 'Cancel')" @click="onModalCancel" />
					</div>
				</Modal>
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
import Modal from '@nextcloud/vue/dist/Components/Modal'
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
		Modal,
		Multiselect,
		Navigation,
		SieveFilterNavigation,
		SieveFilterRules,
	},
	data() {
		const account = this.$store.getters.getAccount(this.$route.params.accountId)
		return {
			account,
			signature: account.signature,
			scriptOrigin: '',
			activeScript: '',
			selectedScript: '',
			newScriptName: 'mc-mail-filter',
			generatorName: 'Nextcloud Mail',
			modal: false,
			scripts: Array(),
			filterRules: Array(),
			supportedSieveStructure: Object(),
			filtersets: Object(),
		}
	},
	computed: {
		scriptSaveable() {
			return this.scriptOrigin == this.generatorName
		},
		displayName() {
			return this.$store.getters.getAccount(this.$route.params.accountId).name
		},
		email() {
			return this.$store.getters.getAccount(this.$route.params.accountId).emailAddress
		},
		isActiveScript: {
			get() {
				return this.activeScript == this.selectedScript
			},
			set(val) {
				this.activeScript = this.selectedScript
			},
		},
	},
	mounted() {
		this.$store.dispatch('listSieveScripts', this.account.accountId).then((data) => {
			this.scripts = data.scripts
			this.activeScript = data.activeScript
			this.selectedScript = data.activeScript
			this.supportedSieveStructure = data.supportedSieveStructure
			this.filtersets[this.selectedScript] = data.scriptContent
			this.extractFilterRules(this.selectedScript)
		})
	},
	methods: {
		extractFilterRules(scriptName) {
			var i = 0
			Vue.set(this, 'filterRules', [])
			this.filtersets[scriptName].forEach((rule, index) => {
				if (rule.type == 'header') {
					this.scriptOrigin = rule.scriptOrigin
				} else if (rule.type == 'rule') {
					rule.index = index
					Vue.set(this.filterRules, i, rule)
					i++
				}
			})
		},
		loadScriptFile(scriptName) {
			Logger.debug('Fetch script file: ' + scriptName)
			if (this.filtersets[scriptName]) {
				this.extractFilterRules(scriptName)
			} else {
				this.$store
					.dispatch('getSieveScriptContent', {accountId: this.account.accountId, scriptName})
					.then((data) => {
						Logger.debug('scriptFile loaded')
						this.filtersets[scriptName] = data.scriptContent
						this.extractFilterRules(scriptName)
					})
			}
		},
		saveScriptFile() {
			// we keep all parameters when tests or actions are modified
			// so before storing the file, delete parameters that are not
			// relevant for the test or action
			this.cleanCurrentFiltersetParameters()
			// merge old and new rules!
			this.mergeNewFilterRules()
			this.$store
				.dispatch('putSieveScriptContent', {
					accountId: this.account.accountId,
					scriptName: this.selectedScript,
					install: this.isActiveScript,
					scriptContent: this.filtersets[this.selectedScript],
				})
				.then((data) => {
					Logger.debug('scriptFile loaded')
				})
		},
		mergeNewFilterRules() {
			this.filterRules.forEach((rule) => {
				if (rule.index == -1) {
					this.filtersets[this.selectedScript].push(rule)
				}
			})
		},
		cleanCurrentFiltersetParameters() {
			this.filterRules.forEach((rule) => {
				rule.parsedrule.actions.forEach((action) => {
					const actionType = action.action
					if (action.parameters) {
						Object.keys(action.parameters).forEach((key) => {
							var re = new RegExp('%[*?]{0,2}' + key, 'i')
							const parameters = this.supportedSieveStructure.supportedAction[actionType].parameters
							const index = parameters.search(re)
							if (index < 0) {
								delete action.parameters[key]
							}
						})
					}
				})
				rule.parsedrule.conditions.testlist.tests.forEach((test) => {
					const testType = test.testSubject
					if (test.parameters) {
						Object.keys(test.parameters).forEach((key) => {
							var re = new RegExp('%[*?]{0,2}' + key, 'i')
							const parameters = this.supportedSieveStructure.supportedTestSubjects[testType].parameters
							const index = parameters.search(re)
							if (index < 0) {
								delete test.parameters[key]
							}
						})
					}
				})
			})
		},
		onModalClose() {
			if (this.newScriptName != '') {
				if (this.addFilterset()) {
					this.modal = false
				}
			}
		},
		onModalCancel() {
			this.modal = false
		},
		addFilterset() {
			if (this.scripts.includes(this.newScriptName)) {
				return false
			}
			this.scripts.push(this.newScriptName)
			var filterRules = [
				{
					index: 0,
					type: 'header',
					scriptOrigin: this.generatorName,
				},
				{
					index: 1,
					name: 'new Rule',
					parsedrule: {
						actions: [],
						conditions: {
							'condition-verb': 'if',
							testlist: {
								sieveListOperator: '',
								tests: [],
							},
						},
					},
					type: 'rule',
				},
			]
			this.scriptOrigin = this.generatorName
			this.selectedScript = this.newScriptName
			this.filtersets[this.newScriptName] = filterRules
			this.extractFilterRules(this.newScriptName)
			return true
		},
		removeFilterset() {
			//todo
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
	border-bottom: solid 1px grey;
	padding-bottom: 10px;
	margin-bottom: 10px;
}
.filterNavigation {
	z-index: 1000;
}
.filter-content {
	padding-left: 10px;
}
.filter-set-head {
	padding-right: 10px;
}
.filter-set-head label {
	display: block;
	height: 40px;
	padding-top: 10px;
}
.icon-filterset {
	display: inline-block;
	vertical-align: middle;
	background-size: 16px 16px;
	background-position-x: left;
	background-position-y: center;
	padding-left: 18px;
	margin: 0 10px 0 0;
}
.modal__content {
	width: 200px;
	text-align: center;
}
div.modal-container {
	margin: 10px;
	padding: 10px;
}
</style>
<style>
input.icon {
	height: 20px;
	background-position-x: 10px;
	background-position-y: center;
	padding-left: 34px;
	text-align: left;
}
.flex_column {
	display: flex;
	flex-direction: column;
}
.flex_row {
	display: flex;
	flex-direction: row;
}
div.note {
	max-width: 150px;
	line-break: loose;
}
</style>
