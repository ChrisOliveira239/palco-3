export interface TicketType {
  id: number
  event_session_id: number
  tit_nome: string
  tit_preco: string
  tit_quantidade_total: number
  tit_quantidade_vendida: number
  tit_venda_inicio: string | null
  tit_venda_fim: string | null
  tit_active: boolean
}
