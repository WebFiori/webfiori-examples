<template>
  <div>
    <h1 class="text-h4 mb-4">Budgets</h1>

    <v-card class="mb-4 pa-4">
      <v-row>
        <v-col cols="3"><v-select v-model="form.categoryId" :items="catItems" label="Category" item-title="text" item-value="value" /></v-col>
        <v-col cols="3"><v-text-field v-model="form.amountLimit" label="Limit" type="number" /></v-col>
        <v-col cols="3"><v-select v-model="form.period" :items="['monthly','weekly']" label="Period" /></v-col>
        <v-col cols="3"><v-btn color="primary" @click="add" block>Add Budget</v-btn></v-col>
      </v-row>
    </v-card>

    <v-row>
      <v-col v-for="b in items" :key="b.id" cols="4">
        <v-card>
          <v-card-title>{{ b.categoryName }}</v-card-title>
          <v-card-text>
            <div class="mb-2">${{ b.spent }} / ${{ b.amountLimit }} ({{ b.period }})</div>
            <v-progress-linear :model-value="(b.spent / b.amountLimit) * 100" :color="b.spent > b.amountLimit ? 'red' : 'green'" height="20" rounded />
          </v-card-text>
          <v-card-actions>
            <v-btn color="red" size="small" @click="remove(b.id)">Remove</v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { budgets, categories } from '../api'

const items = ref<any[]>([])
const cats = ref<any[]>([])
const form = ref({ categoryId: null as number | null, amountLimit: '', period: 'monthly' })

const catItems = computed(() => cats.value.filter(c => c.type === 'expense').map(c => ({ text: c.name, value: c.id })))

const load = async () => {
  const [b, c] = await Promise.all([budgets.list(), categories.list()])
  items.value = b.data.data || []
  cats.value = c.data.data || []
}

const add = async () => {
  if (!form.value.categoryId || !form.value.amountLimit) return
  await budgets.create({ ...form.value, startDate: new Date().toISOString().slice(0, 10) })
  form.value = { categoryId: null, amountLimit: '', period: 'monthly' }
  await load()
}

const remove = async (id: number) => { await budgets.remove(id); await load() }

onMounted(load)
</script>
