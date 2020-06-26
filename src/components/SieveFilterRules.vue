<template v-if="filterrule">
	<div class="app-content-details">
		<label for="rules-name">{{ t('mail', 'Rulename') }}</label>
		<input id="rules-name" v-model="filterrule.name" :disabled="!filterrule.origname" type="text" />
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
			<input
				id="add-filter-criterium"
				type="button"
				:value="t('mail', 'Add Filtercriterium')"
				class="icon-add icon"
				@click="addFilterCriterium"
			/>
			<EmptyContent v-if="filterrule.parsedrule.conditions.testlist.tests.length == 0" icon="icon-filter">
				{{ t('mail', 'No tests defined') }}
				<template #desc>{{ t('mail', 'You need to define at least one test') }}</template>
			</EmptyContent>
			<template v-for="(test, index) in filterrule.parsedrule.conditions.testlist.tests">
				<div :key="'test_wrapper_' + index" class="flex_row condition">
					<input type="button" class="icon-delete" @click="deleteFilterCriterium(index)" />
					<SieveFilterTest
						:key="'test_' + index"
						:test="test"
						:supportedsievestructure="supportedsievestructure"
						:templateid="'sieve-filterrule-' + index"
					/>
				</div>
			</template>
		</template>
		<h2>{{ t('mail', 'Actions') }}</h2>
		<input
			id="add-action"
			type="button"
			:value="t('mail', 'Add Action')"
			class="icon-add icon"
			@click="addAction"
		/>
		<EmptyContent v-if="filterrule.parsedrule.actions.length == 0" icon="icon-filter">
			{{ t('mail', 'No actions defined') }}
			<template #desc>{{
				t('mail', 'You have to define at least one action that specifies what to do with the found mails')
			}}</template>
		</EmptyContent>
		<template v-for="(action, index) in filterrule.parsedrule.actions">
			<div :key="'action_' + index" class="flex_row action">
				<input type="button" class="icon-delete" @click="deleteAction(index)" />
				<SieveFilterAction
					:action="action"
					:supportedsievestructure="supportedsievestructure"
					:templateid="'sieve-filter-action-' + index"
					:accountid="$route.params.accountId"
				/>
			</div>
		</template>
		<pre v-if="false">{{ JSON.stringify(filterrule.rule, null, 2) }}</pre>
		<pre v-if="false">{{ JSON.stringify(filterrule.parsedrule, null, 2) }}</pre>
	</div>
</template>

<script>
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
import SieveFilterTest from './SieveFilterTest'
import SieveFilterAction from './SieveFilterAction'
import Vue from 'vue'

export default {
	name: 'SieveFilterRules',
	components: {
		EmptyContent,
		Multiselect,
		SieveFilterTest,
		SieveFilterAction,
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
	},
	methods: {
		addFilterCriterium() {
			Vue.set(
				this.filterrule.parsedrule.conditions.testlist.tests,
				this.filterrule.parsedrule.conditions.testlist.tests.length,
				{testSubject: '', parameters: {}}
			)
		},
		addAction() {
			// eslint-disable-next-line prettier/prettier
			Vue.set(
				this.filterrule.parsedrule.actions,
				this.filterrule.parsedrule.actions.length,
				// eslint-disable-next-line prettier/prettier
				{action: '', parameters: {}}
			)
		},
		deleteFilterCriterium(index) {
			this.filterrule.parsedrule.conditions.testlist.tests.splice(index, 1)
		},
		deleteAction(index) {
			this.filterrule.parsedrule.actions.splice(index, 1)
		},
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
	border-bottom: solid 1px gray;
}
.action {
	border-bottom: solid 1px gray;
}
.empty-content {
	margin: 0;
}
</style>

<style>
.flex_column {
	display: flex;
	flex-direction: column;
}
.flex_row {
	display: flex;
	flex-direction: row;
}
input.small {
	width: 35px;
	height: 35px;
	padding: 0;
}
</style>
