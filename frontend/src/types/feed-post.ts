import type { ArtistProfile } from './artist-profile'
import type { Group } from './group'

export interface FeedPost {
  id: number
  autor_type: string
  autor_id: number
  fee_tipo: 'ATUALIZACAO' | 'FOTO' | 'VIDEO' | 'EVENTO'
  fee_conteudo: string | null
  fee_midia_url: string | null
  fee_active: boolean
  autor: ArtistProfile | Group
}
