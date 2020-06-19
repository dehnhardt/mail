<template>
	<div class="flex_row">
		<div>
			<label for="sieve-test-subject">{{ t('mail', 'Action') }}</label>
			<div class="wrapper">
				<Multiselect
					id="sieve-test-subject"
					v-model="action.action"
					:options="Object.keys(supportedsievestructure.supportedAction)"
					:searchable="false"
					@select="onSelectAction"
				/>
			</div>
		</div>
	</div>
</template>

<script>
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
import Vue from 'vue'

export default {
	name: 'SieveFilterAction',
	components: {
		Multiselect,
	},
	props: {
		action: {
			type: Object,
			required: true,
		},
		supportedsievestructure: {
			type: Object,
			required: true,
		},
	},
	methods: {
		onSelectAction(action) {
			/*return this.expectedParameters[parameter].multiple
				? this.test.parameters[parameter]
				: this.test.parameters[parameter][0]*/
		},
	},
}
</script>
<style scoped>
.flex_row {
	display: flex;
	flex-direction: row;
}
.flex_column {
	display: flex;
	flex-direction: column;
}
input {
	margin: 0;
}
input.small {
	width: 35px;
	height: 35px;
	padding: 0;
}
</style>
