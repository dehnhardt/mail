<template>
	<Treeselect
		ref="Treeselect"
		:options="mailboxes"
		:multiple="false"
		:clearable="false"
		v-bind="$attrs"
		v-on="$listeners"
	/>
</template>
<script>
// import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
import Treeselect from '@riophae/vue-treeselect'
import '@riophae/vue-treeselect/dist/vue-treeselect.css'
import Vue from 'vue'

export default {
	name: 'MailboxPicker',
	components: {
		Treeselect,
	},
	props: {
		accountid: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			mailboxes: [],
			mailboxNames: Array,
		}
	},
	computed: {
		// nothing
	},
	mounted() {
		this.getFolders(this.mailboxes)
	},
	methods: {
		getFolders(root, folderid) {
			var folders = []
			if (!folderid) {
				folders = this.$store.getters.getFolders(this.accountid)
			} else {
				folders = this.$store.getters.getSubfolders(this.accountid, folderid)
			}
			folders.forEach((folder) => {
				const mailbox = {
					id: folder.displayName,
					label: folder.displayName,
				}
				if (folder.folders.length > 0) {
					mailbox['children'] = []
					this.getFolders(mailbox.children, folder.id)
				}
				root.push(mailbox)
			})
		},
	},
}
</script>
<style>
.vue-treeselect__control {
	padding: 0;
	border: 0;
	width: 300px;
}
.vue-treeselect__control-arrow-container {
	display: none;
}
.vue-treeselect--searchable .vue-treeselect__input-container {
	padding-left: 0;
}
input.vue-treeselect__input {
	margin: 0;
}
</style>
