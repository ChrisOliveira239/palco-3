import { render, screen } from '@testing-library/react'
import { createMemoryRouter } from 'react-router'
import { RouterProvider } from 'react-router/dom'
import { routes } from './router'

describe('router', () => {
  it('renderiza a Home na rota /', () => {
    const router = createMemoryRouter(routes, { initialEntries: ['/'] })
    render(<RouterProvider router={router} />)

    expect(screen.getByText('Palco')).toBeInTheDocument()
  })

  it('renderiza NotFound em rota desconhecida', () => {
    const router = createMemoryRouter(routes, { initialEntries: ['/rota-que-nao-existe'] })
    render(<RouterProvider router={router} />)

    expect(screen.getByText('404')).toBeInTheDocument()
  })
})
