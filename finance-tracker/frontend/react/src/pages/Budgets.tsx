import { useEffect, useState } from 'react'
import { Typography, Card, CardContent, CardActions, Button, Grid, LinearProgress, Select, MenuItem, TextField, Box } from '@mui/material'
import { budgets, categories } from '../api'

export default function Budgets() {
  const [items, setItems] = useState<any[]>([])
  const [cats, setCats] = useState<any[]>([])
  const [form, setForm] = useState({ categoryId: '', amountLimit: '', period: 'monthly' })

  const load = () => Promise.all([budgets.list(), categories.list()]).then(([b, c]) => { setItems(b.data.data || []); setCats(c.data.data || []) })
  useEffect(() => { load() }, [])

  const add = async () => {
    if (!form.categoryId || !form.amountLimit) return
    await budgets.create({ ...form, startDate: new Date().toISOString().slice(0, 10) })
    setForm({ categoryId: '', amountLimit: '', period: 'monthly' })
    load()
  }

  return (
    <>
      <Typography variant="h4" gutterBottom>Budgets</Typography>
      <Box sx={{ display: 'flex', gap: 1, mb: 3 }}>
        <Select value={form.categoryId} onChange={e => setForm({ ...form, categoryId: e.target.value })} displayEmpty size="small">
          <MenuItem value="">Category</MenuItem>
          {cats.filter(c => c.type === 'expense').map(c => <MenuItem key={c.id} value={c.id}>{c.name}</MenuItem>)}
        </Select>
        <TextField size="small" type="number" placeholder="Limit" value={form.amountLimit} onChange={e => setForm({ ...form, amountLimit: e.target.value })} />
        <Select value={form.period} onChange={e => setForm({ ...form, period: e.target.value })} size="small">
          <MenuItem value="monthly">Monthly</MenuItem><MenuItem value="weekly">Weekly</MenuItem>
        </Select>
        <Button variant="contained" onClick={add}>Add Budget</Button>
      </Box>
      <Grid container spacing={2}>
        {items.map(b => (
          <Grid size={4} key={b.id}>
            <Card>
              <CardContent>
                <Typography variant="h6">{b.categoryName}</Typography>
                <Typography>${b.spent} / ${b.amountLimit} ({b.period})</Typography>
                <LinearProgress variant="determinate" value={Math.min((b.spent / b.amountLimit) * 100, 100)} color={b.spent > b.amountLimit ? 'error' : 'success'} sx={{ mt: 1, height: 10, borderRadius: 5 }} />
              </CardContent>
              <CardActions><Button color="error" size="small" onClick={() => { budgets.remove(b.id).then(load) }}>Remove</Button></CardActions>
            </Card>
          </Grid>
        ))}
      </Grid>
    </>
  )
}
