<template v-if="filterrule">
	<div class="app-content-details">
		<label for="rules-name">{{ t('mail', 'Rules Name') }}</label>
		<input id="rules-name" v-model="filterrule.name" type="text" />
		<div>{{ filterrule.rule }}</div>
		<div>{{ filterrule.parsedrule }}</div>
		<template v-if="filterrule.parsedrule.conditions.testlist">
			<label>With list</label>
			<div>{{ filterrule.parsedrule.conditions.testlist.listtype }}</div>
			<template v-for="(test, index) in filterrule.parsedrule.conditions.testlist.tests">
				<div :key="index" class="condition">{{ test }}</div>
			</template>
		</template>
		<template v-if="filterrule.parsedrule.conditions.test">
			<label>Without list</label>
			<div class="condition">{{ filterrule.parsedrule.conditions.test }}</div>
		</template>
		<template v-for="(action, index1) in filterrule.parsedrule.actions">
			<div :key="index1" class="action">{{ action }}</div>
		</template>
	</div>
</template>

<script>
import Vue from 'vue'

export default {
	name: 'FilterRules',
	props: {
		filterrules: {
			type: Array,
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
}
</script>

<style lang="scss" scoped>
@import '@nextcloud/vue/src/assets/variables.scss';
.filterrules {
	margin-left: $navigation-width;
	padding-left: 20px;
	padding-top: 20px;
}
.condition {
	border: solid 2px blue;
}
.action {
	border: solid 2px yellow;
}
</style>
