import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter } from 'react-router'
import { api } from '@/services/api'
import { Home } from './Home'

jest.mock('@/services/api', () => ({
  api: { get: jest.fn() },
  setUnauthorizedHandler: jest.fn(),
}))

const mockedApi = api as unknown as { get: jest.Mock }

const categories = [
  { id: 1, cat_nome: 'Música', cat_slug: 'musica', cat_icone: null, cat_active: true },
]

const evento = {
  id: 10,
  eve_titulo: 'Show de Rock',
  eve_descricao: 'desc',
  category_id: 1,
  organizador_type: 'App\\Models\\ArtistProfile',
  organizador_id: 1,
  eve_status: 'PUBLICADO',
  eve_gratuito: true,
  eve_cartaz_url: null,
  eve_links_externos: null,
  aprovado_por_id: 1,
  eve_aprovado_em: null,
  eve_active: true,
  category: categories[0],
  sessions: [],
}

function mockApiResponses() {
  mockedApi.get.mockImplementation((url: string) => {
    if (url === '/categories') return Promise.resolve({ data: { categories } })
    if (url === '/events') return Promise.resolve({ data: { events: { data: [evento] } } })
    return Promise.reject(new Error(`unmocked GET ${url}`))
  })
}

function renderHome(initialEntry = '/') {
  return render(
    <MemoryRouter initialEntries={[initialEntry]}>
      <Home />
    </MemoryRouter>,
  )
}

describe('Home', () => {
  beforeEach(() => {
    jest.clearAllMocks()
    mockApiResponses()
  })

  it('lista eventos vindos da API', async () => {
    renderHome()

    expect(await screen.findByText('Show de Rock')).toBeInTheDocument()
    expect(mockedApi.get).toHaveBeenCalledWith('/categories')
    expect(mockedApi.get).toHaveBeenCalledWith('/events', { params: { category_id: undefined, cidade: undefined } })
  })

  it('mostra mensagem quando não há eventos', async () => {
    mockedApi.get.mockImplementation((url: string) => {
      if (url === '/categories') return Promise.resolve({ data: { categories: [] } })
      if (url === '/events') return Promise.resolve({ data: { events: { data: [] } } })
      return Promise.reject(new Error(`unmocked GET ${url}`))
    })

    renderHome()

    expect(await screen.findByText('Nenhum evento encontrado.')).toBeInTheDocument()
  })

  it('busca eventos filtrando por cidade', async () => {
    const user = userEvent.setup()
    renderHome()
    await screen.findByText('Show de Rock')

    await user.type(screen.getByLabelText('Cidade'), 'Recife')
    await user.click(screen.getByRole('button', { name: 'Buscar' }))

    await waitFor(() =>
      expect(mockedApi.get).toHaveBeenCalledWith('/events', {
        params: { category_id: undefined, cidade: 'Recife' },
      }),
    )
  })

  it('parte da URL já lê o filtro de cidade inicial', async () => {
    renderHome('/?cidade=Olinda')

    await waitFor(() =>
      expect(mockedApi.get).toHaveBeenCalledWith('/events', {
        params: { category_id: undefined, cidade: 'Olinda' },
      }),
    )
  })
})
