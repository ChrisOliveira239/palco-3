import type { EventSession } from '@/types/event'

export function cidadeDaSessao(session: EventSession): string | null {
  return session.venue?.ven_cidade ?? session.evs_cidade ?? null
}

export function localDaSessao(session: EventSession): string | null {
  return session.venue?.ven_nome ?? session.evs_local_nome ?? null
}

export function formatarData(data: string): string {
  return new Date(data).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' })
}

export function formatarPreco(preco: string): string {
  return Number(preco).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}
