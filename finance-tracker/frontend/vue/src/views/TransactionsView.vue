<template>
  <div>
    <h1 class="text-h4 mb-4">Transactions</h1>

    <v-card class="mb-4 pa-4">
      <v-row>
        <v-col cols="3"><v-select v-model="form.accountId" :items="accountItems" label="Account" item-title="text" item-value="value" /></v-col>
        <v-col cols="2"><v-select v-model="form.type" :items="['income','expense']" label="Type" /></v-col>
        <v-col cols="2"><v-text-field v-model="form.amount" label="Amount" type="number" /></v-col>
        <v-col cols="2"><v-text-field v-model="form.date" label="Date" type="date" /></v-col>
        <v-col cols="2"><v-text-field v-model="form.description" label="Description" /></v-col>
        <v-col cols="1"><v-btn color="primary" @click="add" icon="mdi-plus" /></v-col>
      </v-row>
    </v-card>

    <v-table>
      <thead><tr><th>Date</th><th>Account</th><th>Category</th><th>Type</th><th>Amount</th><th>Description</th></tr></thead>
      <tbody>
        <tr v-for="t in items" :key="t.id">
          <td>{{ t.date }}</td>
          <td>{{ t.accountName }}</td>
          <td>{{ t.categoryName }}</td>
          <td><v-chip :color="t.type === 'income' ? 'green' : 'red'" size="small">{{ t.type }}</v-chip></td>
          <td>${{ t.amount }}</td>
          <td>{{ t.description }}</td>
        </tr>
      </tbody>
    </v-table>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { transactions, accounts } from '../api'

const items = ref<any[]>([])
const accs = ref<any[]>([])
const form = ref({ accountId: null as number | null, type: 'expense', amount: '', date: new Date().toISOString().slice(0, 10), description: '' })

const accountItems = computed(() => accs.value.map(a => ({ text: a.name, value: a.id })))

const load = async () => {
  const [t, a] = await Promise.all([transactions.list(), accounts.list()])
  items.value = t.data.data || []
  accs.value = a.data.data || []
}

const add = async () => {
  if (!form.value.accountId || !form.value.amount) return
  await transactions.create(form.value)
  form.value = { accountId: form.value.accountId, type: 'expense', amount: '', date: new Date().toISOString().slice(0, 10), description: '' }
  await load()
}

onMounted(load)
</script>
