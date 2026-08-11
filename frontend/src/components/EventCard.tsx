import { Link } from 'react-router'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { cidadeDaSessao, formatarData } from '@/lib/event-format'
import type { Event } from '@/types/event'

export function EventCard({ event }: { event: Event }) {
  const proximaSessao = event.sessions[0]
  const cidade = proximaSessao ? cidadeDaSessao(proximaSessao) : null

  return (
    <Link to={`/eventos/${event.id}`}>
      <Card className="transition-colors hover:bg-accent/50">
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
    </Link>
  )
}
