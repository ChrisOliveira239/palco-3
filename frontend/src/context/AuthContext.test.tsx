import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { AuthProvider, useAuth } from './AuthContext'
import { api } from '@/services/api'

jest.mock('@/services/api', () => ({
  api: { get: jest.fn(), post: jest.fn() },
  setUnauthorizedHandler: jest.fn(),
}))

const mockedApi = api as unknown as { get: jest.Mock; post: jest.Mock }

function Probe() {
  const { user, status, login, logout } = useAuth()
  return (
    <div>
      <span data-testid="status">{status}</span>
      <span data-testid="user">{user?.use_name ?? 'sem usuário'}</span>
      <button onClick={() => login('teste@palco.com', 'senha123')}>login</button>
      <button onClick={() => logout()}>logout</button>
    </div>
  )
}

describe('AuthProvider', () => {
  beforeEach(() => {
    localStorage.clear()
    jest.clearAllMocks()
  })

  it('inicia unauthenticated quando não há token salvo', async () => {
    render(
      <AuthProvider>
        <Probe />
      </AuthProvider>,
    )

    await waitFor(() => expect(screen.getByTestId('status')).toHaveTextContent('unauthenticated'))
    expect(mockedApi.get).not.toHaveBeenCalled()
  })

  it('hidrata o usuário via /me quando já existe token salvo', async () => {
    localStorage.setItem('token', 'token-existente')
    mockedApi.get.mockResolvedValueOnce({ data: { user: { use_name: 'Ana' } } })

    render(
      <AuthProvider>
        <Probe />
      </AuthProvider>,
    )

    await waitFor(() => expect(screen.getByTestId('status')).toHaveTextContent('authenticated'))
    expect(screen.getByTestId('user')).toHaveTextContent('Ana')
    expect(mockedApi.get).toHaveBeenCalledWith('/me')
  })

  it('login bem-sucedido guarda token e autentica', async () => {
    const user = userEvent.setup()
    mockedApi.post.mockResolvedValueOnce({
      data: { user: { use_name: 'Bruno' }, token: 'novo-token' },
    })

    render(
      <AuthProvider>
        <Probe />
      </AuthProvider>,
    )
    await waitFor(() => expect(screen.getByTestId('status')).toHaveTextContent('unauthenticated'))

    await user.click(screen.getByText('login'))

    await waitFor(() => expect(screen.getByTestId('status')).toHaveTextContent('authenticated'))
    expect(screen.getByTestId('user')).toHaveTextContent('Bruno')
    expect(localStorage.getItem('token')).toBe('novo-token')
  })

  it('logout limpa token e usuário mesmo se a chamada ao servidor falhar', async () => {
    const user = userEvent.setup()
    localStorage.setItem('token', 'token-existente')
    mockedApi.get.mockResolvedValueOnce({ data: { user: { use_name: 'Ana' } } })
    mockedApi.post.mockRejectedValueOnce(new Error('rede fora'))

    render(
      <AuthProvider>
        <Probe />
      </AuthProvider>,
    )
    await waitFor(() => expect(screen.getByTestId('status')).toHaveTextContent('authenticated'))

    await user.click(screen.getByText('logout'))

    await waitFor(() => expect(screen.getByTestId('status')).toHaveTextContent('unauthenticated'))
    expect(localStorage.getItem('token')).toBeNull()
  })
})
