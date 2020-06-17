<template v-if="filterrule">
	<div class="app-content-details">
		<label for="rules-name">{{ t('mail', 'Rulename') }}</label>
		<input id="rules-name" v-model="filterrule.name" type="text" />
		<template>
			<h2>{{ t('mail', 'Filtercriteria') }}</h2>
			<div v-if="filterrule.parsedrule.conditions.testlist.tests.length > 1">
				<label for="sieveListOperator">{{ t('mail', 'Select List Operator') }}</label>
				<div class="wrapper">
					<Multiselect
						id="sieveListOperator"
						v-model="filterrule.parsedrule.conditions.testlist.sieveListOperator"
						:options="supportedsievestructure.sieveListOperators"
					/>
				</div>
			</div>
			<template v-for="(test, index) in filterrule.parsedrule.conditions.testlist.tests">
				<SieveFilterTest
					:key="'test_' + index"
					:test="test"
					:supportedsievestructure="supportedsievestructure"
					class="condition"
				/>
			</template>
		</template>
		<h2>{{ t('mail', 'Actions') }}</h2>
		<template v-for="(action, index1) in filterrule.parsedrule.actions">
			<div :key="'action_' + index1" class="action">{{ action }}</div>
		</template>
		<div>{{ filterrule.rule }}</div>
		<div>{{ filterrule.parsedrule }}</div>
	</div>
</template>

<script>
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
import SieveFilterTest from './SieveFilterTest'
import Vue from 'vue'

export default {
	name: 'SieveFilterRules',
	components: {
		SieveFilterTest,
		Multiselect,
	},
	props: {
		filterrules: {
			type: Array,
			required: true,
		},
		supportedsievestructure: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			ruleIndex: this.$route.params.ruleIndex,
		}
	},
	computed: {
		filterrule() {
			return this.filterrules[this.$route.params.ruleIndex]
		},
		/*showListOperator() {
			return (
				this.filterrule.parsedrule.conditions.testlist.tests &&
				this.filterrule.parsedrule.conditions.testlist.tests.count > 1
			)
		},*/
	},
}
</script>

<style lang="scss" scoped>
@import '@nextcloud/vue/src/assets/variables.scss';
.app-content-details {
	padding-left: 10px;
}
.app-content-details h2 {
	padding-top: 10px;
}
.filterrules {
	margin-left: $navigation-width;
	padding-left: 20px;
	padding-top: 20px;
}
.condition {
	border-bottom: solid 2px gray;
}
.action {
	border: solid 2px yellow;
}
</style>
