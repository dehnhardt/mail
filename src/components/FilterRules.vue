<template>
	<div class="app-content-details">
		<h3>{{ filterrules.name }}</h3>
		<label>Hallo, hier bin ich</label>
		<label>{{ filterrules.rule }}</label>
	</div>
</template>

<script>
export default {
	name: 'FilterRules',
	props: {
		filterrules: {
			type: Object,
			required: true,
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
</style>
