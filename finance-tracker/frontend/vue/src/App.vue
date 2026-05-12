<template>
  <v-app>
    <v-app-bar color="primary" dark>
      <v-toolbar-title>Finance Tracker</v-toolbar-title>
      <v-spacer />
      <template v-if="loggedIn">
        <v-btn to="/" text>Dashboard</v-btn>
        <v-btn to="/transactions" text>Transactions</v-btn>
        <v-btn to="/accounts" text>Accounts</v-btn>
        <v-btn to="/budgets" text>Budgets</v-btn>
        <v-btn @click="logout" text>Logout</v-btn>
      </template>
    </v-app-bar>
    <v-main>
      <v-container>
        <router-view />
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { auth } from './api'

const router = useRouter()
const loggedIn = ref(false)

onMounted(async () => {
  try {
    await auth.profile()
    loggedIn.value = true
  } catch {
    loggedIn.value = false
    router.push('/login')
  }
})

const logout = () => {
  document.cookie = 'wf-session=; Max-Age=0; path=/'
  loggedIn.value = false
  router.push('/login')
}
</script>
