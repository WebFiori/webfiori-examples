import { BrowserRouter, Routes, Route, Link, Navigate } from 'react-router-dom'
import { AppBar, Toolbar, Typography, Button, Container, CssBaseline, ThemeProvider, createTheme } from '@mui/material'
import { useState, useEffect } from 'react'
import { auth } from './api'
import Login from './pages/Login'
import Dashboard from './pages/Dashboard'
import Transactions from './pages/Transactions'
import Accounts from './pages/Accounts'
import Budgets from './pages/Budgets'

const theme = createTheme()

function App() {
  const [loggedIn, setLoggedIn] = useState<boolean | null>(null)

  useEffect(() => {
    auth.profile().then(() => setLoggedIn(true)).catch(() => setLoggedIn(false))
  }, [])

  if (loggedIn === null) return null

  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <BrowserRouter>
        <AppBar position="static">
          <Toolbar>
            <Typography variant="h6" sx={{ flexGrow: 1 }}>Finance Tracker</Typography>
            {loggedIn && <>
              <Button color="inherit" component={Link} to="/">Dashboard</Button>
              <Button color="inherit" component={Link} to="/transactions">Transactions</Button>
              <Button color="inherit" component={Link} to="/accounts">Accounts</Button>
              <Button color="inherit" component={Link} to="/budgets">Budgets</Button>
              <Button color="inherit" onClick={() => { document.cookie = 'wf-session=; Max-Age=0; path=/'; window.location.href = '/login' }}>Logout</Button>
            </>}
          </Toolbar>
        </AppBar>
        <Container sx={{ mt: 4 }}>
          <Routes>
            <Route path="/login" element={<Login onLogin={() => setLoggedIn(true)} />} />
            <Route path="/" element={loggedIn ? <Dashboard /> : <Navigate to="/login" />} />
            <Route path="/transactions" element={loggedIn ? <Transactions /> : <Navigate to="/login" />} />
            <Route path="/accounts" element={loggedIn ? <Accounts /> : <Navigate to="/login" />} />
            <Route path="/budgets" element={loggedIn ? <Budgets /> : <Navigate to="/login" />} />
          </Routes>
        </Container>
      </BrowserRouter>
    </ThemeProvider>
  )
}

export default App
