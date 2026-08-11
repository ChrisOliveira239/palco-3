import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import type { Event } from '@/types/event'

function cidadeDaSessao(session: Event['sessions'][number]): string | null {
  return session.venue?.ven_cidade ?? session.evs_cidade ?? null
}

function formatarData(data: string): string {
  return new Date(data).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' })
}

export function EventCard({ event }: { event: Event }) {
  const proximaSessao = event.sessions[0]
  const cidade = proximaSessao ? cidadeDaSessao(proximaSessao) : null

  return (
    <Card>
      <CardHeader>
        <CardTitle>{event.eve_titulo}</CardTitle>
      </CardHeader>
      <CardContent className="flex flex-col gap-1 text-sm">
        <span className="text-muted-foreground">{event.category.cat_nome}</span>
        {proximaSessao && (
          <span>
            {formatarData(proximaSessao.evs_data_inicio)}
            {cidade ? ` · ${cidade}` : ''}
          </span>
        )}
        <span>{event.eve_gratuito ? 'Gratuito' : 'Pago'}</span>
      </CardContent>
    </Card>
  )
}
