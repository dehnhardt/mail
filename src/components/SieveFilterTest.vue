<template>
	<div class="flex_row">
		<div>
			<label :for="templateid + '-test-subject'">{{ t('mail', 'Test Subject') }}</label>
			<div class="wrapper">
				<Multiselect
					:id="templateid + '-test-subject'"
					v-model="test.testSubject"
					:options="Object.keys(supportedsievestructure.supportedTestSubjects)"
					:searchable="false"
					@select="onSelectSubject"
				/>
			</div>
		</div>
		<div v-if="showAddressParts">
			<label :for="templateid + '-addres-part'">{{ t('mail', 'Addresparts') }}</label>
			<div class="wrapper">
				<Multiselect
					:id="templateid + '-addres-part'"
					v-model="test.parameters.sieveAddressPart"
					:options="addressParts"
					:searchable="false"
					:allow-empty="expectedParameters.addresspart.optional"
					:multiple="expectedParameters.addresspart.multiple"
				>
				</Multiselect>
			</div>
		</div>
		<div v-if="showMatchTypes">
			<label :for="templateid + '-match-type'">{{ t('mail', 'Matchtype(s)') }}</label>
			<div class="wrapper">
				<Multiselect
					:id="templateid + '-match-type'"
					v-model="matchTypeValue"
					:options="matchtypes"
					:searchable="false"
					:allow-empty="expectedParameters.matchtype.optional"
					:multiple="expectedParameters.matchtype.multiple"
				>
				</Multiselect>
			</div>
		</div>
		<div v-if="showEnvelopeParts">
			<label :for="templateid + '-envelope-part'">{{ t('mail', 'EnvelopeParts') }}</label>
			<div class="wrapper">
				<Multiselect
					:id="templateid + '-envelope-part'"
					v-model="envelopePartValue"
					:options="supportedsievestructure.envelopeParts"
					:searchable="false"
					:allow-empty="expectedParameters.envelopepart.optional"
					:multiple="expectedParameters.envelopepart.multiple"
				>
				</Multiselect>
			</div>
		</div>
		<div v-if="showHeaders">
			<label :for="templateid + '-headers'">{{ t('mail', 'Header(s)') }}</label>
			<div class="wrapper">
				<Multiselect
					:id="templateid + '-headers'"
					v-model="headerValue"
					:options="supportedsievestructure.headers"
					:searchable="true"
					:allow-empty="expectedParameters.headers.optional"
					:multiple="expectedParameters.headers.multiple"
					:taggable="true"
					tag-placeholder="Add this as new header"
					@tag="addCustomHeader"
				>
				</Multiselect>
			</div>
		</div>
		<div v-if="expectedParameters.keylist" class="flex_column">
			<label>{{ t('mail', 'Keylist') }}</label>
			<Multiselect
				:id="templateid + '-keys'"
				v-model="test.parameters.keylist"
				:options="test.parameters.keylist"
				:searchable="true"
				:allow-empty="false"
				:multiple="true"
				:taggable="true"
				tag-placeholder="Add this as new key"
				@tag="addKey"
			>
			</Multiselect>
		</div>
		<div v-if="expectedParameters.size">
			<label :for="templateid + '-size'">{{ t('mail', 'Size') }}</label>
			<div class="wrapper">
				<input :id="templateid + '-size'" v-model="test.parameters.size[0]" />
			</div>
		</div>
	</div>
</template>

<script>
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
import Vue from 'vue'

export default {
	name: 'SieveFilterTest',
	components: {
		Multiselect,
	},
	props: {
		test: {
			type: Object,
			required: true,
		},
		supportedsievestructure: {
			type: Object,
			required: true,
		},
		templateid: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			expectedParameters: {lal: 0},
			tester: 'abcd',
		}
	},
	computed: {
		addressParts() {
			return Object.values(this.supportedsievestructure.supportedAddressParts)
				.filter((item) => {
					return item.usages.includes(this.test.testSubject)
				})
				.map((a) => a.name)
		},
		matchtypes() {
			return Object.values(this.supportedsievestructure.supportedMatchTypes)
				.filter((item) => {
					return item.usages.includes(this.test.testSubject)
				})
				.map((a) => a.name)
		},
		envelopePartValue: {
			get: function () {
				return this.getValueForParameter('envelopepart')
			},
			set: function (value) {
				this.setValueForParameter('envelopepart', value)
			},
		},
		addressPartValue: {
			get: function () {
				return this.getValueForParameter('addresspart')
			},
			set: function (value) {
				this.setValueForParameter('addresspart', value)
			},
		},
		matchTypeValue: {
			get: function () {
				return this.getValueForParameter('matchtype')
			},
			set: function (value) {
				this.setValueForParameter('matchtype', value)
			},
		},
		headerValue: {
			get: function () {
				return this.getValueForParameter('headers')
			},
			set: function (value) {
				this.setValueForParameter('headers', value)
			},
		},
		showAddressParts() {
			return this.addressParts.length > 0 && this.expectedParameters.addresspart ? true : false
		},
		showMatchTypes() {
			return this.matchtypes.length > 0 && this.expectedParameters.matchtype ? true : false
		},
		showEnvelopeParts() {
			return this.supportedsievestructure.envelopeParts.length > 0 && this.expectedParameters.envelopepart
				? true
				: false
		},
		showHeaders() {
			return this.supportedsievestructure.headers.length > 0 && this.expectedParameters.headers ? true : false
		},
	},
	mounted() {
		this.setExpectedParameters(this.test.testSubject)
	},
	methods: {
		getValueForParameter(parameter) {
			return this.expectedParameters[parameter].multiple
				? this.test.parameters[parameter]
				: this.test.parameters[parameter][0]
		},
		setValueForParameter(parameter, value) {
			if (this.expectedParameters[parameter].multiple) {
				this.test.parameters[parameter] = []
				if (Array.isArray(value)) {
					value.forEach((val, index) => {
						Vue.set(this.test.parameters[parameter], index, val)
					})
				}
			} else {
				Vue.set(this.test.parameters[parameter], 0, value)
			}
		},
		addCustomHeader(val) {
			this.supportedsievestructure.headers.push(val)
			this.test.parameters.headers.push(val)
		},
		addKey(val) {
			this.test.parameters.keylist.push(val)
		},
		removeKey(index) {
			this.test.parameters.keylist.splice(index, 1)
		},
		onSelectSubject(val) {
			Object.keys(this.expectedParameters).forEach((key) => {
				this.cleanParameter(key)
			})
			this.setExpectedParameters(val)
		},
		setExpectedParameters(val) {
			if (this.supportedsievestructure.supportedTestSubjects[val].parameters) {
				var parameters = Object.assign({})
				const a = this.supportedsievestructure.supportedTestSubjects[val].parameters.split(' ')
				a.forEach((element) => {
					element = element.substring(1)
					var multiple = false
					var optional = false
					const ast = element.indexOf('*')
					if (element.indexOf('*') == 0) {
						element = element.substring(1)
						multiple = true
					}
					if (element.indexOf('?') == 0) {
						element = element.substring(1)
						optional = true
					}
					parameters[element] = {multiple: multiple, optional: optional}
					if (!this.test.parameters[element]) {
						Vue.set(this.test.parameters, element, [])
					}
					if (!Array.isArray(this.test.parameters[element])) {
						const val = this.test.parameters.element
						Vue.set(this.test.parameters[element], 0, val)
					} else {
						if (this.test.parameters[element].length == 0 && (!multiple || element == 'keylist')) {
							Vue.set(this.test.parameters[element], 0, '')
						}
					}
				})
				Vue.set(this, 'expectedParameters', Object.assign({}, parameters))
			} else {
				Vue.set(this, 'expectedParameters', Object.assign({}))
			}
		},
		cleanParameter(parameter) {
			/*this.test.parameters[parameter] = []
			if (!this.expectedParameters[parameter].multiple) {
				this.test.parameters[parameter][0] = ''
			}*/
		},
	},
}
</script>
<style scoped>
.keylist {
	min-width: 200px;
}
input {
	margin: 0;
}
</style>
