import type { Category } from './category'
import type { Venue } from './venue'

export interface EventSession {
  id: number
  event_id: number
  venue_id: number | null
  evs_local_nome: string | null
  evs_endereco: string | null
  evs_cidade: string | null
  evs_estado: string | null
  evs_data_inicio: string
  evs_data_fim: string | null
  evs_active: boolean
  venue: Venue | null
}

export interface Event {
  id: number
  eve_titulo: string
  eve_descricao: string
  category_id: number
  organizador_type: string
  organizador_id: number
  eve_status: 'RASCUNHO' | 'PENDENTE' | 'APROVADO' | 'PUBLICADO' | 'REJEITADO'
  eve_gratuito: boolean
  eve_cartaz_url: string | null
  eve_links_externos: string[] | null
  aprovado_por_id: number | null
  eve_aprovado_em: string | null
  eve_active: boolean
  category: Category
  sessions: EventSession[]
}
