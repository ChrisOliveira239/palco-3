import { render, screen } from '@testing-library/react'
import { createMemoryRouter } from 'react-router'
import { RouterProvider } from 'react-router/dom'
import { AuthProvider } from '@/context/AuthContext'
import { routes } from './router'

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

    expect(screen.getByText(/Setup do frontend/)).toBeInTheDocument()
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
