<template>
  <div>
    <h1 class="text-h4 mb-4">Accounts</h1>

    <v-card class="mb-4 pa-4">
      <v-row>
        <v-col cols="4"><v-text-field v-model="form.name" label="Account Name" /></v-col>
        <v-col cols="3"><v-select v-model="form.type" :items="['checking','savings','credit','cash']" label="Type" /></v-col>
        <v-col cols="3"><v-text-field v-model="form.balance" label="Balance" type="number" /></v-col>
        <v-col cols="2"><v-btn color="primary" @click="add" block>Add</v-btn></v-col>
      </v-row>
    </v-card>

    <v-table>
      <thead><tr><th>Name</th><th>Type</th><th>Balance</th><th></th></tr></thead>
      <tbody>
        <tr v-for="a in items" :key="a.id">
          <td>{{ a.name }}</td>
          <td>{{ a.type }}</td>
          <td>${{ a.balance }}</td>
          <td><v-btn icon="mdi-delete" size="small" color="red" @click="remove(a.id)" /></td>
        </tr>
      </tbody>
    </v-table>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { accounts } from '../api'

const items = ref<any[]>([])
const form = ref({ name: '', type: 'checking', balance: '0' })

const load = async () => { items.value = (await accounts.list()).data.data || [] }
const add = async () => {
  if (!form.value.name) return
  await accounts.create(form.value.name, form.value.type, parseFloat(form.value.balance))
  form.value = { name: '', type: 'checking', balance: '0' }
  await load()
}
const remove = async (id: number) => { await accounts.remove(id); await load() }

onMounted(load)
</script>
