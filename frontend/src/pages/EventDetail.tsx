import { useEffect, useState } from 'react'
import { useParams } from 'react-router'
import axios from 'axios'
import { api } from '@/services/api'
import { cidadeDaSessao, formatarData, formatarPreco, localDaSessao } from '@/lib/event-format'
import type { Event } from '@/types/event'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

export function EventDetail() {
  const { id } = useParams()
  const [event, setEvent] = useState<Event | null>(null)
  const [notFound, setNotFound] = useState(false)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    setLoading(true)
    setNotFound(false)

    api
      .get<{ event: Event }>(`/events/${id}`)
      .then((response) => setEvent(response.data.event))
      .catch((error: unknown) => {
        if (axios.isAxiosError(error) && error.response?.status === 404) {
          setNotFound(true)
        } else {
          throw error
        }
      })
      .finally(() => setLoading(false))
  }, [id])

  if (loading) return <p className="text-muted-foreground p-8 text-sm">Carregando evento...</p>
  if (notFound) return <p className="text-muted-foreground p-8 text-sm">Evento não encontrado.</p>
  if (!event) return null

  const media = event.media ?? []
  const artists = event.artists ?? []
  const groups = event.groups ?? []

  return (
    <div className="mx-auto flex max-w-2xl flex-col gap-6 p-8">
      {event.eve_cartaz_url && (
        <img src={event.eve_cartaz_url} alt={event.eve_titulo} className="w-full rounded-lg object-cover" />
      )}

      <div>
        <h1 className="text-2xl font-semibold">{event.eve_titulo}</h1>
        <p className="text-muted-foreground text-sm">
          {event.category.cat_nome} · {event.eve_gratuito ? 'Gratuito' : 'Pago'}
        </p>
      </div>

      <p className="text-sm whitespace-pre-line">{event.eve_descricao}</p>

      <div className="flex flex-col gap-4">
        <h2 className="text-lg font-semibold">Sessões</h2>
        {event.sessions.length === 0 && (
          <p className="text-muted-foreground text-sm">Sem sessões cadastradas.</p>
        )}
        {event.sessions.map((session) => {
          const cidade = cidadeDaSessao(session)
          return (
            <Card key={session.id}>
              <CardHeader>
                <CardTitle className="text-base">{formatarData(session.evs_data_inicio)}</CardTitle>
              </CardHeader>
              <CardContent className="flex flex-col gap-2 text-sm">
                <span>
                  {localDaSessao(session)}
                  {cidade ? ` · ${cidade}` : ''}
                </span>
                {(session.ticket_types ?? []).map((ticketType) => (
                  <span key={ticketType.id}>
                    {ticketType.tit_nome}: {formatarPreco(ticketType.tit_preco)}
                  </span>
                ))}
              </CardContent>
            </Card>
          )
        })}
      </div>

      {media.length > 0 && (
        <div className="flex flex-col gap-4">
          <h2 className="text-lg font-semibold">Galeria</h2>
          <div className="grid grid-cols-3 gap-2">
            {media.map((item) =>
              item.evm_tipo === 'FOTO' ? (
                <img key={item.id} src={item.evm_url} alt="" className="aspect-square rounded object-cover" />
              ) : (
                <a
                  key={item.id}
                  href={item.evm_url}
                  target="_blank"
                  rel="noreferrer"
                  className="flex aspect-square items-center justify-center rounded bg-muted text-sm underline"
                >
                  Vídeo
                </a>
              ),
            )}
          </div>
        </div>
      )}

      {(artists.length > 0 || groups.length > 0) && (
        <div className="flex flex-col gap-2">
          <h2 className="text-lg font-semibold">Participantes</h2>
          <ul className="text-sm">
            {artists.map((artist) => (
              <li key={`artist-${artist.id}`}>{artist.art_nome_artistico}</li>
            ))}
            {groups.map((group) => (
              <li key={`group-${group.id}`}>{group.gro_nome}</li>
            ))}
          </ul>
        </div>
      )}
    </div>
  )
}
