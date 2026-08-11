import { render, screen } from '@testing-library/react'
import { createMemoryRouter } from 'react-router'
import { RouterProvider } from 'react-router/dom'
import { AuthProvider } from '@/context/AuthContext'
import { api } from '@/services/api'
import { routes } from './router'

jest.mock('@/services/api', () => ({
  api: { get: jest.fn(), post: jest.fn(), patch: jest.fn() },
  setUnauthorizedHandler: jest.fn(),
}))

const mockedApi = api as unknown as { get: jest.Mock }

beforeEach(() => {
  mockedApi.get.mockImplementation((url: string) => {
    if (url === '/categories') return Promise.resolve({ data: { categories: [] } })
    if (url === '/events') return Promise.resolve({ data: { events: { data: [] } } })
    return Promise.reject(new Error(`unmocked GET ${url}`))
  })
})

function renderAt(path: string) {
  const router = createMemoryRouter(routes, { initialEntries: [path] })
  return render(
    <AuthProvider>
      <RouterProvider router={router} />
    </AuthProvider>,
  )
}

describe('router', () => {
  it('renderiza a Home na rota /', () => {
    renderAt('/')

    expect(screen.getByRole('heading', { name: 'Eventos' })).toBeInTheDocument()
  })

  it('renderiza NotFound em rota desconhecida', () => {
    renderAt('/rota-que-nao-existe')

    expect(screen.getByText('404')).toBeInTheDocument()
  })

  it('renderiza a página de login em /login', () => {
    renderAt('/login')

    expect(screen.getByText(/Não tem conta\?/)).toBeInTheDocument()
  })

  it('renderiza a página de registro em /register', () => {
    renderAt('/register')

    expect(screen.getByText(/Já tem conta\?/)).toBeInTheDocument()
  })
})
