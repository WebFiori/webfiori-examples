<template>
  <div>
    <h1 class="text-h4 mb-4">Dashboard</h1>
    <v-row>
      <v-col cols="4">
        <v-card color="green-lighten-4">
          <v-card-title>Income</v-card-title>
          <v-card-text class="text-h5">${{ summary.income?.toFixed(2) || '0.00' }}</v-card-text>
        </v-card>
      </v-col>
      <v-col cols="4">
        <v-card color="red-lighten-4">
          <v-card-title>Expenses</v-card-title>
          <v-card-text class="text-h5">${{ summary.expense?.toFixed(2) || '0.00' }}</v-card-text>
        </v-card>
      </v-col>
      <v-col cols="4">
        <v-card color="blue-lighten-4">
          <v-card-title>Net</v-card-title>
          <v-card-text class="text-h5">${{ summary.net?.toFixed(2) || '0.00' }}</v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <h2 class="text-h5 mt-6 mb-2">Account Balances</h2>
    <v-table>
      <thead><tr><th>Account</th><th>Type</th><th>Balance</th></tr></thead>
      <tbody>
        <tr v-for="a in balances" :key="a.name">
          <td>{{ a.name }}</td><td>{{ a.type }}</td><td>${{ a.balance }}</td>
        </tr>
      </tbody>
    </v-table>

    <h2 class="text-h5 mt-6 mb-2">Spending by Category</h2>
    <v-table>
      <thead><tr><th>Category</th><th>Total</th></tr></thead>
      <tbody>
        <tr v-for="c in byCategory" :key="c.name">
          <td><v-chip :color="c.color" size="small" text-color="white">{{ c.name }}</v-chip></td>
          <td>${{ parseFloat(c.total).toFixed(2) }}</td>
        </tr>
      </tbody>
    </v-table>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { analytics } from '../api'

const summary = ref<any>({})
const balances = ref<any[]>([])
const byCategory = ref<any[]>([])

onMounted(async () => {
  const [s, b, c] = await Promise.all([
    analytics.summary(),
    analytics.accountBalances(),
    analytics.byCategory(),
  ])
  summary.value = s.data.data || s.data
  balances.value = s.data.data ? [] : (b.data.data || [])
  byCategory.value = c.data.data || []

  // Handle nested response format
  if (Array.isArray(b.data.data)) balances.value = b.data.data
  else if (b.data.data?.income !== undefined) balances.value = []
})
</script>
