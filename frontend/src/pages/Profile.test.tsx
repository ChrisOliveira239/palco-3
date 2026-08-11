import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { AuthProvider } from '@/context/AuthContext'
import { api } from '@/services/api'
import type { User } from '@/types/user'
import { Profile } from './Profile'

jest.mock('@/services/api', () => ({
  api: { get: jest.fn(), post: jest.fn(), patch: jest.fn() },
  setUnauthorizedHandler: jest.fn(),
}))

const mockedApi = api as unknown as { get: jest.Mock; post: jest.Mock; patch: jest.Mock }

const baseUser: User = {
  id: 1,
  use_name: 'Ana',
  email: 'ana@palco.com',
  use_avatar_url: null,
  use_bio: null,
  use_city: null,
  use_state: null,
  use_latitude: null,
  use_longitude: null,
  use_is_admin: false,
  use_tipo_conta: 'PESSOA',
  use_active: true,
  artist_profile: null,
}

async function renderProfile(user: User) {
  localStorage.setItem('token', 'token-existente')
  mockedApi.get.mockResolvedValueOnce({ data: { user } })

  const result = render(
    <AuthProvider>
      <Profile />
    </AuthProvider>,
  )

  await waitFor(() => expect(screen.getByDisplayValue(user.use_name)).toBeInTheDocument())
  return result
}

describe('Profile', () => {
  beforeEach(() => {
    localStorage.clear()
    jest.clearAllMocks()
  })

  it('salva os dados do usuário', async () => {
    const user = userEvent.setup()
    await renderProfile(baseUser)
    mockedApi.patch.mockResolvedValueOnce({ data: { user: { ...baseUser, use_name: 'Ana Silva' } } })

    await user.clear(screen.getByLabelText('Nome'))
    await user.type(screen.getByLabelText('Nome'), 'Ana Silva')
    await user.click(screen.getByRole('button', { name: 'Salvar dados' }))

    await waitFor(() =>
      expect(mockedApi.patch).toHaveBeenCalledWith('/profile', {
        use_name: 'Ana Silva',
        use_bio: null,
        use_city: null,
        use_state: null,
      }),
    )
    expect(await screen.findByText('Dados atualizados.')).toBeInTheDocument()
  })

  it('mostra formulário de criação quando usuário ainda não tem perfil de artista', async () => {
    await renderProfile(baseUser)

    expect(screen.getByRole('button', { name: 'Criar perfil de artista' })).toBeInTheDocument()
  })

  it('cria perfil de artista', async () => {
    const user = userEvent.setup()
    await renderProfile(baseUser)
    mockedApi.post.mockResolvedValueOnce({
      data: { artist_profile: { id: 10, user_id: 1, art_nome_artistico: 'Ana Palco', art_active: true } },
    })

    await user.type(screen.getByLabelText('Nome artístico'), 'Ana Palco')
    await user.click(screen.getByRole('button', { name: 'Criar perfil de artista' }))

    await waitFor(() =>
      expect(mockedApi.post).toHaveBeenCalledWith('/artist-profiles', {
        art_nome_artistico: 'Ana Palco',
        art_bio: null,
        art_drt: null,
        art_telefone: null,
        art_email: null,
        art_site: null,
      }),
    )
    expect(await screen.findByText('Perfil de artista salvo.')).toBeInTheDocument()
  })

  it('edita perfil de artista existente', async () => {
    const user = userEvent.setup()
    const userComPerfil = {
      ...baseUser,
      artist_profile: {
        id: 10,
        user_id: 1,
        art_nome_artistico: 'Ana Palco',
        art_bio: null,
        art_capa_url: null,
        art_verificado: false,
        art_drt: null,
        art_telefone: null,
        art_email: null,
        art_site: null,
        art_active: true,
      },
    }
    await renderProfile(userComPerfil)
    mockedApi.patch.mockResolvedValueOnce({
      data: { artist_profile: { ...userComPerfil.artist_profile, art_nome_artistico: 'Ana Palco Silva' } },
    })

    expect(screen.getByRole('button', { name: 'Salvar perfil de artista' })).toBeInTheDocument()

    await user.clear(screen.getByLabelText('Nome artístico'))
    await user.type(screen.getByLabelText('Nome artístico'), 'Ana Palco Silva')
    await user.click(screen.getByRole('button', { name: 'Salvar perfil de artista' }))

    await waitFor(() =>
      expect(mockedApi.patch).toHaveBeenCalledWith(
        '/artist-profiles/10',
        expect.objectContaining({ art_nome_artistico: 'Ana Palco Silva' }),
      ),
    )
  })

  it('mostra erro de validação por campo', async () => {
    const user = userEvent.setup()
    await renderProfile(baseUser)
    mockedApi.patch.mockRejectedValueOnce({
      isAxiosError: true,
      response: {
        data: {
          message: 'Dados inválidos.',
          errors: { use_name: ['O campo nome é obrigatório.'] },
        },
      },
    })

    await user.click(screen.getByRole('button', { name: 'Salvar dados' }))

    expect(await screen.findByText('O campo nome é obrigatório.')).toBeInTheDocument()
  })
})
