import { useEffect, useState, type FormEvent } from 'react'
import { useSearchParams } from 'react-router'
import { api } from '@/services/api'
import { EventCard } from '@/components/EventCard'
import type { Event } from '@/types/event'
import type { Category } from '@/types/category'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const TODAS_CATEGORIAS = 'todas'
const SEM_RAIO = 'nenhum'
const OPCOES_RAIO_KM = [5, 10, 25, 50, 100]

export function Home() {
  const [searchParams, setSearchParams] = useSearchParams()
  const [categories, setCategories] = useState<Category[]>([])
  const [events, setEvents] = useState<Event[]>([])
  const [loading, setLoading] = useState(true)
  const [locating, setLocating] = useState(false)
  const [locationError, setLocationError] = useState<string | null>(null)

  const categoryId = searchParams.get('category_id') ?? ''
  const cidade = searchParams.get('cidade') ?? ''
  const lat = searchParams.get('lat') ?? ''
  const lng = searchParams.get('lng') ?? ''
  const raioKm = searchParams.get('raio_km') ?? ''

  const [cidadeInput, setCidadeInput] = useState(cidade)

  useEffect(() => {
    api.get<{ categories: Category[] }>('/categories').then((response) => {
      setCategories(response.data.categories)
    })
  }, [])

  useEffect(() => {
    setLoading(true)
    api
      .get<{ events: { data: Event[] } }>('/events', {
        params: {
          category_id: categoryId || undefined,
          cidade: cidade || undefined,
          lat: lat || undefined,
          lng: lng || undefined,
          raio_km: raioKm || undefined,
        },
      })
      .then((response) => setEvents(response.data.events.data))
      .finally(() => setLoading(false))
  }, [categoryId, cidade, lat, lng, raioKm])

  function handleCategoryChange(value: string | null) {
    const next = new URLSearchParams(searchParams)
    if (value && value !== TODAS_CATEGORIAS) next.set('category_id', value)
    else next.delete('category_id')
    setSearchParams(next)
  }

  function handleSubmit(event: FormEvent) {
    event.preventDefault()
    const next = new URLSearchParams(searchParams)
    if (cidadeInput) next.set('cidade', cidadeInput)
    else next.delete('cidade')
    setSearchParams(next)
  }

  function handleRaioChange(value: string | null) {
    if (!value || value === SEM_RAIO) {
      const next = new URLSearchParams(searchParams)
      next.delete('lat')
      next.delete('lng')
      next.delete('raio_km')
      setSearchParams(next)
      setLocationError(null)
      return
    }

    setLocationError(null)
    setLocating(true)
    navigator.geolocation.getCurrentPosition(
      (position) => {
        setLocating(false)
        const next = new URLSearchParams(searchParams)
        next.set('lat', String(position.coords.latitude))
        next.set('lng', String(position.coords.longitude))
        next.set('raio_km', value)
        setSearchParams(next)
      },
      () => {
        setLocating(false)
        setLocationError('Não foi possível obter sua localização. Permita o acesso à localização no navegador.')
      },
    )
  }

  return (
    <div className="mx-auto flex max-w-3xl flex-col gap-6 p-8">
      <h1 className="text-2xl font-semibold">Eventos</h1>

      <form onSubmit={handleSubmit} className="flex flex-wrap items-end gap-4">
        <div className="flex flex-col gap-1.5">
          <Label htmlFor="category_id">Categoria</Label>
          <Select value={categoryId || TODAS_CATEGORIAS} onValueChange={handleCategoryChange}>
            <SelectTrigger id="category_id" className="w-48">
              <SelectValue placeholder="Todas" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value={TODAS_CATEGORIAS}>Todas</SelectItem>
              {categories.map((category) => (
                <SelectItem key={category.id} value={String(category.id)}>
                  {category.cat_nome}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>

        <div className="flex flex-col gap-1.5">
          <Label htmlFor="cidade">Cidade</Label>
          <Input
            id="cidade"
            value={cidadeInput}
            onChange={(event) => setCidadeInput(event.target.value)}
            placeholder="Ex: Recife"
          />
        </div>

        <div className="flex flex-col gap-1.5">
          <Label htmlFor="raio_km">Raio de distância</Label>
          <Select value={raioKm || SEM_RAIO} onValueChange={handleRaioChange} disabled={locating}>
            <SelectTrigger id="raio_km" className="w-48">
              <SelectValue placeholder="Sem filtro" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value={SEM_RAIO}>Sem filtro</SelectItem>
              {OPCOES_RAIO_KM.map((opcao) => (
                <SelectItem key={opcao} value={String(opcao)}>
                  Até {opcao} km
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>

        <Button type="submit">Buscar</Button>
      </form>

      {locating && <p className="text-muted-foreground text-sm">Obtendo sua localização...</p>}
      {locationError && <p className="text-destructive text-sm">{locationError}</p>}

      {loading && <p className="text-muted-foreground text-sm">Carregando eventos...</p>}
      {!loading && events.length === 0 && (
        <p className="text-muted-foreground text-sm">Nenhum evento encontrado.</p>
      )}

      <div className="grid gap-4 sm:grid-cols-2">
        {events.map((event) => (
          <EventCard key={event.id} event={event} />
        ))}
      </div>
    </div>
  )
}
