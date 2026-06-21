import { useEffect, useState } from 'react'
import { Typography, Table, TableHead, TableRow, TableCell, TableBody, TextField, Button, Select, MenuItem, Chip, Box } from '@mui/material'
import { transactions, accounts } from '../api'

export default function Transactions() {
  const [items, setItems] = useState<any[]>([])
  const [accs, setAccs] = useState<any[]>([])
  const [form, setForm] = useState({ accountId: '', type: 'expense', amount: '', date: new Date().toISOString().slice(0, 10), description: '' })

  const load = () => Promise.all([transactions.list(), accounts.list()]).then(([t, a]) => { setItems(t.data.data || []); setAccs(a.data.data || []) })
  useEffect(() => { load() }, [])

  const add = async () => {
    if (!form.accountId || !form.amount) return
    await transactions.create(form)
    setForm({ ...form, amount: '', description: '' })
    load()
  }

  return (
    <>
      <Typography variant="h4" gutterBottom>Transactions</Typography>
      <Box sx={{ display: 'flex', gap: 1, mb: 3, flexWrap: 'wrap' }}>
        <Select value={form.accountId} onChange={e => setForm({ ...form, accountId: e.target.value })} displayEmpty size="small">
          <MenuItem value="">Account</MenuItem>
          {accs.map(a => <MenuItem key={a.id} value={a.id}>{a.name}</MenuItem>)}
        </Select>
        <Select value={form.type} onChange={e => setForm({ ...form, type: e.target.value })} size="small">
          <MenuItem value="income">Income</MenuItem><MenuItem value="expense">Expense</MenuItem>
        </Select>
        <TextField size="small" type="number" placeholder="Amount" value={form.amount} onChange={e => setForm({ ...form, amount: e.target.value })} />
        <TextField size="small" type="date" value={form.date} onChange={e => setForm({ ...form, date: e.target.value })} />
        <TextField size="small" placeholder="Description" value={form.description} onChange={e => setForm({ ...form, description: e.target.value })} />
        <Button variant="contained" onClick={add}>Add</Button>
      </Box>
      <Table>
        <TableHead><TableRow><TableCell>Date</TableCell><TableCell>Account</TableCell><TableCell>Category</TableCell><TableCell>Type</TableCell><TableCell>Amount</TableCell><TableCell>Description</TableCell></TableRow></TableHead>
        <TableBody>{items.map(t => <TableRow key={t.id}><TableCell>{t.date}</TableCell><TableCell>{t.accountName}</TableCell><TableCell>{t.categoryName}</TableCell><TableCell><Chip label={t.type} color={t.type === 'income' ? 'success' : 'error'} size="small" /></TableCell><TableCell>${t.amount}</TableCell><TableCell>{t.description}</TableCell></TableRow>)}</TableBody>
      </Table>
    </>
  )
}
