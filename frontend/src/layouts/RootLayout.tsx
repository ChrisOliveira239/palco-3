import { Link, Outlet, useNavigate } from 'react-router'
import { useAuth } from '@/context/AuthContext'
import { Button } from '@/components/ui/button'

export function RootLayout() {
  const { status, logout } = useAuth()
  const navigate = useNavigate()

  async function handleLogout() {
    await logout()
    navigate('/')
  }

  return (
    <div className="min-h-svh">
      <header className="flex items-center justify-between border-b p-4">
        <Link to="/" className="text-lg font-semibold">
          Palco
        </Link>
        <nav className="flex items-center gap-4">
          {status === 'authenticated' ? (
            <>
              <Link to="/feed" className="text-sm underline">
                Feed
              </Link>
              <Link to="/perfil" className="text-sm underline">
                Perfil
              </Link>
              <Button variant="outline" onClick={handleLogout}>
                Sair
              </Button>
            </>
          ) : (
            <>
              <Link to="/login" className="text-sm underline">
                Entrar
              </Link>
              <Link to="/register" className="text-sm underline">
                Registrar
              </Link>
            </>
          )}
        </nav>
      </header>
      <Outlet />
    </div>
  )
}
