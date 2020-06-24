<template>
	<div class="app-content-list filter-navigation">
		<ul id="filter-list">
			<AppNavigationNew :text="t('mail', 'Add Filterrule')" button-class="icon-add" @click="addRule" />
			<template v-for="(rule, index) in filterrules">
				<AppNavigationItem
					:key="'rule_' + index"
					:to="{
						name: 'filterRules',
						params: {
							accountId: account.id,
							ruleIndex: index,
						},
					}"
					:exact="true"
					:title="rule.name"
					icon="icon-filter"
				>
					<template slot="actions">
						<ActionButton icon="icon-delete" title="delete" @click="removeRule(index)">
							{{ t('mail', 'Delete this filter') }}
						</ActionButton>
					</template>
				</AppNavigationItem>
			</template>
		</ul>
	</div>
</template>

<script>
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew'
import Vue from 'vue'
export default {
	name: 'SieveFilterNavigation',
	components: {
		ActionButton,
		AppNavigationItem,
		AppNavigationNew,
	},
	props: {
		account: {
			type: Object,
			required: true,
		},
		filterrules: {
			type: Array,
			required: true,
		},
	},
	computed: {
		id() {
			return 'filter-navigation'
		},
	},
	methods: {
		addRule() {
			const rule = {
				index: -1,
				name: 'new Rule',
				parsedrule: {actions: [], conditions: {'condition-verb': 'if', testlist: {tests: []}}},
				rule: '',
				type: 'rule',
			}
			this.filterrules.push(rule)
		},
		removeRule(index) {
			this.filterrules.splice(index, 1)
		},
	},
}
</script>

<style lang="scss" scoped>
@import '@nextcloud/vue/src/assets/variables.scss';
#app-navigation.filter-navigation {
	left: $navigation-width;
	top: 300px;
}
</style>
