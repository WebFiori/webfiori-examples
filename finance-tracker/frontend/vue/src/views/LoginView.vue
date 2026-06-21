<template>
  <v-card max-width="400" class="mx-auto mt-10">
    <v-card-title>Login</v-card-title>
    <v-card-text>
      <v-alert v-if="error" type="error" class="mb-4">{{ error }}</v-alert>
      <v-text-field v-model="email" label="Email" type="email" required />
      <v-text-field v-model="password" label="Password" type="password" required />
    </v-card-text>
    <v-card-actions>
      <v-btn color="primary" @click="login" :loading="loading" block>Login</v-btn>
    </v-card-actions>
  </v-card>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { auth } from '../api'

const router = useRouter()
const email = ref('demo@example.com')
const password = ref('demo123')
const error = ref('')
const loading = ref(false)

const login = async () => {
  loading.value = true
  error.value = ''
  try {
    await auth.login(email.value, password.value)
    window.location.href = '/'
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>
