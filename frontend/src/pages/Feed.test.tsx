import { render, screen } from '@testing-library/react'
import { api } from '@/services/api'
import { Feed } from './Feed'

jest.mock('@/services/api', () => ({
  api: { get: jest.fn() },
  setUnauthorizedHandler: jest.fn(),
}))

const mockedApi = api as unknown as { get: jest.Mock }

const postDeArtista = {
  id: 1,
  autor_type: 'App\\Models\\ArtistProfile',
  autor_id: 1,
  fee_tipo: 'ATUALIZACAO' as const,
  fee_conteudo: 'Ensaio marcado pra semana que vem.',
  fee_midia_url: null,
  fee_active: true,
  autor: { id: 1, user_id: 1, art_nome_artistico: 'Banda X', art_bio: null, art_capa_url: null, art_verificado: false, art_drt: null, art_telefone: null, art_email: null, art_site: null, art_active: true },
}

const postDeGrupo = {
  id: 2,
  autor_type: 'App\\Models\\Group',
  autor_id: 5,
  fee_tipo: 'FOTO' as const,
  fee_conteudo: null,
  fee_midia_url: 'https://example.com/foto.jpg',
  fee_active: true,
  autor: { id: 5, user_id: 2, gro_nome: 'Grupo Y', gro_active: true },
}

describe('Feed', () => {
  beforeEach(() => {
    jest.clearAllMocks()
  })

  it('lista posts de artistas e grupos seguidos', async () => {
    mockedApi.get.mockResolvedValueOnce({ data: { feed: { data: [postDeArtista, postDeGrupo] } } })

    const { container } = render(<Feed />)

    expect(await screen.findByText('Banda X')).toBeInTheDocument()
    expect(screen.getByText('Ensaio marcado pra semana que vem.')).toBeInTheDocument()
    expect(screen.getByText('Grupo Y')).toBeInTheDocument()
    expect(container.querySelector('img')).toHaveAttribute('src', 'https://example.com/foto.jpg')
    expect(mockedApi.get).toHaveBeenCalledWith('/feed')
  })

  it('mostra mensagem quando o feed está vazio', async () => {
    mockedApi.get.mockResolvedValueOnce({ data: { feed: { data: [] } } })

    render(<Feed />)

    expect(
      await screen.findByText(/Nenhuma novidade por aqui/),
    ).toBeInTheDocument()
  })
})
