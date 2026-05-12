import { useEffect, useState } from 'react'
import { Typography, Table, TableHead, TableRow, TableCell, TableBody, TextField, Button, Select, MenuItem, IconButton, Box } from '@mui/material'
import DeleteIcon from '@mui/icons-material/Delete'
import { accounts } from '../api'

export default function Accounts() {
  const [items, setItems] = useState<any[]>([])
  const [form, setForm] = useState({ name: '', type: 'checking', balance: '0' })

  const load = () => accounts.list().then(r => setItems(r.data.data || []))
  useEffect(() => { load() }, [])

  const add = async () => {
    if (!form.name) return
    await accounts.create(form.name, form.type, parseFloat(form.balance))
    setForm({ name: '', type: 'checking', balance: '0' })
    load()
  }

  return (
    <>
      <Typography variant="h4" gutterBottom>Accounts</Typography>
      <Box sx={{ display: 'flex', gap: 1, mb: 3 }}>
        <TextField size="small" placeholder="Name" value={form.name} onChange={e => setForm({ ...form, name: e.target.value })} />
        <Select value={form.type} onChange={e => setForm({ ...form, type: e.target.value })} size="small">
          <MenuItem value="checking">Checking</MenuItem><MenuItem value="savings">Savings</MenuItem><MenuItem value="credit">Credit</MenuItem><MenuItem value="cash">Cash</MenuItem>
        </Select>
        <TextField size="small" type="number" placeholder="Balance" value={form.balance} onChange={e => setForm({ ...form, balance: e.target.value })} />
        <Button variant="contained" onClick={add}>Add</Button>
      </Box>
      <Table>
        <TableHead><TableRow><TableCell>Name</TableCell><TableCell>Type</TableCell><TableCell>Balance</TableCell><TableCell></TableCell></TableRow></TableHead>
        <TableBody>{items.map(a => <TableRow key={a.id}><TableCell>{a.name}</TableCell><TableCell>{a.type}</TableCell><TableCell>${a.balance}</TableCell><TableCell><IconButton color="error" onClick={() => { accounts.remove(a.id).then(load) }}><DeleteIcon /></IconButton></TableCell></TableRow>)}</TableBody>
      </Table>
    </>
  )
}
