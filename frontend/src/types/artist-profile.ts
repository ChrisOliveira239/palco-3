export interface ArtistProfile {
  id: number
  user_id: number
  art_nome_artistico: string
  art_bio: string | null
  art_capa_url: string | null
  art_verificado: boolean
  art_drt: string | null
  art_telefone: string | null
  art_email: string | null
  art_site: string | null
  art_active: boolean
}
