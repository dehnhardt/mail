<template>
	<div class="filtercontainer">
		<div>
			<label for="sieve-test-subject">{{ t('mail', 'Select Test Subject') }}</label>
			<div class="wrapper">
				<Multiselect
					id="sieve-test-subject"
					v-model="test.testSubject"
					:options="Object.keys(supportedsievestructure.supportedTestSubjects)"
					:searchable="false"
					@select="onSelectSubject"
				/>
			</div>
		</div>
		<div v-if="showComparators">
			<label for="sieve-comparator">{{ t('mail', 'Select Comparator(s)') }}</label>
			<div class="wrapper">
				<Multiselect
					id="sieve-comparator"
					v-model="test.parameters.sieveComparator[0]"
					:options="comparators"
					:searchable="false"
					:allow-empty="false"
					:multiple="expectedParameters.comparator.multiple"
				>
				</Multiselect>
			</div>
		</div>
		<div v-if="showHeaders">
			<label for="headers">{{ t('mail', 'Select Header(s)') }}</label>
			<div class="wrapper">
				<Multiselect
					id="headers"
					v-model="test.parameters.headers"
					:options="supportedsievestructure.headers"
					:searchable="false"
					:allow-empty="false"
					:multiple="expectedParameters.headers.multiple"
				>
				</Multiselect>
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
	},
	data() {
		return {
			expectedParameters: {lal: 0},
			tester: 'abcd',
		}
	},
	computed: {
		comparators() {
			return Object.values(this.supportedsievestructure.supportedComparators)
				.filter((item) => {
					return item.usages.includes(this.test.testSubject)
				})
				.map((a) => a.name)
		},
		showComparators() {
			return this.comparators.length > 0 && this.expectedParameters.comparator
		},
		showHeaders() {
			return this.supportedsievestructure.headers.length > 0 && this.expectedParameters.headers
		},
	},
	mounted() {
		this.setExpectedParameters(this.test.testSubject)
	},
	methods: {
		onSelectSubject(val) {
			this.setExpectedParameters(val)
			this.cleanComparator(val)
		},
		setExpectedParameters(val) {
			if (this.supportedsievestructure.supportedTestSubjects[val].parameters) {
				var parameters = Object.assign({})
				const a = this.supportedsievestructure.supportedTestSubjects[val].parameters.split(' ')
				a.forEach((element) => {
					element = element.substring(1)
					var multiple = false
					const ast = element.indexOf('*')
					if (ast > -1) {
						element = element.substring(0, ast)
						multiple = true
					}
					parameters[element] = {multiple: multiple}
				})
				Vue.set(this, 'expectedParameters', Object.assign({}, parameters))
			} else {
				Vue.set(this, 'expectedParameters', Object.assign({}))
			}
		},
		cleanComparator(val) {
			this.test.parameters.sieveComparator = []
		},
	},
}
</script>
<style scoped>
.filtercontainer {
	display: flex;
	flex-direction: row;
}
</style>
