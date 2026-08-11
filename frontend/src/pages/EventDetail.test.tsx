import { render, screen } from '@testing-library/react'
import { MemoryRouter, Route, Routes } from 'react-router'
import { api } from '@/services/api'
import { EventDetail } from './EventDetail'

jest.mock('@/services/api', () => ({
  api: { get: jest.fn() },
  setUnauthorizedHandler: jest.fn(),
}))

const mockedApi = api as unknown as { get: jest.Mock }

const evento = {
  id: 5,
  eve_titulo: 'Show de Rock',
  eve_descricao: 'Uma noite de rock.',
  category_id: 1,
  organizador_type: 'App\\Models\\ArtistProfile',
  organizador_id: 1,
  eve_status: 'PUBLICADO',
  eve_gratuito: false,
  eve_cartaz_url: null,
  eve_links_externos: null,
  aprovado_por_id: 1,
  eve_aprovado_em: null,
  eve_active: true,
  category: { id: 1, cat_nome: 'Música', cat_slug: 'musica', cat_icone: null, cat_active: true },
  sessions: [
    {
      id: 1,
      event_id: 5,
      venue_id: null,
      evs_local_nome: 'Teatro Municipal',
      evs_endereco: 'Rua X',
      evs_cidade: 'Recife',
      evs_estado: 'PE',
      evs_data_inicio: '2026-09-01T20:00:00',
      evs_data_fim: null,
      evs_active: true,
      venue: null,
      ticket_types: [
        {
          id: 1,
          event_session_id: 1,
          tit_nome: 'Inteira',
          tit_preco: '50.00',
          tit_quantidade_total: 100,
          tit_quantidade_vendida: 0,
          tit_venda_inicio: null,
          tit_venda_fim: null,
          tit_active: true,
        },
      ],
    },
  ],
  media: [{ id: 1, event_id: 5, evm_tipo: 'FOTO', evm_url: 'https://example.com/foto.jpg', evm_ordem: 0, evm_active: true }],
  artists: [{ id: 1, user_id: 1, art_nome_artistico: 'Banda X', art_bio: null, art_capa_url: null, art_verificado: false, art_drt: null, art_telefone: null, art_email: null, art_site: null, art_active: true, artist_profile: null }],
  groups: [],
}

function renderAt(path: string) {
  return render(
    <MemoryRouter initialEntries={[path]}>
      <Routes>
        <Route path="/eventos/:id" element={<EventDetail />} />
      </Routes>
    </MemoryRouter>,
  )
}

describe('EventDetail', () => {
  beforeEach(() => {
    jest.clearAllMocks()
  })

  it('mostra os dados do evento, sessão, ingresso, galeria e participantes', async () => {
    mockedApi.get.mockResolvedValueOnce({ data: { event: evento } })

    renderAt('/eventos/5')

    expect(await screen.findByText('Show de Rock')).toBeInTheDocument()
    expect(mockedApi.get).toHaveBeenCalledWith('/events/5')
    expect(screen.getByText('Música · Pago')).toBeInTheDocument()
    expect(screen.getByText(/Teatro Municipal/)).toBeInTheDocument()
    expect(screen.getByText(/Recife/)).toBeInTheDocument()
    expect(screen.getByText(/Inteira: R\$\s?50,00/)).toBeInTheDocument()
    expect(screen.getByText('Banda X')).toBeInTheDocument()
  })

  it('mostra mensagem de não encontrado em 404', async () => {
    mockedApi.get.mockRejectedValueOnce({ isAxiosError: true, response: { status: 404 } })

    renderAt('/eventos/999')

    expect(await screen.findByText('Evento não encontrado.')).toBeInTheDocument()
  })
})
