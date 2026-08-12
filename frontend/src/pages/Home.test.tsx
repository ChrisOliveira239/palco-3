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

const paramsBase = { category_id: undefined, cidade: undefined, lat: undefined, lng: undefined, raio_km: undefined }

const getCurrentPosition = jest.fn()

beforeAll(() => {
  Object.defineProperty(window.navigator, 'geolocation', {
    value: { getCurrentPosition },
    configurable: true,
  })

  // jsdom não implementa Pointer Events/ResizeObserver/scrollIntoView; o popup do Select (Base UI) depende deles pra abrir.
  window.HTMLElement.prototype.scrollIntoView = jest.fn()
  window.HTMLElement.prototype.hasPointerCapture = jest.fn()
  window.HTMLElement.prototype.releasePointerCapture = jest.fn()
  window.ResizeObserver = class {
    observe() {}
    unobserve() {}
    disconnect() {}
  }
})

// jsdom não implementa `elementFromPoint` de verdade — o pointerEventsCheck padrão do user-event
// usa isso pra confirmar o alvo do clique, e sempre falha silenciosamente sobre o popup do Select.
function setupUser() {
  return userEvent.setup({ pointerEventsCheck: 0 })
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
    expect(mockedApi.get).toHaveBeenCalledWith('/events', { params: paramsBase })
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
        params: { ...paramsBase, cidade: 'Recife' },
      }),
    )
  })

  it('parte da URL já lê o filtro de cidade inicial', async () => {
    renderHome('/?cidade=Olinda')

    await waitFor(() =>
      expect(mockedApi.get).toHaveBeenCalledWith('/events', {
        params: { ...paramsBase, cidade: 'Olinda' },
      }),
    )
  })

  it('busca eventos por raio de distância usando geolocalização do navegador', async () => {
    getCurrentPosition.mockImplementation((success: PositionCallback) => {
      success({ coords: { latitude: -8.05, longitude: -34.9 } } as GeolocationPosition)
    })

    const user = setupUser()
    renderHome()
    await screen.findByText('Show de Rock')

    await user.click(screen.getByLabelText('Raio de distância'))
    await user.click(await screen.findByRole('option', { name: 'Até 10 km' }))

    await waitFor(() =>
      expect(mockedApi.get).toHaveBeenCalledWith('/events', {
        params: { ...paramsBase, lat: '-8.05', lng: '-34.9', raio_km: '10' },
      }),
    )
  })

  it('mostra erro quando geolocalização falha', async () => {
    getCurrentPosition.mockImplementation((_success: PositionCallback, error: PositionErrorCallback) => {
      error({ code: 1, message: 'denied' } as GeolocationPositionError)
    })

    const user = setupUser()
    renderHome()
    await screen.findByText('Show de Rock')

    await user.click(screen.getByLabelText('Raio de distância'))
    await user.click(await screen.findByRole('option', { name: 'Até 10 km' }))

    expect(await screen.findByText(/Não foi possível obter sua localização/)).toBeInTheDocument()
  })

  it('remover o filtro de raio limpa lat/lng/raio_km da busca', async () => {
    getCurrentPosition.mockImplementation((success: PositionCallback) => {
      success({ coords: { latitude: -8.05, longitude: -34.9 } } as GeolocationPosition)
    })

    const user = setupUser()
    renderHome()
    await screen.findByText('Show de Rock')

    await user.click(screen.getByLabelText('Raio de distância'))
    await user.click(await screen.findByRole('option', { name: 'Até 10 km' }))
    await waitFor(() =>
      expect(mockedApi.get).toHaveBeenCalledWith('/events', {
        params: { ...paramsBase, lat: '-8.05', lng: '-34.9', raio_km: '10' },
      }),
    )

    await user.click(screen.getByLabelText('Raio de distância'))
    await user.click(await screen.findByRole('option', { name: 'Sem filtro' }))

    await waitFor(() => expect(mockedApi.get).toHaveBeenLastCalledWith('/events', { params: paramsBase }))
  })
})
