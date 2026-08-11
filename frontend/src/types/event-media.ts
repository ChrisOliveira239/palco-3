export interface EventMedia {
  id: number
  event_id: number
  evm_tipo: 'FOTO' | 'VIDEO'
  evm_url: string
  evm_ordem: number
  evm_active: boolean
}
