import { Navigate, Outlet } from 'react-router'
import { useAuth } from '@/context/AuthContext'

export function GuestOnly() {
  const { status } = useAuth()

  if (status === 'idle' || status === 'loading') return null
  if (status === 'authenticated') return <Navigate to="/" replace />

  return <Outlet />
}
