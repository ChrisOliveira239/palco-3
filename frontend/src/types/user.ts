export interface User {
  id: number
  use_name: string
  email: string
  use_avatar_url: string | null
  use_bio: string | null
  use_city: string | null
  use_state: string | null
  use_latitude: number | null
  use_longitude: number | null
  use_is_admin: boolean
  use_tipo_conta: 'PESSOA' | 'EMPRESA'
  use_active: boolean
}
