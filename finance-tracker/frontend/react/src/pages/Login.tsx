import { useState } from 'react'
import { TextField, Button, Card, CardContent, Typography, Alert } from '@mui/material'
import { auth } from '../api'

export default function Login({ onLogin }: { onLogin: () => void }) {
  const [email, setEmail] = useState('demo@example.com')
  const [password, setPassword] = useState('demo123')
  const [error, setError] = useState('')

  const submit = async () => {
    try {
      await auth.login(email, password)
      window.location.href = '/'
    } catch (e: any) {
      setError(e.response?.data?.message || 'Login failed')
    }
  }

  return (
    <Card sx={{ maxWidth: 400, mx: 'auto', mt: 10 }}>
      <CardContent>
        <Typography variant="h5" gutterBottom>Login</Typography>
        {error && <Alert severity="error" sx={{ mb: 2 }}>{error}</Alert>}
        <TextField fullWidth label="Email" value={email} onChange={e => setEmail(e.target.value)} sx={{ mb: 2 }} />
        <TextField fullWidth label="Password" type="password" value={password} onChange={e => setPassword(e.target.value)} sx={{ mb: 2 }} />
        <Button variant="contained" fullWidth onClick={submit}>Login</Button>
      </CardContent>
    </Card>
  )
}
