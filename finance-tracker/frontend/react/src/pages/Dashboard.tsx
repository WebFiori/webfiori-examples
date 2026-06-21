import { useEffect, useState } from 'react'
import { Card, CardContent, Typography, Grid, Table, TableHead, TableRow, TableCell, TableBody, Chip } from '@mui/material'
import { analytics } from '../api'

export default function Dashboard() {
  const [summary, setSummary] = useState<any>({})
  const [balances, setBalances] = useState<any[]>([])
  const [byCategory, setByCategory] = useState<any[]>([])

  useEffect(() => {
    Promise.all([analytics.summary(), analytics.accountBalances(), analytics.byCategory()])
      .then(([s, b, c]) => {
        setSummary(s.data.data || {})
        setBalances(b.data.data || [])
        setByCategory(c.data.data || [])
      })
  }, [])

  return (
    <>
      <Typography variant="h4" gutterBottom>Dashboard</Typography>
      <Grid container spacing={2} sx={{ mb: 4 }}>
        <Grid size={4}><Card sx={{ bgcolor: '#e8f5e9' }}><CardContent><Typography variant="h6">Income</Typography><Typography variant="h4">${summary.income?.toFixed(2) || '0.00'}</Typography></CardContent></Card></Grid>
        <Grid size={4}><Card sx={{ bgcolor: '#ffebee' }}><CardContent><Typography variant="h6">Expenses</Typography><Typography variant="h4">${summary.expense?.toFixed(2) || '0.00'}</Typography></CardContent></Card></Grid>
        <Grid size={4}><Card sx={{ bgcolor: '#e3f2fd' }}><CardContent><Typography variant="h6">Net</Typography><Typography variant="h4">${summary.net?.toFixed(2) || '0.00'}</Typography></CardContent></Card></Grid>
      </Grid>

      <Typography variant="h5" gutterBottom>Account Balances</Typography>
      <Table sx={{ mb: 4 }}>
        <TableHead><TableRow><TableCell>Account</TableCell><TableCell>Type</TableCell><TableCell>Balance</TableCell></TableRow></TableHead>
        <TableBody>{balances.map((a, i) => <TableRow key={i}><TableCell>{a.name}</TableCell><TableCell>{a.type}</TableCell><TableCell>${a.balance}</TableCell></TableRow>)}</TableBody>
      </Table>

      <Typography variant="h5" gutterBottom>Spending by Category</Typography>
      <Table>
        <TableHead><TableRow><TableCell>Category</TableCell><TableCell>Total</TableCell></TableRow></TableHead>
        <TableBody>{byCategory.map((c, i) => <TableRow key={i}><TableCell><Chip label={c.name} sx={{ bgcolor: c.color, color: '#fff' }} size="small" /></TableCell><TableCell>${parseFloat(c.total).toFixed(2)}</TableCell></TableRow>)}</TableBody>
      </Table>
    </>
  )
}
