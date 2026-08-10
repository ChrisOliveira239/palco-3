import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter, Route, Routes } from 'react-router'
import { AuthProvider } from '@/context/AuthContext'
import { api } from '@/services/api'
import { Login } from './Login'

jest.mock('@/services/api', () => ({
  api: { get: jest.fn(), post: jest.fn() },
  setUnauthorizedHandler: jest.fn(),
}))

const mockedApi = api as unknown as { get: jest.Mock; post: jest.Mock }

function renderLogin() {
  return render(
    <AuthProvider>
      <MemoryRouter initialEntries={['/login']}>
        <Routes>
          <Route path="/" element={<div>pagina-inicial</div>} />
          <Route path="/login" element={<Login />} />
        </Routes>
      </MemoryRouter>
    </AuthProvider>,
  )
}

describe('Login', () => {
  beforeEach(() => {
    localStorage.clear()
    jest.clearAllMocks()
  })

  it('faz login e navega pra home quando as credenciais são válidas', async () => {
    const user = userEvent.setup()
    mockedApi.post.mockResolvedValueOnce({
      data: { user: { use_name: 'Ana' }, token: 'token-123' },
    })

    renderLogin()

    await user.type(screen.getByLabelText('E-mail'), 'ana@palco.com')
    await user.type(screen.getByLabelText('Senha'), 'senha123')
    await user.click(screen.getByRole('button', { name: 'Entrar' }))

    await waitFor(() => expect(screen.getByText('pagina-inicial')).toBeInTheDocument())
    expect(mockedApi.post).toHaveBeenCalledWith('/login', {
      email: 'ana@palco.com',
      password: 'senha123',
    })
  })

  it('mostra erro de validação quando o servidor recusa as credenciais', async () => {
    const user = userEvent.setup()
    mockedApi.post.mockRejectedValueOnce({
      isAxiosError: true,
      response: {
        data: {
          message: 'As credenciais informadas não conferem.',
          errors: { email: ['As credenciais informadas não conferem.'] },
        },
      },
    })

    renderLogin()

    await user.type(screen.getByLabelText('E-mail'), 'ana@palco.com')
    await user.type(screen.getByLabelText('Senha'), 'senha-errada')
    await user.click(screen.getByRole('button', { name: 'Entrar' }))

    expect(await screen.findByRole('alert')).toHaveTextContent(
      'As credenciais informadas não conferem.',
    )
  })
})
