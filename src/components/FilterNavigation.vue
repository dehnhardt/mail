<template>
	<div class="app-content-list filter-navigation">
		<ul id="filter-list">
			<template v-for="rule in filterrules">
				<AppNavigationItem :key="rule.index" :exact="true" :title="rule.name" icon="icon-filter" />
			</template>
		</ul>
	</div>
</template>

<script>
// import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'

export default {
	name: 'FilterNavigation',
	components: {
		// AppNavigation,
		AppNavigationItem,
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
}
</script>

<style lang="scss" scoped>
@import '@nextcloud/vue/src/assets/variables.scss';
#app-navigation.filter-navigation {
	left: $navigation-width;
	top: 300px;
}
</style>
